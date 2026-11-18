The set action updates the value of one or more settings.
This action accepts an object where each key is the name of a setting and the value is the new value

# Contents

[[_TOC_]]

# Version History
| App Version | Change                                |
|-------------|---------------------------------------|
| 2018.1.0    | Initial introduction of the API       |

# Endpoints
## Set Settings POST Endpoint
### URL
| Url                       | Method     | Description                      |
|---------------------------|------------|----------------------------------|
| `/api/1.0/settings/set`   | POST       | Sets the value of a setting      |

### Request
This endpoint accepts a JSON formatted request body with an object containing the full name of the settings and their values.
At least one setting name should be provided as the request is useless otherwise.

Client settings may have a key with a maximum length of 48 characters (excluding the 7 characters for `client.`) and the value can have a maximum length of 128 characters.

#### Restrictions
- Nextcloud Authentication **required**
- Authenticated API Session **required**

#### Request Example
```json
{
    "user.password.generator.numbers": true
}
```

### Response
The success status code is `200 Ok`.
The response is an object with the updated settings.
The object has the names of the updated settings as the key with the updated setting value as values.

| Status | MIME             | Type   | Description                 |
|--------|------------------|--------|-----------------------------|
| 200    | application/json | object | An object with the settings |
| 400    | application/json | object | An error object             |

#### Response Example
```json
{
    "user.password.generator.numbers" : true
}
```

#### Errors
| Code     | Message        | Description                                                          |
|----------|----------------|----------------------------------------------------------------------|
| 9b106704 | Key too long   | The name of a client setting exceeds the maximum of 48 characters.   |
| e5eeb777 | Value too long | The value of a client setting exceeds the maximum of 128 characters. |

### Notes
- If the scope is not writable, the setting will not be saved and default to `null` in the return value
- If the setting is not defined in the `user` scope, it will not be saved and default to `null` in the return value
- It is not possible to change settings in the "server" scope
