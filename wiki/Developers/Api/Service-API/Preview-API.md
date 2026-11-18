The preview endpoint returns a jpeg preview image for the given domain.

# Contents

[[_TOC_]]

# Version History

| App Version | Change                          |
|-------------|---------------------------------|
| 2018.1.0    | Initial introduction of the API |

# Endpoints

## Website Preview GET Endpoint

#### URL

| Path                                                        | Method | Session required | Rate Limit |
|-------------------------------------------------------------|--------|------------------|------------|
| `/api/1.0/service/preview/{domain}/{view}/{width}/{height}` | GET    | no               | 10/30s     |

### Request

#### Arguments

| Arguments | Type       | Default   | Required | Description                                                                                                                                          |
|-----------|------------|-----------|----------|------------------------------------------------------------------------------------------------------------------------------------------------------|
| domain    | string     | -         | yes      | The domain name                                                                                                                                      |
| view      | string     | "desktop" | no       | Can be `desktop` or `mobile`. Sets device name / viewports.                                                                                          |
| width     | string/int | 640       | no       | The width of the resulting image in pixels. Must be a multiple of 10 and between 240 and 1280. If `0` is given, the service will decide the width.   |
| height    | string/int | "360..."  | no       | The height of the resulting image in pixels. Must be a multiple of 10 and between 240 and 1280. If `0` is given, the service will decide the height. |

###### Range For Width or Height

The `width` and `height` can also be a string which defines a range for the size.
In this case, the service will attempt to find a size within the given range that fits the actual preview the best.

| Pattern     | Example      | Description                                                             |
|-------------|--------------|-------------------------------------------------------------------------|
| `FROM...`   | `640...`     | A range where the minimum size is 640px.                                |
| `FROM...TO` | `640...1080` | A range where the minimum size is 640px and the maximum size is 1080px. |
| `...TO`     | `...1080`    | A range where the maximum size is 1080px.                               |

#### Restrictions

- Nextcloud Authentication **required**
- Rate limit is applied

### Response

The success status code is `200 Ok`.
This action returns a jpeg image file on success

| Status | MIME       | Description           |
|--------|------------|-----------------------|
| 200    | image/jpeg | The avatar image file |
| 500    | text/html  | Error page            |
| 502    | text/html  | Error page            |

### Notes

- If no image can be created, a default image will be used
- This action is known to be slow if the cache is empty
- Previews rarely change and can be cached for a long time
- If a width and height were specified, the image will be cropped to fill the area
- The resulting image will always have a minimum width and height of 240 pixels