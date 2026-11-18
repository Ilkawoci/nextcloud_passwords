# Migration: Standard-Passwords-App → Fork mit Ordner-Sharing

Diese Anleitung beschreibt, wie eine bestehende Nextcloud-Instanz mit der
offiziellen **Passwords**-App (App Store) auf diesen Fork umgestellt wird, ohne
Passwörter, Ordner, Tags, Freigaben oder Verschlüsselung zu verlieren — inklusive
Backup, Rollback und laufender Pflege des Forks.

Platzhalter, die du anpassen musst:

| Platzhalter | Bedeutung | Beispiel |
|---|---|---|
| `$NC` | Nextcloud-Webroot | `/var/www/nextcloud` |
| `$PHP` | PHP-Binary des Webservers | `/usr/bin/php8.4` |
| `$FORK` | Pfad zum geklonten Fork | `~/git/nextcloud_passwords` |

Alle `occ`-Aufrufe laufen als Webserver-Benutzer (`www-data` bei Debian/Ubuntu,
`apache`/`nginx` bei RPM-Distributionen).

---

## 1. Was dieses Paket ist — und was es mit der Original-App teilt

Die App hat eine **eigene App-ID**: `passwordfolders`, Anzeigename „Passwords Folders".
Damit hat sie einen eigenen Ordner (`custom_apps/passwordfolders`), eigene Routen
(`/apps/passwordfolders`), eigene occ-Befehle (`occ passwordfolders:…`) und eine eigene
Einstellungsseite. Die Dateien der Original-App werden **nicht mehr überschrieben**, und
ein Update der Original-App kann dieses Paket nicht mehr ersetzen.

**Geteilt wird die Datenbank.** Beide Apps arbeiten auf denselben Tabellen
(`oc_passwords_*`). Das ist Absicht: Passwörter, Ordner, Tags, Revisions, Freigaben und die
Verschlüsselung bleiben unverändert, es muss nichts umgezogen werden.

Daraus folgen zwei harte Regeln:

1. **Die Original-App (`passwords`) muss deaktiviert bleiben**, solange dieses Paket läuft.
   Zwei Apps auf denselben Tabellen stören sich gegenseitig (doppelte Cron-Jobs,
   Freigabe-Zeilen, die die jeweils andere App nicht kennt).
2. **App-Konfiguration und Benutzereinstellungen müssen einmalig kopiert werden**
   (Abschnitt 6.2). Sonst fehlen der Serverschlüssel (`SSEv1ServerKey`) und die
   Benutzerschlüssel (`SSEv1UserKey`) — bestehende Passwörter wären nicht mehr lesbar.

Technisch ergänzt das Paket nur Spalten, die die Original-App nicht kennt:

| Tabelle | Änderung | Auswirkung auf Bestandsdaten |
|---|---|---|
| `passwords_share` | neue Spalten `source_type` (Default `password`), `source_folder`, `target_folder`, `parent_share` | bestehende Freigaben bleiben gültig |
| `passwords_share` | `source_password` darf jetzt `NULL` sein | keine |
| `passwords_share` | drei neue Indizes | keine |
| `passwords_folder` | neue Spalten `share_id`, `has_shares` | bestehende Ordner unverändert |

Die Migrationen sind idempotent (`hasTable`/`hasColumn`/`hasIndex`) und laufen auch dann
sauber durch, wenn die Tabellen schon von der Original-App angelegt wurden.

## 2. Voraussetzungen

- Nextcloud **32.0.1 – 35** (siehe `src/appinfo/info.xml`)
- PHP **8.4 oder 8.5**, 64-bit
- HTTPS (WebCrypto der App läuft nur in sicheren Kontexten)
- Für das Bauen: **Node.js ≥ 18** + `npm`, `git`, `tar`
- Schreibrechte auf `$NC/custom_apps` (oder `$NC/apps`)
- funktionierender Nextcloud-**System-Cron** (kein AJAX-Cron)

Prüfen:

