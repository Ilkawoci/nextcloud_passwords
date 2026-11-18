## Before you start
- This tutorial was developed for and tested with NextCloudPi 1.53.1 on a RaspberryPI.
- NextCloudPi may behave differently on other systems.
- Nextcloud 28 is required _before_ upgrading to PHP 8.3. 
- This does not work for NextCloudPi Docker. You can't upgrade PHP in a Docker image.
- _Make sure to make a backup of your entire NextCloudPi Instance (config, data, etc.) before you do this._

## Enable SSH
If you already have SSH enabled, you can skip this.

- Open the NextCloudPi management ui by opening the NextCloudPi app.
- Find the "SSH" section under "Networking" in the menu on the left
- Make sure it's active and you know the password
- Login to your NextCloudPi with SSH (e.g. `ssh pi@<your server>` or `ssh ncpadmin@<your server>` on linux)


## Log in as Root
After logging in, start a session as the root user.
```bash
sudo su -s /bin/bash
```


## Install updates
Install the latest system updates on your NextcloudPi using the following command:

```bash
ncp-update
```

> ℹ Installing updates can take a while, this is expected.


## Add PHP Package Archive
> ℹ You can skip this step if you already followed one of our previous guides.

Execute the following commands on your NextCloudPi to add the repository PHP 8.3 from [deb.sury.org](https://deb.sury.org/#php-packages):

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
Execute the following command to install PHP 8.3 and all required packages:

1. Install PHP 8.3
    ```bash
    # Install PHP 8.3
    apt-get -y install php8.3-fpm php8.3-mysql php8.3-xml php8.3-zip php8.3-mbstring php8.3-gd php8.3-curl php8.3-redis php8.3-intl php8.3-bcmath php8.3-gmp php8.3-imagick imagemagick
    ```
2. If you have installed optional php modules for specific Nextcloud apps, you need to upgrade them too.
   Here is a list for the apps mentioned in the [Nextcloud docs](https://docs.nextcloud.com/server/latest/admin_manual/installation/source_installation.html):
    - LDAP integration: `apt-get install -y php8.3-ldap`
    - External Storage with [SMB/CIFS integration](https://docs.nextcloud.com/server/latest/admin_manual/configuration_files/external_storage/smb.html): `apt-get install -y php8.3-smbclient`


## Update the PHP 8.3 configuration
Execute the following command on your NextCloudPi to link the NextCloudPi configuration for PHP from PHP 8.1 to 8.3 and enable it:

```bash
# Enable the image magick module
# You can ignore the "Module imagick ini file doesn't exist under /etc/php/8.1/mods-available" error,
# as this error only means the module isn't be available on the old PHP version.
phpenmod imagick

# Symlink NCP PHP configuration
ln -s /etc/php/8.1/fpm/conf.d/90-ncp.ini /etc/php/8.3/fpm/conf.d/90-ncp.ini

# Restart PHP
systemctl restart php8.3-fpm
```



## Set up Apache for PHP 8.3
Execute the following commands on your NextCloudPi to update the Apache configuration:

```bash
# Configure apache for php8.3
a2enmod proxy_fcgi setenvif
a2enconf php8.3-fpm

# Disable PHP 8.1 for apache if enabled
a2disconf php8.1-fpm

# Restart PHP
systemctl reload apache2
```

## Check for Nextcloud and App updates
It can take some time until updates for apps are shown in the appstore.

```bash
cd /var/www/nextcloud
```

If you don't have the passwords app installed, this command should do that now:
```bash
sudo -u www-data php ./occ app:install passwords
```

If you have the app installed, check for and install any updates:
```bash
sudo -u www-data php ./occ app:update passwords
```


## Check the PHP version in Nextcloud
Log into your Nextcloud with an admin account.
If you have the `nextcloudpi.local` domain set up, you can click this link: [https://192.168.178.53/index.php/settings/admin/serverinfo](https://192.168.178.53/index.php/settings/admin/serverinfo).
Otherwise, go into "Administration Settings" and scroll all the way down in the left sidebar to "System".

Open the System section and scroll down to "PHP".
It should confirm you're using PHP 8.3.


## Check the PHP default version
By default, your NextCloudPi should now be using PHP 8.3.
You can check this by running `php -v`. The output should look like this:
```bash
root@nextcloudpi:/home/pi# php -v
PHP 8.3.3-1+0~20240216.17+debian11~1.gbp87e37b (cli) (built: Feb 16 2024 10:33:07) (NTS)
Copyright (c) The PHP Group
Zend Engine v4.3.3, Copyright (c) Zend Technologies
    with Zend OPcache v8.3.3-1+0~20240216.17+debian11~1.gbp87e37b, Copyright (c), by Zend Technologies
```

If it doesn't, you should use `update-alternatives --config php` and set PHP 8.3 as default:
```bash
root@nextcloudpi:/home/pi# update-alternatives --config php
There are 3 choices for the alternative php (providing /usr/bin/php).

  Selection    Path             Priority   Status
------------------------------------------------------------
* 0            /usr/bin/php8.3   83        auto mode
  1            /usr/bin/php8.1   81        manual mode
  2            /usr/bin/php8.3   83        manual mode

Press <enter> to keep the current choice[*], or type selection number:0
```

## Notes
- It can take a day before app updates show up in the apps store


# How to Switch back to PHP 8.1
You can switch back to PHP 8.1 at any time if something does not work.

1. First, switch back to the PHP 8.1 configuration for Apache:
    ```bash
    a2disconf php8.3-fpm
    a2enconf php8.1-fpm
    ```
2. Then restart apache:
    ```bash
    systemctl restart apache2
    ```
3. Now run the command `update-alternatives --config php` to select which PHP version should be used for the command line.
   Select the option with the path "/usr/bin/php8.1" and confirm.
    ```bash
    root@DietPi:~# update-alternatives --config php
    There are 2 choices for the alternative php (providing /usr/bin/php).
    
      Selection    Path             Priority   Status
    ------------------------------------------------------------
    * 0            /usr/bin/php8.3   83        auto mode
      1            /usr/bin/php8.1   81        manual mode
      2            /usr/bin/php8.3   83        manual mode
    
    Press <enter> to keep the current choice[*], or type selection number: 1
    ```