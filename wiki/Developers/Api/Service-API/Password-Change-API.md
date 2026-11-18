The password change url endpoint will attempt to determine the url of the "Change password page" for the given domain.
It uses the [.well-known/change-password](https://web.dev/articles/change-password-url) standard as well as an internal
database of password change URLs for common services to determine the URL.

# Contents

[[_TOC_]]

# Version History

| App Version | Change                          |
|-------------|---------------------------------|
| 2023.3.0    | Initial introduction of the API |
| 2024.2.0    | Addition of rate limits         |

## Password Change Page POST Endpoint

Retrieves the url of the password change page for the given domain.

#### URL

| Path                               | Method | Session required | Rate Limit |
|------------------------------------|--------|------------------|------------|
| `/api/1.0/service/password-change` | POST   | yes              | 2/8s       |

#### Request

This endpoint accepts a JSON formatted request body with an object with the following properties.

| Property | Type   | Default | Required | Description     |
|----------|--------|---------|----------|-----------------|
| domain   | string | -       | yes      | The domain name |

#### Restrictions

- Nextcloud Authentication **required**
- Authenticated API Session **required**
- Rate limit is applied

#### Response

The success status code is `200 Ok` and `404 Not Found` if no url could be determined.

| Status | MIME             | Type   | Description                                        |
|--------|------------------|--------|----------------------------------------------------|
| 200    | application/json | object | An object with the url of the password change page |
| 404    | application/json | object | An object where the property `url` is `null`.      |
| 429    | text/html        | -      | Rate limit exceeded                                |

| Property | Type   | Description                                        |
|----------|--------|----------------------------------------------------|
| url      | string | The URL of the password change page for the domain |

#### Response Example

```json
{
  "url": "https://test.passwordsapp.org/settings/user/security"
}
```

#### Notes

- In case of a 404 status code, some servers may replace the response with an HTML page.