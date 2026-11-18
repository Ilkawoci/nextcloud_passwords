The delete action deletes a share.

# Contents

[[_TOC_]]

# Version History

| App Version | Change                          |
|-------------|---------------------------------|
| 2018.1.0    | Initial introduction of the API |

# Endpoints

## Delete Share Endpoint

### URL

| Url                     | Method | Session required | Rate Limit | Description    |
|-------------------------|--------|------------------|------------|----------------|
| `/api/1.0/share/delete` | DELETE | yes              | -          | Delete a share |

### Request

The endpoint accepts a JSON formatted DELETE request body with an array the arguments below.

#### Arguments

| Arguments | Type   | Default | Required | Description         |
|-----------|--------|---------|----------|---------------------|
| id        | string | -       | yes      | The id of the share |


#### Restrictions

- Nextcloud Authentication **required**
- Authenticated API Session **required**

### Response

The success status code is `200 Ok`

| Argument | Type   | Description           |
|----------|--------|-----------------------|
| id       | string | The UUID of the share |

### Notes

- You can only delete shares owned by the user.
- If you want to delete a share where the current user is the receiver, you need to delete the password instead
- This action still works if sharing has been disabled