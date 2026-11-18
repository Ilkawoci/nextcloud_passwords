The passwords app provides previews of the main website specified for any password.
Only the front page of the domain is called and at maximum twice for mobile and desktop views.

If you change this setting, clear the preview cache and your browser cache to see changes.

**Note:** If you know a good program or service that has an API and offers a free plan for users, feel free to open an issue to add support for it.


## Contents
[[_TOC_]]


## Pageres CLI
Requires [pageres-cli](https://github.com/sindresorhus/pageres-cli) to installed locally.
Usually very reliable local and headless preview generator with a modern browser engine.

#### Setup
Pageres requires NPM and fontconfig to be installed.
Pageres can be installed with NPM with the command `npm install --global pageres-cli`.
If the installation fails, try `sudo npm install --global pageres-cli --unsafe-perm`.
If you are using a docker container, add `--cap-add=SYS_ADMIN` to the docker command to enable the chrome sandbox.

**Warning:** Locally hosted services can generate previews of websites only available within your local network. (e.g. Intranet/Company Wiki).
This could leak confidential data.


## screeenly
Screeenly is a standalone open source website screenshot service.
It offers unlimited free screenshots and provides a [self hosted](https://github.com/stefanzweifel/screeenly/wiki/Requirements-and-Install) option.
It usually creates proper screenshots and the online version supports HTTPS by default.

**Warning:** Locally hosted services can generate previews of websites only available within your local network. (e.g. Intranet/Company Wiki).
This could leak confidential data.

#### Configuration
If you're using the hosted version, just enter your api key from [screeenly.com](https://secure.screeenly.com/dashboard)

If you're hosting the service yourself, enter the api url and your key as a single string like `https://<domain>/api/v1?key=<apikey>` where everything before `?key=` is the api url and the key is your api key.
E.g. `https://secure.screeenly.com/api/v1?key=vAod3VPDJbmyhGg2Fx1fbB7ZXyz1DrRg3Gvq5haTRc9c86jkX4`

#### Links
- [website](http://screeenly.com)
- [self hosting](https://github.com/stefanzweifel/screeenly#self-hosting)
- [Terms of Service](http://screeenly.com/terms)



## Browshot
Provides 100 free screenshots per month on their free instances.
The api offers HTTPS by default, you can view the screenshots in your account and you can buy additional screenshots as if needed.
The Passwords app will check your account balance and use free screenshots if possible.
If your account balance allows it, passwords will use premium instances if no free screenshots are left.
By default, instance 27 is used for desktop and instance 67 for mobile for free screenshots and instances 58 (desktop) and 275 (mobile) are used for paid screensots.

#### Configuration
You can specify the premium instance to use with the config keys `service/preview/bws/desktop` and `service/preview/bws/mobile` manually.
Only the premium instances can be specified, the free instances are hardcoded.

```bash
php ./occ config:app:set passwords service/preview/bws/mobile --value=58
php ./occ config:app:set passwords service/preview/bws/desktop --value=275
```

#### Links
- [website](https://browshot.com)
- [Terms of Service](https://browshot.com/terms)



## screenshotmachine.com
Offers 100 fresh screenshots for free per month (accumulative, so you get 100 _added_ each month) and impressions are free.
You pay what you use, it is quite fast and supports different devices for desktop and mobile views.
Premium customers can also provide custom error images.

#### Links
- [website](https://screenshotmachine.com/)
- [Terms of Service](https://screenshotmachine.com/termsandconditions.php)
- [Privacy](https://screenshotmachine.com/privacypolicy.php)



## screenshotlayer
Offers 100 free screenshots per month.
The free plan seems to trigger the bot protection on some websites and HTTPS is not supported.

#### Links
- [website](https://screenshotlayer.com/)
- [Terms of Service](https://screenshotlayer.com/terms)
- [Privacy](https://screenshotlayer.com/privacy)



## None
Just delivers one of five default images.