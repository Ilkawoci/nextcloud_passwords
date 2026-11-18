The service api provides access to general services provided by the Passwords app.

# Available api endpoints
| Endpoint                                             | Url                                                         | Methods    | Description                                           |
|------------------------------------------------------|-------------------------------------------------------------|------------|-------------------------------------------------------|
| [password](./Service-API/Generate-Password-API)      | `/api/1.0/service/password`                                 | GET & POST | Generates a password                                  |
| [avatar](./Service-API/Avatar-API)                   | `/api/1.0/service/avatar/{user}/{size}`                     | GET        | Returns the avatar image for a user                   |
| [favicon](./Service-API/Favicon-API)                 | `/api/1.0/service/favicon/{domain}/{size}`                  | GET        | Returns a favicon image for a domain                  |
| [preview](./Service-API/Preview-API)                 | `/api/1.0/service/preview/{domain}/{view}/{width}/{height}` | GET        | Returns a preview image for a domain                  |
| [hashes](./Service-API/Hashes-API)                   | `/service/hashes/{range}`                                   | GET & POST | Returns breached hashes for the given range           |
| [password-change](./Service-API/Password-Change-API) | `/api/1.0/service/password-change`                          | POST       | Find the url of the password change page for a domain |