```bash
sudo -u www-data $PHP $NC/occ status
sudo -u www-data $PHP $NC/occ app:list | grep -i passwordfolders
sudo -u www-data $PHP $NC/occ config:system:get cronMethod   # muss "cron" sein
```

---

## 3. Schritt 1 — Backup der bestehenden Instanz

### 3.1 Wartungsmodus einschalten

```bash
sudo -u www-data $PHP $NC/occ maintenance:mode --on
```

### 3.2 App-internes Backup (alle Benutzer)

Das ist das wichtigste Backup: es enthält die komplette Passwort-Datenbank in
einem app-eigenen Format und lässt sich mit einem Befehl zurückrollen.

```bash
sudo -u www-data $PHP $NC/occ passwordfolders:backup:create vor-fork-migration
sudo -u www-data $PHP $NC/occ passwordfolders:backup:list
```

Zusätzlich pro Benutzer exportierbar (JSON, unabhängig von der App-Version):

```bash
sudo -u www-data $PHP $NC/occ passwordfolders:backup:export --help   # Optionen anzeigen
```

> Das Backup wird im App-Datenverzeichnis abgelegt
> (`$NC/data/appdata_*/passwords/backups/`). Es wandert bei einem Serverumzug
> **nicht** automatisch mit — deshalb zusätzlich Punkt 3.4 sichern.

### 3.3 Datenbank-Dump

MySQL/MariaDB:

```bash
mysqldump --single-transaction --quick --routines \
  -u root -p nextcloud > ~/nextcloud-db-$(date +%F).sql
```

PostgreSQL:

```bash
pg_dump -U nextcloud nextcloud > ~/nextcloud-db-$(date +%F).sql
```

SQLite: einfach die Datei `$NC/data/owncloud.db` kopieren (Instanz vorher in den
Wartungsmodus setzen).

### 3.4 Datei-Backup

```bash
sudo tar -czf ~/nextcloud-files-$(date +%F).tar.gz \
  -C "$NC" config custom_apps apps data/appdata_* 2>/dev/null
```

Mindestens sichern: `$NC/config/config.php`, das App-Verzeichnis der alten
Passwords-App und `$NC/data/appdata_*/passwords`.

### 3.5 Alte App-Version notieren

```bash
sudo -u www-data $PHP $NC/occ app:list | grep -i passwordfolders
```

Notiere die Version (z. B. `2026.11.0`) — du brauchst sie für den Rollback.

---

## 4. Schritt 2 — Fork bauen und paketieren

Der Fork enthält im Git **kein** kompiliertes Frontend (`src/js/Static/` und
`src/css/` sind gitignored). Für den Betrieb muss einmal gebaut werden.

### 4.1 Repo holen

```bash
git clone -b wip/password_folder_share \
  https://github.com/Ilkawoci/nextcloud_passwords.git "$FORK"
cd "$FORK"
```

### 4.2 Frontend bauen

```bash
npm ci
```

Es gibt zwei Varianten, die **zusammenpassen müssen** mit der Einstellung
`dev/app/hash` in der Instanz:

| Build | Dateinamen | `dev/app/hash` | Cache |
|---|---|---|---|
| `npm run build` | `app.<hash>.js`, `admin.<hash>.js` … | **muss** auf den Hash gesetzt sein | automatisch (neue URL pro Build) |
| `npm run build:stable` | `app.js`, `admin.js` … | **muss leer** sein (`occ config:app:delete passwordfolders dev/app/hash`) | Browser-Cache, Hard-Reload nötig |

**Empfohlen für den Betrieb: `npm run build`** (gehashte Dateinamen). Damit
ändert sich die URL des Einstiegspunktes bei jedem Build und Nutzer bekommen
automatisch die neue Version — keine 404-Fehler auf alte Chunk-Dateien.

```bash
npm run build
```

`npm run build` versucht den Hash selbst zu setzen (`docker exec … occ
config:app:set passwords dev/app/hash`). Auf einem Server ohne laufenden
Dev-Container schlägt das fehl (harmlos) — dann manuell setzen:

```bash
HASH=$(ls src/js/Static/app.*.js | sed 's/.*app\.\(.*\)\.js/\1/')
sudo -u www-data $PHP $NC/occ config:app:set passwordfolders dev/app/hash --value="$HASH"
```

