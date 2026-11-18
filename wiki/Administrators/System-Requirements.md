This page is about the minimum system requirements for
the [Passwords app for Nextcloud](https://apps.nextcloud.com/apps/passwords).
We also list the minimum system requirements for the latest version of Nextcloud since Google keeps recommending this
site instead of the official system requirements for Nextcloud in their wiki.

|                | [Nextcloud 35](https://docs.nextcloud.com/server/latest/admin_manual/installation/system_requirements.html)                                                                                                                     | [Passwords App](https://passwordsapp.org) | Recommended Specifications                                                                    |
|----------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|-------------------------------------------|-----------------------------------------------------------------------------------------------|
| PHP            | **8.5**, 8.4, 8.3                                                                                                                                                                                                               | 8.5, **8.4**                              | PHP 8.4                                                                                       |
| Database       | MariaDB 12.3/**11.8**/11.4/10.11, MySQL 8.4/9.7, PostgreSQL 14/15/16/17/**18**, SQLite 3.24+, _Oracle Database 19c/21c/23ai (NC enterprise)_                                                                                    | **MariaDB** / MySQL / PostgreSQL / Sqlite | MariaDB 11.8                                                                                  |
| OS             | **Ubuntu 26.04 LTS**/24.04 LTS, **Red Hat Enterprise Linux 10**/9, Debian 13/12, SUSE Linux Enterprise Server 16/15 SP6, openSUSE Leap 16, CentOS Stream, Alpine Linux                                                          | Linux                                     | Ubuntu 26.04                                                                                  |
| RAM            | 512Mib                                                                                                                                                                                                                          | 512 MiB                                   | 8 GiB                                                                                         |
| Disk           | 256Mib                                                                                                                                                                                                                          | 2 GiB                                     | 64 GiB                                                                                        |
| Processor      | 64-bit CPU                                                                                                                                                                                                                      | 4 Core CPU                                | "AMD Ryzen 3 1200" or better                                                                  |
| Nextcloud      | 35                                                                                                                                                                                                                              | 35, **34**, 33, >=32.0.1                  | 34                                                                                            |
| Webserver      | **Apache 2.4 with mod_php or php-fpm**, nginx with php-fpm                                                                                                                                                                      | HTTPS                                     | nginx 1.29.5 with php-fpm & HTTPS                                                             |
| PHP Extensions | bz2, ctype, curl, dom, fileinfo, GD, JSON, libxml, mbstring, openssl, posix, session, SimpleXML, XMLReader, XMLWriter, zip, zlib, pdo_sqlite, pdo_mysql, pdo_pgsql, filter (only on Mageia and FreeBSD), hash (only on FreeBSD) | intl                                      | intl, zip, imagick                                                                            |
| Libraries      | libxml2 >=2.7.0                                                                                                                                                                                                                 |                                           | imagemagick, librsvg, wbritish, wamerican, wngerman, wfrench, wspanish, witalian, wportuguese |

** Recommendations are in **bold**

### LSR/Legacy Support Releases

Legacy support releases are provided for servers which run a PHP version that is not supported by the passwords app, but
by any version of Nextcloud that is supported.
LSR builds are generated by an automated process during the release which rewrites the source code of the app to work
with an older PHP language level.
This means that the source code of LSR builds does not match the source code in our repositories and the build is also
not tested before the release.
We can only provide LSR builds as long as the automatic conversion is possible so LSR builds may end without any notice
if that is not possible.

|           | LSR                                       | Recommended Specifications                                                                    |
|-----------|-------------------------------------------|-----------------------------------------------------------------------------------------------|
| PHP       | 8.5, **8.4**, 8.3, 8.2, 8.1               | 8.4                                                                                           |
| Database  | **MariaDB** / MySQL / PostgreSQL / Sqlite | MariaDB 11.8                                                                                  |
| OS        | Linux                                     | Ubuntu 26.04                                                                                  |
| Processor | 4 Core CPU                                | "AMD Ryzen 3 1200" or better                                                                  |
| RAM       | 512 MiB                                   | 8 GiB                                                                                         |
| Disk      | 2 GiB                                     | 64 GiB                                                                                        |
| Nextcloud | 35, **34**, 33, 32                        | 34                                                                                            |
| Webserver | HTTPS                                     | Nginx 1.29.5 HTTPS                                                                            |
| PECL      | intl                                      | intl, zip, imagick                                                                            |
| Libraries |                                           | imagemagick, librsvg, wbritish, wamerican, wngerman, wfrench, wspanish, witalian, wportuguese |

Since systems using LSR builds do not meet the minimum requirements, no official support is provided by us.
If you experience any issues, visit the forum or the chat groups for help.

### Version scheme and breaking changes

Passwords versions are simply the year and month of the release of the version.
The exact version scheme is `<year>.<month>.<bugfix>`.
In accordance with [SemVer2.0.0](https://semver.org/spec/v2.0.0.html), only the first version of a new year will
introduce breaks in backward compatibility.
With the first release of the year, the support for any Nextcloud version but the latest as well as any PHP version
without [active support](https://php.net/supported-versions.php) will be ended.

### HTTPS requirement

Passwords requires HTTPS to work. This can not be disabled.
Please take a look at
the [Nextcloud documentation](https://docs.nextcloud.com/server/latest/admin_manual/installation/harden_server.html#use-https-label)
to configure your server correctly.
If you use a proxy or separate ssl termination
point, [configure your Nextcloud accordingly](https://docs.nextcloud.com/server/latest/admin_manual/configuration_server/reverse_proxy_configuration.html).
You can get a free HTTPS certificate from [Let's Encrypt](https://letsencrypt.org/getting-started/).

### Background Jobs

Passwords uses several background jobs to process data.
To ensure that these run as intended, it is recommended to configure Nextcloud to run those jobs trough cron.
Using webcron or ajax for background jobs will cause long delays when passwords are shared since these options will only
execute one background job with each call.
Using ajax for background jobs may also cause unexpected issues since this will execute background jobs in the user
scope instead of the global scope.
More information can be found in
the [documentation](https://docs.nextcloud.com/server/latest/go.php?to=admin-background-jobs).

### Browser Support

We support the current version of Chrome and Firefox and assume that most browsers based on these will work as well.
We do not support specific configurations or browser extensions.

### PHP Requirements

We support **only** versions of PHP with **[active support](https://php.net/supported-versions.php)** by the PHP
community.
The app may run with older PHP versions but this is not tested and bug reports with an unsupported version will be
ignored.

To keep your PHP version updated, we recommend the [PHP PPA from deb.sury.org](https://deb.sury.org/#php-packages) for
Ubuntu and Debian.

If you use NextcloudPi, you can use our [NextcloudPi PHP upgrade guide](./Index#nextcloudpi).

If you use DietPi, [check the PHP upgrade guide for your webserver choice](./Index#dietpi).

### Third Party Services

To provide all api features, passwords may use third party services depending on the settings.
The following list contains all domains which may provide these services.
However, some local service providers like Pageres and the favicon finder may contact any domain as requested via the
api.
The [user handbook](./User-Handbook) also makes direct requests to external servers from the user browser and your
server.

- https://github.com
- https://api.pwnedpasswords.com
- https://raw.githubusercontent.com
- https://icons.passwordsapp.org
- https://breached.passwordsapp.org
- https://statistics.passwordsapp.org
- https://favicongrabber.com
- https://icons.duckduckgo.com
- https://www.google.com
- https://api.browshot.com
- http://api.screenshotlayer.com
- http://api.screenshotmachine.com
- https://secure.screeenly.com
- http://watchout4snakes.com
- http://api.corpora.uni-leipzig.de

### Licenses

All licensing information can be found in
the [Licenses.md](https://github.com/marius-wieschollek/passwords/blob/master/Licenses.md) file