The update action changes the properties of an existing share.

# Contents

[[_TOC_]]

# Version History

| App Version | Change                          |
|-------------|---------------------------------|
| 2018.1.0    | Initial introduction of the API |

# Endpoints

## Update Share Endpoint

### URL

| Url                     | Method | Session required | Rate Limit | Description              |
|-------------------------|--------|------------------|------------|--------------------------|
| `/api/1.0/share/update` | PATCH  | yes              | -          | Update an existing share |

### Request

The endpoint accepts a JSON formatted PATCH request body with an array the arguments below.

#### Arguments

| Argument  | Type       | Default | Required | Description                                        |
|-----------|------------|---------|----------|----------------------------------------------------|
| id        | string     | -       | yes      | The UUID of the share                              |
| expires   | int / null | null    | no       | Unix timestamp when the share will expire          |
| editable  | bool       | false   | no       | Whether or not the receiver can edit the password  |
| shareable | bool       | false   | no       | Whether or not the receiver can share the password |

#### Restrictions

- Nextcloud Authentication **required**
- Authenticated API Session **required**

### Response

The success status code is `200 Ok`

| Argument | Type   | Description           |
|----------|--------|-----------------------|
| id       | string | The UUID of the share |

### Notes

- You can only edit a share if it is owned by the user
- This command will fail if sharing is disabled