Struct
======

Getters and setters made easy by rule bound magic methods. Support for Read-only public properties.

---

MIT Licence

Unless required by applicable law or agreed to in writing, software
distributed under the Licence is distributed on an "AS IS" basis,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.

Contact: info@netsilik.nl  
Latest version available at: https://gitlab.com/Netsilik/Struct

---

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

---

Installation
------------

```
composer require netsilik/struct
```