The passwords server survey is a collection of anonymous server data sent by the passwords app once a month.
The statistics are publicly available on [statistics.passwordsapp.org](https://statistics.passwordsapp.org/).


## Data privacy
All data collected by tge server survey is anonymous (and not pseudonym).
The data can not be traced back to your server and does not contain any form of id.
The data does also not allow to track servers or identify recurring reports from a server.
We do not track your individual users or their use of the app or Nextcloud.
We do not share raw data with anyone.
Only the publicly available statistics on the homepage are available.


## What is the use of the server survey?
There is absolutely no data available about the Nextcloud ecosystem.
We do not know if and how fast new versions of Nextcloud or the app are installed by users or which kind of server they run on.
We also do not know which of our many integrations or third party services really matter to users.
We also do not know how new features like the E2E are accepted or how much deprecated features like the legacy api are still in use.
In order to learn more about this we have created the server survey to collect this data each week and give us more information on the apps usage.

## What does the server survey do for me?
The server survey is your way of telling us what to focus on during development just by using that feature.
Much used services will receive more attention during development.
Much used versions of Nextcloud or PHP may receive longer support even after they are officially no longer supported.
Much used deprecated features may have a longer lifespan and a more "soft" phase out instead of a fast cancellation.
Less used services might get removed.
In future versions of the app, third party services hosted by us might be exclusive to servers who participate in the server survey.

## Server survey report modes
In the admin settings you can specify which data you want to send in the server survey.

## No participation
Will not send any data at all.
Not even that you do not participate.
You will also not receive any further notifications about the survey.

## Basic
Will send the Nextcloud, PHP and app version and information about your OS, CPU architecture, cron execution and which database software is used.
You can check the output on your machine with the command `./occ passwordfolders:system:report --basic`.

This is what a basic report looks like:
```json
{
    "version": {
        "server": "20.0.0.6",
        "app": "2021.1.0",
        "php": "7.4.10"
    },
    "environment": {
        "os": "Linux",
        "architecture": "x86_64",
        "bits": 64,
        "database": "mysql",
        "cron": "cron",
        "proxy": false,
        "sslProxy": false,
        "subdirectory": false
    }
}
```



## Full
Will send all the basic information and the services selected in the admin settings, legacy api usage, how much sharing is used, which encryption types are used, and if selected other apps are installed.
You can check the output on your machine with the command `./occ passwordfolders:system:report`.

This is what a full report looks like:
```json
{
    "version": {
        "server": "20.0.0.6",
        "app": "2021.1.0",
        "php": "7.4.10"
    },
    "environment": {
        "os": "Linux",
        "architecture": "x86_64",
        "bits": 64,
        "database": "mysql",
        "cron": "cron",
        "proxy": false,
        "sslProxy": false,
        "subdirectory": false
    },
    "services": {
        "images": "imagick",
        "favicons": "bi",
        "previews": "pageres",
        "security": "hibp",
        "words": "wo4snakes",
        "previewApi": false,
        "faviconApi": false
    },
    "settings": {
        "channel": "beta",
        "nightlies": false,
        "handbook": false,
        "performance": 6
    },
    "apps": {
        "guests": {
            "installed": false,
            "enabled": false
        },
        "occweb": {
            "installed": true,
            "enabled": false
        },
        "theming": {
            "installed": true,
            "enabled": true
        },
        "passman": {
            "installed": true,
            "enabled": false
        },
        "unsplash": {
            "installed": false,
            "enabled": false
        },
        "impersonate": {
            "installed": false,
            "enabled": false
        }
    },
    "sharing": {
        "shares": 10
    },
    "encryption": {
        "sse": {
            "SSEv1r1": true,
            "SSEv1r2": true,
            "SSEv2r1": false,
            "none": false,
            "default": "SSEv1r1"
        },
        "cse": {
            "CSEv1r1": false,
            "none": true,
            "default": "none"
        }
    }
}
```