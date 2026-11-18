The open action will open an authenticated session with read and write access to all api endpoints.

# Contents

[[_TOC_]]

# Version History

| App Version | Change                          |
|-------------|---------------------------------|
| 2019.7.0    | Initial introduction of the API |

# Endpoints

## Open Session Endpoint

### URL

| Url                     | Method | Session required | Rate Limit | Description        |
|-------------------------|--------|------------------|------------|--------------------|
| `/api/1.0/session/open` | POST   | no               | 10/30s     | Open a new session |

### Request

The endpoint accepts a JSON formatted POST request body with an array the arguments below.

#### Arguments

Which arguments are required can be found out with the request action.

| Argument  | Type   | Default | Required         | Description                                                                   |
|-----------|--------|---------|------------------|-------------------------------------------------------------------------------|
| challenge | string | -       | yes (if enabled) | The solution of the [password challenge](../../Encryption#password-challenge) |
| token     | object | -       | yes (if enabled) | An object with the id of the token as property and the token as value         |

#### Restrictions
- Nextcloud Authentication **required**
- Rate limit is applied

### Response

The success status code is `200 Ok`.
The `keys` property is only available if encryption is used.

| Argument | Type    | Description                                                                                                                  |
|----------|---------|------------------------------------------------------------------------------------------------------------------------------|
| success  | boolean | Whether or not the action was successful                                                                                     |
| keys     | object  | An object with the CSE keychains. The name of the property is the keychain and the value are the encrypted keychain contents |

### Notes

- After five failed login attempts, the client will be [deauthorized](../Session-Api#client-deauthorization).
- If the `token` argument is provided with more than one token, all of them have to be correct or the validation will
  fail