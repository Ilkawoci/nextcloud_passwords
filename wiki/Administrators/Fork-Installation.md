> This page describes the installation of this fork ("Passwords Folders"), not of the official Passwords app.

# Contents

[[_TOC_]]

# What this fork is

This app is a fork of the Passwords app that adds sharing for whole folders.
It has its own app id `passwordfolders`, its own folder in `custom_apps/passwordfolders`, its own routes under
`/apps/passwordfolders` and its own occ commands `passwordfolders:*`.
The files of the official app are not touched, and an update of the official app can no longer replace this app.

Both apps share the **same database tables** (`oc_passwords_*`), which is intended: all passwords, folders, tags,
revisions, shares and the encryption stay exactly as they are.

**This means:**

1. The official app `passwords` must stay **disabled** while this app is enabled.
   Two apps working on the same tables interfere with each other (duplicate background jobs, share rows the other app does not understand).
2. The app configuration and the user settings have to be copied **once** from `passwords` to `passwordfolders`.
   Otherwise the server key (`SSEv1ServerKey`) and the user keys (`SSEv1UserKey`) are missing and existing passwords can not be decrypted.

# Requirements

| Requirement | Value                                        |
|-------------|----------------------------------------------|
| Nextcloud   | 32.0.1 - 35                                  |
| PHP         | 8.4 - 8.5, 64 bit                            |
| HTTPS       | required, the app uses WebCrypto             |
| Cron        | system cron, shares are synchronized in background |

# Installation

## 1. Backup

```
sudo -u www-data php /var/www/nextcloud/occ passwords:backup:create vor-fork-migration
mysqldump -u root -p nextcloud > ~/nextcloud-db.sql
```

## 2. Disable the official app

```
sudo -u www-data php /var/www/nextcloud/occ maintenance:mode --on
sudo -u www-data php /var/www/nextcloud/occ app:disable passwords
```

## 3. Install the app

Download the release archive and unpack it into the app folder of your instance:

```
sudo tar -xzf passwordfolders-*.tar.gz -C /var/www/nextcloud/custom_apps/
sudo chown -R www-data:www-data /var/www/nextcloud/custom_apps/passwordfolders
```

The archive already contains the compiled frontend.
If you build it yourself, see the repository documentation (`npm ci && npm run build`).

## 4. Enable the app

```
sudo -u www-data php /var/www/nextcloud/occ app:enable passwordfolders
```

The migrations run automatically and only add columns and indexes that are still missing.
If Nextcloud asks for an upgrade, run `occ upgrade`.

## 5. Copy configuration and user settings

This step is **mandatory**, see the note at the top of this page.

```sql
INSERT INTO oc_appconfig (appid, configkey, configvalue)
SELECT 'passwordfolders', configkey, configvalue FROM oc_appconfig
WHERE appid = 'passwords' AND configkey NOT IN ('enabled', 'installed_version', 'types')
ON DUPLICATE KEY UPDATE configvalue = VALUES(configvalue);

INSERT INTO oc_preferences (userid, appid, configkey, configvalue)
SELECT userid, 'passwordfolders', configkey, configvalue FROM oc_preferences WHERE appid = 'passwords'
ON DUPLICATE KEY UPDATE configvalue = VALUES(configvalue);
```

Check the result:

```
sudo -u www-data php /var/www/nextcloud/occ config:app:get passwordfolders SSEv1ServerKey
```

## 6. Leave maintenance mode

```
sudo -u www-data php /var/www/nextcloud/occ maintenance:mode --off
```

# Updates

Updates of the official app do not touch this app, but they modify the same tables.
Check after such an update that the official app is still disabled and that the app still works.

To update this app, replace the folder `custom_apps/passwordfolders` with the new release and run:

```
sudo -u www-data php /var/www/nextcloud/occ app:update passwordfolders
sudo -u www-data php /var/www/nextcloud/occ upgrade
```

# Removing the app

The data is kept, because the tables belong to the official app.
Disable this app and enable the official app again:

```
sudo -u www-data php /var/www/nextcloud/occ app:disable passwordfolders
sudo -u www-data php /var/www/nextcloud/occ app:enable passwords
```

Folder shares that were created with this app are not understood by the official app.
If you need a clean state, restore the database backup from step 1.
