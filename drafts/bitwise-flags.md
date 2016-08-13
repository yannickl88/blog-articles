The naive way of storing many boolean options (in a database) is to create for each option a field and storing a `0` when it is `false` and `1` when it is `true`. Consider the following example, something some of you might have written in some variant.

```php
<?php
class Config
{
    public $option1 = false;
    public $option2 = false;
    public $option3 = false;
    public $option4 = false;
    public $option5 = false;
    // ...
}
```

Which of course works, bit adding options will require a new field, which might require creating a compatibility layer for your old data. There is an easier way to do this and it's even more efficient at checking fields.

This brings me to an old topic which I have to explain to all the new people at some point and even once explained not everybody understands how it actually works. So in this post I'm going to explain how to use bitwise operators and how it works internally.

## Setup
Looking back at our example, a couple of changes need to be made. Instead of booleans and fields, the `Config` class will contain constants. Each constant will be a value fits our formula `2^x` as long as `x` is positive. So our first constant will be `2^0` which is `1`. The second `2^1` is `2`. The third `2^2` is `4` and so on.

The result is:
```php
<?php
class Config
{
    const OPTION_1 = 1;
    const OPTION_2 = 2;
    const OPTION_3 = 4;
    const OPTION_4 = 8;
    const OPTION_5 = 16;
    // ...
}
```
> Note here that `2^30` is the maximum since any larger values with exceed the `PHP_INT_MAX`. So you cannot have more than 30 options on a 32bit version of php.

That is it, now you can start using bitwise operations on you options.
## Usage
Using the constants in the `Config` you can add them to each other using the bitwise operator `|`. What this does it add two values in binary (the specific I will explain in a bit). Thus if you want to 'enable' two options all you have to do is: `Config::OPTION_1 | Config::OPTION_4`. This will return a value with both options turned on and the others off. Simply add more options you want to turn on by adding an extra like so: `Config::OPTION_1 | Config::OPTION_4 | Config::OPTION_5`.

In order to check if an option was set you can use the bitwise operator `&`. This is done as so:
```php
$config = Config::OPTION_1 | Config::OPTION_4 | Config::OPTION_5;

var_dump(($config & Config::OPTION_1) == Config::OPTION_1)); // true
var_dump(($config & Config::OPTION_2) == Config::OPTION_2)); // false
var_dump(($config & Config::OPTION_3) == Config::OPTION_3)); // false
var_dump(($config & Config::OPTION_4) == Config::OPTION_4)); // true
var_dump(($config & Config::OPTION_5) == Config::OPTION_5)); // true
```

And that is how you use bitwise to operators to store multiple options into one variable. But I hear you ask: How does this work?
## How it works
As I hinted on, the `&` and `|` operators do not work on the value but on the binary representation of the value. To fully understand how it works you will need to know how integers are stored in memory. First of all, binary works with `0` and `1` and each value can be represented using these two values. For simplicity sake the examples will be in 4bit integers instead of the 32bit. 

For instance: `10` is `1010` in binary. This can be represented in a table as:

|       | 8 | 4 | 2 | 1 |
|-------|:-:|:-:|:-:|:-:|
| `10`: | 1 | 0 | 1 | 0 |

When summing every value that has a `1` it will result in: `8 + 2 = 10`. 

As you might have noticed, the header row of the table corresponds with the `2^x` values defined in the config. This is exactly how it works; each binary place corresponds with the option with that value. So if re-written to the table to use the constants the result is:

|       | OPTION_4 | OPTION_3 | OPTION_2 | OPTION_1 |
|-------|:--------:|:--------:|:--------:|:--------:|
| `10`: | 1        | 0        | 1        | 0        |

Now that you know how the binary internals work, the operators are easy to explain.

First off, the `|` operator compares two numbers binary place by place and when one or the other (or both) is `1` it will result in an `1`. This is very similar to the `||` operator (logical OR) which works on booleans. Let’s consider the `Config::OPTION_1 | Config::OPTION_4` example again which is rewritten to values `1 | 8`. If written as the table:

|          | OPTION_4 | OPTION_3 | OPTION_2 | OPTION_1 |
|----------|:--------:|:--------:|:--------:|:--------:|
| `1`:     | 0        | 0        | 0        | 1        | 
| `8`:     | 1        | 0        | 0        | 0        | 
| *Result* |          |          |          |          | 
| `1 | 8`: | 1        | 0        | 0        | 1        | 

Secondly the `&` operator, you should see the pattern by now. The `&` operator compares two numbers binary place by place and only when both are `1` it will result in an `1`. This is very similar to the `&&` operator (logical AND) which works on booleans. For this example checking if `10` (from the previous example) actually contains `OPTION_4` can be written as the table:

|           | OPTION_4 | OPTION_3 | OPTION_2 | OPTION_1 |
|-----------|:--------:|:--------:|:--------:|:--------:|
| `10`:     | 1        | 0        | 0        | 1        | 
| `8`:      | 1        | 0        | 0        | 0        | 
| *Result*  |          |          |          |          | 
| `10 & 8`: | 1        | 0        | 0        | 0        | 

Here you can see that the result will also be the value you are comparing against and if no present will result in `0`. Also, it is possible to check multiple fields as once using the `&` and will return `> 0` if either one was present of the same as what you are checking if all were present.

Finally another operator that can be useful is the `~` operator which is the negation. This inverts a binary value which results in all bits flipped. For instance `~0110` will result in `1001`. This is useful to create expression like *everything except for `OPTION_3`* which would like `~OPTION_3` which is equivalent to `OPTION_1 | OPTION_2 | OPTION_4`.

And that is it really, the same examples can be applied for 32bit integers and allows you to store a lot more information in a single field then you might first have thought. 

## Real world examples
So who uses this stuff? Well the most obvious example is the [PHP error reporting flags][php-error-flags] which are stored as bitwise flags. These can be manipulated to get the exact error reporting you would like.

Other examples are the [Doctrine metadata association types][doctrine-association-fields]. They have the `Many-to-One` and `One-to-One` a special field which has both these are `Many-to-One | One-to-One` which is `to-One`. The same as with `to-Many` constants.

Do you have another example? Let me know in the comments!


[php-error-flags]: http://php.net/manual/en/errorfunc.constants.php
[doctrine-association-fields]: http://www.doctrine-project.org/api/orm/2.5/class-Doctrine.ORM.Mapping.ClassMetadataInfo.html#ONE_TO_ONE
