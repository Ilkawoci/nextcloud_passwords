The password app lists official clients, third party apps and programming libraries in the included "Apps & Extensions" section.
Apps are also listed in the readme, libraries are also listed in the developer handbook.
If you have developed an app or library and want it to be listed there, this page contains the requirements and process to do so.

## Requirements
Your app must meet all of the following requirements to be listed:

* **Apps must be Open Source**
    
    The source code of your app must be public.
    Since security and trust are very important to our users, they must be able to check the source code of the applications they use.
    
    **Note:** This does not mean that your app must be free or that others must be allowed to use your source code.

* **Apps must not contain Ads**
    
    Any app must not contain any ads.
    Any tracking must be optional and _opt-in_ and compliant with the EU GDPR.

* **Any third party service must be optional**
    
    If your app contacts any server other than the Nextcloud instance of the user, this action should be optional.
    The user must also be aware if data is sent to another service.

* **Apps must not be branded as "Official"**
    
    Apps must not use wording that makes them appear to the user as if they are developed by the developer(s) of the Passwords app for Nextcloud.

* **Apps must support client side encryption**
    
    New apps must support [client side encryption](./Encryption).

* **AI generated code**
    
    Third party apps are free to use AI in any capacity. We just ask you to include a note about the AI usage on your project website/gitlab etc.


## How to list your app
If you want to have your app listed, please [create a ticket in our issue tracker](https://github.com/marius-wieschollek/passwords/issues/new?assignees=&labels=&projects=&template=03_app_submission.yml&title=%5BNEW+APP%5D%3A+).

The ticket must contain the following:
* Name of your app
* Short description of your app (max ~160 characters, optionally in multiple languages)
* Logo of your app (SVG, PNG or JPG, ca 400x400px) (only apps, not libraries)
* Link to the website/store where users can get your app
* Link to the sources
* Supported Systems/Browsers etc.
* Information how we can test your app


## Removing apps
Apps will be removed if one or more of the following issues appear

* They do not or no longer meet requirements
* The author wants them removed
* The app is not working and no longer maintained