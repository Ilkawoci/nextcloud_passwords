# Plan: Ordner-Sharing für Passwords App

## 1. Analyse der aktuellen Architektur

### 1.1 Datenmodell

- **Password** (`passwords_password`): Model-Tabelle mit UUID, userId, `shareId`, `hasShares`.
- **PasswordRevision**: Revision-Tabelle mit allen Passwort-Daten (label, username, password, notes, customFields) sowie `parent` (Ordner-Referenz).
- **Folder** (`passwords_folder`): Model-Tabelle.
- **FolderRevision**: Revision-Tabelle mit `label`, `parent`. Die Hierarchie wird über `parent` als UUID des übergeordneten Ordners abgebildet. Das Base-Folder hat die UUID `00000000-0000-0000-0000-000000000000`.
- **Share** (`passwords_share`): Verknüpft einen Besitzer mit einem Empfänger. Felder: `source_password`, `target_password`, `receiver`, `editable`, `shareable`, `expires`, `type`.

### 1.2 Aktueller Share-Mechanismus

1. `ShareApiController::create()` empfängt eine Passwort-UUID, erstellt ein `Share`-Objekt mit `source_password` und `receiver`.
2. Der Cron-Job `SynchronizeShares` verarbeitet das Share: Er klont die PasswordRevision in eine neue, dem Empfänger gehörende Target-Password-Revision (asynchrone Duplizierung).
3. Bei Änderungen propagiert der Cron Job Sync-Updates bidirektional via `source_updated`/`target_updated`.
4. Das Frontend zeigt geteilte Passwörter über den `share`-Tab in der Sidebar.

### 1.3 Problemstellung

Ordner-Sharing existiert nicht. Die aktuelle Architektur teilt einzelne Passwörter. Ein Ordner-Sharing muss:

- Alle rekursiven Unterordner und darin enthaltenen Passwörter teilen
- Konsistenz über Änderungen hinweg gewährleisten (neue Passwörter in geteilten Ordnern automatisch verfügbar machen)
- Das bestehende Share-Modell erweitern oder ergänzen

---

## 2. Design-Entscheidungen

### 2.1 Ansatz: Erweiterung des Share-Modells statt Neuerstellung

Statt ein zweites Sharing-System zu bauen, wird das bestehende `Share`-Modell erweitert:

| Feld | Typ | Beschreibung |
|------|-----|-------------|
| `source_password` | string \| null | Bisheriges Feld; bei Folder-Share NULL |
| `source_folder` | string \| null | **Neu**: UUID des geteilten Ordners (Owner-Kopie) |
| `target_folder` | string \| null | **Neu**: UUID des empfangenen Ordners (Receiver-Kopie) |
| `type` | string | `'user'`, `'group'`, `'link'` – bleibt gleich |

**Warum nicht eine neue Tabelle?** Das würde Sync-Logik, API-Routing und Frontend massiv vervielfältigen. Die Erweiterung bleibt innerhalb des bestehenden Migrations- und Service-Musters.

### 2.2 Ansatz: Rekursiver Share bei der Quelle, lazy Sync für Neuzugänge

Zwei Szenarien:

1. **Bestehende Passwörter/Ordner**: Bei einem Folder-Share werden alle vorhandenen Unterordner und Passwörter sofort geteilt (rekursiv).
2. **Neue Passwörter in geteilten Ordnern**: Der Cron Job erkennt neue Passwörter (`parent` zeigt auf einen geteilten Ordner) und erstellt automatisch Shares dafür.

### 2.3 Konsistenzgarantie

- **Lösche der Owner den Parent-Ordner** → Alle rekursiven Target-Ordner und Target-Passwörter werden gelöscht.
- **Verschiebt der Owner einen geteilten Ordner** (andere `parent`-UUID) → Die bestehenden Shares bleiben bestehen, die Struktur ändert sich für alle Empfänger synchron mit.
- **Fügt der Owner neue Passwörter hinzu** → Cron Job erstellt automatisch Shares für das Owner-Passwort und klont die Revision.

---

## 3. Detaillierter Implementierungsplan

### Phase 1: Datenbankschema & Models (Backend-Core)

#### 3.1 Migration: Neue Spalten in `passwords_share`

**Datei**: `src/lib/Migration/Version2026MMDD.php`

