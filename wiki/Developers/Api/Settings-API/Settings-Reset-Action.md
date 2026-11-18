The reset action will reset the value of one or more settings to the default.
This action accepts an array of strings where each value is the name of one setting.

# Contents

[[_TOC_]]

# Version History
| App Version | Change                                |
|-------------|---------------------------------------|
| 2018.1.0    | Initial introduction of the API       |

# Endpoints
## List Settings POST Endpoint
### URL
| Url                       | Method | Description      |
|---------------------------|--------|------------------|
| `/api/1.0/settings/reset` | POST   | Resets a setting |

### Request
This endpoint accepts a JSON formatted request body with an array containing the full name of the settings.
At least one setting name should be provided as the request is useless otherwise.

#### Restrictions
- Nextcloud Authentication **required**
- Authenticated API Session **required**

#### Request Example
```json
[
    "user.password.generator.numbers",
    "user.password.generator.special",
    "client.test"
]
```

### Response
The success status code is `200 Ok`.
The response is an object with the changed settings.
The object has the names of the changed settings as the key with the updated setting value as values.

| Status | MIME             | Type   | Description                 |
|--------|------------------|--------|-----------------------------|
| 200    | application/json | object | An object with the settings |

#### Response Example
```json
{
    "user.password.generator.numbers": false,
    "user.password.generator.special": false,
    "client.test": null
}
```

#### Notes
- If you reset a setting in the client scope, it will be deleted and no longer appear in the list action
- If a setting in the client scope is deleted, the value in the response will be `null`
- If the setting does not exist, the value will be `null`
- It is not possible to reset settings in the "server" scope