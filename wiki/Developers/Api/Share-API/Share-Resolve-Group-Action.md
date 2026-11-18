This is a temporary endpoints to allow clients to resolve all members of a group for the purpose of sharing an entry
with them.
Since the backend does not currently support group sharing, a client side implementation is available that simply shares
with all members of a group instead.
This endpoint may be removed without further notice.

# Contents

[[_TOC_]]

# Version History

| App Version | Change                          |
|-------------|---------------------------------|
| 2026.9.0    | Initial introduction of the API |

# Endpoints

## Resolve Group Members Endpoint

### URL

| Url                                       | Method | Session required | Rate Limit | Description                            |
|-------------------------------------------|--------|------------------|------------|----------------------------------------|
| `/api/1.0/share/recipients/resolve-group` | POST   | yes              | 20/30s     | Resolve all members of the given group |

### Request

The endpoint accepts a JSON formatted POST request body with an array the arguments below.

#### Arguments

| Argument | Type   | Default | Required | Description                    |
|----------|--------|---------|----------|--------------------------------|
| groupId  | string | -       | yes      | The id of the group to resolve |

#### Restrictions

- Nextcloud Authentication **required**
- Authenticated API Session **required**

#### Request Example

```json
{
  "groupId": "users"
}
```

### Response

The success status code is `200 Ok`.
The return value is an array with the user id as key and the user display name as value.
The array is at maximum around 256 entries long.
If the autocompletion is disabled by the administrator the result is an empty array

#### Response Example

```json
{
  "admin": "admin",
  "erika": "Erika Mustermann",
  "jane": "Jane Doe",
  "john": "John Doe",
  "mario": "Mario Rossi",
  "max": "Max Mustermann",
  "pierre": "Pierre Dupont"
}
```

#### Notes

- This command will fail if sharing is disabled
- The user must be a member of the given group or the endpoint will return an empty response
- Group sharing must be enabled or the endpoint will return an empty response