```php
// Neue Spalten zur passwords_share Tabelle hinzufügen:
$table->addColumn('source_folder', 'string', ['length' => 36, 'notnull' => true, 'default' => '']);
$table->addColumn('target_folder', 'string', ['length' => 36, 'notnull' => false, 'default' => null]);

// Neue Indizes für Performance
$table->addIndex(['source_folder'], 'share_source_folder_index');
$table->addIndex(['target_folder'], 'share_target_folder_index');
```

#### 3.2 Share Model erweitern

**Datei**: `src/lib/Db/Share.php`

```php
// Neue Properties hinzufügen:
protected ?string $sourceFolder;
protected ?string $targetFolder;

// addType() aufrufen im Konstruktor:
$this->addType('sourceFolder', 'string');
$this->addType('targetFolder', 'string');

// Getter/Setter via @method DocBlock oder direkt implementieren
```

#### 3.3 ShareService erweitern

**Datei**: `src/lib/Services/Object/ShareService.php`

Neue Methoden:

```php
// Alle Shares für einen bestimmten Ordner finden
public function findBySourceFolder(string $folderUuid): array;

// Alle Shares, deren target_folder NULL ist (nicht gesynct)
public function findFolderSharesWithNullTarget(): array;

// Folder-Share erstellen (ohne Target-Erstellung – das übernimmt der Cron)
public function createFolderShare(string $folderUuid, string $receiverId, ...): Share;
```

#### 3.4 ShareMapper erweitern

**Datei**: `src/lib/Db/ShareMapper.php`

Neue Query-Methode:

```php
public function findAllBySourceFolder(string $sourceUuid): array {
    // Query auf source_folder Feld
}
```

---

### Phase 2: Backend-API für Folder-Sharing

#### 4.1 ShareApiController erweitern

**Datei**: `src/lib/Controller/Api/ShareApiController.php`

Neue API-Parameter in `create()`:

| Parameter | Typ | Beschreibung |
|-----------|-----|-------------|
| `password` | string \| null | Bestehender Parameter |
| `folder` | string \| null | **Neu**: UUID des zu teilenden Ordners |
| `type` | string | `'user'`, `'group'`, `'link'` |
| `expires` | int \| null | Ablaufdatum |
| `editable` | bool | Bearbeiten erlauben |
| `shareable` | bool | Weiterteilen erlauben |

**Validierungslogik**:
```php
if ($password === null && $folder === null) {
    throw new ApiException('Either password or folder must be provided', Http::STATUS_BAD_REQUEST);
}
```

#### 4.2 CreateFolderShareHelper erstellen

**Datei**: `src/lib/Helper/Sharing/CreateFolderShareHelper.php`

Diese Klasse kapselt die Ordner-Share-Erstellung:

```php
class CreateFolderShareHelper {
    public function createFolderShare(
        string $folderUuid,
        string $receiverId,
        string $type = 'user',
        ?int $expires = null,
        bool $editable = false,
        bool $shareable = false
    ): array {

        // 1. Root-Folder des Owners laden (FolderRevision)
        // 2. Rekursiv alle Unterordner sammeln (Depth-First oder BFS)
        // 3. Für jeden Ordner ein Share erstellen:
        //    - source_folder = UUID des Owner-Ordners
        //    - target_folder = NULL (wird vom Cron gefüllt)
        //    - receiver, editable, shareable, expires kopiert
        // 4. Alle Passwörter in jedem gesammelten Ordner ebenfalls teilen:
        //    - existing Share prüfen (nicht doppelt teilen)
        //    - CreatePasswordShareHelper für jedes Passwort aufrufen
        
        return ['shares' => [...], 'folders' => [...]];
    }

    private function collectSubFolders(string $parentUuid, FolderService $folderService): array {
        // Recursively walk the folder tree starting from parentUuid
        // Return flat list of [uuid => revision]
    }

    private function findPasswordsInFolder(string $folderUuid, PasswordService $passwordService): array {
        // Query: password_revision WHERE parent = :folderUuid
        // (rekursiv alle untergeordneten Passwörter)
    }
}
```

---

### Phase 3: Cron-Job erweitern (SynchronizeShares)

#### 5.1 Neue Jobs für Folder-Sharing

**Datei**: `src/lib/Cron/SynchronizeShares.php`

Erweiterung der `runJob()`-Methode:

```php
protected function runJob($argument): void {
    // ... existing methods ...
    
    $this->syncFolderShares();      // **Neu**
    $this->propagateNewPasswordsInSharedFolders(); // **Neu**
    $this->cleanupDeletedParentFolders();          // **Neu**
}
```

