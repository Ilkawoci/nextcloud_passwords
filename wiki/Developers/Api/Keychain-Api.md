The keychain api allows the management of the client-side encryption keychains.
This api is only available if the account has a challenge.

# Available api actions
| Action | Url | Method | Session required | Description |
| --- | --- | --- | --- | --- |
| [get](#the-get-action) | `/api/1.0/keychain/get` | GET  | yes | Fetch all keychains of the user |
| [set](#the-set-action) | `/api/1.0/keychain/set` | POST | yes | Update or create a keychain |


# The get action
The get action will return an object with all keychains.

#### Arguments
There are no arguments for this action.

#### Return value
The success status code is `200 Ok` .
The request returns an object with the keychain type as properties and the encrypted keychain data as value of that property.
The format matches the one in of the `keys` property from [open session](./Session-Api#the-open-action).




# The set action
The set action will create or update a keychain.

#### Arguments
| Argument | Type | Default | Required | Description |
| --- | --- | --- | --- | --- |
| id | string | - | yes | Id or type of the keychain |
| data | string | - | yes | The encrypted keychain data |

#### Return value
The success status code is `200 Ok` .
The request returns an object with the property id and the keychain id / type as value.

#### Notes
 - Keychains can only be created if a challenge is set