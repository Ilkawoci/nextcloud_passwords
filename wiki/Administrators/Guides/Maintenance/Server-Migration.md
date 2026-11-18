This guide explains how to migrate the passwords database to a new server.

__Warning:__ 
Restoring a backup deletes the existing data therefore it is not possible to restore multiple backups to one server.
It will not be possible to restore previous data unless you have a backup of that data.
In versions prior to 2025.7.0, the backup restore will overwrite the server secret which may break other apps using encryption.

## With CLI/SSH Access
1. Log onto your old server and open the Nextcloud directory.
2. Create a new backup
    ```
    ./occ passwordfolders:backup:create migration
    ```
3. Export the backup file
    ```
    ./occ passwordfolders:backup:export migration
    ```
4. Copy the file from the old server to the new server
    ```
    scp ./migration.json.gz user@newserver.org:/path/to/new/nextcloud/
    ```
    If the file `migration.json.gz` does not exist, your server probably does not support compression and you can try the following:
    ```
    scp ./migration.json user@newserver.org:/path/to/new/nextcloud/
    ```
5. Now log onto your new server and open the Nextcloud directory
6. Import the backup file (use the file name from step 5)
    ```
    ./occ passwordfolders:backup:import migration.json.gz
    ```
7. Restore the backup file with
    ```
    ./occ passwordfolders:backup:restore migration
    ```
    
## With HTTPS/FTP Access
1. Install [OCC Web](https://apps.nextcloud.com/apps/occweb) on both your Nextclouds
2. Open OCC Web in your old Nextcloud
3. Create a new backup
    ```
    passwordfolders:backup:create migration
    ```
4. Export the backup file
    ```
    passwordfolders:backup:export migration
    ```
5. Open your old server with FTP and open the Nextcloud folder.
6. Download the `migration.json.gz` file (or `migration.json` if your server does not support compression)
7. Open your new server with FTP and open the Nextcloud folder.
8. Upload the `migration.json.gz` file
9. Open OCC Web on the new Server
10. Import the backup file
    ```
    passwordfolders:backup:import migration.json.gz
    ```
11. Restore the backup file in OCC Web with
    ```
    passwordfolders:backup:restore migration
    ```
    
## Troubleshooting

#### My old server supports compression but the new server does not
Just use `gunzip migration.json.gz` or [7Zip](https://7-zip.org/) to unzip the file.

#### I don't have an export/import command
Upgrade to 2019.8.0.

#### I have no SSH/SFTP/FTP access
If you have access to your database, then you can dump all passwords tables (`passwords_*`). 
Also dump all entries from `appconfig` where the `appid` is "passwords".
Then dump all entries from `preferences` where the `appid` is "passwords".
Export the server secret with OCC Web (`config:system:get passwords.secret` or `config:system:get secret`) or read it from the config.php.
Restore all the tables and the secret as `passwords.secret` on your new Server.

#### I have no HTTPS/SFTP/FTP access
If you still have SSH, you can try to move the backups to a new Nextcloud instance with the following command:

```bash
curl -T 'data/<appdata_dir>/passwords/autoBackups/<some_file>' 'https://<new_cloud_domain>/remote.php/dav/files/<new_user>/' --user '<new_user>:<password>'
```

#### I used the export in the app but the server won't import it
That's because this is not how this works.
If you get the error `The file does not contain a valid server backup` or
`This seems to be a client backup. It can only be restored using the web interface`
then you are likely trying to import a backup which was not designed for the server backup.
If you have a backup from the app, then use the app to import it again.
These backups (client backups) are also only for a single user.
Use the guides above to create and export backups properly.

#### I get the error `Unsupported backup version`
Upgrade the app to the latest version.
Downgrading is not supported by backups because it does not work.

#### I have a database dump but not the server secret
You can still try to restore passwords that were encrypted with SSEv1r1.
All other passwords will be lost.
Try this [forum post](https://help.nextcloud.com/t/passwords-error-exception-hmac-does-not-match/69972/4?u=mdw).