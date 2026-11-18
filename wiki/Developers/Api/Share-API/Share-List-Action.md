The list action lists all shares with the user as owner or receiver.

# Contents

[[_TOC_]]

# Version History

| App Version | Change                          |
|-------------|---------------------------------|
| 2018.1.0    | Initial introduction of the API |

# Endpoints

## List Shares Endpoint

### URL

| Url                   | Method | Session required | Rate Limit | Description                                   |
|-----------------------|--------|------------------|------------|-----------------------------------------------|
| `/api/1.0/share/list` | GET    | yes              | -          | List all shares with the default detail level |
| `/api/1.0/share/list` | POST   | yes              | -          | List all shares with the given detail level   |

### Request

The POST endpoint accepts a JSON formatted request body with an array the arguments below.

#### Arguments

| Argument | Type   | Default | Required | Description                                    |
|----------|--------|---------|----------|------------------------------------------------|
| details  | string | "model" | no       | The detail level of the returned share objects |

#### Restrictions

- Nextcloud Authentication **required**
- Authenticated API Session **required**

### Response

The success status code is `200 Ok`
The return value is a list of share objects with the given detail level

### Notes

- This action still works if sharing has been disabled