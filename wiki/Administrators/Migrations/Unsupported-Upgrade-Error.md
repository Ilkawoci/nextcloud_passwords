If you haven't updated the Passwords app or are reinstalling the app after a long time, attempting to install the latest version of the app might result in an `UpgradeUnsupportedException`.
This is because the app ony supports updates from the previous major version (e.g. 2020.12.3 to 2021.1.0).

## Why?
The january version drops support for older versions of Nextcloud and PHP.
It also drops old migration scrips that we do not maintain anymore.
These migrations are not actively tested anymore and executing them all at once can cause data corruption and other issues.
Therefore, the error message actively tells you that this update isn't supported instead of letting you figure out why the app doesn't work anymore after the upgrade.



## Upgrading from old builds
To do upgrade the app correctly, you need to install the december version from the year of the version you had installed.
Then install the january version of the next year.
Then the december version of that year and then the january version of the next year again until you have updated to the latest version.

#### Example
You have 2018.9.0 installed and want to update to 2021.3.0.

- Update to 2018.12.0
- Update to 2019.1.0
- Update to 2019.12.1
- Update to 2020.1.0
- Update to 2020.12.3
- Update to 2021.1.0
- Update to 2021.3.0

#### Downloads of old versions
You can get all versions from the [app store](https://apps.nextcloud.com/apps/passwords/releases).



## Upgrading using a backup
If your version of the app already supports [backups](../Backups), you can import a backup of your data in a new version.

- Create a new backup.
- Save or export the backup file
  - If you have at least 2019.8.0, you can export the backup file
  - Otherwise locate the file in your appdata folder and save it  
- Ensure the file can be read and contains your data.
  - E.g. restore the backup on a test system
- [Delete all app data](#deleting-all-data-and-starting-over)
- Install the latest version of the app  
- Import the backup
- Restore the backup



## Deleting all data and starting over
If you don't want to update, you can delete the data and settings and start over.

- Disable and uninstall the app
- You need to delete the tables created by the app. They start with `*PREFIX*_passwords_[…]`
- You need to delete the app settings in `*PREFIX*_appconfig`. They have the appid `passwords`
- You need to delete the app settings in `*PREFIX*_preferences`. They have the appid `passwords`
- You should delete the passwords folder in the appdata folder of your Nextcloud instance

If you executed those steps correctly, the current version will install without issues.