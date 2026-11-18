Some versions of the Passwords app contain checks to enable or disable additional functionality.
Those features are usually complete in the app itself, but require changes in other apps or extensions to be fully completed.
Therefore, the app uses a file in the user handbook to check if the feature can be enabled safely.
Which features can be enabled depends on the version of the Passwords app.
Features that don't exist in a version of the app can't be enabled and features which are no longer equipped with a feature check can't be disabled.
This system is intended to help with the introduction of new features, it is not intended to manage which features your uses have access to.

## Managing Features
If you want to enable or disable features, you need to [self-host the user handbook](./Self-Hosting).
The file [`_features/features-v1.json`](../../Users/_features/features-v1.json) is used to enable the features.
This file will be fetched once per day from the handbook and is then cached in the default cache of the app.

**Note:** Enabling features which are disabled by default can cause issues and is not supported.

**Note:** Once a feature is considered to be stable, the option to enable/disable it will be removed.

## Feature list
| Scope  | Feature               | Description                                          |
|--------|-----------------------|------------------------------------------------------|
| server | legacy-client-warning | Show a notification if a client is using the old api |
| webapp | first-run-wizard      | Enable the first run wizard with E2E setup           |

#### Disable feature management
It is possible to disable the feature management.
This will also disable all hidden features.
With the following command, the feature management can be disabled on the server and on the webapp.
```bash
./occ config:app:set passwords das/enabled --value 0
```