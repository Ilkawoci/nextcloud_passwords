The list action returns all settings or the settings within a scope if one is given.

# Contents

[[_TOC_]]

# Version History
| App Version | Change                                |
|-------------|---------------------------------------|
| 2018.1.0    | Initial introduction of the API       |

# Endpoints
## List Settings POST Endpoint
### URL
| Url                      | Method     | Description                                |
|--------------------------|------------|--------------------------------------------|
| `/api/1.0/settings/list` | POST       | Lists settings within the requested scopes |

### Request
This endpoint accepts a JSON formatted request body with an object with the following properties.

| Arguments | Type  | Default | Required | Description                                                                                                  |
|-----------|-------|---------|----------|--------------------------------------------------------------------------------------------------------------|
| scopes    | array | -       | no       | An array with the names of all requested scopes as value. Available scopes are "server", "user" and "client" |

#### Restrictions
- Nextcloud Authentication **required**

#### Request Example
```json
{
    "scopes": ["user", "client"]
}
```

### Response
The return value is a JSON formatted object with all settings from the requested scopes.
The object has the names of the requested settings as the key with the current setting value as values.

| Status | MIME             | Type   | Description                 |
|--------|------------------|--------|-----------------------------|
| 200    | application/json | object | An object with the settings |

#### Response Example
```json
{
    "user.password.generator.strength": 1,
    "user.password.generator.numbers": true,
    "user.password.generator.special": true,
    […]
}
```

### Notes
- If a scope has been specified, only the values of this scope will be returned
- If the specified scopes do not exist, an empty array will be returned
- If the scopes property is empty, an empty array will be returned
- If the scopes property is missing, all existing settings will be returned


## List Settings GET Endpoint
### URL
| Url                      | Method | Description        |
|--------------------------|--------|--------------------|
| `/api/1.0/settings/list` | GET    | Lists all settings |

#### Restrictions
- Nextcloud Authentication **required**

### Response
The return value is a JSON formatted object with all settings.
The object has the names of the requested settings as the key with the current setting value as values.

| Status | MIME             | Type   | Description                 |
|--------|------------------|--------|-----------------------------|
| 200    | application/json | object | An object with the settings |

#### Response Example
```json
{
    "user.password.generator.strength": 1,
    "user.password.generator.numbers": true,
    "user.password.generator.special": true,
    […]
}
```