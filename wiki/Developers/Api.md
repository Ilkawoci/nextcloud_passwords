### Api Version Scheme
The api follows this version scheme: `MAJOR.MINOR`.
A major version makes incompatible api changes.
A minor version adds new features.
Bugfixes and security fixes are not visible over the api.
Support for an old version of the api will be ended by a new major version of the app.

##### Special note for the initial release
The version 1.0 api is not complete at this point. This is planned for late 2022.

### API version support
| API | Introduced | Deprecated | Removed |
|-----|------------|------------|---------|
| 0.1 | 2018.1     | 2018.1     | 2022.1  |
| 1.0 | 2018.1     | -          | -       |

### General Notes
 - The api delivers and receives JSON objects
 - The api does not guarantee the order of the properties of an api object
 - The api usually delivers json encoded error responses. On some systems HTTP 403 and 404 status codes will return a server error page instead.
 - Not supported arguments for api actions are silently discarded
 - If a versionable api object is changed in any way, a new revision is created
 - UUIDs follow the [UUID V4 Schema according to RFC 4122](https://wikipedia.org/wiki/Universally_Unique_Identifier)
 - The encrypted properties may contain raw user input
 - The API only works with HTTPS
 - The API requires CORS with basic authentication
 - Non-Standard endpointes, options and values have to be prefixed with `x-`