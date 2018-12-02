# Simple Data Mapping
[📝 Go to index](./index.md)

If the json structure equals to your objects, the mapping is pretty easy.  
You only need to add the `@DataField`-Annotation to the properties you wish to map, instantiate the mapper and call the `mapDataToObject()`-Method


For example you have a json file like this
```json
{
    "firstname": "Pete",
    "surname": "Peterson",
    "age": 28,
    "height": 1.72,
    "is_cool": true,
    "nicknames": [
        "Pepe",
        "Pete"
    ]
}
```

And want to map the json to this object:
```php
use MintWare\DMM\DataField;

class SimplePerson
{
    /** @DataField */
    public $firstname;

    /** @DataField */
    public $surname;

    /** @DataField */
    public $age;

    /** @DataField */
    public $is_cool;

    /** @DataField */
    public $nicknames;
}
```

To map the JSON to the object you need to call the `mapDataToObject()`-method
```php
$mapper = new MintWare\DMM\ObjectMapper();
$data = json_decode(file_get_contents('person.json'), true);
$person = $mapper->mapDataToObject($data, SimplePerson::class);

var_dump($person);
```