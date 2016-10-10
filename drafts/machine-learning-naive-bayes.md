Machine learning is for me an interesting topic since it is slowly becomming ubiquitous in our daily lives. From thermostats which know when you will be home, to smarter cars and the phone we have in our pocket. It seems like it is everywhere and that makes it an intresting field to explore, but what is machine learning? In general terms, it is a way for a system to learn and make predictions. This can be as simple as predicting relevent shopping items to as complex as a digital assistant.

With this blog post I will try to give an introduction into classification using the [Naive Bayes classifier algorithm][naive bayes classifier]. It is an easy algorithm to implement while giving faily decent results. Hopefully by the end of it you might see some applications and even try to implement it yourself!

## Setup
So, what do we want to achieve? Let's say I want to guess the subject of a question. Questions can be either about *time* or about *mood*. For instance, `"How are you?"` is a *mood* question, while `"When are you there?"` is a *time* question. So given a question, I want to system to return the subject of that question. For the types I will create an Enum-like class called `Type` and it will contain `MOOD` and `TIME` constants.

```php
class Type
{
    const MOOD = 'mood';
    const TIME = 'time';
}
```

The actual classifier will be in a class called `Classifier` and will contain a guess method which will return one of the two constants from the `Type` enum. The class would like something like so:

```php
class Classifier
{
    public function guess($question)
    {}
}
```
All set, let us dive into the math!

## Naive Bayes
Naive Bayes works by looking at a training set and seeing how close your input resembels something it already knows and return that group. It does so using simple statistics and a bit of math. For example, when looking at the following training set:

| String | Type |
|---|:---:|
| How are you? | `mood` |
| How do you do? | `mood` |
| What time is it? | `time` |
| What is the time? | `time` |

If given the input `"What's the time?"` we can intuitively say that this input is more like the `time` strings than the `mood` strings. What this means that we want to compare the probability between `time` and `mood` and pick the one that is higher. We do this by looking at each word an calculating the likelyhood it is part of the type. We denote this by `p(string | Type)`. For the example, the likelyhood will be the frequency of that word. Thus `p(string | Type)` will be `(count($words[Type], string) + 1) / (count($words[Type]) + unique_count($words));`

`P(mood) * p("what" | mood) * p("s" | mood) * p("time" | mood)`

We can calculate the probability density of a given string by looking at the individual words. In the case of `"What's the time?"` will will want to know 

[naive bayes classifier]: https://en.wikipedia.org/wiki/Naive_Bayes_classifier
