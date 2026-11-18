The folder api allows listing, creating, updating and deleting folders.

# The Folder Object
| Property | Type | Writable | Encrypted | Versioned | Length | Description |
| --- | --- | --- | --- | --- | --- | --- |
| id | string | no | no | no | 36 | The UUID of the folder |
| label | string | yes | yes | yes | 48 | User defined label of the folder |
| parent | string | yes | no | yes | 36 | UUID of the parent folder |
| revision | string | no | no | yes | 36 | UUID of the current revision |
| cseType | string | yes | no | yes | 10 | Type of the used client side encryption |
| cseKey | string | yes | no | yes | 36 | UUID of the key used for client side encryption |
| sseType | string | no | no | yes | 10 | Type of the used server side encryption |
| client | string | no | no | yes | 256 | Name of the client which created this revision |
| hidden | bool | yes | no | yes | 1 | Hides the folder in list / find actions |
| trashed | bool | no | no | yes | 1 | True if the folder is in the trash |
| favorite | bool | yes | no | yes | 1 | True if the user has marked the folder as favorite |
| created | int | no | no | no | 12 | Unix timestamp when the folder was created |
| updated | int | no | no | yes | 12 | Unix timestamp when the folder was updated |
| edited | int | yes | no | yes | 12 | Unix timestamp when the user last changed the folder name |

#### Detail Levels
| Level | Description |
| --- | --- |
| model | Returns the base model |
| +revisions | Adds the revisions property which contains all revisions. A revision consists of all properties marked as versioned and its own created property |
| +parent | Fills the parent property with the base model of the parent folder |
| +folders | Adds the folders property with the base model of all folders in this folder |
| +folder-ids | Adds the folders property with the ids of all folders in this folder. Can not be used with `+folders` |
| +passwords | Adds the passwords property with the base model of all passwords in this folder |
| +password-ids | Adds the passwords property with the ids of all passwords in this folder. Can not be used with `+passwords` |
| +password-tags | Loads the tags for all passwords in this folder in their respective model. Can only be used with `+passwords` |
| +tags | Not implemented but reserved |

#### Enhanced API special properties
The properties "revisions", "parent", "folders" and "passwords" are also processed if necessary.

| Property | Type | Description |
| --- | --- | --- |
| type | string | Object type, the value is "folder" |
| icon | string | Url for the default folder icon |
| created | Date | Date when the folder was created |
| updated | Date | Date when the folder was last updated |
| edited | Date | Date when the user last renamed the folder |

#### Notes
 - The folder uuid can be the value `00000000-0000-0000-0000-000000000000`. This is the uuid of the base folder
 - The base folder can not be edited at all




