## General Errors
#### [6304571a] HTTPS required
You can not run Passwords without HTTPS on a productive server as this is completely insecure.

#### [a3f7ed53] Access denied
The permissions of yur client do not allow this action.

#### [f84f93d3] Access denied
The requested action requires an authorized session.
Open a session before making the request.


## Validation Errors
#### [7b584c1e] Invalid server side encryption type
This error indicates that the entity was assigned to a server side encryption type that does either not exist or is not supported.

#### [4e8162e6] Invalid client side encryption type
This error indicates that the submitted client side encryption type is not supported by the server.

#### [f43e7b82] No encryption specified
Neither a client side, nor a server side encryption method was specified.

#### [fce89df4] Client side encryption key missing
A client side encryption type with cseKey was used, but the cseKey is empty.

#### [7c31eb4d] Field "label" can not be empty
The field "label" can not be left empty when creating or updating an entity.

#### [5b9e3440] Field "hash" must contain a valid sha1 hash
The field "hash" must contain a valid sha1 hash of the password. This has is used for security checks.

#### [2aff026c] Field "color" can not be empty
The field "color" can not be left empty when creating or updating a tag.

#### [f281915e] Invalid revision id
The given revision uuid does not belong to the given entity uuid.

#### [9c5d2ad8] Invalid revision id
The given revision uuid is not the current revision of the entity.

#### [7c8efc3c] Can not edit base folder
When working with folders, you can not manipulate properties of the base folder as it is a virtual folder.

#### [60177108] Illegal field in search criteria
When using the find command, only a predefined set of properties can be searched.
Look into the documentation of the entity you want to use find on to see which properties are allowed.

#### [f2fc3b4a] Illegal operator in search criteria
The following operators can be used in the find command: `eq`, `nq`, `lt`, `gt`, `le`, `ge`.

#### [e5eeb777] Key too long
The name of a client setting exceeds the maximum of 48 characters.

#### [9b106704] Value too long
The value of a client setting exceeds the maximum of 128 characters.


## Sharing Errors
#### [0b0498c1] Sharing disabled
Sharing has been disabled by the administrator.

#### [2b70fd38] Sharing disabled for user
Sharing has been disabled by the administrator for this specific user.

#### [65782183] Invalid receiver uid
You can not share an entity with the given user.

#### [f58a5f80] Invalid expiration date
You can not set an expiration date that lies in the past.

#### [07f4cb63] Invalid share type
The share type you have chosen is not allowed by the system.
This can happen if the administrator has disabled a specific sharing type or if the api does not support this sharing type.
The info command lists all allowed sharing types.

#### [daf00c23] Entity not shareable
This can happen if you try to share a password which has been shared with the user.
The administrator or the original owner may have permitted this possibility.
If the owner does not allow sharing, this is noted in the "shareable" attribute of the extended "share" property of the entity object.
You can request the "share" property with the detail level "+shares".
If the administrator has permitted resharing, this is noted in the "resharing" filed in the info command.

#### [e860bf51] Entity already shared with user
This error occurs when the given entity was already shared with the receiving user.
You can request all active shares for the entity in the "shares" property with the detail elvel "+shares".

#### [54131ad4] CSE type does not support sharing
If you want to create a share, you need to make sure that the entity is currently encrypted with a CSE type which supports sharing.

#### [7269e742] Shared entity can not be hidden
You can not share entities while they are hidden.


## Service Errors
#### [74b8fcb3] Unable to generate password
The password service was unable to create a password with the given settings.

#### [d786e9f3] Invalid dimensions given
The pageshot service does not support the given image dimensions.

#### [66dfd890] API Request Failed
The service made a request to a third party api or program which failed.
It might be possible that credentials are required but are not given or incorrect.
Look into the Nextcloud error log to find more information.

#### [06af75db] Internal Favicon API Error
The current favicon service was unable to create a favicon for the given domain.
Also the default favicon service failed to create a default icon.
Please check your server configuration.

#### [34e9164c] Internal Website Preview API Error
The current website preview service was unable to create a preview of the given domain.
Also the default preview service failed to load a default image.
Please check your server configuration.

#### [43486931] Internal Words API Error
The current words service was unable to produce usable output.
Please check your server configuration.

#### [49f5c936] Invalid range
The hash range provided to the hashes endpoint is not within the allowed range of 5 - 40 characters.


## Session & Encryption Errors
#### [ab6e13ba] Passphrase invalid
The given passphrase is incorrect or no passphrase was given when it is required.
Too many attempts to open a session with an incorrect passphrase will revoke your clients login credentials.

#### [033d1943] Passphrase verification failed
The given passphrase is incorrect.
Too many attempts to open a session with an incorrect passphrase will revoke your clients login credentials.

#### [3476b978] Reading user keychain failed
The app is unable to read the users SSEv2 or CSE keychain.
This indicates an issue with the encryption keys on the server.

#### [92312b66] Secret invalid
The given challenge secret is invalid, e.g. it doesn't have the correct length.
Consult the [encryption implementation documentation](../Encryption).

#### [ee858005] Salt length invalid
The lengths of the challenge salts are incorrect.
Consult the [encryption implementation documentation](../Encryption).

#### [c0980e3c] Token invalid
The given token is invalid or expired or no token was given when it is required.
Too many attempts to open a session with an incorrect token will revoke your clients login credentials.

#### [8b4f02b4] Token verification failed
The given token is incorrect.
Too many attempts to open a session with an incorrect token will revoke your clients login credentials.

#### [1d21b905] Loading challenge failed
The app was unable to load the users encryption challenge.
More details will be in the server log.

#### [114cbe8d] Challenge update failed
The app was unable to update the user challenge.
The existing user challenge remains active.
The server log may contain more details.


## Other Errors
#### [65611064] 
The application was not designed to be able to fulfill the requested action.