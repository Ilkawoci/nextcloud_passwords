
### How do i keep my passwords secure?
 - Only access the Nextcloud over HTTPS
 - Do not give your credentials to someone else
 - Do not log into Nextcloud on someone else's pc or a public pc
 - If you are using an app or browser extension to access your passwords, always use the latest version


### What do the different encryption types do?
In the details of any password, you can see two entries for encryption.

#### Encryption on server
This information shows how the server encrypts the data before it is stored in the database.

 - **No encryption** 
    
    This is only used with encryption on the client activated. 
    The server just takes the data sent by the app and stores it in the database.
 - **Simple encryption (Gen. 1)** 
    
    The server encrypts the data with three different keys, one for the server, one for the user and one for the password.
    Since all three keys are stored in the database, this encryption is the easiest to crack.
    This is the oldest and most basic encryption and it is no longer used for new passwords.
 - **Simple encryption (Gen. 2)** 
    
    The server encrypts the data with three different keys, one for the server, one for the user and one for the password.
    The server key is stored in the configuration file on the server, the other two keys are stored in the database.
    This ensures that someone with access to the database or a database backup can not decrypt the data.
 - **Advanced encryption (SSE V2)**
    
    For every user, a keychain exists.
    The server encrypts the data with one of the keys from the keychain before it is stored in the database.
    The keychain is encrypted with a specific password which has to be sent to the server by the app.
    The app creates this password with by solving a challenge with the master password from the user.
    The master password is never sent to the server.
    With this method, the passwords can only be decrypted when the user is logged in.


#### Encryption on client
This information shows how the app encrypts the data before it is sent to the server.

 - **No encryption**
    
    The passwords are not encrypted before it is sent to the server.
    Since the passwords are sent to the server with HTTPS, they can only be read by the app and the server.
 - **Encryption with libsodium**
   
    For every user, an encrypted keychain exists.
    When the user opens the app, the server sends a challenge to the app.
    The app requests the user to enter a master password which it uses to solve the challenge.
    Then the app sends the solution to the server which validates it and sends the encrypted keychain to the app.
    The app uses the master password to decrypt the keychain.
    The master password is never sent to the server.
    With the keys from the keychain, the app can now decrypt the passwords from the server.
    Before a password is sent to the server, it is encrypted with one of the keys from the keychain.


### Attacks on the passwords app
This section describes potential ways how an attacker could gain access to your passwords.
Contents of this section can also apply to any other Nextcloud app and also other password managers.

 - **Scenario: The server was hacked or one of the administrators is evil**
    
    If the attacker has access to the server, he can decrypt the passwords in the database and steal them.
    If the passwords are encrypted with "Encryption with libsodium", the attacker can modify the code of the app to steal your master password the next time you log in.
    
    > :thumbsup: Use "Encryption with libsodium" to make it difficult to decrypt your passwords
    
    > :thumbsup: Use the official clients to access your passwords instead of the Nextcloud app.
    > This prevents the attacker from stealing your master password

 - **Scenario: Someone is able to decrypt the HTTPS communication between your browser and the server**
    
    If the attacker is able to read the communication between the browser and the server, he could steal your access credentials for Nextcloud.
    The attacker can also read all passwords which are not encrypted with "Encryption with libsodium".
    If the attacker is also able to edit the data sent to the browser, he could add code to the webstite that steals your master password and use that to decrypt passwords encrypted with "Encryption with libsodium"
    
    > :thumbsup: Use "Encryption with libsodium" to make it difficult to decrypt your passwords
    
    > :thumbsup: Use the official clients to access your passwords instead of the Nextcloud app.
    > This prevents the attacker from stealing your master password
    
    > :thumbsup: Make sure that you access Nextcloud only with HTTPS and there is no warning from your browser

 - **Scenario: There is an evil extension installed in your browser**
    
    Extensions with the right permissions can access anything you do on the internet, including any password you enter on any website. 
    That also means they can read all your passwords when you log into the passwords app.
    An evil extension could steal your master password, the Nextcloud credentials and the decrypted passwords from the app.
    
    > :thumbsup: Only install trustworthy extensions from official sources
    
    > :thumbsup: Use the official clients to access your passwords instead of the Nextcloud app.
    > This prevents the attacker from stealing your master password

 - **Scenario: You have a virus/malware on your computer**
 
    Software on your computer/smartphone can access all the data stored there.
    Malware can use that access to steal any private data you have, including the passwords stored in the app as soon as you open it.
    
    > :thumbsup: Keep your antivirus software up to date
    
    > :thumbsup: Don't use passwords on a computer that is not yours