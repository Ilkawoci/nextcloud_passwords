## Usecase
SSEv3 provides an option for third party apps to provide the user specific encryption key used to encrypt passwords, folders and tags on the server.
This can improve security if the encryption key is stored outside the Nextcloud environment and access by potential attackers can be stopped.


## Implementation
You must crate a new Nextcloud app that manages the user encryption keys. You can crate an app skeleton in the Nextcloud appstore or start from one of the example implementations below.

### Implement the SseV3KeyProviderInterface
Create a new class that implements the `\OCA\Passwords\Encryption\Object\SseV3KeyProviderInterface`.
The interface specifies two methdos, `isAvailable(): bool` which returns whether your provider is currently capable of providing encryption keys
and `getUserKey(string $userId): string` which returns the encryption key for the given user id.

Here is an example for the implementation without any custom code.
```php
<?php

namespace OCA\MyEncryptionProvider\Encryption;

use OCA\Passwords\Encryption\Object\SseV3KeyProviderInterface;

class MyKeyProvider implements SseV3KeyProviderInterface {

    /**
     * Returns if the key provider is ready to provide encryption keys
     *
     * @return bool
     * @since 2023.2.0
     */
    public function isAvailable(): bool {
        // @TODO: Implement isAvailable() method.
    }

    /**
     * Get the encryption key for the given $userId.
     * The key must be at least 32 characters long and consist of at least 8 different characters
     *
     * @param string $userId
     *
     * @since 2023.2.0
     * @return string
     */
    public function getUserKey(string $userId): string {
        // @TODO: Implement getUserKey() method.
    }
}
```

When implementing your key provider, keep the following in mind:
- The user key must remain the same. The app expects the same user key for decrypting an item as it got when it encrypted the item.
  If you want to change a users key, you must implement the upgrade process yourself.
  Also keep [backups](../../Administrators/Backups) in mind.
  If you ever restore a passwords backup, you must be able to restore the corresponding user keys.
- The key must be at least 32 characters long and consist of at least 8 different characters.
- Your provider must be able to provide the key for a user even when the user is currently not logged in.
  Things like sharing or upgrades work via background jobs outside of the user context and may have to decrypt SSE.
  If decrypting with the key does not work during the `./occ maintenance:repair` command, the affected item is deleted.
- The user key is requested every time the app decrypts an item.
  So if one user loads all their passwords, the app will request the key for every single password.
  Your provider should be able to handle that amount of requests quickly or the app will be very slow.
- Passwords does not take care of making backups of these keys.

### Registering your key provider
You must register your key provider globally so the passwords app can make use of it.
To do so, you must add the following call to the `register` method of your `Application` class.

```php
    OC::$server->registerService(
        \OCA\Passwords\Encryption\Object\SseV3KeyProviderInterface::class,
        function() {
            return new \OCA\MyEncryptionProvider\Encryption\MyKeyProvider();
        }
    );
```

**Only one provider can be registered at the same time.**


## Exceptions
If something goes wrong while using the key provider, the app thows an exception which you may be able to find in your Nextcloud logs.
The following exceptions exist:

- `SSEv3InvalidKeyException`: The provided key did not meet quality requirements (min 32 characters and at least 8 different characters)
- `SSEv3ProviderInvalidException`: The registered provider does not implement the interface
- `SSEv3ProviderNotAvailableException`: The provider was not available when it was supposed to be used.
- `SSEv3ProviderNotFoundException`: There was no provider registered when it was supposed to be used.


## Debugging
Here are some of the points where you can set a breakpoint to check the usage of your provider

- `\OCA\Passwords\Services\EncryptionService::getDefaultEncryption`: Selects the provider to be used.
- `\OCA\Passwords\Encryption\Object\SseV3Encryption::isAvailable`: Checks if SSEv3 is available
- `\OCA\Passwords\Encryption\Object\SseV3Encryption::getKeyProvider`: Validates the SSEv3 provider
- `\OCA\Passwords\Encryption\Object\SseV3Encryption::validateKey`: Validates keys from the provider

## Example implementations
- [**Key provider template App**](../_files/basic-encryption-provider.zip)
  Just the minimum boilerplate code for an app and the key provider.
- [**Example key provider app**](../_files/full-encryption-provider.zip)
  An app that provides a complete, working SSEv3 key provider.


## Enabling third party encryption
If a third party encryption provider is registered, the setting "Allow third party encryption" becomes available in the admin section for passwords.
Enable this setting to start using SSEv3 as default server side encryption method.

You can confirm that it's working by adding a new password and checking the server side encryption method in its technical details.