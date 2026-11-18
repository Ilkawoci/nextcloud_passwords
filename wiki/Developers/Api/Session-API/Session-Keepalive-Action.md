The keepalive action will prevent the session from being closed.

# Contents

[[_TOC_]]

# Version History

| App Version | Change                          |
|-------------|---------------------------------|
| 2019.7.0    | Initial introduction of the API |

# Endpoints

## Session Keepalive Endpoint

### URL

| Url                          | Method | Session required | Rate Limit | Description            |
|------------------------------|--------|------------------|------------|------------------------|
| `/api/1.0/session/keepalive` | GET    | no               | -          | Keep the session alive |

### Request

The endpoint expects a GET request with no arguments.

#### Arguments

There are no arguments for this action.

#### Restrictions
- Nextcloud Authentication **required**

### Response

The success status code is `200 Ok`.
The action will return an object with the property `success` with the value `true`.

### Notes

- The setting `user.session.lifetime` determines the session lifetime