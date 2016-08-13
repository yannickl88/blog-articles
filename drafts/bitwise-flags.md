Consider the following example:
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
The naive way of storing this (in a database) is to create for each option a field and storing a `0` when it is `false` and `1` when it was `true`. Which of course works, bit adding options will require a new field, which might require creating a compatibility layer for your old data. Moreover, checking multiple options at once requires to expressions. i.e., `$config->option1 && !$config->option2`. There is an easier way to do this and it's even more efficient at checking fields.

This brings me to an old topic which I have to explain to all the new people at some point and even once explained not everybody understand how it actually works. So in this post I'm going to explain how to use bitwise operators and how it works.

## Setup
Looking back at our example, a couple of changes need to be made. Instead of booleans and fields, the `Config` class will contain constants. Each constant will be a value fits our fomula `2^x` as long as `x` is positive. So our first constant will be `2^0` which is `1`. The second `2^1` is `2`. The third `2^2` is `4` and so on.

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
> Note here that `2^30` is the maximum since it will hit the `PHP_INT_MAX` with more options. So you can not have more than 30 options on a 32bit version of php.

That is it, now you can start using bitwise operations on you options.
## Usage
Using the constants in the `Config` you can add them to eachother using the bitwise operator `|`. What this does it add two values in binary (the specific I will explain in a bit). Thus if you want to 'enable' two options all you have to do is: `Config::OPTION_1 | Config::OPTION_4`. This will return a value with both options turned on and the others off. Simply add more options you want to turn on by adding an extra like so: `Config::OPTION_1 | Config::OPTION_4 | Config::OPTION_5`.

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
