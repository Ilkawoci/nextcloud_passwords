> **This api will be removed permanently in version 2022.1.**
> 
> This is the legacy api of the original passwords app by Fallon Turner.
> The rewritten passwords app provides an implementation of this api, but does not recommend using it.
> Due to its limitations, this api returns incomplete data and does not support client side encryption.
> If you are planning to use the passwords api in your own application, please use the new api instead.

|Method |Scope      |Semantics|
|  ---  |    ---    | --- |
|GET    |collection |Retrieve all resources in a collection|
|GET    |resource   |Retrieve a single resource|
|HEAD   |collection |Retrieve all resources in a collection (header only)|
|HEAD   |resource   |Retrieve a single resource (header only)|
|POST   |collection |Create a new resource in a collection|
|PUT    |resource   |Update a resource|
|DELETE |resource   |Delete a resource|
|OPTIONS|any        |Return available HTTP methods and other options|

## Authentication
The api can only be accessed with CORS compatible clients and requires basic auth.

## Version
#### The Version Object
This api endpoint always returns the number `21`

#### Api Url
`https://{host}/index.php/apps/passwordfolders/api/0.1/version/`


## Passwords
#### The Category Object
| Property | Type | Writable | Description |
| --- |--- |--- | --- |
| id  | int | no | Numeric id of the category |
| user_id | string | no | Nextcloud user name of the current user |
| name | string | no | Label of the password. Added in 2019.5.0, otherwise not present |
| loginname | string | yes | Username for the password |
| pass | string | yes | The password |
| website | string | no | The domain of the password. It will always be the domain of the address |
| address | string | yes | The url of the website |
| notes | string | yes | Notes taken by the user. May containe markdown |
| deleted | bool | yes | True if the password is in the trash |
| creation_date | string | no | Date when the password was created. Format `YYYY-MM-DD` |
| properties | string | no | JSON encoded string with properties |
| properties.loginname | string | yes | See loginname |
| properties.address | string | yes | See address | 
| properties.strength | int | no | Security status of the password. (0 = broken, 10 = Weak, 20 = Good) |
| properties.length | int | no | Length of the password |
| properties.lower | bool | no | True if the password contains lowercase letters |
| properties.upper | bool | no | True if the password contains uppercase letters |
| properties.number | bool | no | True if the password contains numbers |
| properties.special | bool | no | True if the password contains special characters |
| properties.category | int | yes | Id of the fiest associated tag |
| properties.datechanged | string | yes | Date when the password was last edited. Format `YYYY-MM-DD` |
| properties.notes | string | yes | See notes |

#### Api Url
`https://{host}/index.php/apps/passwordfolders/api/0.1/passwords/{?id}`

#### Rest Methods
| Method | Fields | Description |
| ------ | --- | --- |
| POST   | pass, loginname, address, notes, category | Creates a new password with the given attributes. Reurns the password |
| PUT    | id, pass, loginname, address, notes, category, deleted, datechanged | Updates the password with the given id. Note that the id is a get parameter, the rest are JSON post parameters |
| GET    |  | Lists all passwords |
| GET    | id | Returns the passwords with the given id |
| DELETE | id | Moves the passwords with the given id to the trash and deletes it when the deleted attribute is set to true |

## Categories
#### The Category Object
| Property | Type | Writable | Description |
| --- |--- |--- | --- |
| id  | int | no | Numeric id of the category |
| user_id | string | no | Nextcloud user name of the current user |
| category_name | string | yes | The label of the tag |
| category_colour | string | yes | The color of the tag as hexadecimal number *without* the leading '#' |

#### Api Url
`https://{host}/index.php/apps/passwordfolders/api/0.1/categories/{?id}`

#### Rest Methods
| Method | Fields | Description |
| ------ | --- | --- |
| POST   | category_name, category_colour | Creates a new tag with the given attributes. Returns the created tag. |
| GET    |  | Lists all tags |
| GET    | id | Returns the tag with the given id |
| DELETE | id | Moves the tag with the given id to the trash |

## Incompatibilities / Notes
- Some passwords will not have addresses or websites as this is not a required field in the new app
- The delete command will move passwords into the trash if they do not have the deleted attribute
- The attribute strength has only three values: 0 - 10 - 20
- The sharing api is not supported.
- Categories are mapped to tags. There is only the first category shown.
- Tags will only be moved in the trash when deleted
- Client side encryption or SSEv2 encryption are not supported
- The new app will fill all fields with values, the legacy app only the properties