#### 5.2 `syncFolderShares()` – Target-Ordner und Passwörter erstellen

```php
protected function syncFolderShares(): void {
    // 1. Alle Shares finden wo source_folder != '' AND target_folder IS NULL
    $shares = $this->shareService->findFolderSharesWithNullTarget();
    
    foreach ($shares as $share) {
        // a) Target-Folder erstellen (Kopie des Owner-Folders für den Receiver)
        // b) Share aktualisieren: target_folder = UUID des neuen Target-Folders
        // c) Rekursiv alle Unterordner kopieren und deren Shares updaten
        // d) Alle enthaltenen Passwörter kopieren (analog zu createNewShares())
    }
}
```

**Ordner-Klon-Logik**:

```php
private function cloneFolderTree(
    string $sourceFolderUuid,
    string $targetParentUuid,
    string $receiverId,
    array &$shareMapping = [] // source_uuid => target_uuid
): string {
    // 1. FolderRevision des Source laden
    // 2. Neue FolderRevision für Receiver erstellen (gleiche Daten)
    // 3. Neues Folder-Model mit receiverId erstellen
    // 4. newFolderUuid zurückgeben
    
    // Rekursiv Unterordner klonen:
    $children = $this->folderService->findByParent($sourceFolderUuid);
    foreach ($children as $child) {
        $childTargetUuid = $this->cloneFolderTree(
            $child->getUuid(), 
            $newFolderUuid, 
            $receiverId, 
            $shareMapping
        );
        $shareMapping[$child->getUuid()] = $childTargetUuid;
    }
    
    return $newFolderUuid;
}
```

#### 5.3 `propagateNewPasswordsInSharedFolders()` – Neuzugänge erkennen

```php
protected function propagateNewPasswordsInSharedFolders(): void {
    // 1. Alle Shares finden die source_folder gesetzt haben
    $folderShares = $this->shareService->findAllBySourceFolderNotNull();
    
    foreach ($folderShares as $folderShare) {
        // a) Alle aktuellen Child-Ordner des Source-Folders ermitteln
        // b) Alle Current-Target Ordner des Receivers ermitteln
        // c) Differenz berechnen: Neue Target-Ordner fehlen
        // d) Für jede neue Target-Ordner-ID:
        //    - Alle enthaltenen Passwörter prüfen
        //    - Falls Password noch kein Share hat: CreatePasswordShareHelper aufrufen
        //    - Das Password bekommt den entsprechenden Source Folder als parent
        
        // e) Gleiche Logik für Änderungen in der Ordner-Struktur (Verschiebungen)
    }
}
```

#### 5.4 `cleanupDeletedParentFolders()` – Bereinigung bei Löschungen

```php
protected function cleanupDeletedParentFolders(): void {
    // 1. Alle Folder-Shares finden
    $folderShares = $this->shareService->findAllBySourceFolderNotNull();
    
    foreach ($folderShares as $folderShare) {
        try {
            // Prüfen ob Source-Ordner noch existiert
            $this->folderService->findByUuid($folderShare->getSourceFolder());
        } catch (DoesNotExistException $e) {
            // Source-Ordner wurde gelöscht → Alle Target-Ordner und deren Passwörter löschen
            $this->deleteSharedFolderTree($folderShare->getTargetFolder());
            $this->shareService->delete($folderShare);
            
            // Weitere Shares die denselben Source-Folder referenzieren ebenfalls bereinigen
        }
    }
}

private function deleteSharedFolderTree(string $folderUuid): void {
    // Rekursiv alle Unterordner löschen, dann den Ordner selbst
}
```

---

### Phase 4: Frontend-Integration

#### 6.1 Share-API in der passwords_client erweitern

**Datei**: `src/js/PasswordsClient/LegacyPasswordsApi.js`

Neue API-Endpunkte hinzufügen:

```javascript
// Folder Share erstellen
share/folder/create: {
    method: 'post',
    url: '/api/1.0/share/folder/create'
}
```

Oder über das existierende `share/create` mit neuem Parameter `folder`:

```javascript
// Bestehender endpoint nutzt, aber 'folder' Parameter akzeptiert
share/create: {
    method: 'post',
    url: '/api/1.0/share/create'
}
```

#### 6.2 CreateFolderShareAction erstellen

**Datei**: `src/js/Actions/Share/CreateFolderShareAction.js`

