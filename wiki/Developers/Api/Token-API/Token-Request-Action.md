The request action is required for some token in order to send the user the token.
For example the email token will send an email to the users mail account.
It is recommended to only call this action if the user has chosen that token, not just trigger it for all available
tokens.

# Version History

| App Version | Change                          |
|-------------|---------------------------------|
| 2019.7.0    | Initial introduction of the API |

# Endpoints

## Get Token Endpoint

### URL

| Url                                 | Method | Session required | Rate Limit | Description                        |
|-------------------------------------|--------|------------------|------------|------------------------------------|
| `/api/1.0/token/{provider}/request` | GET    | no               | 3/60s      | Requests a token with the provider |

### Request

This Endpoint expects a GET request with the given arguments.

#### Arguments

| Argument | Type   | Description              |
|----------|--------|--------------------------|
| provider | string | The name of the provider |

#### Restrictions

- Nextcloud Authentication **required**
- Rate limit is applied

### Response

The success status code is `200 Ok`.

| Argument | Type    | Description                                                                                                 |
|----------|---------|-------------------------------------------------------------------------------------------------------------|
| success  | boolean | Whether or not the action was successful                                                                    |
| data     | object  | For the "Nextcloud Notification" token, this object will contain the property `token` with the token value. |