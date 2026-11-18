## Compiling the Pwned Passwords database locally
The app offers an option to generate the password database from the source files provided by Hibp?.
In order to process the file, your server should have at least have ~100MiB free RAM per 1 million hashes and around 60 GiB of disk space.
Even if you used the import option, the app may download the database again if the cache is cleared or a new version is released.

#### Compiling the database
1. Install the [haveibeenpwned-downloader](https://github.com/HaveIBeenPwned/PwnedPasswordsDownloader)
2. Download all hashes into one file with `haveibeenpwned-downloader pwnedpasswords`.
3. Place the file on the server and ensure it is readable by the webserver user.
4. Log into the command line of your server and navigate to the root folder of your Nextcloud installation.
5. Now run the command `php ./occ passwordfolders:pwned-list:process --size <size> --mode=file <file>` where `<file>` is the location to your file and `<size>` is the number of passwords to import in millions (e.g. 25).
   The option `--mode=file` will automatically import the resulting file into the passwords app.
   If you just want to import the hashes directly, use `--mode=import` to do so and not generate a file.
   When choosing the `<size>`, be aware that a greater size will also require more RAM.

##### Configuring the database url
If you have created an update file, place it on a server that is accessible via https by your Nextcloud server.
The url used by the app to download can be configured as an app setting. It should contain the placeholders `:format` for the format of the database ("json" or "gzip") and `:version` for the version of the database used by the app.
Make sure that the file type you generated before is the one required by the server.
Use the commands below to update the download url and reset the locally installed version to trigger an update.

```bash
./occ config:app:set passwords passwords/localdb/source --value="https://yourdomain/yourpath/<size>m-v:version-:format.zip";
./occ config:app:set passwords passwords/localdb/version --value="0";
```