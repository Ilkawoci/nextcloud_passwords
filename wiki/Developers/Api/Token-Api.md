The token api allows clients to trigger 2FA token providers in order to send the user a 2FA token.

# Object and Token Types
See the [token types](./Token-API/Token-Types) documentation.

# Available api actions

| Action                                      | Url                                 | Method | Description                        |
|---------------------------------------------|-------------------------------------|--------|------------------------------------|
| [request](./Token-API/Token-Request-Action) | `/api/1.0/token/{provider}/request` | GET    | Requests a token with the provider |