Ergebnis prüfen:

```bash
ls -l src/js/Static/app.*.js src/css/app.css
```

> Nicht mischen: `app.<hash>.js` gebaut, aber `dev/app/hash` leer (oder
> umgekehrt) → die Templates fordern eine Datei an, die es nicht gibt, und die
> App hängt im Spinner. Bei `npm run build:stable` also immer
> `occ config:app:delete passwordfolders dev/app/hash`, bei `npm run build` immer den
> Hash setzen.

### 4.3 Paket erzeugen

Die App-Wurzel ist `src/` — nicht das Repo-Root. Paket mit korrektem
Ordnernamen `passwords/`:

```bash
mkdir -p dist
tar -czf dist/passwords-$(grep -oP '(?<=<version>)[^<]+' src/appinfo/info.xml).tar.gz \
  --exclude='./vue' \
  --exclude='./scss' \
  --exclude='./report-stats.json' \
  --exclude='./bundle-report.html' \
  --exclude='./js/Static/*.map' \
  --transform 's,^\./,passwords/,' \
  -C src .
```

Kontrolle:

```bash
tar -tzf dist/passwords-*.tar.gz | head
# ./passwords/appinfo/info.xml, passwords/js/Static/app.js, ...
```

Alternativ kannst du direkt per `rsync` deployen (ohne Tarball), siehe 5.2.

---

## 5. Schritt 3 — Als zusätzliche App installieren

### 5.1 Original-App deaktivieren

```bash
sudo -u www-data $PHP $NC/occ app:disable passwords
```

Die Dateien der Original-App kannst du liegen lassen (sie wird nicht mehr geladen) oder
zur Seite legen:

```bash
sudo mv $NC/custom_apps/passwords $NC/custom_apps/passwords.store-backup 2>/dev/null || true
sudo mv $NC/apps/passwords $NC/apps/passwords.store-backup 2>/dev/null || true
```

### 5.2 Neues Paket entpacken

```bash
sudo tar -xzf passwords-*.tar.gz -C $NC/custom_apps/
sudo chown -R www-data:www-data $NC/custom_apps/passwordfolders
sudo find $NC/custom_apps/passwordfolders -type d -exec chmod 755 {} \;
sudo find $NC/custom_apps/passwordfolders -type f -exec chmod 644 {} \;
```

Kontrolle: `ls $NC/custom_apps/passwordfolders/appinfo/info.xml`

Variante ohne Tarball:

```bash
sudo rsync -a --delete "$FORK/src/" $NC/custom_apps/passwordfolders/
sudo chown -R www-data:www-data $NC/custom_apps/passwordfolders
```

## 6. Schritt 4 — Aktivieren, migrieren, Konfiguration übernehmen

### 6.1 App aktivieren

```bash
sudo -u www-data $PHP $NC/occ app:enable passwordfolders
```

Die Migrationen laufen dabei automatisch und legen nur fehlende Spalten/Indizes an.
Erwartete Ausgabe (gekürzt, je nach Zustand der Tabellen):

```
Creating column source_type in table passwords_share
Creating column source_folder in table passwords_share
...
Creating column has_shares in table passwords_folder
```

Falls Nextcloud einen Upgrade verlangt:

```bash
sudo -u www-data $PHP $NC/occ upgrade
```

### 6.2 Konfiguration und Benutzereinstellungen kopieren (Pflicht!)

Beide Apps speichern ihre Einstellungen und **ihre Verschlüsselungsschlüssel** unter der
eigenen App-ID. Ohne diesen Schritt kann die neue App die vorhandenen Passwörter nicht
entschlüsseln (Fehler `HMAC does not match`).

