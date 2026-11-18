Folders can be shared with other users of your Nextcloud instance, just like passwords.
Sharing a folder also shares everything inside it - all subfolders and all passwords in them.

**Note:** The administrator of your Nextcloud instance may disable sharing.
If someone shares a folder with you, he can also disable re-sharing.

## Share a folder with another user
To share a folder, click on the "⋯"-menu of the folder and then click `Share`.
The sharing options can be found in the `Share` tab of the folder sidebar.
Type the name or the username of the person in the search field and select the user from the search results.
If the user does not appear in the search results it is likely that he does not exist or you are not allowed to share with him.

#### What the receiver gets
The folder is copied into the account of the receiver, including all subfolders and all passwords.
The receiver can then use the folder like his own - without access to your password library.
This also applies to changes you make later: passwords and folders that you add, move or delete inside the shared folder are synchronized to the receiver in the background.

**Note:** Only passwords that are not end-to-end encrypted can be shared.
If the folder contains end-to-end encrypted passwords, sharing the folder is refused with an error.

#### Write permissions
By clicking on the `✎`-icon you can allow a user to change the passwords in the shared folder.
The icon will be light grey if the user can not edit them and full black if he can.

#### Sharing permissions
By clicking on the `Share`-icon you can allow a user to share the folder with other users.
The icon will be light grey if the user can not share the folder and full black if he can.

#### Expiration date
By clicking on the `🗓`-icon, you can open the expiration date dialog.
You can now set an expiration date via the date field in the dialog.
On this date, the share and all copies of the folder will automatically be deleted.
If you clear the date field and confirm the dialog, any existing expiration date will be removed.

#### Unshare a folder
By clicking on the `🗑`-icon, the folder will be no longer shared with the user.
The copy in the account of the user is deleted by the server in the background, so it may take some minutes until it is gone.

## Share a folder with a group
You can also select a Nextcloud group in the search field.
Since shares are always stored per user, the group is resolved into its members and each member gets his own share.
Members that already have a share for this folder are skipped.

**Note:** You can only share with groups that you are a member of yourself.
Users that join the group later do not get the folder automatically, and users that leave the group keep their copy until you revoke the share.

## Folder details
The `Details` tab of the folder sidebar shows the name of the folder, its parent folder, the creation and modification date, the number of contained folders and passwords and the number of shares.

