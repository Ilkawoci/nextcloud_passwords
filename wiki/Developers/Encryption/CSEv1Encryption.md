### What is encrypted
With the CSEv1 encryption, each property which is marked as encrypted in the api reference is encrypted separately.
CSEv1 encryption is used for [Passwords](../Api/Password-Api#The-password-object), [Folders](../Api/Folder-Api#The-folder-object) and [Tags](../Api/Tag-Api#The-tag-object).

The standard implementation of the CSEv1 encryption can be found [in the passwords-client package](https://git.mdns.eu/nextcloud-passwords/passwords-client/blob/master/src/Encryption/CSEv1Encryption.js).

| Object | Encrypted Properties |
| --- | --- |
| Password | `url`, `label`, `notes`, `password`, `username`, `customFields` |
| Folder   | `label` |
| Tag      | `label`, `color` |


### Preconditions
Decrypting and encrypting CSEv1r1 encrypted objects requires the [CSEv1 keychain](./CSEv1Keychain).


### Decrypting an object
The property `cseKey` stores the uuid of the key which was used to encrypt the object.
If this no key with this uuid in the keychain, the object can not be decrypted.

**Note:** The key is the same for every value in the same object, but the nonce is of course different for each value.

The encrypted values are stored as [hexadecimal](https://download.libsodium.org/doc/helpers#hexadecimal-encoding-decoding) encoded strings and need to be decoded before they can be decrypted.

**Important:** Prior to Passwords 2020.2.0 the encrypted value string was encoded with [base64](https://download.libsodium.org/doc/helpers#base64-encoding-decoding).
Users who set up CSE before the official release may have objects with base64 encoded values which should be taken into account.

Before the decoded value of any property can be decrypted, it needs to be split into the nonce and the cipher text.
The nonce are the first `crypto_secretbox_NONCEBYTES` bytes of the keychain string.
The cipher text comes after the first `crypto_secretbox_NONCEBYTES` bytes.

With the cipher text, the nonce and the key, [`crypto_secretbox_open_easy`](https://download.libsodium.org/doc/secret-key_cryptography/secretbox#example) can be used to decrypt the value.
The cipher text is used for `c`, the nonce is `n` and key is `k`.

The result is the plain text value for the property of the object.


### Encrypting an object
To encrypt an object, the current encryption key needs to be fetched from the keychain.

**Note:** It is _not necessary or intended_ to create a new key for every object that is encrypted.
This will only increase the size of the keychain and does not improve security at all since all keys are encrypted with the same master password.

Before a value of any property can be encrypted, a nonce with the length of `crypto_secretbox_NONCEBYTES` with random data needs to be created.
Libsodium provides the function [`randombytes_buf`](https://download.libsodium.org/doc/generating_random_data#usage) to generate secure random data.

**Important:** Always generate a new nonce when encrypting a value and never re-use a previously used nonce.

With the value, the nonce and the key, [`crypto_secretbox_easy`](https://download.libsodium.org/doc/secret-key_cryptography/secretbox#example) can be used to encrypt the value.
The value is used for `m`, the nonce is `n` and encryption key is `k`.

Then, the nonce is prepended to the encrypted value.
(Encrypted Value = `nonce` + `value`)
Finally, the resulting string is encoded as [hexadecimal](https://download.libsodium.org/doc/helpers#hexadecimal-encoding-decoding) string.

After encrypting the object, the property `cseType` has to be set to "CSEv1r1" and the property `cseKey` to the uuid of the used encryption key.




