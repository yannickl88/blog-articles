[//]: # (TITLE: Avoiding static in your code)
[//]: # (DATE: 2016-08-05T09:00:00+01:00)
[//]: # (TAGS: php, static, phpunit)

For anyone not familiar with the `static` keyword in languages, I suggest reading up on it a bit and then come back here to know when you should be using it. In essence, defining things as static will define something on class level instead of instance level, so you can access the same referece from anywhere. And as a new programmer that might seems very useful. I do not blame you for I did do too not all that long ago. But as you write more code, you learn. Learn that defining things `static` might make things more difficult than they are worth.

## Singelton and sorts
For anyone following a computer science batchelor you will undoubtely have had a course or lesson on software patterns as did I in my batchelor courses. We had the classics, like Model-View-Controller, Factory, Visitor and of course Singelton. The latter one I always found most interesting. For those unaware, a simple example would be:
```php
<?php
class Singelton
{
    private static $instance;
    
    public static function getInstance()
    {
        if (!self::$instance instanceof Singelton) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    private function __construct()
    {}
}
```
As a new programmer, this was so elegant and simple; you can make your database connection accessable from everywhere, allowing easy access to your ORM or the likes. Or you can create a cool routing system which can register routes from everywhere, [something Laravel does][laravel-quickstart].

And as you start building your applications, you find that calling static methods from others is an easy way quickly having access to the things you need. So why the title of this post? Why should you not use it then?

## Hidden dependecy hell
Calling a method static or accessing a static property is a dependecy on the class where that method or property is defined. Consider the following example:
```php
<?php
use Orm\Core\Connection;
use Orm\Core\Hydration;
use Orm\Model\EntityFields;
use Orm\Model\EntityTable;
use Psr\LoggerInterface

class Model
{
    private $logger;
	
	public function __construct(LoggerInterface $logger)
    {
		$this->logger = $logger;
	}
	
	public function find($id, $type = Hydration::ARRAY)
	{
		return Connection::query(sprintf(
			'SELECT * FROM %s WHERE %s = %d',
			EntityTable::NAME,
			EntityFields::IDENTIFIER,
			$id
		), $type);
	}
}
```
At first glace you might think the `Model` class has only two dependencies. One obvious is the `LoggerInterface`, you need this in order to initialize it. The second is the `Connection`, on which `::query()` is staticly called. For the more observent, the use statements reveal that there are three more: `Hydration` for the type parameter, `EntityTable` for the table name and `EntityFields` for the identifier field.

But this is not all, if you were to simply initialize this class and call `::find()` you might find that `Connection::query()` might not work since you never actually initialized the database connection. This creates a __hidden__ dependecy on whatever the `Connection` needs to properly execute the `::query()` method. 'Fine' you must think, 'I will never use this without a database connection, what is the problem?'.

This is where experiance and knowledge comes into play. Every heard of unit testing?

## Unit testing
As you write more and more complex code you will come to a point that you cannot judge the impact of a change. It would have been useful if you had a way to test all your code and see if it still behaves the same as it did before.

This is the problem (or challange) most programmer at some point reach. This is usually the moment you will start looking into unit testings. With `php` you will most likely end up with `PHPUnit`. _"PHPUnit is a programmer-oriented testing framework for PHP."_ [as phpunit.de describes itself][phpunit].

Looking back at our previous example, static code is by definition hard to deal with in test. You cannot mock the called methods which means you will have to initialze the underlaying code. Which in turn might require you to setup a database connection and possibly fill the database with fixtures. When you find yourself doing this you are no longer unit testing, you are now intergration testing or functional testing. This means that your test no longer covers a unit (Class or method) but a large chunk of your application.

I think you might start to see how static is not sure useful anymore. From a unit-test perspective, a static is like a private: part of the code you are testing.

## So never use static?
Well no, there are valid use cases. One is that if you have a list of pre defined items static can help reduce memory since it will be on class level and not in any instances. Like so:
```php
<?php
class SomeOtherClass
{
    private static $visitors = [
		'foo' => function ($i, Context $c) {
			// ...
		},
		'bar' => function ($i, Context $c) {
			// ...
		},
	];
	
	public function visit($i, Context $c)
	{
		foreach (self::$visitors as $v) {
			$v($i, $c);
		}
	}
}
```

Other cases are utility methods which do not require outside dependecies, like a slugify method. Like so:
```php
<?php
class Util
{
	public static function slug($string)
	{
		return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '_', $string)));
	}
}
```

These methods can even be unit tested since they do not require initialization.

I am sure there are other use cases which do not break unit testing, but these are the most common I found in my daily programming. So now you know better when you create a static method and might think twice before actually commiting the code!

[laravel-quickstart]: https://laravel.com/docs/5.2/quickstart
[phpunit]: https://phpunit.de/
