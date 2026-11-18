The avatar endpoint returns a png avatar icon for the given user id.

# Contents

[[_TOC_]]

# Version History

| App Version | Change                          |
|-------------|---------------------------------|
| 2018.1.0    | Initial introduction of the API |

# Endpoints

## User Avatar GET Endpoint

### URL

| Path                                    | Method | Session Required | Rate Limit |
|-----------------------------------------|--------|------------------|------------|
| `/api/1.0/service/avatar/{user}/{size}` | GET    | no               | 15/15s     |

### Request

This endpoint expects a GET request with the parameters below.

#### Arguments

| Arguments | Type   | Default | Required | Description                                                                               |
|-----------|--------|---------|----------|-------------------------------------------------------------------------------------------|
| user      | string | -       | yes      | The user id                                                                               |
| size      | int    | 32      | no       | The size of the avatar in pixels. I must be a multiple of `8` and between `16` and `256`. |

#### Restrictions

- Nextcloud Authentication **required**

### Response

The success status code is `200 Ok`.
This action returns a png image file on success

| Status | MIME      | Description           |
|--------|-----------|-----------------------|
| 200    | image/png | The avatar image file |
| 500    | text/html | Error page            |
| 502    | text/html | Error page            |

### Notes

- If the user did not specify an avatar a default image will be generated