# Available api actions
| Action | Url | Method | Session required | Description |
| --- | --- | --- | --- | --- |
| [list](#the-list-action) | `/api/1.0/folder/list` | | GET | yes | List all folders with the default detail level |
| [list](#the-list-action) | `/api/1.0/folder/list` | POST | yes | List all folders with the given detail level |
| [show](#the-show-action) | `/api/1.0/folder/show` | POST | yes | Show a folder |
| [find](#the-find-action) | `/api/1.0/folder/find` | POST | yes | Find folders matching given criteria |
| [create](#the-create-action) | `/api/1.0/folder/create` | POST | yes | Create a new folder |
| [update](#the-update-action) | `/api/1.0/folder/update` | PATCH | yes | Update an existing folder |
| [delete](#the-delete-action) | `/api/1.0/folder/delete` | DELETE | yes | Delete a folder |
| [restore](#the-restore-action) | `/api/1.0/folder/restore` | PATCH | yes | Restore an earlier state of a folder |




# The create action
The create action creates a new folder with the given attributes.

#### Arguments
| Argument | Type | Default | Required | Description |
| --- | --- | --- | --- | --- |
| label | string | - | yes | The label of the folder |
| parent | string | Base folder | no | The current parent folder |
| cseType | string | "none" | no | The client side encryption type |
| cseKey | string | "" | no | The UUID of the key used for client side encryption. Required if `cseType` not "none" |
| edited | int | 0 | no | Unix timestamp when the user has last renamed the folder |
| hidden | bool | false | no | Whether or not the folder should be hidden |
| favorite | bool | false | no | Whether or not the user has marked this folder as favorite |

#### Return value
The success status code is `201 Created`

| Argument | Type | Description |
| --- | --- | --- |
| id | string | The UUID of the folder |
| revision | string | The UUID of the revision |

#### Notes
 - If the uuid of the parent folder is invalid or does not exist, the base folder uuid will be used instead
 - If the folder is not hidden and should be created in a hidden folder, it will be created in the base folder instead
 - If the `edited` argument is "0", missing or in the future, the current time will be used




# The update action
The update action creates a new revision of a folder with an updated set of attributes.

#### Arguments
| Argument | Type | Default | Required | Description |
| --- | --- | --- | --- | --- |
| id | string | - | yes | The id of the folder |
| revision | string | - | no | The current revision known to the client. If not the latest revision, the request will fail. |
| label | string | - | yes | The label of the folder |
| parent | string | Base folder | no | The current parent folder |
| cseType | string | "none" | no | The client side encryption type |
| cseKey | string | "" | no | The UUID of the key used for client side encryption. Required if `cseType` not "none" |
| edited | int | 0 | no | Unix timestamp when the user has last renamed the folder |
| hidden | bool | false | no | Whether or not the folder should be hidden |
| favorite | bool | false | no | Whether or not the user has marked this folder as favorite |

#### Return value
The success status code is `200 Ok`

| Argument | Type | Description |
| --- | --- | --- |
| id | string | The UUID of the folder |
| revision | string | The UUID of the new revision |

#### Notes
 - If the uuid of the parent folder is invalid or does not exist, the base folder uuid will be used instead
 - If the folder is not hidden and should be moved to a hidden parent folder, it will be moved to the base folder instead
 - If hou hide a folder, all folders and passwords in it will be hidden as well
 - If you unhide a folder no change to the folders and passwords in it will be made and they will remain hidden
 - If the `revision` argument is present, the value must match the latest revision or the action will fail with an error.
   This prevents the client from overwriting a more recent revision on the server with old data.
 - If the `edited` argument is "0" or missing, the timestamp from the last revision will be used
 - If the `edited` time is in the future, the current time will be used




# The delete action
The delete action moves a folder and its content to the trash or deletes it completely if it is already in the trash.

#### Arguments
| Arguments | Type | Default | Required | Description |
| --- | --- | --- | --- | --- |
| id | string | - | yes | The id of the folder |
| revision | string | - | no | Assumed current revision of the folder (Since 2019.6.0) |

#### Return value
The success status code is `200 Ok`

| Argument | Type | Description |
| --- | --- | --- |
| id | string | The UUID of the folder |
| revision | string | The UUID of the new revision. Only if the folder was moved to the trash |

#### Notes
 - If a folder is moved to the trash, all passwords and folders in it will be suspended and hidden from list and find actions
 - If a folder is moved to the trash, the relations between tags and passwords in the folder will be hidden from the tag, but not the password
 - If a folder is deleted, all passwords and folders in it will be deleted as well
 - If the `revision` is set, the folder will only be deleted if that revision is the current revision. 
   This way, a folder is not accidentally deleted instead of trashed if the client is out of sync.




# The restore action
The restore action can restore an earlier state of a folder.

#### Arguments
| Arguments | Type | Default | Required | Description |
| --- | --- | --- | --- | --- |
| id | string | - | yes | The id of the folder |
| revision | string | - | no | The id of the revision |

#### Return value
The success status code is `200 Ok`

| Argument | Type | Description |
| --- | --- | --- |
| id | string | The UUID of the folder |
| revision | string | The UUID of the new revision |

#### Notes
 - If no revision is given and the folder is in trash, it will be removed from trash
 - If no revision is given and the folder is not in trash, nothing is done
 - If a revision is given and the revision is marked as in trash, it will be removed from trash
 - This action will always create a new revision
 - The server side encryption type may change
 - If the parent folder does not exist anymore, it will be moved to the base folder
 - Deleted folders can not be restored




# The show action
The show action lists the properties of a single folder.

#### Arguments
| Argument | Type | Default | Required | Description |
| --- | --- | --- | --- | --- |
| id | string | - | yes | The id of the folder |
| details | string | "model" | no | The detail level of the returned folder object |

#### Return value
The success status code is `200 Ok`
The return value is a folder object with the given detail level

#### Notes
 - This is the only action that can access hidden folders




# The list action
The list action lists all folders of the user except those in trash and the hidden ones.

#### Arguments
| Argument | Type | Default | Required | Description |
| --- | --- | --- | --- | --- |
| details | string | "model" | no | The detail level of the returned folder objects |

#### Return value
The success status code is `200 Ok`
The return value is a list of folder objects with the given detail level

#### Notes
 - The list will not include trashed folders
 - The list will not include hidden folders
 - The list will not include suspended folders where a parent folder is in the trash




# The find action
The find action can be used to find all folders matching the given search criteria.
Only a specific set of fields is allowed in the criteria.
How the criteria array works is explained on the [object search page](./Object-Search).

#### Arguments
| Argument | Type | Default | Required | Description |
| --- | --- | --- | --- | --- |
| criteria | array | [] | no | The search criteria |
| details | string | "model" | no | The detail level of the returned folder objects |

#### Allowed search fields
| Field | Type | Description |
| --- | --- | --- |
| created | int | Unix timestamp when the folder was created |
| updated | int | Unix timestamp when the folder was updated |
| edited | int | Unix timestamp when the user last renamed the folder |
| cseType | string | The client side encryption type |
| sseType | string | The server side encryption type |
| parent | string | The id of the parent folder |
| trashed | bool | Whether or not the folder is in the trash |
| favorite | bool | Whether or not the user has marked the folder as favorite |

#### Return value
The success status code is `200 Ok`
The return value is a list of folder objects that match the criteria with the given detail level

#### Notes
 - The property `trashed` will be set to `false` if not present
 - The property `parent` is only supported in 2019.5.0 and later
 - The list will not include hidden folders
 - The list will not include suspended folders where a parent folder is in the trash