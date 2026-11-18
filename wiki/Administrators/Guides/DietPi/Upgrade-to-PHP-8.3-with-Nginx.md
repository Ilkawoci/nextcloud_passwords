## Before you start
- This tutorial was developed for and tested with DietPi 9.1.1 based on Debian Bookworm on a RaspberryPI.
- This tutorial only works if you use "Nginx" as webserver.
- DietPi may behave differently on other systems.
- **Nextcloud 28 is required _before_ upgrading to PHP 8.3.**
    - If you're using DietPi based on Debian Bullseye, you should follow our previous [PHP 8.1 upgrade guide](./Upgrade-to-PHP-8.1-with-Nginx) if you haven't already since Nextcloud 28 doesn't work with PHP 7.4.
- Upgrading PHP may affect other software on your DietPi that uses PHP. Make sure it is compatible before you upgrade
- _Make sure to make a backup of your entire DietPi Instance (config, data, etc.) before you do this._



# Upgrade PHP on DietPI with Nginx
After you have read the information above and ensured you're ready to start, follow the steps below to upgrade your DietPi to PHP 8.3 with the Nginx webserver.


## Log in as Root
- If you're using SSH, log in with `ssh root@<your dietpi ip>`.
- If you're directly on the device, use `sudo su`


## Add PHP Package Archive
With the following commands you will add the PHP 8.3 repository from [deb.sury.org](https://deb.sury.org/#php-packages) to your DietPi:

> ℹ If you already followed the previous PHP 8.0, PHP 8.1 or PHP 8.2 upgrade guides, this may not be necessary.

1. Install dependencies
    ```bash
    apt-get -y install apt-transport-https lsb-release ca-certificates curl
    ```
2. Download the public key and add the repository
    ```bash
    curl -sSL -o /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
    sh -c 'echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list'
    ```
3. Update the list of available software
    ```bash
    apt-get update
    ```


## Install PHP 8.3
Now install PHP 8.3 on your DietPi with the following commands:

1. Remove conflicting packages
    ```bash
    apt-get -y remove php-apcu php-igbinary php-redis
    ```
2. Install PHP 8.3
    ```bash
    apt-get -y install php8.3-common php8.3-fpm php8.3-cli php8.3-opcache php8.3-apcu php8.3-mysql php8.3-xml php8.3-zip php8.3-mbstring php8.3-gd php8.3-curl php8.3-redis php8.3-intl php8.3-bcmath php8.3-gmp php8.3-bz2 php8.3-imagick php8.3-igbinary php8.3-readline php8.3-phpdbg libmagickwand-dev imagemagick
    ```
3. If you have installed optional php modules for specific Nextcloud apps, you need to upgrade them too.
   Here is a list for the apps mentioned in the [Nextcloud docs](https://docs.nextcloud.com/server/latest/admin_manual/installation/source_installation.html):
    - LDAP integration: `apt-get install -y php8.3-ldap`
    - External Storage with [SMB/CIFS integration](https://docs.nextcloud.com/server/latest/admin_manual/configuration_files/external_storage/smb.html): `apt-get install -y php8.3-smbclient`


## Update the PHP 8.3 configuration
#### Update the PHP FPM pool configuration
You need to copy and edit the php-fpm configuration for PHP 8.3.

1. Copy the existing configuration file from PHP 8.2 to 8.3
    ```bash
    cp /etc/php/8.2/fpm/pool.d/www.conf /etc/php/8.3/fpm/pool.d/www.conf
    ```
2. Open the configuration file with nano to edit it
   > You can use `CTRL` + `w` to search in the file, with `CRTL` + `o` you can save the changed file and with `CRTL` + `x` you can close the editor.
    ```bash
    nano /etc/php/8.3/fpm/pool.d/www.conf
    ```
3. Find the following line:
    ```bash
    listen = /run/php/php8.2-fpm.sock
    ```
   and replace it with this line:
    ```bash
    listen = /run/php/php8.3-fpm.sock
    ```
4. Now save the file with `CRTL` + `o` and exit the editor with `CRTL` + `x`


#### Enable the DietPi configuration for PHP 8.3
1. Enable the image magick module (_Errors related to PHP 8.2 can be ignored_)
    ```bash
    phpenmod imagick
    ```
2. Symlink DietPi PHP 8.2 configuration to PHP 8.3
    ```bash
    ln -s /etc/php/8.2/mods-available/dietpi-nextcloud.ini /etc/php/8.3/mods-available/dietpi-nextcloud.ini
    ln -s /etc/php/8.2/mods-available/dietpi.ini /etc/php/8.3/mods-available/dietpi.ini
    ```
3. Enable the DietPi configuration for PHP 8.3
    ```bash
    phpenmod dietpi
    phpenmod dietpi-nextcloud
    ```


#### Restart the php-fpm service
```bash
service php8.3-fpm restart
```


## Set up Nginx for PHP 8.3
Now you need to edit the configuration for nginx to instruct it to use PHP 8.3.

1. Open the configuration file with nano to edit it
   > You can use `CTRL` + `w` to search in the file, with `CRTL` + `o` you can save the changed file and with `CRTL` + `x` you can close the editor.
    ```bash
    nano /etc/nginx/nginx.conf
    ```
2. Find the following section:
    ```bash
        upstream php {
            server unix:/run/php/php8.2-fpm.sock;
        }
    ```
    Change the "server" from "unix:/run/php/php8.2-fpm.sock;" to "unix:/run/php/php8.3-fpm.sock;".
    The section should now read like this:
    ```bash
        upstream php {
            server unix:/run/php/php8.3-fpm.sock;
        }
    ```
3. Now save the file with `CRTL` + `o` and exit the editor with `CRTL` + `x`
4. Restart nginx
    ```bash
    service nginx restart
    ```


## Set the PHP command line version
By default, your DietPi should now be using PHP 8.3.
You can check this by running `php -v`. The output should look like this:
```bash
root@DietPi:~# php -v
PHP 8.3.3-1+0~20240216.17+debian12~1.gbp87e37b (cli) (built: Feb 16 2024 10:32:28) (NTS)
Copyright (c) The PHP Group
Zend Engine v4.3.3, Copyright (c) Zend Technologies
    with Zend OPcache v8.3.3-1+0~20240216.17+debian12~1.gbp87e37b, Copyright (c), by Zend Technologies
```

It's important that it shows `PHP 8.3.*`.
If it doesn't, use the command `update-alternatives --config php` to set PHP 8.3 as default.
Select the option with the path "/usr/bin/php8.3" and confirm.
```bash
root@DietPi:~# update-alternatives --config php
There are 2 choices for the alternative php (providing /usr/bin/php).

  Selection    Path             Priority   Status
------------------------------------------------------------
* 0            /usr/bin/php8.3   83        auto mode
  1            /usr/bin/php8.2   82        manual mode
  2            /usr/bin/php8.3   83        manual mode

Press <enter> to keep the current choice[*], or type selection number:0
```


## Check for Nextcloud and App updates
Check for server and app updates for your Nextcloud.

1. Check for updates
    ```bash
    ncc update:check
    ```
2. Install the passwords app if not installed
    ```bash
    ncc app:install passwords
    ```
3. Install updates of the passwords app if available
    ```bash
    ncc app:update passwords
    ```


## Check the PHP version in Nextcloud
Log into your Nextcloud with an admin account.
Go into "Administration Settings" and scroll all the way down in the left sidebar to "System".
Open the System section and scroll down to "PHP".
It should confirm you're using PHP 8.3.


## Done
Congratulations! You're done with the upgrade to PHP 8.3 now.
To verify that everything has worked, log into your Nextcloud, click on your account icon and click on "Administration settings".
There, open the "System" section and scroll down to PHP.
You should also go to the "Overview" section and take care of any warnings that show up there.

#### Notes
- It can take a day before app updates show up in the apps store



# How to Switch back to PHP 8.2
You can switch back to PHP 8.2 at any time if something does not work.

1. First, edit the configuration for nginx and instruct it to use PHP 8.2 again.
   > You can use `CTRL` + `w` to search in the file, with `CRTL` + `o` you can save the changed file and with `CRTL` + `x` you can close the editor.
    ```bash
    nano /etc/nginx/nginx.conf
    ```
2. Find the section that you changed:
    ```
        upstream php {
            server unix:/run/php/php8.3-fpm.sock;
        }
    ```
    Change the "server" from "unix:/run/php/php8.3-fpm.sock;" back to "unix:/run/php/php8.2-fpm.sock;".
    The section should now read like this:
    ```
        upstream php {
            server unix:/run/php/php8.2-fpm.sock;
        }
    ```
3. Now save the file with `CRTL` + `o` and exit the editor with `CRTL` + `x`
4. Restart nginx
    ```bash
    service nginx restart
    ```
5. Now run the command `update-alternatives --config php` to select which PHP version should be used for the command line.
    Select the option with the path "/usr/bin/php8.2" and confirm.
    ```bash
    root@DietPi:~# update-alternatives --config php
    There are 2 choices for the alternative php (providing /usr/bin/php).
    
      Selection    Path             Priority   Status
    ------------------------------------------------------------
    * 0            /usr/bin/php8.3   83        auto mode
      1            /usr/bin/php8.2   82        manual mode
      2            /usr/bin/php8.3   83        manual mode
    
    Press <enter> to keep the current choice[*], or type selection number: 1
    ```