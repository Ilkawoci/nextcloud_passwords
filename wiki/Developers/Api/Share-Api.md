The share api provides functionality to share passwords with other users on the same server.
This api is restricted by the sharing settings of the server, so
the [related server.sharing.* settings](./Settings-Api#scopes-and-settings) should be checked before using it.

# Object
See the [share object](./Share-API/The-Share-Object) documentation.

# Available api actions

| Action                                                  | Url                                       | Method   | Description                                 |
|---------------------------------------------------------|-------------------------------------------|----------|---------------------------------------------|
| [list](./Share-API/Share-List-Action)                   | `/api/1.0/share/list`                     | POST+GET | List all shares with the given detail level |
| [show](./Share-API/Share-Show-Action)                   | `/api/1.0/share/show`                     | POST     | Show a share                                |
| [find](./Share-API/Share-Find-Action)                   | `/api/1.0/share/find`                     | POST     | Find shares matching given criteria         |
| [create](./Share-API/Share-Create-Action)               | `/api/1.0/share/create`                   | POST     | Create a new share                          |
| [update](./Share-API/Share-Update-Action)               | `/api/1.0/share/update`                   | PATCH    | Update an existing share                    |
| [delete](./Share-API/Share-Delete-Action)               | `/api/1.0/share/delete`                   | DELETE   | Delete a share                              |
| [recipients](./Share-API/Share-Recipients-Action)       | `/api/1.0/share/recipients`               | POST+GET | Find users and groups to share with         |
| [resolve group](./Share-API/Share-Resolve-Group-Action) | `/api/1.0/share/recipients/resolve-group` | POST     | Temporary endpoint to resolve group members |
| ~~[partners](./Share-API/Share-Partners-Action)~~       | `/api/1.0/share/partners`                 | POST+GET | Deprecated                                  |








