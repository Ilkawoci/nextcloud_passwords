The session api allows clients to use an authorized session which is a requirement for client-side encryption.

# Available api actions

| Action                                              | Url                          | Method | Description                               |
|-----------------------------------------------------|------------------------------|--------|-------------------------------------------|
| [request](./Session-API/Session-Request-Action)     | `/api/1.0/session/request`   | GET    | Request the information to open a session |
| [open](./Session-API/Session-Open-Action)           | `/api/1.0/session/open`      | POST   | Open a new session                        |
| [close](./Session-API/Session-Close-Action)         | `/api/1.0/session/close`     | GET    | Close the current session                 |
| [keepalive](./Session-API/Session-Keepalive-Action) | `/api/1.0/session/keepalive` | GET    | Keep the session alive                    |

# Session ID

The passwords app will send the `nc_passwords` session cookie and the `X-API-SESSION` header with each request.
If you use the session cookie, you need to send the most recent cookie you received from the server with every following
request.
If you use the header, you need to send the most recent header you received from the server with every following
request.

**Node:** If the value of the cookie or header changes, it's likely that the server has terminated the previous session.

# Backwards compatibility

To remain compatible with older clients which do neither support encryption nor sessions, the api does currently not
require an authorized session if the user does not use encryption.

This may be changed in the future.

# Client deauthorization

If a client tries to open an authorized session with invalid credentials more than five times, it will be blocked.

If the client used an app or device token to access the api, the token will be deleted.
If the client used the password to access the api, the api will no longer accept password authentication.

The user will receive a notification about the incident.