```javascript
export default class CreateFolderShareAction {
    #folder;
    #recipient;
    #options;

    constructor(folder, recipient, options) {
        this.#folder = folder;
        this.#recipient = recipient;
        this.#options = options;
    }

    async run() {
        // 1. API.createShare aufrufen mit folder: this.#folder.id
        // 2. Auf Response warten (listet alle erstellten Share-IDs)
        // 3. emitMultiple für Event-Bus Updates
    }
}
```

#### 6.3 Kontextmenü in der Ordner-Liste erweitern

**Datei**: `src/vue/Components/ContentList/Item/FolderItem/` (existiert bereits?)

Das FolderItem muss einen Share-Eintrag im Aktion-Menü erhalten, analog zum PasswordItem:

```vue
<nc-action-item @click="shareFolder()">
    <share-variant-icon slot="icon" />
    {{ t('Share folder') }}
</nc-action-item>
```

#### 6.4 ShareDialog für Ordner erstellen

**Datei**: `src/vue/Dialog/FolderShareDialog.vue` (Neu)

```vue
<template>
    <NcDialog name="folder-share-dialog" :open.sync="show">
        <ShareOptionsForm ref="shareOptions" @options="onOptions" />
        <ShareUserSelect 
            ref="userSelect" 
            @recipient-selected="onRecipientSelected" 
        />
        <template #actions>
            <NcButton @click="createFolderShare">Teilen</NcButton>
            <NcButton @click="close" type="secondary">Abbrechen</NcButton>
        </template>
    </NcDialog>
</template>
```

#### 6.5 Sidebar für Ordner: Share-Tab

Die bestehende `PasswordSidebar.vue` ist bereits generic genug (sie nimmt ein `sidebar` Prop). Der `hasSharing`-computed Check (`SettingsService.get('server.sharing.enabled')`) reicht. Es muss nur sichergestellt werden, dass FolderItem auch den `Share`-Tab rendert.

Der Share-Tab verwendet das `share.vue` Component mit dem `:password` prop – hier muss für Ordner ein analoges `folder` prop übergeben werden. Das `Sharing.vue` Component muss angepasst werden, um zwischen `password` und `folder` zu unterscheiden:

```javascript
// In Sharing.vue:
computed: {
    // Current item (password OR folder)
    currentItem() { return this.password || this.folder; },
    
    shares() {
        // Falls folder: Shares über source_folder filtern
        if (this.folder && this.folder.shares) {
            return Object.values(this.folder.shares);
        }
        return this.shares; // existing password logic
    }
}
```

---

### Phase 5: Tests und Qualitätssicherung

#### 7.1 Backend-Tests (PHPUnit)

| Test | Beschreibung |
|------|-------------|
| `CreateFolderShareHelperTest` | Rekursive Ordner-Sammlung funktioniert korrekt |
| `ShareServiceTest::testFindBySourceFolder` | Queries auf neue Spalten funktionieren |
| `SynchronizeSharesTest::testSyncFolderTree` | Cron Job klont Ordner-Tree + Passwörter |
| `SynchronizeSharesTest::testPropagateNewPasswords` | Neue Passwörter in geteilten Ordnern werden geteilt |
| `SynchronizeSharesTest::testCleanupOnParentDelete` | Parent-Löschung löscht alle Targets |
| `ShareApiControllerTest::testCreateWithFolder` | API Endpunkt akzeptiert folder Parameter |

#### 7.2 Frontend-Tests (Cypress)

| Test | Beschreibung |
|------|-------------|
| `share-folder.cy.js` | Share-Dialog für Ordner öffnet sich korrekt |
| `share-folder-receive.cy.js` | Empfänger sieht geteilten Ordner mit Inhalt |
| `share-folder-add-password.cy.js` | Neues Passwort in geteiltem Ordner erscheint automatisch |

---

## 4. Risikobewertung & Risiken

| Risiko | Auswirkung | Gegenmaßnahme |
|--------|-----------|---------------|
| Performance bei großen Ordner-Strukturen | Lange Ladezeiten, Timeouts | Batching im Cron Job; Paginierung für API; Timeout-Erkennung |
| Dateninkonsistenz bei Fehlern während Sync | Teilweise geteilte Ordner | Transaktions-basierte Migration + idempotente Operationen |
| Berechtigungsvererbung (editable/shareable) | Falsche Zugriffsrechte beim Receiver | Konsistente Vererbung der ersten Ebene; rekursive Anwendung nur für `editable` |
| Resharing-Beschränkung | Benutzer können geteilte Ordner nicht weitergeben | `shareable` Flag wird an alle Unter-Objekte vererbt |

