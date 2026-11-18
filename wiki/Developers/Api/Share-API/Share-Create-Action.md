The share create action creates a new share with the given attributes.

# Contents

[[_TOC_]]

# Version History

| App Version | Change                          |
|-------------|---------------------------------|
| 2018.1.0    | Initial introduction of the API |
| 2026.11.1   | Added `folder` to share a whole folder tree |

# Endpoints

## Create Share Endpoint

### URL

| Url                     | Method | Session required | Rate Limit | Description        |
|-------------------------|--------|------------------|------------|--------------------|
| `/api/1.0/share/create` | POST   | yes              | -          | Create a new share |

### Request

The endpoint accepts a JSON formatted POST request body with an array the arguments below.

#### Arguments

| Argument  | Type       | Default | Required | Description                                                      |
|-----------|------------|---------|----------|------------------------------------------------------------------|
| password  | string     | -       | no       | The UUID of the password (required if no folder is given)        |
| folder    | string     | -       | no       | The UUID of the folder, shares subfolders and passwords as well  |
| receiver  | string     | -       | yes      | The user id of the user which the password should be shared with |
| type      | string     | "user"  | no       | The type of the share                                            |
| expires   | int / null | null    | no       | Unix timestamp when the share will expire                        |
| editable  | bool       | false   | no       | Whether or not the receiver can edit the password                |
| shareable | bool       | false   | no       | Whether or not the receiver can share the password               |

#### Restrictions

- Nextcloud Authentication **required**
- Authenticated API Session **required**

### Response

The success status code is `201 Created`

| Argument | Type   | Description           |
|----------|--------|-----------------------|
| id       | string | The UUID of the share |

### Notes

- This action will fail if sharing is disabled
- This action will fail if the password is hidden or the CSE does not support sharing
- You can not share a password with the same user more than once
- Exactly one of `password` and `folder` must be given
- Sharing a folder is refused if it contains end-to-end encrypted passwords, is hidden or trashed, or if you do not own it
- The copies for the receiver are created by the background job, so the share is pending until the next cron run
- Groups are not accepted here: resolve them with [Share Resolve Group Action](./Share-Resolve-Group-Action) and create one share per member
