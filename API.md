# Passwords Folders — HTTP API

Beschreibt den Ist-Zustand dieses Forks, generiert aus `src/appinfo/routes.php`
und den Controllern. Die Endpunkte der Original-App sind unverändert; die
Ergänzungen für Ordner-Freigaben stehen am Ende.

## Basis

| | |
|---|---|
| Base-URL | `https://<host>/index.php/apps/passwordfolders/api/1.0/` |
| Auth (Skripte) | HTTP Basic (`-u user:passwort`) |
| Auth (Browser) | Session-Cookie + `requesttoken`-Header |
| Antwortformat | JSON |
| Fehlerformat | `{"status":"error","id":<code>,"message":"..."}` |

```bash
curl -sk -u max:passwort \
  "https://cloud.example.com/index.php/apps/passwordfolders/api/1.0/password/list"
```

Die Spalte *Parameter* listet die Felder aus der Controller-Signatur (JSON-Body,
bei `GET` als Query-Parameter). `show`/`list`/`find`/`delete` erben ihre Parameter
aus `AbstractObjectApiController`.

## Endpunkte

### Session

| Methode | Pfad | Parameter |
|---|---|---|
| `GET` | `/api/1.0/session/close` | — |
| `GET` | `/api/1.0/session/keepalive` | — |
| `POST` | `/api/1.0/session/open` | — |
| `GET` | `/api/1.0/session/request` | — |
| `GET` | `/api/1.0/token/{provider}/request` | — |

### Keychain

| Methode | Pfad | Parameter |
|---|---|---|
| `GET` | `/api/1.0/keychain/get` | `details` |
| `POST` | `/api/1.0/keychain/set` | `id`, `data` |

### Account

| Methode | Pfad | Parameter |
|---|---|---|
| `GET` | `/api/1.0/account/challenge/get` | — |
| `POST` | `/api/1.0/account/challenge/set` | — |
| `GET` | `/api/1.0/account/reset` | `code` |
| `POST` | `/api/1.0/account/reset` | `code` |

### Password

| Methode | Pfad | Parameter |
|---|---|---|
| `POST` | `/api/1.0/password/create` | `password`, `username`, `cseKey`, `cseType`, `hash`, `label`, `url`, `notes`, `customFields`, `folder` … |
| `DELETE` | `/api/1.0/password/delete` | `id`, `revision` |
| `POST` | `/api/1.0/password/find` | `criteria`, `details` |
| `GET` | `/api/1.0/password/list` | `details` |
| `POST` | `/api/1.0/password/list` | `details` |
| `PATCH` | `/api/1.0/password/restore` | `id`, `revision` |
| `POST` | `/api/1.0/password/show` | `id`, `details` |
| `PATCH` | `/api/1.0/password/update` | `id`, `password`, `revision`, `username`, `cseKey`, `cseType`, `hash`, `label`, `url`, `notes` … |

### Folder

| Methode | Pfad | Parameter |
|---|---|---|
| `POST` | `/api/1.0/folder/create` | `label`, `parent`, `cseKey`, `cseType`, `edited`, `hidden`, `favorite` |
| `DELETE` | `/api/1.0/folder/delete` | `id`, `revision` |
| `POST` | `/api/1.0/folder/find` | `criteria`, `details` |
| `GET` | `/api/1.0/folder/list` | `details` |
| `POST` | `/api/1.0/folder/list` | `details` |
| `PATCH` | `/api/1.0/folder/restore` | `id`, `revision` |
| `POST` | `/api/1.0/folder/show` | `id`, `details` |
| `PATCH` | `/api/1.0/folder/update` | `id`, `label`, `revision`, `parent`, `cseKey`, `cseType`, `edited`, `hidden`, `favorite` |

### Tag

