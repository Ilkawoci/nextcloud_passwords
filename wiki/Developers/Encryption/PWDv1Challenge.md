### How it works
The idea behind the PWDv1 challenge is, that the server should be able to verify that a client is in possession of the master password before handing over the encrypted keychain(s).
To archive this, a salted hash (referred to as secret) of the master password is sent to the server when the challenge is created.
A client trying to open a session must be able to compute the hash before it can continue.
The secret is not stored on the server.
It is used to encrypt the SSEv2 keychain and discarded afterwards.
The server assumes that a salt is correct if it can decrypt the keychain with it.

The PWDv1 challenge is implemented using [libsodium](https://download.libsodium.org/doc/bindings_for_other_languages).
The standard implementation of the PWDv1 challenge can be found [in the passwords-client package](https://git.mdns.eu/nextcloud-passwords/passwords-client/blob/master/src/Authorization/Challenge/PWDv1Challenge.js).


### Structure
| Property | Type | Description |
| --- | --- | --- |
| `salts` | Array | An array with the salts to compute the secret |
| `salts[0]` | string | A 512 byte [hexadecimal](https://download.libsodium.org/doc/helpers#hexadecimal-encoding-decoding) encoded string with the password salt. |
| `salts[1]` | string | A 128 byte [hexadecimal](https://download.libsodium.org/doc/helpers#hexadecimal-encoding-decoding) encoded string with the key for the generic hash. |
| `salts[2]` | string | A 32 byte [hexadecimal](https://download.libsodium.org/doc/helpers#hexadecimal-encoding-decoding) encoded string with the password hash salt. |
| `secret`   | string | A 64 byte [hexadecimal](https://download.libsodium.org/doc/helpers#hexadecimal-encoding-decoding) encoded string with the secret. This is only present in the request to create the challenge |


### Solving the challenge
The master password is required to solve the challenge.
It must be at least 12 and no more than 128 characters long.
The provided salts need to be decoded before they can be used.

First, [`crypto_generichash`](https://download.libsodium.org/doc/hashing/generic_hashing#usage) must be used to compute the generic hash.
The hash size is `crypto_generichash_BYTES_MAX`.
The message consists of the user provided master password with the password salt (salt 0) appended.
The key is the generic hash (salt 1) with the length `crypto_generichash_KEYBYTES_MAX`.

With the generic hash, the secret can be computed using [`crypto_pwhash`](https://download.libsodium.org/doc/password_hashing/default_phf#example-1-key-derivation).
The key length is `crypto_box_SEEDBYTES`.
The password is the generic hash.
The salt is the password hash salt (salt 2) with the length `crypto_pwhash_SALTBYTES`.
The ops limit is `crypto_pwhash_OPSLIMIT_INTERACTIVE`.
The mem limit is `crypto_pwhash_MEMLIMIT_INTERACTIVE`.
The algorithm is `crypto_pwhash_ALG_ARGON2ID13` which is currently `crypto_pwhash_ALG_DEFAULT`.

The resulting key must then be encoded as a [hexadecimal](https://download.libsodium.org/doc/helpers#hexadecimal-encoding-decoding) string and be sent to the server.


### Creating the challenge
The master password is required to create the challenge.
It must be at least 12 and no more than 128 characters long.

The password salt (salt 0) must be generated with 256 bytes of random data.
The generic hash key (salt 1) must be generated with `crypto_generichash_KEYBYTES_MAX` bytes of random data.
The password hash salt (salt 2) must be generated with `crypto_pwhash_SALTBYTES` bytes of random data.
Libsodium provides the function [`randombytes_buf`](https://download.libsodium.org/doc/generating_random_data#usage) to generate secure random data.

**Important:** Always generate new salts when creating a new challenge and never re-use previously used salts.

First, [`crypto_generichash`](https://download.libsodium.org/doc/hashing/generic_hashing#usage) must be used to compute the generic hash.
The hash size is `crypto_generichash_BYTES_MAX`.
The message consists of the user provided master password with the password salt (salt 0) appended.
The key is the generic hash (salt 1) with the length `crypto_generichash_KEYBYTES_MAX`.

With the generic hash, the secret can be computed using [`crypto_pwhash`](https://download.libsodium.org/doc/password_hashing/default_phf#example-1-key-derivation).
The key length is `crypto_box_SEEDBYTES`.
The password is the generic hash.
The salt is the password hash salt (salt 2) with the length `crypto_pwhash_SALTBYTES`.
The ops limit is `crypto_pwhash_OPSLIMIT_INTERACTIVE`.
The mem limit is `crypto_pwhash_MEMLIMIT_INTERACTIVE`.
The algorithm is `crypto_pwhash_ALG_ARGON2ID13` which is currently `crypto_pwhash_ALG_DEFAULT`.

The challenge has to be sent to the server as an object with two keys.
The `salts` property is an Array with the three salts encoded as [hexadecimal](https://download.libsodium.org/doc/helpers#hexadecimal-encoding-decoding) strings.
The `secret` property is the secret encoded as [hexadecimal](https://download.libsodium.org/doc/helpers#hexadecimal-encoding-decoding) string.
