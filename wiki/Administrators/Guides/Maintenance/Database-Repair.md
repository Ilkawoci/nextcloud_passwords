## The database repair job
Passwords has an integrated job to perform database upgrades and repair broken or inconsistent databases.
This job can be executed manually using the command `./occ maintenance:repair` form the cli or trough occ web.
Some, but not all, checks are also run during an app update.

## What the database repair does
- Remove passwords, tag and folder models which do not have any revision at all.
- Restore the last revision if the current revision does not exist for any password, tag or folder model.
- Restore the last revision if the current revision is linked to another model for any password, tag or folder model.
- Remove any password, tag or folder revision which can not be decrypted. (Only with `maintenance:repair`)
- Remove any password, tag or folder revision which does not belong to an existing model.
- Change the parent folder of any folder revision to the default if the set parent folder does not exist.
- Set the default value for the custom fields for any password revision where custom fields are not set.
- Fix any password revision where the custom fields attribute was initialized with an unencrypted default value (see [#66](https://github.com/marius-wieschollek/passwords/issues/66))
- Upgrade custom fields to the latest format for any password revision which does not use E2E. (Only with `maintenance:repair`)
- Remove invalid attributes from custom fields for any password revision which does not use E2E. (Only with `maintenance:repair`)
- Reset custom fields data to default when the JSON can't be parsed for any password revision which does not use E2E. (Only with `maintenance:repair`)
- Set the custom field type to text if invalid for any password revision which does not use E2E. (Only with `maintenance:repair`)
- Reset the password security check status for any password revision if the status code is invalid.
- Move any password revision to the default folder if the set folder does not exist.
- Remove any relation between passwords and tags if either of them do not exist or belong to someone else.
- Create an uuid for any relation between passwords and tags if it does not have one.
- Create an uuid for any deleted relation between passwords and tags if it does not have one.