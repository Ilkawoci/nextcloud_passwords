The import format "Other / Custom CSV" can import any CSV file.
Use this import for files which you created yourself or files from other services which are not officially supported.



## File Options
![Parsing options for custom csv files](../_files/import-custom-csv-options.png)

##### Line Break
Specifies the new line character used in the csv file.
The `Detect` option will just check which line break is the most common and use it.
If different line breaks are used in the csv, it is recommended to set it manually.

##### Field Delimiter
Specifies the character used to separate the fields in the csv. 
The import can try to `Detect` the field delimiter.
Alternatively you can choose one of the options manually.

##### Quote Character
The quote character is used to mark the contents of a field in the csv. 
Anything after the quote character will be interpreted as content until another quote character is found.

##### Escape Character
This character is used to escape the quote character within a field. 
If the escape character has to come before the quote character if it appears as part of the content.

##### Detect unescaped quotes
This option can help to parse faulty csv files. 
It tries to detect whether a quote character is used to signal the beginning or ending of a field or if it is part of the content.



## Import Options
![Import options and field mapping for a custom csv file](../_files/import-custom-csv-mapping.png)

> :star: This section describes only describes the import options which are specific to the custom CSV import.
> For all other options please see [the general import options](../Import#Import-Options).

#### Database
Select what type of data you want to import.
You can choose between `Passwords`, `Folders` and `Tags`.
This option defines which fields you can import and which conversion methods are applied.

#### Skip first line
Skips the first line of the csv.
Select this option if your csv file has a header which should not be imported.

#### Interpolate missing fields
This option will make the Importer try to guess missing fields like the url.



## CSV Field Mapping
Here you can map the columns of your csv file. 
You don't have to map every column, just the ones you wish to import.
For passwords you will have to map at least the `Password` column, for folders and tags the `Label` column is required.
You can use the `Preview Line` option to preview a different line.

### Custom Fields format
To import custom fields from a CSV file, the type of the column must be set to "Custom Fields" and the formatting must be as shown below.
Each line contains one custom field.
First, there is the label of the field which is optionally followed by a comma and the type of the field.
After that is a colon and then the value of the field.
The type can be one of `text`, `email`, `url`, `secret`, `file` or `data`.

```
Label,type:value
E-Mail,email:email@example.com
Url,url:https://www.example.com
Text,text:some sample text
Password,secret:secret password
File,file:/path/of/file/on/your/webdav.fil
Data,data:applicationdatafield
```

## Troubleshooting
See [import troubleshooting](../Import#Troubleshooting).