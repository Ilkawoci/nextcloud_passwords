The account api allows general the management of the user account.

# Available api actions
| Action | Url | Method | Session required | Description |
| --- | --- | --- | --- |
| [reset](#the-reset-action)                 | `/api/1.0/account/reset`         | GET  | yes | Reset the user account |
| [reset](#the-reset-action)                 | `/api/1.0/account/reset`         | POST | yes | Reset the user account |
| [challenge/get](#the-get-challenge-action) | `/api/1.0/account/challenge/get` | GET  | no  | Get the current login challenge |
| [challenge/set](#the-set-challenge-action) | `/api/1.0/account/challenge/set` | POST | no  | Set or update the login challenge |


# The reset action
The reset action will delete all user data and settings and reset the account to a blank state.
This is a destructive action and can only be undone with a backup.
Any request without a code will return a random reset code.
The client should show the code to the user and request that the user enters the code manually to confirm the account reset.
If the request contains a code, the app will check if it matches the last given code and perform the reset if so.

#### Arguments
| Argument | Type | Default | Required | Description |
| --- | --- | --- | --- | --- |
| code | string | - | no | The code for the user to enter |

#### Return value
The success status code is `200 Ok` or `202 Accepted`.

| Argument | Type | Description |
| --- | --- | --- |
| status | string | Either "accepted" or "ok". Ok means the action was completed |
| code | string | Random string for the user to enter to confirm the request |

#### Notes
 - This request will close the session
 - This request requires an active session to work
 - This request will not delete any app or device tokens




# The get challenge action
The get challenge action will return the current password challenge.

#### Arguments
There are no arguments for this action.

#### Return value
The success status code is `200 Ok` .
The request returns an object with the challenge data and the property `token` which identifies the challenge type.
The format matches the one in of the `challenge` property from [request session](./Session-Api#the-request-action).




# The set challenge action
The set challenge action will create or update the user challenge and enable CSE and SSEv2.
It is not possible to specify the challenge type and the server always expects you to use the latest challenge method.
This is currently `PWDv1r1`.

#### Arguments
| Argument | Type | Default | Required | Description |
| --- | --- | --- | --- | --- |
| secret | string | - | yes | The computed secret of the new challenge |
| data | array | - | yes | An array with the challenge data |
| oldSecret | string/null | null | sometimes | If the user already has CSE set up, the secret of the current challenge is required |

#### Return value
The success status code is `200 Ok` .
The request returns an object property success and the value true if successful.

#### Notes
 - This action will fail if the `client-side-encryption` feature is disabled on the server
 - This action will authorize the current session if successful