This API Endpoint is deprecated and should no longer be used. Use the [Share Recipients Endpoint](./Share-Recipients-Action) instead.

This partners action returns an array of users that the current user can share with.

#### Arguments

| Argument | Type   | Default | Required | Description                             |
|----------|--------|---------|----------|-----------------------------------------|
| search   | string | -       | no       | Find users containing the search string |
| limit    | int    | 5       | no       | The maximum amount of matches to return |

#### Return value

The success status code is `200 Ok`.
The return value is an array with the user id as key and the user display name as value.
The array is at maximum around 256 entries long.
If the autocompletion is disabled by the administrator the result is an empty array

#### Notes

- This command will fail if sharing is disabled
- The `limit` can not be less than 5 or more than 256
- This api endpoint has a rate limit of 10 requests per 30 seconds