### How it works
The CSEv1 keychain is an encrypted string which contains a JSON object with the keys used for the client side encryption.

The CSEv1 keychain is implemented using [libsodium](https://download.libsodium.org/doc/bindings_for_other_languages).
The standard implementation of the CSEv1 keychain can be found [in the passwords-client package](https://git.mdns.eu/nextcloud-passwords/passwords-client/blob/master/src/Encryption/Keychain/CSEv1Keychain.js).


### Structure
The encrypted keychain string consists of three parts, first the salt, then the nonce and then the actual encrypted data.
This structure describes the decrypted keychain. Not the encrypted string.

| Property | Type | Description |
| --- | --- | --- |
| `keys` | Object | A list with the keys. The identifier is an UUID v4 and the value is the key as [hexadecimal](https://download.libsodium.org/doc/helpers#hexadecimal-encoding-decoding) encoded string. |
| `current` | string | The UUID of the latest key which should be used for encryption. |


### Decrypt the keychain
The master password is required to decrypt the keychain.
It must be at least 12 and no more than 128 characters long.
The encrypted keychain is stored as a [hexadecimal](https://download.libsodium.org/doc/helpers#hexadecimal-encoding-decoding) encoded string and needs to be decoded before it can be decrypted.

**Important:** Prior to Passwords 2020.2.0 the encrypted keychain string was encoded with [base64](https://download.libsodium.org/doc/helpers#base64-encoding-decoding).
Users who set up CSE before the official release may have a base64 encoded keychain which should be taken into account.

After the keychain string has been decoded, it needs to be separated into the salt and the payload.
The salt are the first `crypto_pwhash_SALTBYTES` bytes of the keychain string.
The payload comes after the first `crypto_pwhash_SALTBYTES` bytes.

With the salt from the keychain string and the users master password, the decryption key can be computed using [`crypto_pwhash`](https://download.libsodium.org/doc/password_hashing/default_phf#example-1-key-derivation).
The key length is `crypto_box_SEEDBYTES`.
The password is the master password.
The salt is the salt from the keychain string with the length `crypto_pwhash_SALTBYTES`.
The ops limit is `crypto_pwhash_OPSLIMIT_INTERACTIVE`.
The mem limit is `crypto_pwhash_MEMLIMIT_INTERACTIVE`.
The algorithm is `crypto_pwhash_ALG_ARGON2ID13` which is currently `crypto_pwhash_ALG_DEFAULT`.

Now the payload needs to be split into the nonce and the cipher text.
The nonce are the first `crypto_secretbox_NONCEBYTES` bytes of the payload.
The cipher text comes after the first `crypto_secretbox_NONCEBYTES` bytes of the payload.

With the cipher text, the nonce and the decryption key, [`crypto_secretbox_open_easy`](https://download.libsodium.org/doc/secret-key_cryptography/secretbox#example) can be used to decrypt the keychain.
The cipher text is used for `c`, the nonce is `n` and decryption key is `k`.

The result is now a JSON encoded string representing the actual keychain object.
The JSON object contains the property `keys` which contains an object representing all the keys in the keychain.
The name of each property in `keys` is the id of the key and the value is the key as [hexadecimal](https://download.libsodium.org/doc/helpers#hexadecimal-encoding-decoding) encoded string.
Each key needs to be decoded before it can be used for encryption.


### Encrypt the keychain
The master password is required to encrypt the keychain.
It must be at least 12 and no more than 128 characters long.

**Important:** Always generate a new salt when encrypting the keychain and never re-use previously used salts.

The keychain object must fit the [structure](#Structure).
It consists of the two properties `keys` and `current`.
The property `keys` is an object with the uuid of the key as property and the key as [hexadecimal](https://download.libsodium.org/doc/helpers#hexadecimal-encoding-decoding) encoded string as value of that property.
The property `current` contains the UUID of the current default encryption key.
The key referred to in `current` must exist in `keys`.

The encryption key is generated using [`crypto_pwhash`](https://download.libsodium.org/doc/password_hashing/default_phf#example-1-key-derivation).
A salt with the length of `crypto_pwhash_SALTBYTES` needs to be generated for this.
Libsodium provides the function [`randombytes_buf`](https://download.libsodium.org/doc/generating_random_data#usage) to generate secure random data.
The key length is `crypto_box_SEEDBYTES`.
The password is the master password.
The salt with the length `crypto_pwhash_SALTBYTES`.
The ops limit is `crypto_pwhash_OPSLIMIT_INTERACTIVE`.
The mem limit is `crypto_pwhash_MEMLIMIT_INTERACTIVE`.
The algorithm is `crypto_pwhash_ALG_ARGON2ID13` which is currently `crypto_pwhash_ALG_DEFAULT`.

Before the keychain object can be encrypted with the encryption key, it needs to be converted to a JSON string.
It is also necessary to create a nonce with the length `crypto_secretbox_NONCEBYTES`.
Libsodium provides the function [`randombytes_buf`](https://download.libsodium.org/doc/generating_random_data#usage) to generate secure random data.

With the keychain json, the nonce and the key, [`crypto_secretbox_easy`](https://download.libsodium.org/doc/secret-key_cryptography/secretbox#example) can be used to create the encrypted keychain.
The keychain json string is used for `m`, the nonce is `n` and encryption key is `k`.

Then, the nonce is prepended to the encrypted keychain and after that the password salt is prepended to this string.
(Encrypted Keychain = `salt` + `nonce` + `keychain`)
Finally, the resulting string is encoded as [hexadecimal](https://download.libsodium.org/doc/helpers#hexadecimal-encoding-decoding) string.


### Adding a new key to the keychain
A new key should only be added to the keychain if the master password was changed.
Adding new keys without changing the master password will only increase the size of the keychain but does not provide any security benefits.

To add a new key, first a UUIDv4 must be generated.
The key itself consist of random data and has the length `crypto_secretbox_KEYBYTES`.
Libsodium provides the function [`randombytes_buf`](https://download.libsodium.org/doc/generating_random_data#usage) to generate secure random data.
The generated key is stored as value in `keys` in the keychain object.
The uuid is used as name of the property.
After this, the uuid is stored in `current` to set the new key as the current encryption key.