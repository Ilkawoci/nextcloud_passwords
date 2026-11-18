## Why am i seeing this notification?

You have configured the passwords app to use service for the password security check feature which is using a local database of breached passwords. This database must first be downloaded from breached.passwordsapp.org. The background job which is supposed to download the database was unable to do this for some reason. The app will make three attempts to download the database and send a notification each time the update fails. After three attempts, the app will no longer try to download the database and instead inlcude the reason "none" or "Too many failed attempts" in the notification.

## How can i fix this?

1. Read the reason given in the notification. If it suggests that there were timeouts or the host could not be reached, your server may not be able to reach the download server or timeouts (e.g. for background jobs) may be too short. You should check PHP settings like `max_execution_time`, time limits for cronjobs on your server and your firewall.
2. You can always try again. Run the command `php ./occ config:app:set passwords passwords/db/attempts --value=0;` in the installation directory of your Nextcloud to reset the attempt counter.
3. Check the [Nextcloud log](../Guides/Maintenance/App-Debugging) for any related errors.
4. Configure a different [password security check service](../Services/Password-Security-Check-Services#have-i-been-pwned).
5. Ask for [support](../Index#support)