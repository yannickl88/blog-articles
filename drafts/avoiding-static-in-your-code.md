[//]: # (TITLE: Avoiding static in your code)
[//]: # (DATE: 2016-08-05T09:00:00+01:00)
[//]: # (TAGS: php, static, phpunit)

For anyone not familiar with the `static` keyword in languages, it is in essence a way of defining things on class level instead of instance level. This allows you to access the same reference from anywhere in your code. 

As a new programmer that has been introduced to the `static` keyword, that might seems very useful. I do not blame you for thinking so, since I did so too not all that long ago. As you write more and more code, you learn and improve and your coding matures. You start to form new ideas as your applications grow bigger and you maintain code that is slowly becoming legacy code. When you look back you will discover that defining things `static` might make things more difficult than they are worth.

## Singelton and sorts
For anyone following a computer science bachelor you will undoubtedly have had a course or lesson on software patterns as did I in my bachelor courses. We had the classics, like [Model-View-Controller][wiki-mvc], [Factory][wiki-factory], [Visitor][wiki-visitor] and of course [Singelton][wiki-singelton]. The latter one I always found most interesting. For those unaware, a simple example would be:
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
What this bit of code allows you to statically call your class to get an instance, this will always be the same instance. So any state that is stored in the instance can be accessible from everywhere.

As a new programmer, this was so elegant and simple; you can make your database connection accessible from everywhere, allowing easy access to your ORM or the likes. Or you can create a cool routing system which can register routes from everywhere, [something Laravel does][laravel-quickstart].

And as you start building your applications, you find that calling static methods from others is an easy way quickly having access to the things you need. So why the title of this post? Why should you not use it then?

## Hidden dependency hell
Calling a method static or accessing a static property is a dependency on the class where that method or property is defined. Consider the following example:
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
At first glance you might think the `Model` class has only two dependencies. One obvious is the `LoggerInterface`, you need this in order to initialize it. The second is the `Connection`, on which `::query()` is statically called. For the more observant, the use statements reveal that there are three more: `Hydration` for the type parameter, `EntityTable` for the table name and `EntityFields` for the identifier field.

But this is not all, if you were to simply initialize this class and call `::find()` you might find that `Connection::query()` might not work since you never actually initialized the database connection. This creates a __hidden__ dependency on whatever the `Connection` needs to properly execute the `::query()` method. 'Fine' you must think, 'I will never use this without a database connection, what is the problem?'.

Every heard of unit testing?

## Unit testing
As you write more and more complex code you will come to a point that you cannot judge the impact of a change. It would have been useful if you had a way to test all your code and see if it still behaves the same as it did before.

This is the problem (or challenge) most programmer at some point reach. This is usually the moment you will start looking into unit testings. With `php` you will most likely end up with `PHPUnit`. _"PHPUnit is a programmer-oriented testing framework for PHP."_ [as phpunit.de describes itself][phpunit].

Looking back at our previous example, static code is by definition hard to deal with in test. You cannot mock the called methods which means you will have to initialize the underlaying code. Which in turn might require you to setup a database connection and possibly fill the database with fixtures. When you find yourself doing this you are no longer unit testing, you are now integration testing or functional testing. This means that your test no longer covers a unit (Class or method) but a large chunk of your application.

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
In this example everything is contained within the class. No external static calls to worry about and it's a small optimization in memory usage for something that will not change.

Other cases are utility methods which do not require outside dependencies, like a slugify method. Like so:
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
The `slug` method only does a very well defined behavior. It is easy to take the behavior into account in unit tests and I would not be too worried when I see this call.

These methods can even be unit tested since they do not require initialization.

Another common use for static is creating static constructors. This is something Doctrine does for its exceptions. [An example of this is the `InvalidArgumentException`][doctrine-invalid-argument-exception].

```php
<?php
namespace Doctrine\Common\Proxy\Exception;

use InvalidArgumentException as BaseInvalidArgumentException;

class InvalidArgumentException extends BaseInvalidArgumentException implements ProxyException
{
    public static function proxyDirectoryRequired()
    {
        return new self('You must configure a proxy directory. See docs for details');
    }

    public static function notProxyClass($className, $proxyNamespace)
    {
        return new self(sprintf('The class "%s" is not part of the proxy namespace "%s"', $className, $proxyNamespace));
    }

    public static function invalidPlaceholder($name)
    {
        return new self(sprintf('Provided placeholder for "%s" must be either a string or a valid callable', $name));
    }

    // ...
}
```
These static call only provide shorthands and better readability, all they do is call the constructor. Here again is that they do not use outside dependencies.

I am sure there are other use cases which do not break unit testing, but these are the most common I found in my daily programming. So now you know better when you create a static method and might think twice before actually committing the code!

[wiki-mvc]: https://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller
[wiki-factory]: https://en.wikipedia.org/wiki/Factory_(object-oriented_programming)
[wiki-visitor]: https://en.wikipedia.org/wiki/Visitor_pattern
[wiki-singelton]: https://en.wikipedia.org/wiki/Singleton_pattern
[laravel-quickstart]: https://laravel.com/docs/5.2/quickstart
[phpunit]: https://phpunit.de/
[doctrine-invalid-argument-exception]: https://github.com/doctrine/common/blob/master/lib/Doctrine/Common/Proxy/Exception/InvalidArgumentException.php
