The close action will close the current session.

# Contents

[[_TOC_]]

# Version History

| App Version | Change                          |
|-------------|---------------------------------|
| 2019.7.0    | Initial introduction of the API |

# Endpoints

## Close Session Endpoint

### URL

| Url                      | Method | Session required | Rate Limit | Description               |
|--------------------------|--------|------------------|------------|---------------------------|
| `/api/1.0/session/close` | GET    | yes              | -          | Close the current session |

### Request

The endpoint expects a GET request with no arguments.

#### Arguments

There are no arguments for this action.

#### Restrictions
- Nextcloud Authentication **required**
- Authenticated API Session **required**

#### Response

The success status code is `200 Ok`.
The action will return an object with the property `success` with the value `true`.