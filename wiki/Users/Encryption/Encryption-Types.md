


# Server-Side Encryption
Server-Side encryption is used on the server before the data is stored in the database.

### None
If the client already encrypted the password, the server does not need to add additional encryption.

### SSEv1 (Server-side encryption (Gen. 1))
This is a basic server side encryption which creates an individual encryption key for each password.
The key is generated using the server-wide Nextcloud secret, a user specific key and a per-item key.
This type of encryption does not require a master password.
This encryption protects against partially compromised servers, e.g. when the attacker has access to the database or backups of it.

### SSEv2 (Server-side encryption (Gen. 2))
This encryption uses an encrypted keychain to store the keys used to decrypt passwords.
The data necessary to decrypt the keychain is controlled by the  client and only available on the server when the client makes a request.
To get around this encryption, an attacker would need continuous access to the server while the user is logged in.

### SSEv3 (Third party server side encryption)
This encryption works similar to SSEv1, however the user specific encryption key is provided by a third party application and the server key is omitted.
With this encryption method, the user key is usually provided by another application outside Nextcloud itself, e.g. an SSO service.
This encryption method can be safer than SSEv1 since an attacker needs access to the third party application to decrypt any data.

# Client-Side Encryption
Client-Side encryption is implemented in the clients such as the webapp, Desktop apps or smartphone apps and is applied before the data is sent to the server.

### None
If the user does not use a master password, the client does not apply any encryption.
The data is transferred to the server securely via HTTPS and encrypted there before it is stored in the database.

### CSEv1 (Encryption with libsodium)
With CSEv1, the user chooses a master password.
This master password is used to encrypt a keychain which contains the keys used to encrypt the passwords.
The passwords are then encrypted with a key from the keychain before being sent to the server.
This method is the most secure way to store your passwords.
Any attacker with access to the server or capable of intercepting the communication between the client and the server will only see already encrypted data.
The master password from the user is never sent to the server and is only known to the user.