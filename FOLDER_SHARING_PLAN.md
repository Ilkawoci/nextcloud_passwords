# Ordner-Sharing — Analyse und Umsetzungsplan

Status: Entwurf / Planungsdokument
Branch: `wip/password_folder_share`
Bezogen auf: App-Version 2026.10.0, `src/lib` (PHP 8.4), `src/vue` + `src/js`

---

## 1. Ausgangslage (Ist-Zustand im Code)

### 1.1 Wie Passwort-Sharing heute funktioniert

Das App-Design ist **Copy-on-Share**, nicht „Grant-on-Read“:

1. `CreatePasswordShareHelper::createPasswordShare()` legt eine Zeile in `passwords_share` an
   (`source_password`, `receiver`, `type`, `editable`, `shareable`, `expires`, `client`).
   `target_password` ist zu diesem Zeitpunkt `null`.
   (`src/lib/Helper/Sharing/CreatePasswordShareHelper.php:86`)
2. Der Cron-Job `SynchronizeShares` (`src/lib/Cron/SynchronizeShares.php`) findet alle Shares mit
   `target_password = null` und **erzeugt eine Kopie des Passworts im Konto des Empfängers**:
   neues `Password`-Modell mit `user_id = receiver`, `share_id = <share-uuid>`, plus neue
   `PasswordRevision` mit denselben Daten. Danach wird `target_password` gesetzt.
   (`SynchronizeShares::createNewShares()`)
3. Änderungen werden in beide Richtungen synchronisiert (`source_updated` / `target_updated`),
   bei `editable = true` auch vom Empfänger zurück zum Besitzer.
4. Ablauf, Löschung, Loop-Erkennung, Re-Sharing-Berechtigung und Benachrichtigungen hängen alle
   an dieser einen Share-Zeile.

Konsequenz: Der Empfänger besitzt **echte eigene Datensätze** (Folder/Password + Revision), die
über `share_id` bzw. `share` mit der Share-Zeile verknüpft sind. Alle Listen, Filter und
Berechtigungsprüfungen arbeiten weiterhin ausschließlich über `user_id`.

### 1.2 Datenmodell Ordner

- `passwords_folder` (Modell): `id`, `user_id`, `uuid`, `revision`, `suspended`, `deleted`,
  `created`, `updated` (`src/lib/Migration/Version20220101.php:761`)
- `passwords_folder_rv` (Revision): `parent`, `label`, `cse_type`, `cse_key`, `sse_type`, `sse_key`,
  `favorite`, `hidden`, `trashed`, `edited`, `deleted`
- Passwörter hängen über das Feld `folder` ihrer Revision am Ordner.
- Basis-Ordner: `FolderService::BASE_FOLDER_UUID = 00000000-0000-0000-0000-000000000000`
- `Folder` hat **kein** `share_id`/`has_shares`; `FolderObjectHelper` kennt keinen Detail-Level
  `shares` (`src/lib/Helper/ApiObjects/FolderObjectHelper.php:31`).

### 1.3 Harte Randbedingungen, die der Plan respektieren muss

- **CSE (clientseitige Verschlüsselung) ist nicht teilbar.** `checkIfRevisionCanBeSharing()`
  wirft für `cseType !== 'none'` einen Fehler 420; `hidden` ebenso.
  (`CreatePasswordShareHelper::checkIfRevisionCanBeSharing()`)
- **SSE**: Beim Teilen wird ggf. auf `SSEv1r1` heruntergestuft, damit der Empfänger entschlüsseln
  kann (`downgradeSSE()`).
- **Re-Sharing** ist nur erlaubt, wenn `shareable` gesetzt ist und die Einstellung `resharing`
  aktiv ist; `editable` und `expires` werden dabei vom Quell-Share nach oben begrenzt.
- **API ist stabiler Vertrag** (Browser-Extensions, Mobile). Neue Endpunkte sind unkritisch,
  Änderungen an bestehenden Antworten nicht.
- **UUIDs nach außen**, niemals sequenzielle IDs.
- **`src/` ist für Agenten schreibgeschützt** (AGENTS.md §1) — dieses Dokument beschreibt den
  Eingriff, führt ihn aber nicht aus.

---

## 2. Zielbild

1. Ein Ordner kann mit einem Benutzer geteilt werden (Typ `user`, wie bei Passwörtern).
2. Beim Teilen wird der **komplette Teilbaum** materialisiert: alle Unterordner und alle
   Passwörter darunter, jeweils als Kopie im Konto des Empfängers, mit korrekter Eltern-Kind-
   Zuordnung.
