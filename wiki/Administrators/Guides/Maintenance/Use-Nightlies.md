Nightly versions of of the passwords app are development builds that give faster access to new features and bugfixes.
It is possible to upgrade from a nightly version to the final release trough the normal app store. 

## Enable nightly updates
The easiest way to install nightly builds of the app is trough the app store

1. Go to the Passwords admin settings
2. Enable "Show Nightly Updates in "Apps""
3. Go to "Apps"
4. Update the "Passwords" app if an update is available

It might take some time until nightly updates appear in the app store.
If no update is available after enabling the setting, just check again later.


## Download from Nextcloud Apps
You can download the current nightly from the Nextcloud Apps webpage and install it manually.

1. Go to [Passwords on Nextcloud Apps](https://apps.nextcloud.com/apps/passwords)
2. Check for nightly versions in the downloads section. (marked in red and called "Nightly version (unstable)")
3. Download the nightly version (The file is called "passwords.tar.gz")
4. Upload the archive to the apps folder of your Nextcloud instance
5. Delete the existing passwords folder
6. Unpack the archive with `tar zxf passwords.tar.gz` or `gunzip passwords.tar.gz && tar xf passwords.tar`
7. Open the base folder and run the Nextcloud updater with `php ./occ upgrade` or trough the web interface.