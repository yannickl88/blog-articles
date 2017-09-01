There is a famous problem that was presented in the American television game show Let's Make a Deal. In the show there is a part where the host presents a probability puzzle for the contestant. What makes this puzzle interesting is that the answer is a bit counter-intuitive at first. Yet, even when explained and proven, some still are not convinced by the answer. This problem is the Monty Hall Problem.

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
> Note here that we are assuming `random_int(1, 3)` is truly random. PHP does do a good job of having a real random value but it remains pseudo-random. However, for the sake of this example, it is good enough.

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

## Are we right?

So, how accurate is the simulation? Did we perform enough tests? Those are valid questions to ask when doing these kind of things. Luckily we can verify these questions by testing the hypothesis using a null-hypothesis.

Let's assume there is no difference in switching. That means that after the first round there are two doors left and they have equal probability to contain a car. That means that the probability of winning a car in the second round should be 50%. For this, let _p_ be the probability that we win the car. The null-hypothesis (H<sub>0</sub>) would then be _p_ = 0.5. 

Time to do some sampling. With 10000 samples a run resulted in 3267 wins. Because this is a Binomial distribution we use a  Binomial test in R to calculate the _p-value_.  The result is 2.2e-16, which is an extremely low value, so we have to reject the null-hypothesis and say that it is not 0.5. 

From our other tests we concluded that is somewhere around 0.3333. So the new null-hypothesis would be that _p_ = 0.3333. When calculating this again we get a _p-value_ of 0.1615. With a confidence interval of 95% that is more than enough to accept the new null-hypothesis. That also means that switching will have a probability of 0.6666.

# Wrapping up

After running the simulations we can conclude that it the best option is to switch. This is the same answer the formal math finds as the result.