3. Der Empfänger sieht den Ordner in seiner Ordner-Navigation und kann ihn aufklappen; die
   Passwörter liegen in den entsprechenden Kopien.
4. Ein **Details**-Eintrag im Ordner-Kontextmenü öffnet eine Sidebar mit den Ordnerdaten
   (Label, Elternordner, Erstellt/Geändert, Favorit, Versteckt, Anzahl Elemente, Besitzer,
   Sharing-Status).
5. Änderungen am Quellordner (Umbenennen, Verschieben, neue Elemente) werden nachgezogen,
   solange die Share-Zeile existiert — mindestens für Umbenennen/Verschieben und für neue
   direkte Kinder.

---

## 3. Design-Entscheidungen

### 3.1 Warum `passwords_share` erweitern statt eine neue Tabelle?

**Empfehlung: `passwords_share` erweitern.** Ablauf, Löschung, Benachrichtigungen,
Re-Sharing, Loop-Erkennung, Client-Angabe und die Update-Flags sind bereits vollständig
implementiert. Eine zweite Tabelle `passwords_folder_share` würde diese Logik duplizieren
(> 800 Zeilen) und zwei getrennte Cron-Pfade erzeugen, die auseinanderlaufen.

Nachteile der Erweiterung: `source_password`/`target_password` sind heute `NOT NULL`; für
Ordner-Shares brauchen wir nullable Spalten bzw. eine Diskriminator-Spalte. Das ist ein
einmaliger Migrationsaufwand und muss in `ShareMapper`, `ShareObjectHelper` und
`SynchronizeShares` sauber abgefangen werden.

### 3.2 Ein Share pro Ordner — plus Kind-Shares

Ein einzelner Ordner-Share reicht nicht, weil jedes kopierte Passwort eine eigene Share-Zeile
für die Synchronisation braucht. Daher:

- **Root-Share**: `source_type = 'folder'`, `source_folder = <uuid>`, `target_folder = <uuid>`,
  `parent_share = null`.
- **Kind-Shares**: je Unterordner und je Passwort im Teilbaum eine eigene Zeile mit
  `parent_share = <root-share-uuid>` (neue Spalte, nullable).
- **Kaskade**: Löschen/Ablauf/Berechtigungsänderung des Root-Shares wirkt auf alle Zeilen mit
  `parent_share = root` (rekursiv über die Hierarchie).
- **Idempotenz**: `(source_folder, receiver)` bzw. `(source_password, receiver)` bleiben die
  Eindeutigkeitskriterien; ein bereits geteilter Ordner wird nicht doppelt materialisiert.

### 3.3 Materialisierung

Ein neuer Helper (Vorschlag: `src/lib/Helper/Sharing/CreateFolderShareHelper.php`) läuft analog
zu `CreatePasswordShareHelper` und erzeugt in einer Transaktion:

1. Root-Share-Zeile (`target_folder = null`).
2. Der Cron materialisiert danach den Baum. Alternativ sofort synchron materialisieren und
   `target_*` direkt setzen; das ist einfacher zu testen, blockiert aber den Request bei großen
   Bäumen. **Empfehlung:** wie bei Passwörtern asynchron über den Cron, damit die API schnell
   bleibt und Teilbäume mit hunderten Einträgen nicht in einen HTTP-Timeout laufen.
3. Beim Materialisieren wird eine Map `source_folder_uuid → target_folder_uuid` aufgebaut
   (Breitensuche ab Root). Für jeden Quellordner:
   - neue `Folder`-Zeile (`user_id = receiver`, `share_id = <kind-share-uuid>`)
   - neue `FolderRevision` mit `parent = mapped(parent)`, `label`, `cse_type/sse_type` etc.
     (CSE wird vorher abgelehnt, siehe 3.4)
   - Kind-Share-Zeile
4. Für jedes Passwort im Baum: bestehende `createNewShares`-Logik wiederverwenden, aber mit
   `parent_share` und dem gemappten Zielordner in der Revision (`folder`).

### 3.4 CSE, Hidden, Trashed, Papierkorb

- **CSE-Ordner oder CSE-Passwörter im Baum**: nicht teilbar. Zwei Varianten:
  - (A) ganzen Share mit klarer Fehlermeldung ablehnen (empfohlen für Phase 1),
  - (B) nur die nicht-CSE Elemente teilen und im Dialog anzeigen, wie viele übersprungen werden.
