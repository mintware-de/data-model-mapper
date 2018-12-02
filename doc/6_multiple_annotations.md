# Multiple Annotations
[📝 Go to index](./index.md)

In some edge-cases a property name in the json can vary:
```json
[
    {"first name": "Foo"},
    {"first*name": "Bar"},
    {"first-name": "Baz"},
    {"first_name": "FooBarBaz"}
]
```

In this case you can add multiple `@DataField` annotations for a property.

```php
class CrappySourceData {
    /**
     * @DataField(name="first name") 
     * @DataField(name="first*name") 
     * @DataField(name="first-name") 
     * @DataField(name="first_name") 
     */
    public $firstName;
}
```