The request action returns all requirements that the client must meet in order to open an authenticated session.

# Contents

[[_TOC_]]

# Version History

| App Version | Change                          |
|-------------|---------------------------------|
| 2019.7.0    | Initial introduction of the API |

# Endpoints

## Request Session Endpoint

### URL

| Url                        | Method | Session required | Rate Limit | Description                               |
|----------------------------|--------|------------------|------------|-------------------------------------------|
| `/api/1.0/session/request` | GET    | no               | -          | Request the information to open a session |

### Request

The endpoint expects a GET request with no arguments.

#### Arguments

There are no arguments for this action.

#### Restrictions
- Nextcloud Authentication **required**

### Response

The success status code is `200 Ok`.
The returned object only contains a challenge or token if the user has set those up.
If nothing is required, the result is an empty object.

| Argument  | Type   | Description                                                                                                                     |
|-----------|--------|---------------------------------------------------------------------------------------------------------------------------------|
| challenge | object | An object with the [password challenge](../../Encryption#password-challenge). The property `type` specifies the challenge type. |
| token     | array  | An array with available [2FA token](../Token-Api). One of them must be used.                                                    |