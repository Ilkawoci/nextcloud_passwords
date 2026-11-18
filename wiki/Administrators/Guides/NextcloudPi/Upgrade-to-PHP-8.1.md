> **❗ ❗ ❗ PHP 8.1 is no longer the recommended version for Nextcloud. ❗ ❗ ❗**
>
> **We recommend upgrading to [PHP 8.3](./Upgrade-to-PHP-8.3) instead _if you're on NC 28 or newer_.**
> **For Nextcloud 26 or 27, follow the [PHP 8.2](./Upgrade-to-PHP-8.2) guide first.**

## Before you start
- This tutorial was developed for and tested with NextCloudPi 1.43.3 on a RaspberryPI.
- NextCloudPi may behave differently on other systems.
- Nextcloud 24 is required _before_ upgrading to PHP 8.1. 
- This does not work for NextCloudPi Docker. You can't upgrade PHP in a Docker image.
- _Make sure to make a backup of your entire NextCloudPi Instance (config, data, etc.) before you do this._

## Enable SSH
If you already have SSH enabled, you can skip this.

- Open the NextCloudPi management ui by opening the NextCloudPi app.
- Find the "SSH" section under "Networking" in the menu on the left
- Make sure it's active and you know the password
- Login to your NextCloudPi with SSH (e.g. `ssh pi@<server>` on linux)


## Log in as Root
After logging in, run `sudo su -s /bin/bash` to start a session as root.


## Add PHP Package Archive
You can skip this step if you already followed the [Upgrade to PHP 7.4](./Upgrade-to-PHP-7.4) or [Upgrade to PHP 8.0](./Upgrade-to-PHP-8.0) guide.
Execute the following commands on your NextCloudPi to add the repository PHP 8.1 from [deb.sury.org](https://deb.sury.org/#php-packages):

```bash
# Add the PHP PPA from deb.sury.org
apt-get -y install apt-transport-https lsb-release ca-certificates curl
curl -sSL -o /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
sh -c 'echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list'
apt-get update
```


## Install PHP 8.1
Execute the following command on your NextCloudPi to install PHP 8.1:

```bash
# Install PHP 8.1
apt-get -y install php8.1-fpm php8.1-mysql php8.1-xml php8.1-zip php8.1-mbstring php8.1-gd php8.1-curl php8.1-redis php8.1-intl php8.1-bcmath php8.1-gmp php8.1-imagick imagemagick
```


## Update the PHP 8.1 configuration
Execute the following command on your NextCloudPi to link the NextCloudPi configuration for PHP from PHP 7.3 to 8.1 and enable it:

```bash
# Enable the image magick module
phpenmod imagick

# Symlink NCP PHP configuration
ln -s /etc/php/7.3/fpm/conf.d/90-ncp.ini /etc/php/8.1/fpm/conf.d/90-ncp.ini

# Restart PHP
systemctl restart php8.1-fpm
```



## Set up Apache for PHP 8.1
Execute the following commands on your NextCloudPi to update the Apache configuration:

```bash
# Configure apache for php8.1
a2enmod proxy_fcgi setenvif
a2enconf php8.1-fpm

# Disable PHP 7.3 for apache if enabled
a2disconf php7.3-fpm
# Disable PHP 7.4 for apache if enabled.
# If you don't have PHP 7.4 installed, this will report an error which you can ignore
a2disconf php7.4-fpm

# Restart PHP
systemctl reload apache2
```

## Check for Nextcloud and App updates
It can take some time until updates for apps are shown in the appstore.

```bash
cd /var/www/nextcloud

# Install updates of the passwords app if available
sudo -u www-data php ./occ app:update passwords
```


## Check the PHP default version
By default, your NextCloudPi should now be using PHP 8.1.
You can check this by running `php -v`. The output should look like this:
```bash
root@nextcloudpi:/home/pi# php -v
PHP 8.1.0 (cli) (built: Mar  5 2021 08:38:30) ( NTS )
Copyright (c) The PHP Group
Zend Engine v4.0.3, Copyright (c) Zend Technologies
    with Zend OPcache v8.1.3, Copyright (c), by Zend Technologies
```

If it doesn't, you should use `update-alternatives --config php` and set PHP 8.1 as default:
```bash
root@nextcloudpi:/home/pi# update-alternatives --config php
There are 3 choices for the alternative php (providing /usr/bin/php).

  Selection    Path             Priority   Status
------------------------------------------------------------
* 0            /usr/bin/php8.1   81        auto mode
  1            /usr/bin/php8.0   80        manual mode
  2            /usr/bin/php8.1   81        manual mode

Press <enter> to keep the current choice[*], or type selection number:0
```

## Notes
- It can take a day before app updates show up in the apps store