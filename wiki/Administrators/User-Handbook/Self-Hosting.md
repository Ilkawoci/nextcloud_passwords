## Handbook for Passwords app
This wiki article describes how you can replace the built-in user handbook of the passwords app with your own user handbook.
If you just want to have the official handbook hosted on your server instead of Github, consider the [Passwords Handbook app](https://apps.nextcloud.com/apps/passwords_handbook).

## Requirements
A server which can be accessed by your users without authentication and which allows requests from the domain of your Nextcloud server.

**Note:** Check your CORS setup to make sure the browser won't block requests to the handbook server.

## Clone our wiki
The handbook is part of our wiki, therefore you should clone it if you don't want to write your own handbook from scratch.
Make sure that folders like `.git` are not accessible through your webserver.

#### Clone the wiki to your own server
```bash
git clone https://git.mdns.eu/nextcloud-passwords/passwords.wiki.git
cd passwords.wiki
```
The wiki is now cloned to the folder `passwords.wiki`.
The data for the user handbook is in the subfolder `Users`.

#### Use GitHub
You can use the [GitHub import](https://github.com/new/import) to clone the wiki to your GitHub account.
You need to enter the url `https://git.mdns.eu/nextcloud-passwords/passwords.wiki.git` and make sure it's public.


## Update the handbook url
Use the following command to point the app to your custom handbook
```bash
./occ config:app:set passwords handbook/url --value https://yourdomain.org/path/Users/
```
With the given example, Passwords will try to load the file `https://yourdomain.org/path/Users/Index.md` when the handbook is opened.
If you used the GitHub import, the correct url looks like this: `https://raw.githubusercontent.com/<username>/<repository>/master/Users/`.

If you set an invalid handbook url, the default url will be used.


## Update the web handbook url
At some locations, the app will include links to a web version of the handbook, e.g. when the app is in a state where the in-app handbook can't be used.
The path structure is the same, but the `.md` file ending won't be used.
So instead of linking to `/Index.md`, the app would link to `/Index`.

Use the following command to point the app to your custom handbook
```bash
./occ config:app:set passwords handbook/url/web --value https://yourdomain.org/path/Users/
```