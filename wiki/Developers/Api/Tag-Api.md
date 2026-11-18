The tag api allows listing, creating, updating and deleting tags.

# The Tag Object
| Property | Type | Writable | Encrypted | Versioned | Length | Description |
| --- | --- | --- | --- | --- | --- | --- |
| id | string | no | no | no | 36 | The UUID of the tag |
| label | string | yes | yes | yes | 48 | User defined label of the tag |
| color | string | yes | yes | yes | 8 | The color of the tag. Any valid CSSv3 color is accepted. |
| revision | string | no | no | yes | 36 | UUID of the current revision |
| cseType | string | yes | no | yes | 10 | Type of the used client side encryption |
| cseKey | string | yes | no | yes | 36 | UUID of the key used for client side encryption |
| sseType | string | no | no | yes | 10 | Type of the used server side encryption |
| client | string | no | no | yes | 256 | Name of the client which created this revision |
| hidden | bool | yes | no | yes | 1 | Hides the tag in list / find actions |
| trashed | bool | no | no | yes | 1 | True if the tag is in the trash |
| favorite | bool | yes | no | yes | 1 | True if the user has marked the tag as favorite |
| created | int | no | no | no | 12 | Unix timestamp when the tag was created |
| updated | int | no | no | yes | 12 | Unix timestamp when the tag was updated |
| edited | int | yes | no | yes | 12 | Unix timestamp when the user last edited the tag name or color |

#### Detail Levels
| Level | Description |
| --- | --- |
| model | Returns the base model |
| +revisions | Adds the revisions property which contains all revisions. A revision consists of all properties marked as versioned and its own created property |
| +passwords | Adds the passwords property with the base model of all passwords in this tag |
| +password-ids | Adds the passwords property with the ids of all passwords in this tag. Can not be used with `+passwords` |
| +password-tags | Loads the tags for all passwords in this tag in their respective model. Can only be used with `+passwords` |

#### Enhanced API special properties
The properties "revisions" and "passwords" are also processed if necessary.

| Property | Type | Description |
| --- | --- | --- |
| type | string | Object type, the value is "tag" |
| created | Date | Date when the tag was created |
| updated | Date | Date when the tag was last updated |
| edited | Date | Date when the user last edited the tag |