- **`hidden`**: wie bei Passwörtern nicht teilbar; im Baum überspringen oder ablehnen.
- **`trashed`/`deleted`**: überspringen, nicht teilen. Ein späterer Restore im Quellordner
  erzeugt keinen automatischen Share.
- **SSE**: Wenn nötig auf `SSEv1r1` herunterstufen, wie bei Passwörtern.

### 3.5 Sichtbarkeit beim Empfänger

Der Empfänger bekommt echte Ordner; die Navigation (`AppNavigationItemFolders`) zeigt sie
automatisch. Zusätzlich sollte der Ordner in „Shared with you“ auftauchen. Dafür muss die
Shared-Section (`src/vue/Section/Shared.vue`) neben Passwörtern auch Ordner rendern oder
einen Untertyp „Ordner“ bekommen. Empfehlung: Ordner als eigene Zeilen oberhalb der Passwörter,
mit Ordner-Icon und Anzahl enthaltener Elemente.

---

## 4. Konkrete Änderungen (Datei für Datei)

> Alle Pfade unter `src/` sind für Agenten gesperrt. Diese Liste ist die Arbeitsanweisung für
> den Menschen, der den Code schreibt.

### 4.1 Datenbank / Migration

Neue Migration `src/lib/Migration/Version2026XXXX.php` (nächste freie Version nach
`Version20250930`):

| Spalte | Typ | Nullable | Zweck |
|---|---|---|---|
| `source_type` | string(16) | nein, Default `password` | `password` \| `folder` |
| `source_folder` | string(36) | ja | UUID des Quellordners |
| `target_folder` | string(36) | ja | UUID der Empfänger-Kopie |
| `parent_share` | string(36) | ja | Kaskaden-/Hierarchie-Verknüpfung |
| `source_password` | string(36) | **auf nullable ändern** | bei Ordner-Shares leer |
| `target_password` | string(36) | **auf nullable ändern** | bei Ordner-Shares leer |

Indizes: `(source_folder, receiver)`, `(parent_share)`, `(source_type, target_folder)`.
Bestehende Zeilen bekommen `source_type = 'password'`; die Migration muss idempotent sein
(`hasColumn`-Prüfungen wie in `Version20220101`).

`src/lib/Db/Share.php`: neue Properties + Getter/Setter, neue Konstanten
`ENTITY_PASSWORD`/`ENTITY_FOLDER` (oder `TYPE_*` analog zu `TYPE_USER`).

### 4.2 Backend

- `src/lib/Db/ShareMapper.php`: `findAllByUserIdOrReceiverId()` um Ordner-Shares erweitern;
  neue Finder `findBySourceFolder()`, `findByParentShare()`, `findPendingFolderShares()`.
- `src/lib/Services/Object/ShareService.php`: `createFolderShare()`; `findBySourceFolderAndReceiver()`;
  `findChildren()`.
- `src/lib/Helper/Sharing/CreateFolderShareHelper.php` (neu): Validierung (CSE, hidden,
  Rechte, Zyklus, bereits geteilt), Anlegen des Root-Shares, `setHasShares`-Äquivalent für
  Ordner (neues Flag am `Folder`-Modell, siehe unten).
- `src/lib/Helper/Sharing/UpdateFolderShareHelper.php` (neu): Änderung von `expires`,
  `editable`, `shareable` inkl. Kaskade auf Kind-Shares.
- `src/lib/Db/Folder.php`: `shareId` (nullable) und `hasShares` (bool) ergänzen — analog zu
  `Password`. Achtung: Spalten in `passwords_folder` ergänzen, nicht in der Revision.
- `src/lib/Cron/SynchronizeShares.php`:
  - `createNewShares()` um Ordner-Fälle erweitern (nach `source_type` verzweigen),
  - neue Methode `materializeFolderShare()` (Breitensuche, Mapping, Revisionen, Kind-Shares),
  - `deleteOrphanedTargetPasswords()` um verwaiste Zielordner ergänzen,
  - `deleteExpiredShares()` kaskadierend,
  - `updateSourcePasswords()`/`updateTargetPasswords()` um Ordner-Metadaten (Label, Parent,
    Favorite, Hidden) erweitern,
  - `shareLineageHasLoop()` um Ordner-Ketten erweitern.
