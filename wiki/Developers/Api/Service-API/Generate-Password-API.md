The password endpoint uses the service defined in the app settings andthe users settings to generate a new password
which is not in the breached passwords database.

# Contents

[[_TOC_]]

# Version History

| App Version | Change                                |
|-------------|---------------------------------------|
| 2018.1.0    | Initial introduction of the API       |
| 2018.12.0   | The "strength" limit of 4 is enforced |

# Endpoints

## Generate Password GET Endpoint

This API endpoint generates a new password with the users default settings

### URL

| Path                        | Method | Session required | Rate Limit |
|-----------------------------|--------|------------------|------------|
| `/api/1.0/service/password` | GET    | yes              | 60/60s     |

### Request

This endpoint expects a GET request with no parameters and will generate a password with the users default settings.

#### Restrictions

- Nextcloud Authentication **required**
- Authenticated API Session **required**

### Response

The success status code is `200 Ok`

| Status | MIME             | Type   | Description                       |
|--------|------------------|--------|-----------------------------------|
| 200    | application/json | object | An object with generated password |
| 503    | application/json | object | Error object.                     |
| 502    | application/json | object | Error object.                     |

| Property | Type   | Description                                                 |
|----------|--------|-------------------------------------------------------------|
| password | string | The generated password                                      |
| words    | string | The words used in the password                              |
| strength | int    | The strength setting used                                   |
| numbers  | bool   | Whether or not numbers were used in the password            |
| special  | bool   | Whether or not special characters were used in the password |

#### Response Example

```json
{
  "password": "FutureReleaseDrawRed",
  "words": [
    "Future",
    "Release",
    "Draw",
    "Red"
  ],
  "strength": 1,
  "numbers": false,
  "special": false
}

```

### Errors

| Code     | Message                     | Description                                                     |
|----------|-----------------------------|-----------------------------------------------------------------|
| 74b8fcb3 | Unable to generate password | The service could not create a password for the given settings. |
| 43486931 | Internal Words API Error    | The configured random words service failed.                     |

### Notes

- Generated passwords are checked against the breached passwords database automatically

## Generate Password POST Endpoint

Generate a password with the settings provided in the request and use the users default setting for any not provided
setting.

### URL

| Path                        | Method | Session required | Rate Limit |
|-----------------------------|--------|------------------|------------|
| `/api/1.0/service/password` | POST   | yes              | 60/60s     |

### Request

This endpoint accepts a JSON formatted request body with an object with the following properties.

| Argument | Type | Default | Required | Description                                                                                   |
|----------|------|---------|----------|-----------------------------------------------------------------------------------------------|
| strength | int  | 1       | no       | A value from 0 to 4. (`>=0 && <=4`) A higher value creates a longer and more complex password |
| numbers  | bool | false   | no       | Whether or not numbers should be used in the password                                         |
| special  | bool | false   | no       | Whether or not special characters should be used in the password                              |

#### Restrictions

- Nextcloud Authentication **required**
- Authenticated API Session **required**

### Response

The success status code is `200 Ok`

| Status | MIME             | Type   | Description                       |
|--------|------------------|--------|-----------------------------------|
| 200    | application/json | object | An object with generated password |
| 503    | application/json | object | Error object.                     |
| 502    | application/json | object | Error object.                     |

| Property | Type   | Description                                                 |
|----------|--------|-------------------------------------------------------------|
| password | string | The generated password                                      |
| words    | string | The words used in the password                              |
| strength | int    | The strength setting used                                   |
| numbers  | bool   | Whether or not numbers were used in the password            |
| special  | bool   | Whether or not special characters were used in the password |

#### Response Example

```json
{
  "password": "FutureReleaseDrawRed",
  "words": [
    "Future",
    "Release",
    "Draw",
    "Red"
  ],
  "strength": 1,
  "numbers": false,
  "special": false
}
```

### Errors

| Code     | Message                     | Description                                                     |
|----------|-----------------------------|-----------------------------------------------------------------|
| 74b8fcb3 | Unable to generate password | The service could not create a password for the given settings. |
| 43486931 | Internal Words API Error    | The configured random words service failed.                     |

### Notes

- Generated passwords are checked against the breached passwords database automatically