# Available api actions
| Action | Url | Method | Session required | Description |
| --- | --- | --- | --- | --- |
| [list](#the-list-action) | `/api/1.0/tag/list` | GET | yes | List all tags with the default detail level |
| [list](#the-list-action) | `/api/1.0/tag/list` | POST | yes | List all tags with the given detail level |
| [show](#the-show-action) | `/api/1.0/tag/show` | POST | yes | Show a tag |
| [find](#the-find-action) | `/api/1.0/tag/find` | POST | yes | Find tags matching given criteria |
| [create](#the-create-action) | `/api/1.0/tag/create` | POST | yes | Create a new tag |
| [update](#the-update-action) | `/api/1.0/tag/update` | PATCH | yes | Update an existing tag |
| [delete](#the-delete-action) | `/api/1.0/tag/delete` | DELETE | yes | Delete a tag |
| [restore](#the-restore-action) | `/api/1.0/tag/restore` | PATCH | yes | Restore an earlier state of a tag |




# The create action
The create action creates a new tag with the given attributes.

#### Arguments
| Argument | Type | Default | Required | Description |
| --- | --- | --- | --- | --- |
| label | string | - | yes | The label of the tag |
| color | string | - | yes | The color of the tag |
| cseType | string | "none" | no | The client side encryption type |
| cseKey | string | "" | no | The UUID of the key used for client side encryption. Required if `cseType` not "none" |
| edited | int | 0 | no | Unix timestamp when the user has last edited the tag |
| hidden | bool | false | no | Whether or not the tag should be hidden |
| favorite | bool | false | no | Whether or not the user has marked this tag as favorite |

#### Return value
The success status code is `201 Created`

| Argument | Type | Description |
| --- | --- | --- |
| id | string | The UUID of the tag |
| revision | string | The UUID of the revision |

#### Notes
 - If the `edited` argument is "0", missing or in the future, the current time will be used




# The update action
The update action creates a new revision of a tag with an updated set of attributes.

#### Arguments
| Argument | Type | Default | Required | Description |
| --- | --- | --- | --- | --- |
| id | string | - | yes | The id of the tag |
| revision | string | - | no | The current revision known to the client. If not the latest revision, the request will fail. |
| label | string | - | yes | The label of the tag |
| color | string | - | yes | The color of the tag |
| cseType | string | "none" | no | The client side encryption type |
| cseKey | string | "" | no | The UUID of the key used for client side encryption. Required if `cseType` not "none" |
| edited | int | 0 | no | Unix timestamp when the user has last edited the tag |
| hidden | bool | false | no | Whether or not the tag should be hidden |
| favorite | bool | false | no | Whether or not the user has marked this tag as favorite |

#### Return value
The success status code is `200 Ok`

| Argument | Type | Description |
| --- | --- | --- |
| id | string | The UUID of the tag |
| revision | string | The UUID of the new revision |

#### Notes
 - If hou hide a tag, the tag will be no longer visible in passwords which are not hidden, but the passwords will be visible in the tag
 - If the `revision` argument is present, the value must match the latest revision or the action will fail with an error.
   This prevents the client from overwriting a more recent revision on the server with old data.
 - If the `edited` argument is "0" or missing, the timestamp from the last revision will be used
 - If the `edited` time is in the future, the current time will be used




# The delete action
The delete action moves a tag to the trash or deletes it completely if it is already in the trash.

#### Arguments
| Arguments | Type | Default | Required | Description |
| --- | --- | --- | --- | --- |
| id | string | - | yes | The id of the tag |
| revision | string | - | no | Assumed current revision of the tag (Since 2019.6.0) |

#### Return value
The success status code is `200 Ok`

| Argument | Type | Description |
| --- | --- | --- |
| id | string | The UUID of the tag |
| revision | string | The UUID of the new revision. Only if the tag was moved to the trash |

#### Notes
 - If a tag is moved to the trash, the relation to all passwords which are not in trash will be hidden from the password
 - If a tag is deleted, all relations to passwords are deleted
 - If the `revision` is set, the tag will only be deleted if that revision is the current revision. 
   This way, a tag is not accidentally deleted instead of trashed if the client is out of sync.




# The restore action
The restore action can restore an earlier state of a tag.

#### Arguments
| Arguments | Type | Default | Required | Description |
| --- | --- | --- | --- | --- |
| id | string | - | yes | The id of the tag |
| revision | string | - | no | The id of the revision |

#### Return value
The success status code is `200 Ok`

| Argument | Type | Description |
| --- | --- | --- |
| id | string | The UUID of the tag |
| revision | string | The UUID of the new revision |

#### Notes
 - If no revision is given and the tag is in trash, it will be removed from trash
 - If no revision is given and the tag is not in trash, nothing is done
 - If a revision is given and the revision is marked as in trash, it will be removed from trash
 - This action will always create a new revision
 - The server side encryption type may change
 - Deleted tags can not be restored




# The show action
The show action lists the properties of a single tag.

#### Arguments
| Argument | Type | Default | Required | Description |
| --- | --- | --- | --- | --- |
| id | string | - | yes | The id of the tag |
| details | string | "model" | no | The detail level of the returned tag object |

#### Return value
The success status code is `200 Ok`
The return value is a tag object with the given detail level

#### Notes
 - This is the only action that can access hidden tags




# The list action
The list action lists all tags of the user except those in trash and the hidden ones.

#### Arguments
| Argument | Type | Default | Required | Description |
| --- | --- | --- | --- | --- |
| details | string | "model" | no | The detail level of the returned tag objects |

#### Return value
The success status code is `200 Ok`
The return value is a list of tag objects with the given detail level

#### Notes
 - The list will not include trashed tags
 - The list will not include hidden tags




# The find action
The find action can be used to find all tags matching the given search criteria.
Only a specific set of fields is allowed in the criteria.
How the criteria array works is explained on the [object search page](./Object-Search).

#### Arguments
| Argument | Type | Default | Required | Description |
| --- | --- | --- | --- | --- |
| criteria | array | [] | no | The search criteria |
| details | string | "model" | no | The detail level of the returned tag objects |

#### Allowed search fields
| Field | Type | Description |
| --- | --- | --- |
| created | int | Unix timestamp when the tag was created |
| updated | int | Unix timestamp when the tag was updated |
| edited | int | Unix timestamp when the user last renamed the tag |
| cseType | string | The client side encryption type |
| sseType | string | The server side encryption type |
| trashed | bool | Whether or not the tag is in the trash |
| favorite | bool | Whether or not the user has marked the tag as favorite |

#### Return value
The success status code is `200 Ok`
The return value is a list of tag objects that match the criteria with the given detail level

#### Notes
 - The property `trashed` will be set to `false` if not present
 - The list will not include hidden tags