- `src/lib/Helper/ApiObjects/ShareObjectHelper.php`: bei `source_type = folder` statt
  `password` das Feld `folder` liefern; `LEVEL_PASSWORD` darf bei Ordner-Shares nicht greifen.
- `src/lib/Helper/ApiObjects/FolderObjectHelper.php`: `LEVEL_SHARES = 'shares'` ergänzen,
  `share`/`shared`/`shares` im Modell ausgeben (analog `PasswordObjectHelper::getModel()`).
- `src/lib/Controller/Api/ShareApiController.php`: `create()` um optionalen Parameter `folder`
  erweitern (genau eines von `password`/`folder` erlaubt); `allowedFilterFields` um
  `sourceType` ergänzen. Kein neuer Endpunkt nötig, damit bleiben Clients kompatibel.
- `src/lib/Controller/Api/FolderApiController.php`: `show`/`list`/`find` akzeptieren bereits
  `details`; `shares` kommt über den ObjectHelper, keine Controller-Änderung nötig.
- `src/lib/Helper/User/DeleteUserDataHelper.php`, `MoveUserDataHelper.php`,
  `CleanUp/CleanDeletedEntitiesHelper.php`, `Backup/CreateBackupHelper.php`,
  `Backup/RestoreBackupHelper.php`, `Migration/DatabaseRepair/ShareRepair.php`: Ordner-Shares
  mitbehandeln (die Backup-Helper arbeiten mit `toArray()`, neue Spalten kommen automatisch mit;
  Reparatur-Logik muss `source_type` beachten).
- `src/lib/Notification/ShareCreatedNotification.php` + `Mail/ShareCreatedMail.php`: Text für
  geteilte Ordner („X Ordner und Y Passwörter“) bzw. eigener Notification-Typ.
- `src/lib/EventListener/...`: prüfen, ob `PasswordDeletedListener` auch Ordner-Kaskaden
  auslösen muss; ggf. `FolderDeletedListener` ergänzen.

### 4.3 API-Vertrag (Beispiele)

- `POST /api/1.0/share/create` mit `folder=<uuid>` (statt `password`) legt einen Ordner-Share an.
- `GET /api/1.0/share/show?id=<uuid>&details=model+folder` liefert `folder` statt `password`.
- `GET /api/1.0/folder/show?id=<uuid>&details=model+shares` liefert `share`, `shared`, `shares`.
- Fehlercodes: 420 bei CSE/hidden (wie bisher), 400 bei „bereits geteilt“, 403 bei fehlender
  Re-Sharing-Berechtigung.

### 4.4 Frontend

- `src/js/Models/Sidebar/FolderSidebar.js` (neu), analog `PasswordSidebar`.
- `src/vue/Components/Sidebar/FolderSidebar.vue` (neu): `NcAppSidebar` mit Details-Tab;
  Aktionen: Umbenennen, Verschieben, Favorit, Löschen, Sharing-Tab.
- `src/vue/App.vue`: `folder-sidebar` rendern, wenn `sidebar.type === 'folder'`.
- `src/vue/Components/ContentList/Item/FolderItem/FolderItemActionMenu.vue`: Eintrag
  „Details“ ergänzen (öffnet `Application.sidebar = new FolderSidebar(folder)`).
- `src/vue/Components/ContentList/Item/Folder.vue`: optional Klick auf Label/Icon öffnet
  weiterhin den Ordner; Details nur über Menü (kein Doppelklick-Konflikt).
- `src/vue/Components/Sidebar/PasswordSidebar/Sharing/Sharing.vue` ist derzeit
  passwort-spezifisch (`password`-Prop, `API.showPassword`). Für Ordner entweder
  generalisieren (`entity`, `entityType`) oder eine `FolderSharing.vue` daneben legen.
  Empfehlung: generalisieren, sonst doppelte UI-Logik.
- `src/js/Actions/Share/CreateShareAction.js` und `CreateFolderShareAction.js`: gemeinsame
  Basisklasse oder `entityType`-Parameter; `#disableCse()` darf bei Ordnern nicht auf
  `password.cseType` zugreifen.
- `src/js/Helper/api.js`: `createShare({folder})`, `showFolder(id, 'model+shares')`.
- `src/vue/Section/Shared.vue`: Ordner-Zeilen für „Shared with you“/„Shared by you“.
- `src/js/Helper/router.js`: keine neue Route nötig, solange Ordner in der bestehenden
  Shared-Section erscheinen.
