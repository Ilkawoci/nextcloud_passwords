## I bricked my server, how do i restore my passwords?
Check the [app backups](./Backups) and then follow the [server migration guide](./Guides/Maintenance/Server-Migration) to restore the latest backup on another server.

## How do i share folders and tags?
You don't. This feature is not yet supported.

## How do i share passwords with groups?
You don't. This feature is not yet supported.

## Why can't i reuse an existing user name?
When a user gets deleted, all his data will be permanently removed with the next execution of the cron jobs.
The value of the `Remove deleted objects from database` setting will be ignored and the data will be deleted permanently.
While a user is queued for complete removal, you can not reassign the username.
You can delete the user data yourself with the occ command `passwordfolders:user:delete`.

## Why does sharing passwords take so long?
Shared passwords are synchronized using a background job.
This means that creating, updating or deleting a shared password will not be completed until the background job has been executed.
If you want to speed up this process, we recommend running the background jobs more often.
We also strongly recommend using cron instead of webcron or ajax.
Ajax and webcron will only execute one background job at a time which will cause long delays when sharing a password.

## How do i fix database issues?
Passwords offers a repair job which can fix database inconsistencies like orphaned revisions or broken relations between entities.
It will also perform upgrades of entities when a new field is added or changed.
To execute the repair job, execute the Nextcloud maintenance command from the nextcloud folder `./occ maintenance:repair`.
See [Database Repair](./Guides/Maintenance/Database-Repair) for more information.

## How can i prevent the app from causing high server load
Some actions like loading favicons, importing passwords or enabling encryption creates many requests and can cause high server load.
The app provides a server performance preference to clients to prevent too many requests.
If the automatically determined preference is not right for you, you can set preference with
`occ config:app:set passwords performance --type=int --value=<number>` to a number between 0 and 6.
0 would be very low performance, 5 high performance and 6 removes any restrictions.

## How do i make the app look like in the app store?
- We use **Besticon** as **Favicon Service**. We recommend [hosting it your self](./Guides/Services/Besticon-Self-Hosting)
- We use **Pageres** as **Website Preview Service**
- We use **Imagick** for **Image Rendering**

## How do i use the app on a server without internet access?

The app is not designed to run offline.

[We provide a list of the best fitting options for that scenario here](./App-Settings#optimal-settings)