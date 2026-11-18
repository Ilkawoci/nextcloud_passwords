
# Contents

[[_TOC_]]

# Version History

| App Version | Change                          |
|-------------|---------------------------------|
| 2018.1.0    | Initial introduction of the API |

# The Share Object

| Property      | Type       | Writable | Description                                                    |
|---------------|------------|----------|----------------------------------------------------------------|
| id            | string     | no       | The UUID of the share                                          |
| created       | int        | no       | Unix timestamp when the share was created                      |
| updated       | int        | no       | Unix timestamp when the share was updated                      |
| expires       | int / null | yes      | Unix timestamp when the share will expire                      |
| editable      | bool       | yes      | Whether or not the receiving user can edit the password        |
| shareable     | bool       | yes      | Whether or not the receiving user can share the password again |
| updatePending | bool       | no       | Whether or not data changes for this share are in queue        |
| password      | string     | yes      | The UUID of the password                                       |
| owner         | object     | yes      | Object with the id and the full name of the owner              |
| receiver      | object     | yes      | Object with the id and the full name of the receiver           |

## Detail Levels

| Level     | Description                                                     |
|-----------|-----------------------------------------------------------------|
| model     | Returns the base model                                          |
| +password | Fills the password property with the base model of the password |

## Enhanced API special properties

The property "password" is also processed if necessary.

| Property      | Type   | Description                                               |
|---------------|--------|-----------------------------------------------------------|
| type          | string | Object type, the value is "share"                         |
| created       | Date   | Date when the share was created                           |
| updated       | Date   | Date when the share was last updated                      |
| expires       | Date   | Date when the share will expire                           |
| owner.icon    | string | Url of the default avatar icon of the owner in 32x32px    |
| receiver.icon | string | Url of the default avatar icon of the receiver in 32x32px |

# Notes

- Shares have no revisions
- The value of the `password` attribute is different for the owner and the receiver
- The `shareable` will always be `false` for the receiver if resharing is disabled system wide