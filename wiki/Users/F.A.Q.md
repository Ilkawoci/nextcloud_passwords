## General
#### Can I access passwords offline?
The Passwords app in your browser does not support any offline storage.
But some of the apps in the [appstore](web+passlink://goto/apps) do include offline storage capabilities.

#### I have hidden passwords, how do I get them?
Go to "[Settings](web+passlink://goto/settings)" -> "Danger Zone", then click "Recover hidden items".
Select any type of items you want to unhide and press "Ok".


## Security
#### How secure is Passwords?
How secure your passwords are with the Passwords app depends on two main factors.
_You are one of it._

- Enable our [end-to-end encryption for your passwords](./Encryption/Enable-End-to-End-Encryption)
- Make sure to keep your Nextcloud account safe.
- Only access the cloud over HTTPS and do not give your credentials to someone else.
- Do not log into Nextcloud on someone else's pc or a public pc.
- If you are using a client to access your passwords, make sure it is always up-to-date.

The second main factor is the administrator of your Nextcloud instance.

Any security issues in Nextcloud, other Nextcloud apps and the server in general is also a security issue for the Passwords app.
To keep your passwords safe, your server administrator has to keep the server safe.

A bad administrator could also manipulate Nextcloud in a way to steal your passwords even when end-to-end encryption is enabled.
You should be able to trust your administrator to respect your privacy rights and keep your data safe.

#### Suspicious amount of failed login attempts detected
The Passwords app counts failed attempts to unlock the password database with the encryption passphrase.
If **five** failed login attempts are detected, the app will:
- Revoke the app password the of the client that made the attempt.
- Revoke the app password of _any_ other client after the _first_ failed login attempt.
- Prevent any client not using an app password from accessing the app at all.
  - Clients which use your Nextcloud username and password to access the passwords app will not be blocked until the restrictions are lifted.

All restrictions will be lifted as soon as you unlock the password database in with the correct encryption passphrase.
However, even after the restrictions are lifted, revoked app passwords will not be reinstated.
You will have to create a new app password for any client that had its app password revoked before.


#### A password is marked as duplicate, how do I find the others
By default, tha app will scan your passwords for duplicates and mark them as "Weak (Duplicate)".
You can quickly see the status of a password by looking at the color of the "🛡" icon.
Yellow means weak, red means it's compromised.

To find other duplicates of the same password, just click on the "🛡" icon.

There are some instances where a duplicate isn't shown:
- If it's already in the trash. Check the trash bin and empty it.
- If it has recently been deleted. The status will update within one day.
- If the duplicate itself is hidden. You can use the "Recover hidden items" action in the settings to unhide any hidden password.


#### What does the Passwords Session Token do?
When you use the Passwords app, tokens with the naming schema "Passwords Session MM.DD.YY HH:MM - User@IP Address" will appear in your device & sessions list.
These tokens are generated automatically when you access the Passwords app in Nextcloud.
The tokens are only valid for a short period of time and usually do not have file system access.
It is safe to delete the tokens, but you should be aware that this will close currently open sessions.


#### How do I create a master password?
Take a look at [the guide to enable end-to-end encryption](Encryption/Enable-End-to-End-Encryption).
Open the [Settings](web+passlink://goto/settings) and look for the "Encryption" section.
Enable the client-side encryption option and set an encryption passphrase.

**Note:** This can not be undone. You can change the master password but not remove it.


#### How do I use two-factor authentication (2FA)?
If your account has 2FA set up and the passwords app is configured to enable the feature,
it will automatically require 2FA when you log in with your encryption passphrase.

This feature only works when an end-to-end encryption is enabled and is currently disabled by default due to user complaints.

**Note:** Some third party clients do not support 2FA and will be unusable after enabling it.



## Sharing
#### I have shared / unshared a password, but the other person can not / still see it
Passwords permits users to access the data of other users themselves.
Because of this, changes in shared passwords need to be processed by a privileged automated process on the server.
This is usually done every 15 minutes.
This process will always execute changes made by the original owner first and discard the changes of others if there is any conflict.

#### Some of my shares show a loading that says "Some data is waiting to be synchronized"
As described in the [question above](#i-have-shared-unshared-a-password-but-the-other-person-can-not-still-see-it), shared passwords are synchronized in the background.
So if you or someone else changes the shared password, it is queued for synchronizing.
Until all changes have been synchronized, the loading animation will tell you that changes are waiting to be synchronized.

#### What happens if I delete a password that was shared with me?
If you delete a password that was shared with you, it will only be deleted from your account and the accounts of the people you may have shared the password with.
Even if you have the rights to edit the password, you can not delete it in the account of the person who shared it with you.



## Notifications and Emails
#### Passwords tells me that one of my passwords is insecure
If companies get hacked, the passwords of their users often end up in online databases of hacked passwords.
Because of this, the passwords app checks on a regular basis if one of your passwords appears in these databases and notifies you if it does.
This does not necessarily mean that you or the website which you use the password for has been hacked.
But you should change the password immediately, as hackers now have it in their databases as well.
You can view all your insecure passwords in "[Security](web+passlink://goto/security)" -> "[Breached](web+passlink://goto/security/2)".

#### Password tells me that one of my passwords could not be shared
Given that the option is enabled, you can share passwords which have been shared with you.
By doing so, it might happen that the person you want to share the password with is the one who originally shared it.
If that is the case, Passwords will not complete the sharing process as this would create an endless loop.
You can view all your shared passwords in "[Shares](web+passlink://goto/shared)" -> "[Shared by me](web+passlink://goto/shared/1)".

#### The notification "We have detected several failed attempts to unlock your password database by ..."
If you use 2FA or a master password, the app will send you a notification if a client tries to gain access to the database without correct credentials.
If this was not you, we recommend revoking the app password of that client in the "Security" section of your Nextcloud account settings.
If the client was not using an app password, we recommend that you change your account password and delete all the app passwords (Changing your password will invalidate the app passwords).

#### The notification "Administrative access to your account"
An administrator has opened the passwords app with your account through an app like [impersonate](https://apps.nextcloud.com/apps/impersonate).
If you use a master password, then this does not necessarily mean that access to your passwords was possible.
For more information please talk to your administrator.

#### The notification "Suspicious amount of failed login attempts detected"
See [Suspicious amount of failed login attempts detected](#suspicious-amount-of-failed-login-attempts-detected).



## Import and Export
#### I want to import from another password manager but there is no option for this service
Most password managers do offer a CSV export.
If you have that csv file, you can choose the option "Other / Custom CSV" and map the fields yourself.
See the [custom CSV import guide](./Import/Import-from-custom-CSV) for further information.

#### A warning says that the service i am trying to import from is known to generate faulty csv files
Some password services create export files where the columns are not formatted properly.
If that happens, Passwords may be unable to import the file as it is and you have to fix it manually.
See the [import section](./Import#how-to-fix-faulty-csv-files) for further information.



## Performance
#### The app is slow / The app is slow after failed logins
If you repeatedly tried to login with any third party client using incorrect login data, Nextcloud will see this as a brute force attack.
This results in a slowdown of any further api requests.
Since the app uses the api, this slowdown will also affect it.
The normal performance should be restored after some time.

#### The "All" section or folders with many items are slow
All passwords, folders and tags are stored encrypted and decrypting can take up considerable amounts of time.
The generation of favicons for each password can also take some time initially.
Using folders or tags to organize and browse your passwords will increase performance.



## Error Messages
#### Error "Your Browser is outdated"
If your browser does not support the necessary APIs used by Passwords, you will see this message.
This means that your browser does not provide some functionality which is absolutely required to run the app.
Passwords checks the existence of such functionality and shows the message only if it is not present.
You may use a known working browser like Firefox or Vivaldi to run the app.

**NOTE:** Please do not ask us to "make your browser compatible". If we could, you would not see this message.

#### Error "HTTPS Required"
This error appears if you tried to open the app with an unsecure connection.
Using Passwords via HTTP is not only insecure, the app will also not work.
You can solve this issue by prefixing the url with `https://<your nextcloud url>`.
If the server does not support HTTPS, this error can not be solved.