```bash
mysql -u root -p nextcloud <<'SQL'
INSERT INTO oc_appconfig (appid, configkey, configvalue)
SELECT 'passwordfolders', configkey, configvalue FROM oc_appconfig
WHERE appid = 'passwords'
  AND configkey NOT IN ('enabled', 'installed_version', 'types')
ON DUPLICATE KEY UPDATE configvalue = VALUES(configvalue);

INSERT INTO oc_preferences (userid, appid, configkey, configvalue)
SELECT userid, 'passwordfolders', configkey, configvalue FROM oc_preferences
WHERE appid = 'passwords'
ON DUPLICATE KEY UPDATE configvalue = VALUES(configvalue);
SQL
```

PostgreSQL:

```sql
INSERT INTO oc_appconfig (appid, configkey, configvalue)
SELECT 'passwordfolders', configkey, configvalue FROM oc_appconfig
WHERE appid = 'passwords' AND configkey NOT IN ('enabled','installed_version','types')
ON CONFLICT (appid, configkey) DO UPDATE SET configvalue = EXCLUDED.configvalue;

INSERT INTO oc_preferences (userid, appid, configkey, configvalue)
SELECT userid, 'passwordfolders', configkey, configvalue FROM oc_preferences
WHERE appid = 'passwords'
ON CONFLICT (userid, appid, configkey) DO UPDATE SET configvalue = EXCLUDED.configvalue;
```

Kontrolle:

```bash
sudo -u www-data $PHP $NC/occ config:app:get passwordfolders SSEv1ServerKey | head -c 20
```

Ist der Wert leer, wurde nichts kopiert — dann bleiben bestehende Passwörter unlesbar.

### 6.3 Wartungsmodus aus

```bash
sudo -u www-data $PHP $NC/occ maintenance:mode --off
```

## 7. Schritt 5 — Verifizieren

### 7.1 Version und Schema

```bash
sudo -u www-data $PHP $NC/occ app:list | grep -i passwordfolders     # erwartet 2026.11.1
```

Spalten prüfen (MySQL/MariaDB):

```bash
mysql -u root -p nextcloud -e "SHOW COLUMNS FROM oc_passwords_share;" | grep -E "source_type|source_folder|target_folder|parent_share"
mysql -u root -p nextcloud -e "SHOW COLUMNS FROM oc_passwords_folder;" | grep -E "share_id|has_shares"
```

### 7.2 Bestandsdaten prüfen

- Anmeldung, Passwortliste, Ordner, Tags, Favoriten, Papierkorb öffnen
- ein Passwort öffnen: Benutzername/Passwort müssen entschlüsselt erscheinen
  (bei CSE mit der gewohnten Verschlüsselungspassphrase)
- eine **bestehende** Passwort-Freigabe beim Empfänger prüfen

### 7.3 Neue Funktion testen (Ende-zu-Ende)

1. Ordner anlegen, ein Passwort hineinlegen
2. Ordner → „…" → **Teilen** → Kollegen auswählen → „Freigabe anlegen"
3. Cron einmal manuell anstoßen:
   ```bash
   sudo -u www-data $PHP -d memory_limit=2G $NC/cron.php
   sudo -u www-data $PHP -d memory_limit=2G $NC/cron.php   # zweiter Lauf kopiert die Passwörter
   ```
4. Als Empfänger anmelden: Ordner und Passwörter müssen erscheinen, Werte lesbar.

> Freigaben werden **asynchron** materialisiert. Ohne (zwei) Cron-Läufe sieht der
> Empfänger zunächst nichts — das ist kein Fehler.

---

## 8. Schritt 6 — Cron dauerhaft sicherstellen

```bash
sudo crontab -u www-data -e
# */5 * * * * /usr/bin/php -f /var/www/nextcloud/cron.php
```

Zusätzlich in den Grundeinstellungen der Instanz den Hintergrundaufgaben-Modus auf
**Cron** stellen. Ohne Cron werden Freigaben, Benachrichtigungen und der
Lösch-/Verschiebe-Abgleich innerhalb geteilter Ordner nicht ausgeführt.

---

## 9. Rollback

### 9.1 Sofort-Rollback (App-Ebene, Daten unverändert)

