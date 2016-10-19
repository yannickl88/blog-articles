[//]: # (TITLE: Avoiding static in your code)
[//]: # (DATE: 2016-10-21T08:00:00+01:00)
[//]: # (TAGS: php, machine learning, naive bayes)

Machine learning is becoming more and more ubiquitous in our daily lives. From thermostats which know when you will be home, to smarter cars and the phone we have in our pocket. It seems like it is everywhere and that makes it an interesting field to explore! But what is machine learning? In general terms, it is a way for a system to learn and make predictions. This can be as simple as predicting relevant shopping items to as complex as a digital assistant.

With this blog post I will try to give an introduction into classification using the [Naive Bayes classifier algorithm][naive bayes classifier]. It is an easy algorithm to implement while giving decent results. But it will need some statistic knowledge to understand, so bear with me. By the end of it you might see some applications and even try to implement it yourself!

## Setup
So, what does the classifier want to achieve? Well, it should be able to guess if a sentence is *Positive* or *Negative*. For instance, `"Symfony is the best"` is a *Positive* sentence, while `"No Symfony is bad"` is a *Negative* sentence. So given a new sentence, I want the classifier to return the type without me implementing new rules. 

From this, the basic setup for the classifier will be in a class called `Classifier` with a guess method. This method gets a sentence as input and will return either *Positive* or *Negative*. The class would like something like so:
```php
class Classifier
{
    public function guess($statement)
    {}
}
```

Moreover, I prefer using a Enum-like class instead of strings. This Emum will be called `Type` and will contain `POSITIVE` and `NEGATIVE` constants. These constants which will be the output of the guess method.

```php
class Type
{
    const POSITIVE = 'positive';
    const NEGATIVE = 'negative';
}
```

Setup done, time to create an algorithm that can make predictions!

## Naive Bayes

Naive Bayes works by looking at a training set and makes a guess based on that training set. It does so using simple statistics and a bit of math to calculate the result. For example, when looking at the following training set, it consisting of 4 documents:

| Statement | Type |
|---|:---:|
| Symfony is the best | *Positive* |
| PhpStorm is great | *Positive* |
| Iltar complains a lot | *Negative* |
| No Symfony is bad | *Negative* |

If given the sentence `"Symfony is great"` you can say that this input is a *Positive* statement. You usually do this by looking at what was taught before and make a decision on that information. This is what Naive Bayes also does: it looks at the training set and sees which type is more likely. 

### Definitions
To explain how the algorithm works a couple of definitions are needed. First of all, lets define the probability that the input is one of the given types. This is denoted with `P(Type)`. This is done by dividing the number of knows documents of a type, by the total of documents in the training set. A document is in this case an entry in the training set. For now, this method will be called `totalP` and would look like so:

```php
function totalP($type)
{
    return ($this->documents[$type] + 1) / (array_sum($this->documents) + 1);
}
```

> Note here that `1` is added to both the numerator and denominator. This is to prevent `0` probabilities which will ruin the total probability.

In the given example, both *Positive* and *Negative* would result in `0.6`. There are 2 items each of the total 4 documents so `(2 + 1) / (4 + 1)`.

The second definition is that of the probability a *word*, given a certain *Type*. This is defined as `P(word | Type)`. This is done by first counting how often a word occurred in the training documents for the given `Type`. This result is then divided by the total words in the documents for that `Type`. This method is called `p` and would look like so:

```php
function p($word, $type)
{
    $count = isset($this->words[$type][$word]) ? $this->words[$type][$word] : 0;

    return ($count + 1) / (array_sum($this->words[$type]) + 1);
}
```

In the training set, the probability the word `"is"` is *Positive* would be `0.375`. The word occurs twice in the total of 7 words for the *Positive* training set, which would result in `(2 + 1) / (7 + 1)`.

Finally, the algorithm should only consider words and ignore anything else. A simple method to give a list of words from a string can be implemented as such:

```php
function getWords($string)
{
    return preg_split('/\s+/', preg_replace('/[^A-Za-z0-9\s]/', '', strtolower($string)));
}
```
All set, time to start implementing!

### Learning

Before the algorithm can do anything, it requires a training set with historic information. It must know two things: which word occurs how many times for each type, and how many documents are there per type. This implementation will store this information in two arrays. One which will contain the word counts per type and one which contains the documents counts per type. All the other information can be aggregated from those arrays. An implementation would look like:

```php
function learn($statement, $type)
{
    $words = $this->getWords($statement);

    foreach ($words as $word) {
        if (!isset($this->words[$type][$word])) {
            $this->words[$type][$word] = 0;
        }
        $this->words[$type][$word]++; // increment the word count for the type
    }
    $this->documents[$type]++; // increment the document count for the type
}
```

So with that set, the training can learn so it can make educated guesses.

### Guessing

To guess the `Type` of a sentence, the algorithm should calculate for each `Type` the probability given a sentence. Formally this is written would as `P(Type | sentence)`. The `Type` with the highest probability will be the result of the classification and returned by the algorithm. 

To calculate `P(Type | sentence)` the algorithm uses Bayes' theorem. The theorem is defined as `P(Type | sentence) = P(Type) * P(sentence | Type) / P(sentence)`. This means that the probability for the `Type` given a sentence is the same as the probability of the `Type` times the probability for the sentence given a `Type` divided by the probability of the sentence.

Since the algorithm calculates each `P(Type | sentence)` for the same sentence, the `P(sentence)` is always the same. This means that it can be omitted since we only care about the highest probability, not the actual value. The simplified calculation would be: `P(Type | sentence) = P(Type) * P(sentence | Type)`.

Finally, to calculate `P(sentence | Type)` we can apply the chain rule to each word in the sentence. So if there are `n` words in the sentence this is the same as `P(word_1 | Type) * P(word_2 | Type) * P(word_3 | Type) * ... * P(word_n | Type)`. The calculation of the probability of each word is using the definition as seen earlier.

Okay, all set, time for the actual implementation in php:

```php
function guess($statement)
{
    $words           = $this->getWords($statement); // get the words
    $best_likelihood = 0;
    $best_type       = null;

    foreach ($this->types as $type) {
        $likelihood = $this->pTotal($type); // calculate P(Type)

        foreach ($words as $word) {
            $likelihood *= $this->p($word, $type); // calculate P(word | Type)
        }

        if ($likelihood > $best_likelihood) {
            $best_likelihood = $likelihood;
            $best_type       = $type;
        }
    }

    return $best_type;
}
```

And that is it, now the algorithm can guess which type a statement is. All that is needed is to put it all together like so:

```php
$classifier = new Classifier();
$classifier->learn('Symfony is the best', Type::POSITIVE);
$classifier->learn('PhpStorm is great', Type::POSITIVE);
$classifier->learn('Iltar complains a lot', Type::NEGATIVE);
$classifier->learn('No Symfony is bad', Type::NEGATIVE);

var_dump($classifier->guess('Symfony is great')); // string(8) "positive"
var_dump($classifier->guess('I complain a lot')); // string(8) "negative"
```

The full implementation I have added to [the git repository of this post, see Classifier.php][github-classifier]

## Wrapping up

There you have it! Even with a **very** small training set the algorithm can still return some decent results. In a real world example you would have hundreds of learning records to give more accurate results. For example, [Naive Bayes has been proven to give decent results in sentiment analyses][nb-twitter-sentiment].

Moreover, Naive Bayes can be applied to more than just text. If you have other ways of calculating the probabilities of your metrics you can also plug those in and it will just as good.

Hopefully with this post you I have made the world of machine learning a bit more accessible to you. If you like this one, let me know in the comment below!

[naive bayes classifier]: https://en.wikipedia.org/wiki/Naive_Bayes_classifier
[github-classifier]: https://github.com/yannickl88/blog-articles/blob/master/src/machine-learning-naive-bayes/Classifier.php
[nb-twitter-sentiment]: http://www-nlp.stanford.edu/courses/cs224n/2009/fp/3.pdf
