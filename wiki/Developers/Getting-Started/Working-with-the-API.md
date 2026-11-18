# Requirements to use the api

- Nextcloud API credentials are required
   - We recommend using an app token to access the api.
     App tokens are the only kind of authentication working with 2FA-enabled accounts.
     The usage of app tokens also makes it easier for users to revoke access of lost or compromised clients.
- API requests can not be made from your browser and trying to do so will end your NC session.
   This is a rule from Nextcloud and can't be changed
- Your client must be able to handle the `nc_passwords` session cookie or be able to receive and send the `X-API-SESSION` with each request.
  [See Session ID](../Api/Session-Api#session-id)
- [More information can be found on the API page](../Api#general-notes)

# Inspecting API requests with Postman
To make development with the api easier for developers, we have a collection for [Postman](https://www.postman.com/).

- [Download Postman collection](../_files/postman-collection.json)

Using the collection does not require a Postman account, you can skip the login screen and work locally.
In order to import the collection, select the `Import` option  in the `File` menu or press `Crtl` + `O` to open the import dialog.
Then import the file by dropping it into the dialog and click the `Import` button.

The collection contains all requests to the api and gives you the option to inspect the api endpoints without having to write your own client.

## Sample Data
The collection works best with our [Sample Data Server Backup](./Sample-Data#server-backup).

## Variables
The "Variables" tab in the main page of the collection contains all variables used in the requests.
You may need to set the `base_url`, `http_auth_user` and `http_auth_pass`.
The `http_api_session` can be used to use the api with a session context.

## End-to-End Encryption
If you're working with e2e, you can log in the webapp and then use the session token and app token from that session.
You should be aware that there is no decryption / encryption logic available in Postman.

 - The session token can be found in each request in the X-Api-Session header.
 - The app token can be found in the html source of the page in the meta tag with the name "pw-api-token".
