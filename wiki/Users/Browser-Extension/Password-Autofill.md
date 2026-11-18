## Reset Suggestion Data
The extension remembers which credentials were used on a website and will rank these credentials higher in the suggested credentials for this website.
The data for this is stored locally and can be deleted in the extension settings.

1. Open the extension settings (E.g. by clicking "Open Extension Settings" in the "Tools" tab)
2. Open the "Debug" tab
3. Click "Reset Recommendation Data"


## Extra Login Form Fields
When credentials are pasted into a website, it is possible to supply additional values which should be pasted into additional fields.
These values must also be specified in the password entry using a specific format.

##### Get the id of an input field

> :star: Only HTML input fields with an id are supported

1. Right-click the input field and select "Inspect"
2. Locate the `<input >`-HTML tag (should be highlighted) and check for the `id` attribute.
    E.g. if the code looks like this: `<input name="extra-field" type="text" placeholder="write something" id="extra-id">`, then the id field is `id="extra-id"` and the id itself is `extra-id`.
3. Copy the id.

##### Create a custom field
1. Open the edit dialog for the password entry
2. Scroll down to the custom field section and enter `ext:field/` and the id you copied as the label of a new custom field.
3. Select "Form field" in the custom field type dropdown.
4. Enter the value you want to be pasted in the field as the custom field value
5. Save the password entry