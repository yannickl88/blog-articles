There is a famous problem that was presented in the American television game show Let's Make a Deal. In the show there is a part where the host presents a probability puzzle. It has some interesting properties which make the answer counter-intuitive at first. But, upon further inspection it has an interesting solution. This problem is the Monty Hall Problem.

With this post I will show a way to get the correct answer to the puzzel by implementing it. Then simulating the puzzel enough times to have an answer without complicated math.

## The puzzel

Consider three closed doors. Behind one of them is a nice car and the other two are zonks - or something else of no value. The host asks you to pick one of the three doors which you think is the car. Once picked, the host will pick one door one of the other doors which contains a zonk and reveal it. This leaves you with two doors and you have the option to stick with your choice, or pick the other door. What should you do?

This is where it puzzle becomes interesting, what has the highest probability? To stick with your original choice, or to switch to the other door?

So, how would you go about to calculate this? You could do the formal math and of course come to a solution. But there are other ways to find the correct answer without needing a university degree. Another approach is to use Computer Simulation and repeat the puzzle many times. Doing this enough times the average will converge to the correct solution.

## Programming the Puzzle

Let us first setup the problem a bit. We have a set of doors, a correct one and a way to pick a door. Moreover, we need to be able to perform a second choice. We can write this in code as follows.

```php
<?php
class MoneyHall
{
    private $doors;
    private $door_with_prize;

    public function __construct()
    {
        $this->doors = [1, 2, 3];
        $this->door_with_prize = random_int(1, 3);
    }

    public function pick(int $choice, callable $second_pick): bool
    {
        // Play the game.
        return true;
    }
}

$problem = new MoneyHall();
$problem->pick(random_int(1, 3), function (array $doors) {});
```

This is also a nice encapsulation example. From the outside (i.e., from an instance of `MontyHall`) you cannot see the correct answer. The only way to find out is to call `::pick()` and see if it was correct.

Anyhow, there is still the implementation of the `pick` method. A simple way would be to pick a random door which is not the one picked and then the correct one. Then remove this door from the options and use it for the second choice. If this choice returns the correct door, return `true` else `false`. This could look like so, where we pick a random door for the second choice.

```php
<?php
class MoneyHall
{
    // ...

    public function pick(int $choice, callable $second_pick): bool
    {
        $doors = $this->doors;

        // Pick a random door until we have a 'zonk' which was not the picked door and not the prize door.
        do {
            $zonk = random_int(1, 3);
        } while ($zonk === $choice || $zonk === $this->door_with_prize);

        // Remove the zonk.
        unset($doors[array_search($zonk, $doors)]);

        // Check the result of the second pick.
        return $this->door_with_prize === $second_pick($doors);
    }
}

$problem = new MoneyHall();
$problem->pick(random_int(1, 3), function (array $doors) {
    shuffle($doors);
    
    return array_pop($doors);
});
```

## Running the simulation

Now we have a working simulation of the Monty Hall Problem the next step is sampling. The goal is to perform many samples to get a reliable answer. While there is no golden number, this depends on the correctness of the simulation. In this case, even after 100 iterations you can get quite a decent answer. But, better results are in ranges of 10000 or even 1000000.

To do this, a simple while loop will suffice.

```php
$total = 0;
$correct = 0;

while ($total < 100000) {
    $total++;

    $problem = new MoneyHall();

    $correct += (int) $problem->pick(random_int(1, 3), function (array $doors) {
        shuffle($doors);

        return array_pop($doors);
    });
}

echo sprintf('%f at %d iterations', $correct / $total, $total), "\n";
```

When executing this code, you will get a value of around `0.5`. This is indeed what to expect when selecting a random door, you would expect to be right half of the time. To get even better answers, run the test multiple times and average the result. This will result in a number closer to the correct value.

So what about sticking with your original choice? No problem, a small change to the second choice callback will give this result.

```php
$total = 0;
$correct = 0;

while ($total < 100000) {
    $total++;

    $problem = new MoneyHall();

    $choice = random_int(1, 3);

    $correct += (int) $problem->pick($choice, function (array $doors) use ($choice) {
        return $choice;
    });
}

echo sprintf('%f at %d iterations', $correct / $total, $total), "\n";
```

This will result in a value of around `0.3333`, and this is the correct answer. So, what about when we switch?

```php
$total = 0;
$correct = 0;

while ($total < 100000) {
    $total++;

    $problem = new MoneyHall();

    $choice = random_int(1, 3);

    $correct += (int) $problem->pick($choice, function (array $doors) use ($choice) {
        unset($doors[array_search($choice, $doors)]);

        return array_pop($doors);
    });

}
echo sprintf('%f at %d iterations', $correct / $total, $total), "\n";
```

Here you will find the answer is around `0.6666`, and again this is correct! 

## Wrapping up

After running the simulations we can conclude that it the best option is to switch. This is the same answer the formal math finds as the result.