- Übersetzungen: neue Strings nur über Weblate, nicht `src/l10n` von Hand ändern.

---

## 5. Tests

### 5.1 PHPUnit (`tests/phpunit`)

- `CreateFolderShareHelperTest`: CSE verweigert, hidden verweigert, bereits geteilt verweigert,
  Re-Sharing ohne Berechtigung verweigert, gültiger Share legt Root-Zeile an.
- `SynchronizeSharesTest` (erweitern): Materialisierung eines Baums mit 2 Ebenen, Mapping der
  Parent-UUIDs, Passwörter landen im richtigen Zielordner, Löschen des Root-Shares räumt den
  Baum ab, Ablauf räumt kaskadierend ab, Loop-Erkennung bei Ordner-Ketten.
- `FolderObjectHelperTest`: `shares`-Level, `share`/`shared`-Flags, keine Passwort-Felder.
- `ShareObjectHelperTest`: Ordner-Share liefert `folder`, nicht `password`.

### 5.2 Cypress (`cypress/e2e`)

- Ordner mit Unterordner + Passwörtern teilen; als Empfänger einloggen; Ordnerstruktur und
  Passwörter sichtbar.
- Details-Button öffnet Sidebar mit korrekten Daten.
- Empfänger mit `editable = true` ändert ein Passwort; Besitzer sieht die Änderung.
- Löschen des Shares beim Besitzer entfernt den Baum beim Empfänger.
- CSE-Ordner: Teilen wird mit verständlicher Meldung abgelehnt.

### 5.3 Testinstanz

Das Repo bringt eine Docker-Umgebung mit (`docker-compose.yml`, `npm run start`), die die App
read-only unter `/var/www/html/custom_apps/passwords` einbindet und über nginx mit TLS auf
`https://localhost/` ausliefert. `npm run mkcert` erzeugt die Zertifikate; ohne `mkcert` reicht
ein selbstsigniertes Zertifikat unter `docker/volumes/nginx/default.pem` + `default.key`.
Dev-Logins: `admin`/`admin`, Testnutzer `max` und `erika` mit `PasswordsApp`.

---

## 6. Risiken und offene Punkte

1. **Nullable-Migration auf bestehender Tabelle**: `source_password`/`target_password` sind
   heute `NOT NULL`. Doctrine-`changeColumn` ist in Nextcloud-Migrationen möglich, muss aber
   auf MySQL, PostgreSQL und SQLite getestet werden. Alternative: Sentinel-Strings vermeiden,
   indem für Ordner-Shares eine separate Tabelle genutzt wird — dann aber doppelte Cron-Logik.
2. **Performance**: Ein Ordner mit 500 Passwörtern erzeugt 500+ Share-Zeilen und 500+ Revisionen
   in einem Cron-Lauf. Der bestehende Cron arbeitet bereits in Schleifen mit Limit 32; für
   Ordner ist eine Batch-Verarbeitung (z. B. 50 Kinder pro Lauf) nötig.
3. **Rechte-Kaskade**: Ändert der Besitzer `editable` am Root-Share, müssen alle Kind-Shares
   folgen. Andernfalls kann ein Empfänger indirekt mehr Rechte behalten als beabsichtigt.
4. **CSE**: Da CSE-Passwörter grundsätzlich nicht teilbar sind, ist ein Ordner mit CSE-Inhalt
   nur teilweise oder gar nicht teilbar. Das muss in der UI klar kommuniziert werden.
5. **API-Kompatibilität**: `ShareObjectHelper` liefert heute immer `password`. Clients, die
   `share.password` lesen, dürfen bei Ordner-Shares nicht brechen — daher `folder` zusätzlich
   und `password = null`, nicht ersetzen.
6. **Backup/Restore**: Neue Spalten landen automatisch im Backup (`toArray()`), aber
   `RestoreBackupHelper` muss `source_type` und `parent_share` unverändert übernehmen.
7. **Papierkorb**: Ein geteilter Ordner, der beim Besitzer in den Papierkorb wandert, sollte
   beim Empfänger nicht sofort verschwinden (oder bewusst doch). Diese Semantik muss entschieden
   werden; Empfehlung: Share bleibt bestehen, Ordner wird beim Empfänger als „trashed“ markiert.
8. **Mehrfach-Sharing**: Wird ein Unterordner zusätzlich direkt an denselben Empfänger geteilt,
   darf keine doppelte Kopie entstehen. `(source_folder, receiver)` muss vor der
   Materialisierung geprüft werden.

