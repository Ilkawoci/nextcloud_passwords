The find action can be used to find all shares matching the given search criteria.
Only a specific set of fields is allowed in the criteria.
How the criteria array works is explained on the [object search page](../Object-Search).

# Contents

[[_TOC_]]

# Version History

| App Version | Change                          |
|-------------|---------------------------------|
| 2018.1.0    | Initial introduction of the API |

# Endpoints

## Find Shares Endpoint

### URL

| Url                   | Method | Session required | Rate Limit | Description                         |
|-----------------------|--------|------------------|------------|-------------------------------------|
| `/api/1.0/share/find` | POST   | yes              | -          | Find shares matching given criteria |

### Request

The endpoint accepts a JSON formatted POST request body with an array the arguments below.

#### Arguments

| Argument | Type   | Default | Required | Description                                    |
|----------|--------|---------|----------|------------------------------------------------|
| criteria | array  | []      | no       | The search criteria                            |
| details  | string | "model" | no       | The detail level of the returned share objects |

#### Available search fields

| Field     | Type       | Description                                        |
|-----------|------------|----------------------------------------------------|
| created   | int        | Unix timestamp when the share was created          |
| updated   | int        | Unix timestamp when the share was updated          |
| owner     | string     | User id of the owner of the share                  |
| receiver  | string     | User id of the receiver of the share               |
| expires   | int / null | Unix timestamp when the share will expire          |
| editable  | bool       | Whether or not the receiver can edit the password  |
| shareable | bool       | Whether or not the receiver can share the password |

#### Restrictions

- Nextcloud Authentication **required**
- Authenticated API Session **required**

### Response

The success status code is `200 Ok`
The return value is a list of shares objects that match the criteria with the given detail level

### Notes

- The `owner` and `receiver` fields allow the special value `_self` which is replaced with the users id
- This action still works if sharing has been disabled