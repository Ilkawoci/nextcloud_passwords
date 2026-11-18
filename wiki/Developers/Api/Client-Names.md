### How the client name is selected
Clients can not chose their own name, it is selected by the app.

* If the client is using a token to log in, then the name of that token is used as client name.
  If the token name can not be used, a generic client name with the pattern `<user id> via Token` is used.
* If the client is using a password to log in, then the user agent is used.
  If the user agent can not be used, a generic client name with the pattern `<user id> via <ip>` is used.
* If an administrator uses the Impersonate app to log in, the client name will be `<user id> via Impersonate` or `<user id> via Impersonate <date> - <user id>@<ip>`.


### Reserved client names
There are some reserved client names which are used by the app for various actions.
Normal clients can not use these names.

| Client | Description |
| --- | --- |
| CLIENT::MAINTENANCE | The Nextcloud maintenance mode |
| CLIENT::UNKNOWN     | Unknown client. Was assigned to all revisions created before the introduction clients. |
| CLIENT::SYSTEM      | The application. Used for the default folder |
| CLIENT::PUBLIC      | Not a logged in user. E.g. public shares |
| CLIENT::CRON        | Any background job |
| CLIENT::CLI         | The occ command line tool |