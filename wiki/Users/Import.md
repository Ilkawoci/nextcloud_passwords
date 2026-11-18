The import feature is located under "[Restore or Import](web+passlink://goto/backup/import)" in the "Backup and Restore" section which is accessed through the "🗃" icon in the app navigation.
It can be used to restore a previously made backup, import a backup from another instance or import data from another password manager.

![The Import section](_files/import-section.png)



## The Import Steps
##### 1. Choose Format
Choose the format of the file you want to import.
If you want to import a CSV file from an application which is not listed, choose "Custom CSV".

##### 2. Select File
Select the file you want to import.
If you want to import a custom csv file, make sure to set the csv options before you open the file.

##### 3. Select Options
Select the options for the import.
Depending on the file type, different options may be available which are explained below.

##### 4. Run Import
If you're ready to go, hit the "Import" button and start importing your files



## Specific importers
Most of the imports for files from a third party service are just a profile with preselected options for the CSV import.
There are some imports which do have special options just for that service.
The description of these options dav be found in the import guide for that service.

- [Import from Passman](./Import/Import-from-Passman)
- [Import from Chrome](./Import/Import-from-Chrome)
- [Import from database backup](./Import/Import-from-Backup)
- [Import from Enpass](./Import/Import-from-Enpass)
- [Import from custom CSV](./Import/Import-from-custom-CSV)



## Import Options
#### Conflict Handling Mode
The "Conflict handling" mode defines how the Importer should handle conflicts.
"Skip if same revision" will skip an entry if it already exists and the revision matches. Works only if the import contains a revision field.
"Skip always" will skip an entry if it already exists in the database.
"Overwrite existing" will overwrite an entry if it already exists in the database.
"Merge with existing" will merge the new data with the current data. This does not apply for custom fields.
"Create new entry" will always create a new entry, even if it already exists.

**Note:** If no id field is available, entries will be matched by their name. The "Database Backup" option will always use id based matching.

#### Don't edit passwords shared with me
If this option is selected, passwords which were shared with you by other users will not be overwritten by the import.



## Troubleshooting

### How to fix faulty CSV files
![Warning for services which may create faulty exports](_files/import-faulty-csv.png)

Some password managers create faulty export files which can not be parsed by the importer.
These files need to be fixed manually in order to work properly.

1. Open the file in a spreadsheet program like [LibreOffice Calc](https://libreoffice.org) or Microsoft Excel.
2. Check the file for errors. Most of these programs do a good job in fixing faulty CSVs. You should make sure that all colums are filled in properly.
3. Export the file as CSV.
4. Try the Import again

##### Example errors
![Quotes are not escaped properly](_files/import-faulty-csv-error.png)

### Files can not be imported
Some password managers (e.g. Passman, Enpass, Bitwarden) allow files to be stored with passwords.
The Import will show the warning ""Password Name" has files attached which can not be imported." for each password which has files attached.
The password will be imported anyway but the files will not be imported.
Passwords does not offer any file storage functionality as we believe that it is better to leave this to Nextcloud itself.

Passwords does support the linking of files stored in Nextcloud to passwords.
Therefore we recommend downloading the files manually from your old Password manager and storing them in Nextcloud.
After this you can edit the password and add a custom field with the type "file" and choose the file from the file dialog.

**Note:** If you want to store secret files like private keys, we recommend that you use the [Nextcloud E2E Encryption](https://nextcloud.com/endtoend/).

### Long custom field names / values are truncated
If you have custom fields with a label that exceeds 48 characters or a value that exceeds 320 characters, it will be truncated to fit the length limitations of Passwords.
Custom fields are intended to contain usual password related information like and e-mail address or the like.
They are not intended to hold large amounts of data.
If you want to store long texts, we recommend to use the "Notes" field.

### Custom field type mismatch
If you import passwords with custom fields from any source other than the "Database Backup", they will be validated.
Fields with the type "url" or "email" will require to be in a correct format or their type will be set to "text".
Additionally, all fields with the type "text" will be checked if they are in the right format for "url" or "email" and if so, their type will be changed. 
