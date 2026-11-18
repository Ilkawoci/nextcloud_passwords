The recipients action returns an array of users and groups that the current user can share passwords with.

# Contents

[[_TOC_]]

# Version History

| App Version | Change                          |
|-------------|---------------------------------|
| 2026.9.0    | Initial introduction of the API |

# Endpoints

## Find Share Recipients Endpoint

### URL

| Url                         | Method | Session required | Rate Limit | Description                                       |
|-----------------------------|--------|------------------|------------|---------------------------------------------------|
| `/api/1.0/share/recipients` | GET    | yes              | 20/30s     | Find sharing recipients matching the search query |
| `/api/1.0/share/recipients` | POST   | yes              | 20/30s     | Find sharing recipients matching the search query |

### Request

The endpoint accepts a JSON formatted POST request body with an array the arguments below.

#### Arguments

| Argument   | Type    | Default | Required | Description                                                  |
|------------|---------|---------|----------|--------------------------------------------------------------|
| search     | string  | -       | no       | Find users containing the search string                      |
| limit      | int     | 5       | no       | The maximum amount of matches to return. Range from 5 - 256. |
| withGroups | boolean | true    | no       | Include matching groups in the response                      |

#### Restrictions

- Nextcloud Authentication **required**
- Authenticated API Session **required**

#### Request Example
```json
{
  "search": "m",
  "limit": 25,
  "withGroups": true
}
```

### Response

The success status code is `200 Ok`.
The return value is an array of objects with the basic user or group information for each match.
The `type` property defines if the match is a group or user.
The `id` property contains the user/group id, the `name` property contains the display name and the `context` property may contain additional identifying information such as the email address.
The array is at maximum around 256 entries long.

#### Response Example
```json
[
  {
    "id": "admin",
    "name": "admin",
    "type": "user",
    "context": "admin@passwords.app"
  },
  {
    "id": "erika",
    "name": "Erika Mustermann",
    "type": "user",
    "context": "erika@passwordsapp.org"
  },
  {
    "id": "chrome",
    "name": "Google Chrome",
    "type": "user",
    "context": "chrome@passwordsapp.org"
  },
  {
    "id": "mario",
    "name": "Mario Rossi",
    "type": "user",
    "context": "mario@passwordsapp.org"
  }
]
```

#### Notes

- This command will fail if sharing is disabled
- The `limit` can not be less than 5 or more than 256
- If autocompletion is disabled, the result will only contain exact matches of either the uid, display name or email address