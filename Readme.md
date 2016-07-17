Struct
======

Getters and setters made easy by rule bound magic methods.

Installation
------------

```
composer require netsilik/struct
```

or, in your composer file, add

```json
"require": {
	"netsilik/struct": "^1.0"
}
```


Usage
-----

```php
namespace Nestilik\Example;

use Netsilik\Lib\Struct;

/**
 * Foo class
 */
class Foo extends Struct
{
	/**
	 * @var array $_settableProperties The list of properties that are settable
	 */
	protected $_settableProperties = ['name', 'description'];
	
	/**
	 * @var integer $objectId
	 */
	protected $objectId;

	/**
	 * @var string $name
	 */
	protected $name;

	/**
	 * @var string $description
	 */
	protected $description;
}

$myFoo = new Foo();

$myFoo->name = 'Test';

echo $myFoo->name; // Test

```