---

## 5. Umsetzungspriorität (Phasen)

### Phase 1 – MVP (Kernfunktionalität)
- Migration: Neue Spalten in `passwords_share`
- Share Model + Service erweitert
- API Endpoint `share/create` mit `folder` Parameter
- CreateFolderShareHelper (rekursive Share-Erstellung)
- Frontend: Share-Kontextmenü für Ordner, ShareDialog

### Phase 2 – Sync & Konsistenz
- Cron-Job `syncFolderShares()`
- Ordner-Klon-Mechanismus
- Target-Passwort-Sync im Folder-Bereich

### Phase 3 – Automatische Propagierung
- `propagateNewPasswordsInSharedFolders()`
- Bereinigung bei Parent-Löschung (`cleanupDeletedParentFolders()`)
- Ordnerverschiebungserkennung

### Phase 4 – UI-Vervollständigung
- Share-Tab in Ordner-Sidebar
- "Geteilte Ordner"-Navigation (analog zu "Geteilte Passwörter")
- Fehlerbehandlungen und Benachrichtigungen

---

## 6. Geplante API-Endpunkte

### Bestehende, erweitert:

```
POST /api/1.0/share/create
Body: {
    folder?: string,          // Neud - UUID des zu teilenden Ordners
    password?: string,        // Bisheriger Parameter
    receiver: string,
    type: 'user' | 'group' | 'link',
    editable: boolean,
    shareable: boolean,
    expires?: integer|null
}

// Antwort bei folder-Share:
{
    "success": true,
    "shares": [
        {"source_folder": "...", "target_folder": null, ...},  // Folder-Shares (pending sync)
        {"source_password": "...", "target_password": "...", ...}  // Direct password shares
    ]
}
```

### Neue Endpunkte (optional für direkte Kontrolle):

```
POST /api/1.0/share/folder/sync/{share_id}
// Cron-job-ähnliche Sync-Operation on-demand triggern (für Testing/Admin)

POST /api/1.0/share/folder/propagate
// Alle neuen Passwörter in geteilten Ordnern scannen
```

---

## 7. Zusammenfassung der Änderungen

### Neu hinzugefügt:
| Datei | Beschreibung |
|-------|-------------|
| `src/lib/Migration/Version2026MMDD.php` | DB-Migration für neue Spalten |
| `src/lib/Helper/Sharing/CreateFolderShareHelper.php` | Ordner-Share-Erstellung mit rekursiver Sammlung |
| `src/js/Actions/Share/CreateFolderShareAction.js` | Frontend-Aktion für Ordner-Sharing |
| `src/vue/Dialog/FolderShareDialog.vue` | Share-Dialog für Ordner |

### Geändert:
| Datei | Beschreibung |
|-------|-------------|
| `src/lib/Db/Share.php` | Neue Felder `source_folder`, `target_folder` |
| `src/lib/Services/Object/ShareService.php` | Neue Query-Methoden |
| `src/lib/Db/ShareMapper.php` | Neue DB-Queries |
| `src/lib/Controller/Api/ShareApiController.php` | `folder` Parameter in `create()` |
| `src/lib/Cron/SynchronizeShares.php` | 3 neue Sync-Methoden |
| `src/vue/Components/Sharing/ShareOptionsForm.vue` | Folder-spezifische Optionen (optional) |
| `src/vue/Components/Sidebar/PasswordSidebar/Sharing/Sharing.vue` | Unterscheidung password vs. folder |

---

## 8. Offene Fragen

1. **Soll Ordner-Sharing auch Gruppen und Link-Shares unterstützen?** → Ja, das existierende `type` Feld macht das bereits möglich.
2. **Sollen Empfänger neue Passwörter in geteilten Ordnern anlegen können?** → Nur wenn `editable = true`. Dann wird das neue Passwort automatisch dem Owner als Target zugewiesen (Bidirektionaler Sync über bestehende Share-Logik).
3. **Welches Verhalten bei Zirkel-Sharing von Ordnern?** → Der Cron Job sollte eine Loop-Erkennung für Folder-Shares implementieren, analog zu `shareLineageHasLoop()`.
4. **Soll das Base-Folder (`0000...`) nicht geteilt werden können?** → Ja, wie beim Löschen auch.
