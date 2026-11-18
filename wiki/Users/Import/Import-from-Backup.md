This guide explains the import from a Passwords database backup which can be created with the export.

> :thumbsup: The Passwords database backup can also be used to move from one Nextcloud instance to another.

## Export from Passwords
1. Open the Passwords app.
2. Click on the "🗃" icon in the bottom left corner to open "Backup and Restore".
3. Choose "[Backup or export](web+passlink://goto/backup/export)".
4. Choose export options as wanted.
5. Click the "Export" button.
6. Wait for the export to finish, then click the "Download JSON" button.
7. Save the file on your computer

## Import into Passwords
1. Open the Passwords app.
2. Click on the "🗃" icon in the bottom left corner to open "Backup and Restore".
3. Choose "[Restore or import](web+passlink://goto/backup/import)".
4. Select "Database Backup" as import format.
5. Open the file you exported before using the file dialog.
6. Choose import options as wanted.
7. Click the "Import" button.
8. After the import has finished, check the imported passwords.
9. Delete the export file from your computer.


## Import Options

> :star: This section describes only describes the import options which are specific to the database import.
> For all other options please see [the general import options](../Import#Import-Options).

#### Backup password
If the backup has been secured with a password, you will have to enter it here.


## Troubleshooting
See [import troubleshooting](../Import#Troubleshooting).