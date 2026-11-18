This tutorial will help you to import your passwords from the Passman app.
You can either watch the video or follow the instructions below.

[![How To: Import your passwords from Passman](../_files/_previews/passman-import.jpg)](https://raw.githubusercontent.com/wiki/marius-wieschollek/passwords/_files/videos/passman-import.webm)


## Exporting your passwords from Passman
1. Open the Passman app and log into your password vault.
2. Click on "Settings" in the bottom left corner.
3. Open the "Export credentials" tab.
4. Choose the "Export type" "JSON".
5. Enter your vault password in the password field.
6. Click on "Export".
7. Wait for the export and save the file created by the export.


## Import your passwords into Passwords
1. Open the Passwords app.
2. Click on the "🗃" icon in the bottom left corner to open "Backup and Restore".
3. Choose "[Restore or import](web+passlink://goto/backup/import)".
4. Select "Passman JSON" as import format.
5. Open the file you exported before using the file dialog.
6. Click the "Import" button.
7. After the import has finished, check the imported passwords.
8. Delete the export file from your computer.



## Import Options
See [the general import options](../Import#Import-Options).



## Issues and Workarounds for the Passman Export
#### Export stops at "Decrypting credentials"
If the Export stops at "Decrypting credentials", the cause is usually that you have no passwords which contain a file.
Appending a file to at least one password will fix this issue.
To add a file, open the details of a password, click "Edit", then open the "Files" tab and upload a file via the "Choose a file"-button.
After the file has uploaded, click "Save".

> :star: The Passwords Import can not import files.
> You will get a warning like `"Password Name" has files attached which can not be imported.`, which you can ignore in this case.

#### No file download in Firefox / Stuck at "Done"
In some versions of Passman, the file download will not work in Firefox.
You can solve this by applying this [patch](https://github.com/nextcloud/passman/pull/460/commits/2e64d6c59498da26426933d726f94c21a08cf568) to the source code of Passman or by using another browser like Chrome.




## Compatibility Issues and Workarounds for the Passwords Import
#### Files can not be imported
If you have attached files to your passwords in Passman, the Import will show the warning `"Password Name" has files attached which can not be imported.` for each affected password.
Passwords does not offer any file storage functionality as we believe that it is better to leave this to Nextcloud itself.

Passwords does support the linking of files stored in Nextcloud to passwords.
Therefore we recommend downloading the files manually from Passman and storing them in Nextcloud.
After this you can edit the password and add a custom field with the type "file" and choose the file from the file dialog.

> :star: If you want to store secret files like private keys, we recommend that you use the [Nextcloud E2E Encryption](https://nextcloud.com/endtoend/).

#### Long custom field names / values are truncated
If you have custom fields with a label that exceeds 48 characters or a value that exceeds 320 characters, it will be truncated to fit the length limitations of Passwords.
Custom fields are intended to contain usual password related information like and e-mail address or the like.
They are not intended to hold large amounts of data.
If you want to store long texts, we recommend to use the "Notes" field.

#### OTP/TOTP is not supported
We do currently not offer OTP/TOTP.
The implementation is [planned](https://github.com/marius-wieschollek/passwords/issues/69).
You can safely import your passwords as of now and the OTP secret will be stored as custom field.
Our OTP support will be able to import the OTP fields created by the import.

> :warning: Storing OTP and Passwords for the same service in your password database will eliminate the security gains of 2FA.
> You should not store OTP and the password at the same time.