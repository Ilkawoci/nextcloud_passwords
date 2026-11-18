The passwords api allows listing, creating, updating and deleting password entries.

# The Password Object
| Property     | Type          | Writable | Encrypted | Versioned | Length | Description                                                                               |
|--------------|---------------|----------|-----------|-----------|--------|-------------------------------------------------------------------------------------------|
| id           | string        | no       | no        | no        | 36     | The UUID of the password                                                                  |
| label        | string        | yes      | yes       | yes       | 64     | User defined label of the password                                                        |
| username     | string        | yes      | yes       | yes       | 64     | Username associated with the password                                                     |
| password     | string        | yes      | yes       | yes       | 256    | The actual password                                                                       |
| url          | string        | yes      | yes       | yes       | 2048   | Url of the website                                                                        |
| notes        | string        | yes      | yes       | yes       | 4096   | Notes for the password. Can be formatted with Markdown                                    |
| customFields | string        | yes      | yes       | yes       | 8192   | Custom fields created by the user. (See [custom fields](#custom-fields))                  |
| status       | int           | no       | no        | yes       | 1      | Security status level of the password. (See [security status](#security-status))          |
| statusCode   | string        | no       | no        | yes       | 12     | Specific code for the current security status (See [security status](#security-status))   |
| hash         | string        | yes      | no        | yes       | 40     | SHA1 hash of the password                                                                 |
| folder       | string        | yes      | no        | yes       | 36     | UUID of the current folder of the password                                                |
| revision     | string        | no       | no        | yes       | 36     | UUID of the current revision                                                              |
| share        | string / null | no       | no        | no        | 36     | UUID of the share if the password was shared by someone else with the user                |
| shared       | bool          | no       | no        | no        | 1      | True if the password is shared with other users                                           |
| cseType      | string        | yes      | no        | yes       | 10     | Type of the used client side encryption                                                   |
| cseKey       | string        | yes      | no        | yes       | 36     | UUID of the key used for client side encryption                                           |
| sseType      | string        | no       | no        | yes       | 10     | Type of the used server side encryption                                                   |
| client       | string        | no       | no        | yes       | 256    | Name of the client which created this revision                                            |
| hidden       | bool          | yes      | no        | yes       | 1      | Hides the password in list / find actions                                                 |
| trashed      | bool          | no       | no        | yes       | 1      | True if the password is in the trash                                                      |
| favorite     | bool          | yes      | no        | yes       | 1      | True if the user has marked the password as favorite                                      |
| editable     | bool          | no       | no        | no        | 1      | Specifies if the encrypted properties can be changed. Might be false for shared passwords |
| edited       | int           | yes      | no        | yes       | 12     | Unix timestamp when the user last changed the password                                    |
| created      | int           | no       | no        | no        | 12     | Unix timestamp when the password was created                                              |
| updated      | int           | no       | no        | yes       | 12     | Unix timestamp when the password was updated                                              |

#### Detail Levels
| Level      | Description                                                                                                                                                         |
|------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| model      | Returns the base model                                                                                                                                              |
| +revisions | Adds the revisions property which contains all revisions. A revision consists of all properties marked as versioned and its own created property                    |
| +folder    | Fills the folder property with the base model of the folder. If the password is not hidden but the folder is, the base folder will be used                          |
| +tags      | Adds the tags property filled with the base model of all tags. Hidden tags are not included in this list if the password is not hidden                              |
| +tag-ids   | Adds the tags property filled with the ids of all tags. Hidden tags are not included in this list if the password is not hidden. Can not be used with `+tags`       |
| +shares    | Adds the shares property filled with the base model of all shares with other users. Fills the share property with the base model of the original share if available |

#### Notes
 - Since the status check is done once per day server side, the DUPLICATE status may take some time to be applied to all affected passwords
 - The difference between `updated` and `edited` is that updated is always set by the server when the password is changed and edited has to be set by the client.




# Available api actions
| Action                         | Url                         | Method | Session required | Description                                      |
|--------------------------------|-----------------------------|--------|------------------|--------------------------------------------------|
| [list](#the-list-action)       | `/api/1.0/password/list`    | GET    | yes              | List all passwords with the default detail level |
| [list](#the-list-action)       | `/api/1.0/password/list`    | POST   | yes              | List all passwords with the given detail level   |
| [show](#the-show-action)       | `/api/1.0/password/show`    | POST   | yes              | Show a password                                  |
| [find](#the-find-action)       | `/api/1.0/password/find`    | POST   | yes              | Find passwords matching given criteria           |
| [create](#the-create-action)   | `/api/1.0/password/create`  | POST   | yes              | Create a new password                            |
| [update](#the-update-action)   | `/api/1.0/password/update`  | PATCH  | yes              | Update an existing password                      |
| [delete](#the-delete-action)   | `/api/1.0/password/delete`  | DELETE | yes              | Delete a password                                |
| [restore](#the-restore-action) | `/api/1.0/password/restore` | PATCH  | yes              | Restore an earlier state of a password           |




# The create action
The create action creates a new password with the given attributes.

#### Arguments
| Argument     | Type   | Default     | Required | Description                                                                           |
|--------------|--------|-------------|----------|---------------------------------------------------------------------------------------|
| password     | string | -           | yes      | The password                                                                          |
| label        | string | -           | yes      | The label of the password                                                             |
| username     | string | empty       | no       | The username associated with the password                                             |
| url          | string | empty       | no       | The url of the associated website                                                     |
| notes        | string | empty       | no       | The users notes                                                                       |
| customFields | string | empty       | no       | The custom fields defined by the user                                                 |
| hash         | string | empty       | yes      | The SHA1 hash of the password                                                         |
| cseType      | string | "none"      | no       | The client side encryption type                                                       |
| cseKey       | string | ""          | no       | The UUID of the key used for client side encryption. Required if `cseType` not "none" |
| folder       | string | Base folder | no       | The current folder of the password                                                    |
| edited       | int    | 0           | no       | Unix timestamp when the user has last changed the actual password                     |
| hidden       | bool   | false       | no       | Whether or not the password should be hidden                                          |
| favorite     | bool   | false       | no       | Whether or not the user has marked this password as favorite                          |
| tags         | array  | empty       | no       | The id of all tags associated with this passwords                                     |

#### Return value
The success status code is `201 Created`

| Argument | Type   | Description              |
|----------|--------|--------------------------|
| id       | string | The UUID of the password |
| revision | string | The UUID of the revision |

#### Notes
 - If the password is not hidden and should be created in a hidden folder, it will be created in the base folder instead
 - If the folder uuid is invalid or does not exist, the base folder uuid will be used instead
 - If the `edited` argument is "0" or missing, the current time will be used
 - If the `edited` time is in the future, the current time will be used
 - If the `cseType` is set to "none", the `hash` will be calculated on the server
 - If the `tags` argument contains invalid tag ids, they will be ignored
 - You can assign hidden tags to a not hidden password, but they will not be visible.
   Therefore another client might remove the tag by accident




# The update action
The update action creates a new revision of a password with an updated set of attributes.

#### Arguments
| Argument     | Type   | Default     | Required | Description                                                                                  |
|--------------|--------|-------------|----------|----------------------------------------------------------------------------------------------|
| id           | string | -           | yes      | The id of the password object                                                                |
| revision     | string | -           | no       | The current revision known to the client. If not the latest revision, the request will fail. |
| password     | string | -           | yes      | The password                                                                                 |
| label        | string | -           | yes      | The label of the password                                                                    |
| username     | string | empty       | no       | The username associated with the password                                                    |
| url          | string | empty       | no       | The url of the associated website                                                            |
| notes        | string | empty       | no       | The users notes                                                                              |
| customFields | string | empty       | no       | The custom fields defined by the user                                                        |
| hash         | string | empty       | yes      | The SHA1 hash of the password                                                                |
| cseType      | string | "none"      | no       | The client side encryption type                                                              |
| cseKey       | string | ""          | no       | The UUID of the key used for client side encryption. Required if `cseType` not "none"        |
| folder       | string | Base folder | no       | The current folder of the password                                                           |
| edited       | int    | 0           | no       | Unix timestamp when the user has last changed the actual password                            |
| hidden       | bool   | false       | no       | Whether or not the password should be hidden                                                 |
| favorite     | bool   | false       | no       | Whether or not the user has marked this password as favorite                                 |
| tags         | array  | empty       | no       | The id of all tags associated with this password                                             |

#### Return value
The success status code is `200 Ok`

| Argument | Type   | Description                  |
|----------|--------|------------------------------|
| id       | string | The UUID of the password     |
| revision | string | The UUID of the new revision |

#### Notes
 - If the password is not editable any change to the encrypted properties, the cseType and the hash will be ignored.
 - If the password is shared you can only use cse types which support sharing
 - If the password is shared you can not hide the password
 - If the password is not hidden and should be moved to a hidden folder, it will be moved to the base folder instead
 - If the password has tags and you want to remove all tags, you need to submit an array with one invalid tag id
 - If the folder uuid is invalid or does not exist, the base folder uuid will be used instead
 - If the `revision` argument is present, the value must match the latest revision or the action will fail with an error.
   This prevents the client from overwriting a more recent revision on the server with old data.
 - If the `edited` argument is "0" or missing, the timestamp from the last revision will be used
 - If the `edited` time is in the future, the current time will be used
 - If the `hash` has not changed, the `edited` field from the last revision will be used
 - If the `cseType` is set to "none", the `hash` will be calculated on the server
 - If the `tags` argument is empty or missing, no changes will be made
 - If the `tags` argument contains invalid tag ids, they will be ignored
 - You can assign hidden tags to a not hidden password, but they will not be visible.
   Therefore another client might remove the tag by accident




# The show action
The show action lists the properties of a single password.

#### Arguments
| Argument | Type   | Default | Required | Description                                      |
|----------|--------|---------|----------|--------------------------------------------------|
| id       | string | -       | yes      | The id of the password                           |
| details  | string | "model" | no       | The detail level of the returned password object |

#### Return value
The success status code is `200 Ok`
The return value is a password object with the given detail level

#### Notes
 - This is the only action that can access hidden passwords




# The list action
The list action lists all passwords of the user except those in trash and the hidden ones.

#### Arguments
| Argument | Type   | Default | Required | Description                                       |
|----------|--------|---------|----------|---------------------------------------------------|
| details  | string | "model" | no       | The detail level of the returned password objects |

#### Return value
The success status code is `200 Ok`
The return value is a list of password objects with the given detail level

#### Notes
 - The list will not include trashed passwords
 - The list will not include hidden passwords
 - The list will not include suspended passwords where the folder or a parent folder is in the trash




# The find action
The find action can be used to find all passwords matching the given search criteria.
Only a specific set of fields is allowed in the criteria.
How the criteria array works is explained on the [object search page](./Object-Search).

#### Arguments
| Argument | Type   | Default | Required | Description                                       |
|----------|--------|---------|----------|---------------------------------------------------|
| criteria | array  | []      | no       | The search criteria                               |
| details  | string | "model" | no       | The detail level of the returned password objects |

#### Allowed search fields
| Field    | Type   | Description                                                 |
|----------|--------|-------------------------------------------------------------|
| created  | int    | Unix timestamp when the password was created                |
| updated  | int    | Unix timestamp when the password was updated                |
| edited   | int    | Unix timestamp when the user last changed the password      |
| cseType  | string | The client side encryption type                             |
| sseType  | string | The server side encryption type                             |
| status   | int    | The server side detected security status                    |
| trashed  | bool   | Whether or not the password is in the trash                 |
| favorite | bool   | Whether or not the user has marked the password as favorite |

#### Return value
The success status code is `200 Ok`
The return value is a list of password objects that match the criteria with the given detail level

#### Notes
 - The property `trashed` will be set to `false` if not present
 - The list will not include hidden passwords
 - The list will not include suspended passwords where the folder or a parent folder is in the trash




# The delete action
The delete action moves a password to the trash or deletes it completely if it is already in the trash.

#### Arguments
| Arguments | Type   | Default | Required | Description                                               |
|-----------|--------|---------|----------|-----------------------------------------------------------|
| id        | string | -       | yes      | The id of the password                                    |
| revision  | string | -       | no       | Assumed current revision of the password (Since 2019.6.0) |

#### Return value
The success status code is `200 Ok`

| Argument | Type   | Description                                                               |
|----------|--------|---------------------------------------------------------------------------|
| id       | string | The UUID of the password                                                  |
| revision | string | The UUID of the new revision. Only if the password was moved to the trash |

#### Notes
 - If a password is moved to the trash, the relations to tags will be hidden from the tag, but not the password.
 - If the `revision` is set, the password will only be deleted if that revision is the current revision.
   Otherwise an "Outdated revision id" error is returned.
   This way, a password is not accidentally deleted instead of trashed if the client is out of sync.




# The restore action
The restore action can restore an earlier state of a password.

#### Arguments
| Arguments | Type   | Default | Required | Description            |
|-----------|--------|---------|----------|------------------------|
| id        | string | -       | yes      | The id of the password |
| revision  | string | -       | no       | The id of the revision |

#### Return value
The success status code is `200 Ok`

| Argument | Type   | Description                  |
|----------|--------|------------------------------|
| id       | string | The UUID of the password     |
| revision | string | The UUID of the new revision |

#### Notes
 - If no revision is given and the password is in trash, it will be removed from trash
 - If no revision is given and the password is not in trash, nothing is done
 - If a revision is given and the revision is marked as in trash, it will be removed from trash
 - If a revision is given that does not belong to the model, an "Invalid revision id" error will be returned.
 - The action will fail if the password is shared but the revision to restore does not meet the requirements for sharing
 - This action will always create a new revision
 - The server side encryption type may change
 - If the folder does not exist anymore, it will be moved to the base folder
 - Tag relations can not be restored
 - Deleted passwords can not be restored




# Custom Fields
The custom fields attribute contains a JSON formatted array with user defined custom fields.
Custom fields are part of the shared attributes.

**Note:** Some clients may initialize the customFields attribute as an empty array (`"[]"`) others may use an empty string (`""`).

#### Format
Each field has three attributes: the name, the type and the value.
The `label` contains the name of the field, the `type` describes the value and the `value` contains the content of the field.
```json
[
    {
        "label": "Field Name",
        "type" : "text",
        "value": "Field Value"
    }
]
```

#### Field Types
| Type   | Description                                                                                                                      |
|--------|----------------------------------------------------------------------------------------------------------------------------------|
| text   | Generic text value                                                                                                               |
| secret | A secret value which should be treated like a password                                                                           |
| email  | An email address                                                                                                                 |
| url    | A valid full url. Any protocol is allowed                                                                                        |
| file   | The path to a file accessible over WebDav. The base url of the WebDav service is defined in the setting `server.baseUrl.webdav`. |
| data   | A field with technical information. Should not be shown to the user.                                                             |

#### Notes
- **The format of the JSON was changed in 2019.4.0**
- Only 20 fields per password are allowed including hidden fields
- The label has a maximum length of 48 characters
- The value has a maximum length of 320 characters
- Data fields can have 370 characters for label and value combined
- The total length of the stringified customFields JSON can not exceed 8192 characters
- The value should not be but may be empty


# Security Status
Each password entry has the `status` and `statusCode` properties which describe the current security status of the password.
The `status` is an integer value which categorizes the overall status of the password and the `statusCode` is a string which may give a reason for the status.
The status is maintained by the server and updated automatically.

| Status | Description                        | Status Code | Status Code Description                                                                                     |
|--------|------------------------------------|-------------|-------------------------------------------------------------------------------------------------------------|
| 0      | The password is secure             | GOOD        | The password is secure                                                                                      |
| 1      | User specified rules were violated | DUPLICATE   | The password occurs multiple times in the database                                                          |
|        |                                    | OUTDATED    | The user specified maximum age for passwords is exceeded                                                    |
| 2      | The password is insecure           | BREACHED    | The password was found in a database of breached passwords                                                  |
| 3      | Unknown                            | NOT_CHECKED | The security status of the password was not checked. This can occur if the user has disabled the SHA1 hash. |
