The find command allows you to find api objects that match given search criteria.
This can be used if you just need a specific set ob objects and can be faster than the list command.
The allowed fields are listed on the objects documentation page.

## Comparison Operators
| Operator | PHP Equivalent | Description |
| --- | --- | --- |
| eq | == | Field `equals` value |
| ne | != | Field `not equals` value |
| lt | < | Field `is less than` value |
| gt | > | Field `is greater than` value |
| le | <= | Field `is less or equals` value |
| ge | >= | Field `is greater or equals` value |

## The criteria object
The criteria object passed to the find action can contain two types of search criteria.
The simple type can be used to select objects where the field equals the value.
For this you can simply give the field name as key, and the value as value in your search criteria.
The second type allows the usage of the operators listed above.
To use this type, give the field name as key and the supply an array where the first entry is the operator andthe second the value.

This is what search criteria might look like:
```json
{
    "trashed": true,
    "cseType": "none",
    "created": [ "gt", "1516741234" ],
    "sseType": [ "nq", "SSEv1r1" ]
}
```
