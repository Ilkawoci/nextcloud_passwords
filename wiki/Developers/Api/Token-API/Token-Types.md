# Token types

### The token object

The [session request](../Session-API/Session-Request-Action) action will return an array of possible tokens.
Each token object looks like this:

```json
{
  "type": "user-token",
  "id": "tokenid",
  "label": "Human readable label",
  "description": "A short description of the token process",
  "request": true
}
```

| Argument    | Type    | Description                                                                                                                            |
|-------------|---------|----------------------------------------------------------------------------------------------------------------------------------------|
| type        | string  | The token type is either `user-token` or `request-token`                                                                               |
| id          | object  | This id must be used in the [token request](./Token-Request-Action) and the [session open](../Session-API/Session-Open-Action) action. |
| label       | string  | The label of the token                                                                                                                 |
| description | string  | A description of the token adn the authentication process                                                                              |
| request     | boolean | Whether or not the authentication process must be triggered with the [request action](./Token-Request-Action)                          |

### The user token

Tokens with the type `user-token` require that the user enters a code which is then sent to the server.
It might be required that the [request action](./Token-Request-Action) is executed in order to provide the
user with that
code.

### The request token

Currently only the "Nextcloud Notification" is a `request-token`.
This token does not require a user input.
Instead the user will confirm the token trough a second app or device.
The [request action](./Token-Request-Action) will provide the token that needs to be sent to the server.