```bash
sudo -u www-data $PHP $NC/occ maintenance:mode --on
sudo -u www-data $PHP $NC/occ app:disable passwords
sudo rm -rf $NC/custom_apps/passwordfolders
sudo mv $NC/custom_apps/passwordfolders.store-backup $NC/custom_apps/passwordfolders
sudo -u www-data $PHP $NC/occ app:enable passwords
sudo -u www-data $PHP $NC/occ maintenance:mode --off
```

Die zusätzlichen Spalten bleiben in der Datenbank zurück. Die alte App ignoriert
sie — das ist unkritisch. In geteilten Ordnern angelegte **Ordner**-Freigaben
werden von der alten App allerdings nicht mehr ausgewertet.

### 9.2 Vollständiger Rollback (Datenbank)

Nur nötig, wenn du Ordner-Freigaben genutzt hast und wieder exakt den Zustand von
vorher brauchst:

```bash
sudo -u www-data $PHP $NC/occ maintenance:mode --on
mysql -u root -p nextcloud < ~/nextcloud-db-JJJJ-MM-TT.sql
sudo -u www-data $PHP $NC/occ maintenance:mode --off
```

Oder über das App-Backup (überschreibt die Passwort-Daten mit dem Stand des
Backups — Änderungen danach gehen verloren):

```bash
sudo -u www-data $PHP $NC/occ passwordfolders:backup:restore vor-fork-migration
```

---

## 10. Den Fork aktuell halten (Updates aus dem Original)

### 10.1 Remotes einrichten (einmalig)

```bash
cd "$FORK"
git remote add upstream https://github.com/marius-wieschollek/passwords.git
git remote -v
```

### 10.2 Upstream einspielen

```bash
git fetch upstream --tags
git checkout wip/password_folder_share
git rebase upstream/master        # oder: git merge upstream/master
git push --force-with-lease origin wip/password_folder_share
```

`rebase` hält die Historie linear und macht spätere Upstream-Syncs einfacher.
Falls du den Branch mit anderen teilst, ist `merge` die unkritischere Variante.

### 10.3 Version nach jedem Sync anheben

Nextcloud vergleicht Versionsstrings. Nach einem Upstream-Sync **muss** die
Fork-Version höher sein als die Upstream-Version, sonst führt `app:update` die
Migration nicht aus:

```xml
<!-- src/appinfo/info.xml -->
<version>2026.11.1</version>   <!-- Upstream 2026.11.0 → Fork .1 -->
```

Beim nächsten Upstream-Release (z. B. `2026.12.0`) setzt du `2026.12.1`.
Die Fork-Version muss außerdem **über der aktuell im App Store verfügbaren**
liegen, sonst bietet Nextcloud ein Update an und ersetzt den Fork
(siehe Abschnitt 12).
Zusätzlich einen Eintrag in `CHANGELOG.md` unter `## [Unreleased]` pflegen.

### 10.4 Konflikt-Hotspots

Diese Dateien hat der Fork geändert — hier sind Rebase-Konflikte am
wahrscheinlichsten:

```
src/lib/Db/Share.php
src/lib/Db/ShareMapper.php
src/lib/Db/Folder.php
src/lib/Cron/SynchronizeShares.php
src/lib/Services/Object/ShareService.php
src/lib/Controller/Api/ShareApiController.php
src/lib/Helper/ApiObjects/{ShareObjectHelper,FolderObjectHelper}.php
src/vue/App.vue
src/vue/Components/Sidebar/PasswordSidebar/Sharing/{ShareEditForm,ShareListItem}.vue
src/vue/Components/ContentList/Item/FolderItem/FolderItemActionMenu.vue
src/vue/Components/Sharing/ShareOptionsForm.vue
```

Neue Dateien des Forks (keine Konflikte, aber ggf. anzupassen, wenn Upstream die
Signaturen ändert):

```
src/lib/Helper/Sharing/{CreateFolderShareHelper,UpdateFolderShareHelper,FolderShareMaterializer}.php
src/lib/Migration/Version20260115.php
src/vue/Components/Sidebar/FolderSidebar.vue
src/vue/Components/Sidebar/FolderSidebar/{Details,Sharing}.vue
src/js/Models/Sidebar/FolderSidebar.js
```

