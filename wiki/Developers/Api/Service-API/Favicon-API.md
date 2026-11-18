The favicon endpoint returns a png favicon icon for the given domain.

# Contents

[[_TOC_]]

# Version History

| App Version | Change                          |
|-------------|---------------------------------|
| 2018.1.0    | Initial introduction of the API |

# Endpoints

## Website Favicon GET Endpoint

### URL

| Path                                       | Method | Session required | Rate Limit |
|--------------------------------------------|--------|------------------|------------|
| `/api/1.0/service/favicon/{domain}/{size}` | GET    | no               | 15/15s     |

### Request

This endpoint expects a GET request with the parameters below.

#### Arguments

| Arguments | Type   | Default | Required | Description                                                                                |
|-----------|--------|---------|----------|--------------------------------------------------------------------------------------------|
| domain    | string | -       | yes      | The domain name                                                                            |
| size      | int    | 32      | no       | The size of the favicon in pixels. I must be a multiple of `8` and between `16` and `256`. |

#### Restrictions

- Nextcloud Authentication **required**

#### Response

The success status code is `200 Ok`.
This action returns a png image file on success

| Status | MIME      | Description           |
|--------|-----------|-----------------------|
| 200    | image/png | The avatar image file |
| 500    | text/html | Error page            |
| 502    | text/html | Error page            |

#### Notes

- If no favicon can be found, a default image will be generated
- Favicons rarely change and can be cached for a long time
