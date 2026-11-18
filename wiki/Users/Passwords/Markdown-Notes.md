It is possible to use Markdown syntax to add some styling and formatting to password notes.
This page shows and explains the markdown syntax available for the notes.

> :star: Some apps don't support markdown styling for notes and will show plain text instead

## Bold
Text can be formatted as bold by adding two `**` before and after the text.
If more than one line should be bold, every line needs to marked separately.

#### Example
```markdown
**BOLD**

**
This
doesn't
work
**
```

#### Result
**BOLD**

**
This
doesn't
work
**


## Italic
Text can be formatted as italic by adding underscores (`_`) before and after the text.
If more than one line should be bold, every line needs to marked separately.

#### Example
```markdown
_ITALIC_

_
This
doesn't
work
_
```

#### Result
_ITALIC_

_
This
doesn't
work
_



## Lists
It is possible to create numbered lists and bullet point lists.
Numbered lists can be created by starting the line with a number.
The first number is the number the list items start with, after that the items will be incremented automatically no matter what number is used.
Bullet point lists can be created by starting the list items with `* ` or `- `. 
It is not possible to mix the two within a list.
It is possible to create a list within a list item by intending the list items with two spaces.

#### Example
```markdown
1. First Point
2. Second Point
3. Third Point
2. The numbers don't actually matter

- Bullet Point
- Bullet Point
- Bullet Point

* Bullet Point
* Bullet Point
* Bullet Point


* Bullet Point

  with more than one line
* Bullet Point
  1. First subitem
* Bullet Point
  * Subitem
```

#### Result
1. First Point
2. Second Point
3. Third Point
2. The numbers don't actually matter

- Bullet Point
- Bullet Point
- Bullet Point

* Bullet Point
* Bullet Point
* Bullet Point


* Bullet Point

  with more than one line
* Bullet Point
    1. First subitem
* Bullet Point
    * Subitem



## Tables
The syntax for tables is a little more complex.
It is always required to create a header and a body for the table.
The column count should be the same for all rows of the table, and there can never be more columns than in the header.
The header and body are separated by a row where every column is filled with at least three hyphens, (e.g. `---`).

#### Example
```markdown
| Header 1 | Header 2 |
| --- | --- |
| Column 1 | Column 2 |
| | Column 2 |
| Column 1 | |
```

#### Result
| Header 1 | Header 2 |
| --- | --- |
| Column 1 | Column 2 |
| | Column 2 |
| Column 1 | |



## Horizontal Lines
Lines can be created with by leaving a line empty and then writing at least three hyphens at the beginning of the next line that contains nothing otherwise.
If the empty line is omitted, the previous text will become a headline instead.

#### Example
```markdown
Text

---
Text

---Doesn't work
```

#### Result
Text

---
Text

---Doesn't work



## Links
Links can be created by just adding any valid url to the text.
It is also possible to create a text link by writing the text with square brackets `[]` and the link with regular brackets just afterwards `()`.
Headlines can also be linked to, by starting the link with a pound sign `#` and then writing the headline with hyphens `-` instead of spaces.

#### Example
```markdown
[Example Link](https://www.example.com)

https://www.example.com

[Go to *BOLD*](#bold)
```

#### Result
[Example Link](https://www.example.com)

https://www.example.com

[Go to *BOLD*](#bold)



## Quotes
Text can be formatted as quote or blockquote by adding a `>` at the beginning of the line.
You need to have at least ony empty line after a quote, otherwise the following text will still be formatted as quote.

#### Example
```markdown
> First line of
quoted text
> Second line

Not quoted text
```

#### Result
> First line of
quoted text
> Second line

Not quoted text



## Code
It is possible to format text as code block.

#### Example
~~~markdown
```
Code
```
~~~

#### Result
```
Code
```



## Headlines
It is possible to add six different levels of headlines by adding `#` at the beginning of the line.
The more `#`, the smaller the resulting headline.

#### Example
```markdown
# H1
## H2
### H3
#### H4
##### H5
###### H6
```

#### Result
# H1
## H2
### H3
#### H4
##### H5
###### H6