**Migrationsnamen:** Upstream vergibt Migrationsnamen nach Datum
(`Version20250930`). Wenn Upstream eine Migration mit demselben Namen wie
`Version20260115` ergänzt, benenne die Fork-Migration um (z. B.
`Version20260115000000`) und hebe die Version erneut an. Das ist gefahrlos, weil
alle Schritte idempotent sind.

### 10.5 Checkliste nach jedem Upstream-Sync

```bash
npm ci
npm run build                     # gehashte Dateinamen, Hash wird gesetzt (siehe 4.2)
docker compose run --rm phpunit composer install --no-interaction   # nur beim ersten Mal
docker compose run --rm phpunit vendor/bin/phpunit --no-coverage
NODE_TLS_REJECT_UNAUTHORIZED=0 npx cypress run --browser firefox \
  --spec cypress/e2e/folder-sharing.cy.js --config screenshotOnRunFailure=false
```

Dann: Version anheben → Tarball bauen → auf einer **Testinstanz** deployen
(Schritte 5–7) → erst danach produktiv.

---

## 11. An Gruppen teilen

Ordner lassen sich wie Passwörter auch an **Gruppen** teilen. Nextcloud-Freigaben
sind technisch immer pro Benutzer, deshalb wird eine Gruppe beim Teilen in ihre
Mitglieder aufgelöst: jedes Mitglied bekommt eine eigene Freigabe.

Voraussetzungen:

- In Nextcloud muss das Teilen mit Gruppen erlaubt sein
  (Core-Einstellung `shareapi_allow_group_sharing`, Standard: `yes`).
  Prüfen: `occ config:app:get core shareapi_allow_group_sharing`
- Der **Besitzer des Ordners muss selbst Mitglied der Gruppe** sein — die App
  schlägt nur die eigenen Gruppen als Empfänger vor.
- Das eigene Konto wird beim Auflösen übersprungen.

Verhalten:

- Mitglieder werden **beim Anlegen** der Freigabe aufgelöst. Wer der Gruppe
  später beitritt, bekommt die Freigabe nicht automatisch (gleiches Verhalten wie
  bei Passwörtern); wer die Gruppe verlässt, behält seine Kopie, bis die Freigabe
  widerrufen wird.
- In der Freigabeliste erscheinen die einzelnen Mitglieder, nicht die Gruppe.
- Bereits geteilte Mitglieder werden beim erneuten Teilen übersprungen (kein
  Fehler, keine doppelte Freigabe).
- **Widerrufen ist immer pro Person.** Es gibt kein Gruppen-Objekt, das man
  entfernen könnte: in der Freigabeliste stehen die einzelnen Mitglieder und
  müssen einzeln widerrufen werden. Wer die Gruppe in Nextcloud verlässt, behält
  seine Kopie, bis die Freigabe widerrufen wird; wer später beitritt, bekommt
  nichts automatisch.
- Beim Widerruf entfernt der Server die Kopie im Empfängerkonto. Die Ordner-Kopie
  wird dabei spätestens mit dem nächsten Cron-Lauf gelöscht (Abschnitt 8).

Prüfen, ob die Gruppe gefunden wird:

```bash
B="https://cloud.example.com/index.php/apps/passwordfolders/api/1.0"
curl -s -u admin:admin "$B/share/recipients?search=team"
curl -s -u admin:admin -H 'Content-Type: application/json' \
  -X POST "$B/share/recipients/resolve-group" -d '{"groupId":"team"}'
```

---

## 12. Updates der Original-App

Weil dieses Paket eine eigene App-ID hat, kann ein Update der Original-App die Dateien
**nicht mehr überschreiben**. Im App Store erscheinen "Passwords" und "Passwords Folders"
als zwei getrennte Apps.

Wichtig bleibt:

- **Die Original-App darf nicht aktiviert werden.** Sie arbeitet auf denselben Tabellen.
  Laeuft sie zusaetzlich, schreibt ihr Cron bei jedem Durchlauf Fehler
  (`Could not create target password ... for share <uuid>`), weil sie die
  Ordner-Freigabe-Zeilen nicht kennt, und beide Apps synchronisieren parallel.
