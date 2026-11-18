The get action retrieves the value of one or more settings.
This action accepts an array of strings where each value is the name of one setting.

# Contents

[[_TOC_]]

# Version History
| App Version | Change                                |
|-------------|---------------------------------------|
| 2018.1.0    | Initial introduction of the API       |

# Endpoints
## Get Settings POST Endpoint
### URL
| Url                       | Method     | Description                      |
|---------------------------|------------|----------------------------------|
| `/api/1.0/settings/get`   | POST       | Returns the value of one setting |

### Request
This endpoint accepts a JSON formatted request body with an array containing the full name of the settings.
At least one setting name should be provided as the request is useless otherwise.

#### Restrictions
- Nextcloud Authentication **required**

#### Request Example
```json
[
    "user.password.generator.numbers"
]
```

### Response
The success status code is `200 Ok`.
The return value is an object with the requested settings.
The object has the names of the requested settings as the key with the current setting value as values.

| Status | MIME             | Type   | Description                 |
|--------|------------------|--------|-----------------------------|
| 200    | application/json | object | An object with the settings |

#### Response Example
```json
{
    "user.password.generator.numbers" : false
}
```

#### Notes
- If the setting is not defined, it will default to `null`
- Accessing an undefined setting in the `client` scope will not create it