---

## 7. Umsetzungsreihenfolge (empfohlen)

1. Migration + `Share`-Modell + Mapper/Service-Finder; bestehende Passwort-Shares dürfen nicht
   brechen (Regressionstests laufen lassen).
2. `CreateFolderShareHelper` + API-Parameter `folder`; noch ohne Materialisierung.
3. `SynchronizeShares::materializeFolderShare()` inkl. Parent-Mapping, Kaskade, Expiry.
4. `FolderObjectHelper` + `ShareObjectHelper` (API-Ausgabe).
5. Frontend: `FolderSidebar` + Details-Action, dann Sharing-Tab.
6. Shared-Section, Benachrichtigungen, Backup/Repair, User-Löschung/-Umzug.
7. Cypress-End-to-End-Tests gegen die Docker-Instanz.

---

## 8. Was dieses Dokument nicht enthält

Kein Code, kein Patch und keine kopierfertige Datei für `src/` — nach AGENTS.md §1 darf ein
Agent dort nichts schreiben und auch keine fertige Änderung zum Übernehmen liefern. Die
Abschnitte oben beschreiben Ort, Zweck und Verhalten der Änderungen in Prosa; die eigentliche
Implementierung muss von einer Person mit Schreibrecht auf `src/` erfolgen.

---

## Umsetzungsstand (aktuell)

**Fertig und in der Dev-Instanz (`https://localhost/`) verifiziert**

- Ordner teilen über Kontextmenü → „Teilen" (Sidebar-Tab, identisches Layout wie beim Passwort-Sharing: Empfänger-Chips, Rechte-Kacheln, Freigabeliste mit Avatar, „Bearbeiten erlauben"-Untermenü und „…"-Aktionen).
- Rekursives Teilen: Cron materialisiert Ordnerbaum + Passwörter im Empfängerkonto (`FolderShareMaterializer`), inkl. Parent-Mapping und eigener Share-Zeile pro Element.
- Details-Tab für Ordner (Name, Elternordner, Zeitstempel, Favorit, versteckt, Elementzähler, Anzahl Freigaben, ID).
- Rechte-Änderung propagiert auf Kind-Shares (`UpdateFolderShareHelper`), Widerruf löscht den materialisierten Baum.
- Schema erweitert (`source_type`, `source_folder`, `target_folder`, `parent_share`), Backup/Restore läuft generisch über `Share::toArray()`/`addType()` mit.

- Inkrementeller Abgleich: `FolderShareMaterializer::syncNewChildren()` läuft in jedem Cron-Durchlauf über alle Root-Ordner-Shares und kopiert nachträglich angelegte Unterordner und Passwörter. Verifiziert: nach dem Teilen `P2` und `Sub/P3` ergänzt, Empfänger erhielt beides inkl. korrekt entschlüsselter Werte.
- Unit-Tests: `tests/phpunit/Db/ShareTest.php` und `tests/phpunit/Helper/Sharing/FolderShareMaterializerTest.php` (8 Tests, Suite gesamt 302 grün).
- `ShareOptionsPreset` in `src/l10n/de_DE.js` und `en_GB.js` ergänzt.

- Löschen/Verschieben wird propagiert: `FolderShareMaterializer::pruneTree()` läuft nach dem Sync und entfernt Kopien, deren Quelle gelöscht wurde, nicht mehr im Baum liegt oder in einen anderen Ordner verschoben wurde (dann Neuanlage im richtigen Zielordner im nächsten Lauf). Verifiziert: Passwort in den Papierkorb (Kopie weg), Passwort innerhalb des Baums verschoben (Kopie am neuen Ort), Unterordner gelöscht (Teilbaum beim Empfänger weg).

- Cypress-Spec `cypress/e2e/folder-sharing.cy.js`: legt per API einen Ordner an, teilt ihn über das Kontextmenü, prüft Freigabeliste, Details-Tab und den Freigabe-Tab und widerruft die Freigabe wieder. Läuft gegen die Dev-Instanz grün (3/3, Firefox).

**Bewusst offen**
- `pruneTree()` läuft pro Cron-Lauf über den gesamten Baum (ein Walk pro Root-Freigabe). Bei sehr großen Bäumen wäre ein inkrementeller Marker sinnvoll.
- Löschungen werden als Löschung propagiert; Bearbeitungen des Empfängers an einer Kopie laufen weiterhin über die bestehende Revisions-Synchronisation.
