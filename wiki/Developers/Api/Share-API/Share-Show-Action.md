The show share action lists the properties of a single share.

# Contents

[[_TOC_]]

# Version History

| App Version | Change                          |
|-------------|---------------------------------|
| 2018.1.0    | Initial introduction of the API |

# Endpoints

## Show Share Endpoint

### URL

| Url                   | Method | Session required | Rate Limit | Description  |
|-----------------------|--------|------------------|------------|--------------|
| `/api/1.0/share/show` | POST   | yes              | -          | Show a share |

### Request

The endpoint accepts a JSON formatted POST request body with an array the arguments below.

#### Arguments

| Argument | Type   | Default | Required | Description                                   |
|----------|--------|---------|----------|-----------------------------------------------|
| id       | string | -       | yes      | The id of the share                           |
| details  | string | "model" | no       | The detail level of the returned share object |

#### Restrictions

- Nextcloud Authentication **required**
- Authenticated API Session **required**

### Response

The success status code is `200 Ok`
The return value is a share object with the given detail level

### Notes

- This action still works if sharing has been disabled