- Pruefen, ob sie aus ist:

  ```bash
  sudo -u www-data $PHP $NC/occ app:list | grep -E "passwords|passwordfolders"
  ```

  Erwartet: `passwordfolders` unter "Enabled", `passwords` unter "Disabled".
- Ein **Update der Original-App ist unkritisch**, solange sie deaktiviert bleibt. Nach einem
  solchen Update trotzdem einmal pruefen:

  ```bash
  sudo -u www-data $PHP $NC/occ app:update passwordfolders
  sudo -u www-data $PHP $NC/occ upgrade
  ```

- Wird dieses Paket deinstalliert, bleiben die Daten erhalten (gemeinsame Tabellen). Die
  Original-App kann danach wieder aktiviert werden.

---

## 13. Troubleshooting

| Symptom | Ursache | Lösung |
|---|---|---|
| App lädt, bleibt im Spinner | mit `npm run build` gebaut → `app.<hash>.js` | `npm run build:stable`, neu deployen, Browser-Cache (Strg+Shift+R) |
| `Column not found: source_type` | `app:update` nicht gelaufen | `occ app:update passwords`, ggf. `occ upgrade` |
| Empfänger sieht geteilten Ordner nicht | Cron läuft nicht / erst ein Lauf | `occ config:system:get cronMethod` → `cron`; zweimal `cron.php` ausführen |
| „Teilen" fehlt im Ordner-Menü | altes JS im Browser-Cache | Hard-Reload; prüfen ob `js/Static/app.js` das neue Datum hat |
| `HMAC does not match` / Passwörter unlesbar | Konfiguration nicht kopiert | Abschnitt 6.2 ausführen (`SSEv1ServerKey`, `SSEv1UserKey`) |
| `app:enable` meldet „already installed" | Store-Version noch vorhanden | `occ app:disable passwords`, Verzeichnis ersetzen, neu aktivieren |
| Update wird nicht angeboten | Fork-Version ≤ Upstream-Version | Version in `src/appinfo/info.xml` anheben |
| 500 nach Update | PHP-Version zu alt | PHP 8.4/8.5 bereitstellen |

Log prüfen:

```bash
sudo tail -f $NC/data/nextcloud.log
```

---

## 14. Kurzfassung (für Eilige)

```bash
# 1. Backup
sudo -u www-data $PHP $NC/occ passwords:backup:create vor-fork-migration
mysqldump -u root -p nextcloud > ~/nextcloud-db.sql

# 2. Bauen (Hashed-Build, siehe 4.2)
git clone -b master https://github.com/Ilkawoci/nextcloud_passwords.git "$FORK"
cd "$FORK" && npm ci && npm run build
tar -czf passwords.tar.gz --exclude='./vue' --exclude='./scss' \
  --exclude='./report-stats.json' --exclude='./bundle-report.html' \
  --transform 's,^\./,passwordfolders/,' -C src .

# 3. Installieren (Original-App aus, neue App daneben)
sudo -u www-data $PHP $NC/occ maintenance:mode --on
sudo -u www-data $PHP $NC/occ app:disable passwords
sudo tar -xzf passwords.tar.gz -C $NC/custom_apps/
sudo chown -R www-data:www-data $NC/custom_apps/passwordfolders
sudo -u www-data $PHP $NC/occ app:enable passwordfolders

# 4. Schluessel und Einstellungen uebernehmen (Pflicht, siehe 6.2)
mysql -u root -p nextcloud -e "<SQL aus Abschnitt 6.2>"

# 5. Hash setzen und Wartungsmodus aus
HASH=$(ls "$FORK"/src/js/Static/app.*.js | sed 's/.*app\.\(.*\)\.js/\1/')
sudo -u www-data $PHP $NC/occ config:app:set passwordfolders dev/app/hash --value="$HASH"
sudo -u www-data $PHP $NC/occ maintenance:mode --off

# 6. Pruefen
sudo -u www-data $PHP $NC/occ app:list | grep -E "passwords|passwordfolders"
```