| Methode | Pfad | Parameter |
|---|---|---|
| `POST` | `/api/1.0/tag/create` | `label`, `color`, `cseKey`, `cseType`, `edited`, `hidden`, `favorite` |
| `DELETE` | `/api/1.0/tag/delete` | `id`, `revision` |
| `POST` | `/api/1.0/tag/find` | `criteria`, `details` |
| `GET` | `/api/1.0/tag/list` | `details` |
| `POST` | `/api/1.0/tag/list` | `details` |
| `PATCH` | `/api/1.0/tag/restore` | `id`, `revision` |
| `POST` | `/api/1.0/tag/show` | `id`, `details` |
| `PATCH` | `/api/1.0/tag/update` | `id`, `label`, `color`, `revision`, `cseKey`, `cseType`, `edited`, `hidden`, `favorite` |

### Share

| Methode | Pfad | Parameter |
|---|---|---|
| `POST` | `/api/1.0/share/create` | `password`, `recipient`, `type`, `expires`, `editable`, `shareable`, `receiver`, `folder` |
| `DELETE` | `/api/1.0/share/delete` | `id` |
| `POST` | `/api/1.0/share/find` | `criteria`, `details` |
| `GET` | `/api/1.0/share/list` | `details` |
| `POST` | `/api/1.0/share/list` | `details` |
| `GET` | `/api/1.0/share/partners` | `search`, `limit` |
| `POST` | `/api/1.0/share/partners` | `search`, `limit` |
| `GET` | `/api/1.0/share/recipients` | `search`, `limit`, `withGroups` |
| `POST` | `/api/1.0/share/recipients` | `search`, `limit`, `withGroups` |
| `POST` | `/api/1.0/share/recipients/resolve-group` | `groupId` |
| `POST` | `/api/1.0/share/show` | `id`, `details` |
| `PATCH` | `/api/1.0/share/update` | `id`, `expires`, `editable`, `shareable` |

### Settings

| Methode | Pfad | Parameter |
|---|---|---|
| `POST` | `/api/1.0/settings/get` | — |
| `GET` | `/api/1.0/settings/list` | `scopes` |
| `POST` | `/api/1.0/settings/list` | `scopes` |
| `POST` | `/api/1.0/settings/reset` | — |
| `POST` | `/api/1.0/settings/set` | — |

### Service

| Methode | Pfad | Parameter |
|---|---|---|
| `GET` | `/api/1.0/service/avatar/{user}/{size}` | — |
| `GET` | `/api/1.0/service/coffee` | — |
| `GET` | `/api/1.0/service/favicon/{domain}/{size}` | — |
| `POST` | `/api/1.0/service/hashes` | — |
| `GET` | `/api/1.0/service/hashes/{range}` | — |
| `GET` | `/api/1.0/service/password` | — |
| `POST` | `/api/1.0/service/password` | — |
| `POST` | `/api/1.0/service/password-change` | — |
| `GET` | `/api/1.0/service/preview/{domain}/{view}/{width}/{height}` | — |

## Ordner-Freigaben (Änderungen dieses Forks)

`POST /share/create` akzeptiert zusätzlich zu `password` das Feld `folder`:

```json
{
  "folder": "<folder-uuid>",
  "recipient": "<user-id>",
  "type": "user",
  "editable": true,
  "shareable": false,
  "expires": null
}
```

Genau eines von `password` oder `folder` muss gesetzt sein. Gruppen werden
clientseitig über `POST /share/recipients/resolve-group` (`{"groupId": "<id>"}`)
in einzelne Benutzer aufgelöst; pro Mitglied entsteht eine eigene Freigabe.

| Endpunkt | Ergänzung |
|---|---|
| `POST /folder/show` | Detail-Level `shares`; mit `model+shares` enthält das Objekt `share`, `shared`, `shares` |
| `POST /folder/show` | Detail-Level `folders` und `passwords` (Kinder des Ordners) |
| `POST /share/find` | Freigabe-Objekte enthalten `sourceType` (`password`/`folder`), `folder`, `parentShare` |
| `GET /share/recipients` | Parameter `search`, `limit`, `withGroups` |

Freigaben werden **asynchron** materialisiert: nach `share/create` erscheint beim
Empfänger erst nach dem nächsten Cron-Lauf etwas (`php cron.php` der Instanz).
Widerrufene Freigaben werden ebenfalls im Cron aufgeräumt.

