If you are attempting to install a release of the passwords app which requires a newer version of PHP or Nextcloud than in use on your server an exception prevents the upgrade.

# Questions

### But i have the right PHP version installed

Please note that PHP versions differ between the command line, the webserver and even different domains on your webserver.
Run `php -v` as the the user executing cron command on your server to see which version of php is in use there.
Look at the "System" section in the Nextcloud admin area to see which version of PHP is used for your webserver.

### Is it safe to upgrade Nextcloud?

Nextcloud 20.0.4 and before do not check PHP version requirements.
They will install the latest available version of the app during the upgrade even if it does not work.
This will leave your Nextcloud stuck in maintenance mode.
You need to fulfill the PHP minimum requirement before updating Nextcloud.
Nextcloud 21 and later seem to be not affected by this.

# Compatible versions

**NOTE:** The releases listed here are outdated and do not receive any support anymore!

**NOTE:** Only versions where your PHP version _and_ your Nextcloud version are listed will work on your server.

| PHP Version | Nextcloud Version | App Version | Download                                                                                                        |
|-------------|-------------------|-------------|-----------------------------------------------------------------------------------------------------------------|
| 7.1         | 12, 13, 14        | 2018.12.0   | [download](https://git.mdns.eu/nextcloud-passwords/passwords/-/jobs/7245/artifacts/raw/passwords.tar.gz)        |
| 7.1         | 15, 16, 17        | 2019.12.1   | [download](https://git.mdns.eu/nextcloud-passwords/passwords/-/jobs/9150/artifacts/raw/passwords.tar.gz)        |
| 7.2         | 13, 14            | 2018.12.0   | [download](https://git.mdns.eu/nextcloud-passwords/passwords/-/jobs/7245/artifacts/raw/passwords.tar.gz)        |
| 7.2         | 15, 16            | 2019.12.1   | [download](https://git.mdns.eu/nextcloud-passwords/passwords/-/jobs/9150/artifacts/raw/passwords.tar.gz)        |
| 7.2         | 17, 18, 19        | 2020.12.4   | [download](https://git.mdns.eu/nextcloud-passwords/passwords/-/jobs/12403/artifacts/raw/passwords.tar.gz)       |
| 7.2         | 20                | 2021.12.10  | [download](https://git.mdns.eu/nextcloud-passwords/passwords/-/jobs/13229/artifacts/raw/passwords-lsr.tar.gz)   |
| 7.3         | 15, 16            | 2019.12.1   | [download](https://git.mdns.eu/nextcloud-passwords/passwords/-/jobs/9150/artifacts/raw/passwords.tar.gz)        |
| 7.3         | 17, 18, 19        | 2020.12.4   | [download](https://git.mdns.eu/nextcloud-passwords/passwords/-/jobs/12403/artifacts/raw/passwords.tar.gz)       |
| 7.3         | 20, 21, 22        | 2021.12.10  | [download](https://git.mdns.eu/nextcloud-passwords/passwords/-/jobs/13229/artifacts/raw/passwords-lsr.tar.gz)   |
| 7.3         | 23                | 2022.12.11  | [download](https://git.mdns.eu/nextcloud-passwords/passwords/-/jobs/14833/artifacts/raw/passwords-lsr.tar.gz)   |
| 7.4         | 17, 18, 19        | 2020.12.4   | [download](https://git.mdns.eu/nextcloud-passwords/passwords/-/jobs/12403/artifacts/raw/passwords.tar.gz)       |
| 7.4         | 20, 21, 22        | 2021.12.10  | [download](https://git.mdns.eu/nextcloud-passwords/passwords/-/jobs/13229/artifacts/raw/passwords-lsr.tar.gz)   |
| 7.4         | 23, 24            | 2022.12.11  | [download](https://git.mdns.eu/nextcloud-passwords/passwords/-/jobs/14833/artifacts/raw/passwords-lsr.tar.gz)   |
| 7.4         | 25                | 2023.12.12  | [download](https://git.mdns.eu/api/v4/projects/45/packages/generic/passwords/2023.12.2/passwords-lsr-74.tar.gz) |
| 8.0         | 21, 22            | 2021.12.10  | [download](https://git.mdns.eu/nextcloud-passwords/passwords/-/jobs/13229/artifacts/raw/passwords-lsr.tar.gz)   |
| 8.0         | 23, 24            | 2022.12.11  | [download](https://git.mdns.eu/nextcloud-passwords/passwords/-/jobs/14833/artifacts/raw/passwords-lsr.tar.gz)   |
| 8.0         | 25, 26, 27        | 2023.12.22  | [download](https://git.mdns.eu/api/v4/projects/45/packages/generic/passwords/2023.12.2/passwords-lsr-80.tar.gz) |
| 8.0         | 28, 29            | 2024.12.11  | [download](https://git.mdns.eu/api/v4/projects/45/packages/generic/passwords/2024.12.1/passwords-lsr-80.tar.gz) |
| 8.1         | 24                | 2022.12.11  | [download](https://git.mdns.eu/nextcloud-passwords/passwords/-/jobs/14833/artifacts/raw/passwords-lsr.tar.gz)   |
| 8.1         | 25, 26, 27        | 2023.12.22  | [download](https://git.mdns.eu/api/v4/projects/45/packages/generic/passwords/2023.12.2/passwords-lsr-80.tar.gz) |
| 8.1         | 28, 29            | 2024.12.11  | [download](https://git.mdns.eu/api/v4/projects/45/packages/generic/passwords/2024.12.1/passwords-lsr-80.tar.gz) |
| 8.2         | 26, 27            | 2023.12.22  | [download](https://git.mdns.eu/api/v4/projects/45/packages/generic/passwords/2023.12.2/passwords-lsr-80.tar.gz) |
| 8.2         | 28, 29            | 2024.12.11  | [download](https://git.mdns.eu/api/v4/projects/45/packages/generic/passwords/2024.12.1/passwords-lsr-80.tar.gz) |
| 8.3         | 28, 29            | 2024.12.11  | [download](https://git.mdns.eu/api/v4/projects/45/packages/generic/passwords/2024.12.1/passwords-lsr-80.tar.gz) |

In case this list is incomplete and does not list your version of Nextcloud, you can check the list in the [official appstore](https://apps.nextcloud.com/apps/passwords/releases) for a
compatible version.

# Downgrade the app

If you have already attempted to upgrade to an incompatible version of the app, you can still downgrade to the latest compatible version manually.
You need command line access or file access to the installation folder of your Nextcloud for this

### Via the command line

##### Determine your "currently installed" version

1. Open the base folder of your Nextcloud installation
2. Run `php ./occ config:app:get passwords installed_version`
3. Continue with the correct steps based on the version number

##### If the result is the previous version number

Example: You tried to upgrade _from_ 2020.12.1 and the result of the previous command _is_ 2020.12.1.

1. Open the apps or custom_apps folder where the passwords app is installed
2. Remove the current version with `rm -rf passwords`
3. Download the supported version for your server with `wget <copy link of compatible version listed above>`
4. Unpack the release with `tar -xf passwords.tar.gz`
5. Open the base folder of your Nextcloud installation
6. Run `php ./occ maintenance:repair`
7. Run `php ./occ maintenance:mode` to see if your Nextcloud is still in maintenance mode
8. Run `php ./occ maintenance:mode --off` if your Nextcloud is stuck in maintenance mode

##### The result is the version number of the upgrade

Example: You tried to upgrade from 2020.12.1 _to_ 2021.1.0 and the result of the previous command _is_ 2021.1.0.

1. Open the apps or custom_apps folder where the passwords app is installed
2. Remove the current version with `rm -rf passwords`
3. Download the supported version for your server with `wget <copy link of compatible version listed above>`
4. Unpack the release with `tar -xf passwords.tar.gz`
5. Open the base folder of your Nextcloud installation
6. Run `php ./occ config:app:set passwords installed_version --version 2020.12.1`
7. Run `php ./occ maintenance:repair`
8. Run `php ./occ maintenance:mode` to see if your Nextcloud is still in maintenance mode
9. Run `php ./occ maintenance:mode --off` if your Nextcloud is stuck in maintenance mode

### Via file access

##### Determine your "currently installed" version

- Is your Nextcloud stuck in maintenance mode and no app can be opened?
    - **yes:** Choose option 1
    - **no:** Choose option 2

##### Option 1: Recover from a failed upgrade

1. Open the apps or custom_apps folder where the passwords app is installed
2. Remove the "passwords" folder an all its contents
3. Download the supported version for your server from the lists above
4. Unpack it on your PC with `tar -xf passwords.tar.gz` or [7Zip](https://7-zip.org/)
5. Make sure that the unpacked passwords folder contains the app files and not a second passwords folder
6. Upload the unpacked passwords folder to your apps or custom apps folder
7. If your Nextcloud is stuck in maintenance mode, remove the maintenance flag manually.
    - Open the `config` folder in the root folder of your Nextcloud installation and download the `config.php`.
    - Edit the file in a text editor and remove the line `"maintenance" => true,`.
    - Upload the changed file
8. If your Nextcloud still requires an upgrade, you might have to overwrite the app version in the database.
    - Open the `<PREFIX>_appconfig` table and look for the entry where the appid is `passwords` and the configkey is `installed_version`.
    - Set the value to the version you just downloaded and installed
    - Example: ```UPDATE `<PREFIX>_appconfig` SET configvalue = '2020.12.1' WHERE `appid` = 'passwords' AND `configkey` = 'installed_version'```

##### Option 2: Downgrade the app

This option expects the app [occ web](https://apps.nextcloud.com/apps/occweb) to be installed.

1. Run `occ config:app:set passwords installed_version --version 2020.12.1` in Nextcloud with occ web
2. Open the apps or custom_apps folder of your Nextcloud installation where the passwords app is installed
3. Remove the "passwords" folder an all its contents
4. Download the supported version for your server from the lists above
5. Unpack it on your PC with `tar -xf passwords.tar.gz` or [7Zip](https://7-zip.org/)
6. Make sure that the unpacked passwords folder contains the app files and not a second passwords folder
7. Upload the unpacked passwords folder to your apps or custom apps folder
8. Check the passwords app in Nextcloud