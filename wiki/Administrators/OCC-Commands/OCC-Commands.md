# Backup Commands
Commands related to managing backups of the passwords app database.

## passwordfolders:backup:create
This command creates a manual backup of the complete password database.
Unlike automatic backups, manual backups are not deleted automatically.

| Parameter | Type   | Required? | Description                                                                                  |
|-----------|--------|-----------|----------------------------------------------------------------------------------------------|
| `<name>`  | string | no        | The name of the backup. ASCII letters only. If none is given, the current date will be used. |


## passwordfolders:backup:delete
This command deletes a manually created backup.

| Parameter | Type   | Required? | Description                       |
|-----------|--------|-----------|-----------------------------------|
| `<name>`  | string | yes       | The name of the backup to delete. |


## passwordfolders:backup:restore
This command restores the given backup.

| Parameter             | Type   | Required? | Description                                                                    |
|-----------------------|--------|-----------|--------------------------------------------------------------------------------|
| `<name>`              | string | yes       | The name of the backup to restore.                                             |
| `--user`, `-u`        | string | no        | Restore data only for the given user id.                                       |
| `--no-data`           | -      | no        | Do not restore user data and encryption keys. This will only restore settings. |
| `--no-user-settings`  | -      | no        | Do not restore user settings.                                                  |
| `--no-app-settings`   | -      | no        | Do not restore app settings.                                                   |
| `--no-interaction`    | -      | no        | Disables all questions and assumes "yes" for every confirmation                |

## passwordfolders:backup:list
This command prints a list of all available backups.

| Parameter         | Type | Required? | Description                                                           |
|-------------------|------|-----------|-----------------------------------------------------------------------|
| `--details`, `-d` | -    | no        | List additional details such as the version and user and item counts. |

## passwordfolders:backup:export
This command exports a given backup to a file.

| Parameter             | Type   | Required? | Description                                                                                               |
|-----------------------|--------|-----------|-----------------------------------------------------------------------------------------------------------|
| `<name>`              | string | yes       | The name of the backup to export.                                                                         |
| `<file>`              | string | no        | Path of the file to export the backup to. Otherwise the backup will be exported to the current directory. |

## passwordfolders:backup:import
This command imports the given file as a manual backup and validates it.

| Parameter             | Type   | Required? | Description                     |
|-----------------------|--------|-----------|---------------------------------|
| `<file>`              | string | yes       | The path to the file to import. |



# Users
## passwordfolders:user:delete
Deletes the passwords app data of a user.
The command will also delete the data of any deleted user and remove the lock on the user id.
After the user data has been deleted, the user id can be assigned to a new user.

| Parameter          | Type   | Required? | Description                                                     |
|--------------------|--------|-----------|-----------------------------------------------------------------|
| `<user>`           | string | yes       | The id of the user.                                             |
| `--no-interaction` | -      | no        | Disables all questions and assumes "yes" for every confirmation |

## passwordfolders:user:move
Moves all data from one user account to another.
If the target user already has data, you will be given the option to delete that data before moving or to abort the process.

| Parameter          | Type   | Required? | Description                                                     |
|--------------------|--------|-----------|-----------------------------------------------------------------|
| `<source_user>`    | string | yes       | The id of the user whose data should be moved.                  |
| `<target_user>`    | string | yes       | The id of the user which the data should be moved to.           |
| `--no-interaction` | -      | no        | Disables all questions and assumes "yes" for every confirmation |


# Debugging
## passwordfolders:system:report
Print system information as detected by the app as JSON.
This is mostly helpful for debugging reasons.
With `./occ passwordfolders:system:report debug`, you get the report you are supposed to attach if you're submitting a bug report.

| Parameter       | Type            | Required? | Description                                                    |
|-----------------|-----------------|-----------|----------------------------------------------------------------|
| `<sections>`    | string/string[] | no        | Print a report with the given sections.                        |
| `--basic`, `-b` | -               | no        | Print just a basic report. This will limit available sections. |

#### Sections
| Section     | Description                                                                  |
|-------------|------------------------------------------------------------------------------|
| debug       | Shortcut for "version, environment, services, status, settings, encryption". |
| version     | App, Nextcloud and PHP version information.                                  |
| environment | Server environment information, e.g. OS, database or webserver setup.        |
| services    | Services configured for the passwords app.                                   |
| settings    | The values of some passwords app settings.                                   |
| status      | Internal status information, e.g. for updates and migrations.                |
| apps        | List of selected other apps installed on the server.                         |
| sharing     | Approximate count of shared items.                                           |
| encryption  | Information about encryption methods used.                                   |


# Other
## passwordfolders:pwned-list:process
Takes the Pwned Passwords list from [haveibeenpwned.com](https://haveibeenpwned.com/Passwords) (or any list with the same structure) and converts it for the local database password security check service.
[More details to use this command can be found in our guide](../Guides/Services/Generating-the-password-database)


| Parameter        | Type   | Required? | Description                                                                                                                                                                                                                                  |
|------------------|--------|-----------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `<file>`         | string | no        | The location of the pwnedpasswords.txt file. Defaults to `pwnedpasswords.txt`                                                                                                                                                                |
| `--size`, `-s`   | number | no        | The amount of hashes to process in millions. E.g. "25". Defaults to 25.                                                                                                                                                                      |
| `--mode`, `-m`   | string | no        | The mode for processing the hashes. `import` to import the m directly, `json` to pack it as JSON files, `gzip` to pack it as compressed files or `file` to pack it in whichever variant is used by the current server. Defaults to `import`. |