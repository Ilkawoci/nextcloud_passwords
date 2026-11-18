Passwords creates a full database backup on a regular schedule.
These backups contain all data, keys and settings stored on the server.
The backup settings can be changed on the [admin settings page](./Administrative-Settings#Backup-Settings).

**Note:** Backups do not include tokens for third party apps.

**Note:** Using backups requires command line access. But if your version of passwords supports client side encryption you can use [occweb](https://apps.nextcloud.com/apps/occweb).

## Backup Compatibility
The list below contains all versions of Passwords that introduced new changes to the backup file format.
While it is possible to read older backups on newer versions of the app, it not possible to read newer backups on older versions of the app.

| App Version | Backup Version                    |
|-------------|-----------------------------------|
| 2018.11.0   | 100, 101                          |
| 2019.1.0    | 100, 101, 102                     |
| 2019.4.0    | 100, 101, 102, 103                |
| 2019.6.0    | 100, 101, 102, 103, 104           |
| 2019.7.0    | 100, 101, 102, 103, 104, 105      |
| 2021.1.0    | 100, 101, 102, 103, 104, 105, 106 |

**Note:** Backups will be gzipped automatically if your system supports it.
Restoring gzipped backups on a system without the PHP-Gzip extension will not work.


## Backup Security
Backups contain a complete snapshot of the raw data in the database.
This includes the encrypted entities and their encryption keys.
Backups are not encrypted themselves and can be restored on any Nextcloud system.
Therefore you should make sure that their storage location is protected from public access.
You can do so by blocking all access to your data directory via the web or just keeping your data directory outside your webroot at all.


## Creating Backups
There are two ways of creating backups.
Passwords will create backups automatically according to the app [settings](./Administrative-Settings#Backup-Settings).
You can also crate backups manually by running the cli command `./occ passwordfolders:backup:create` in your Nextcloud directory.
The command will output the name of the backup, the file size and whether it is plain json or gzip compressed json.
You can also specify the name of the backup by adding it to the command `./occ passwordfolders:backup:create <backup name>`.


## Listing Backups
The cli command `./occ passwordfolders:backup:list` will list all backups with their name, file size and file format.


## Restoring Backups
Backups can be restored with the command `./occ passwordfolders:backup:restore <backup name>`.
You can choose which data should be restored with the following options for the command.

**Note:** Restoring user data from backups will erase the current user data.

**Note:** Restoring data from backups will also restore things like expired shared passwords. Run your cron jobs after restoring a backup.

* `--user[=USER]` Accepts an user id and will restore data only for this user. If you use user backends like LDAP, the user id is not the login name.
* `--no-data` Will not restore user data and encryption keys.
* `--no-user-settings` Will not restore user settings and user client settings.
* `--no-app-settings` Will not restore application settings. This is set automatically if you use the `--user` option.
* `--no-interaction` Will skip interactive parts like the confirmation before restoring the backup.

If you're trying to restore a backup from a bricked Nextcloud instance, take a look at the [Server Migration Guide](./Guides/Maintenance/Server-Migration) for possible options.


## Exporting & Importing Backups
The command `./occ passwordfolders:backup:export <backup name> [<file>]` can be used to export backup files to another location.
The `<file>` parameter is optional and can be a file or directory.

The command `./occ passwordfolders:backup:import <file>` can be used to import a backup into the list of user created backups.
The command will not restore backups.

## Accessing Backup Files
The backup files are stored in the data directory of your Nextcloud instance.
You should be able to access the backup directory with the following command:

```
DATA_DIR="$(./occ config:system:get datadirectory)";
INSTANCE_ID="$(./occ config:system:get instanceid)";

# Automated backups
cd ${DATA_DIR}/appdata_${INSTANCE_ID}/passwords/autoBackups;

# Manual backups
cd ${DATA_DIR}/appdata_${INSTANCE_ID}/passwords/backups;
```

If you choose to add a backup file, you will have to rescan the app data directory afterwards to make sure it shows up in the backup list:

```
./occ files:scan-app-data
```

**Note:** We do absolutely _not_ recommend messing with the backup files.