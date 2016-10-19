Machine learning is for me an interesting topic since it is becoming more and more ubiquitous in our daily lives. From thermostats which know when you will be home, to smarter cars and the phone we have in our pocket. It seems like it is everywhere and that makes it an interesting field to explore, but what is machine learning? In general terms, it is a way for a system to learn and make predictions. This can be as simple as predicting relevant shopping items to as complex as a digital assistant.

With this blog post I will try to give an introduction into classification using the [Naive Bayes classifier algorithm][naive bayes classifier]. It is an easy algorithm to implement while giving decent results. But it will need some statistic knowledge to understand, so bear with me. By the end of it you might see some applications and even try to implement it yourself!

## Setup
So, what does the classifier want to achieve? Well, it should be able to guess if a sentence is *Positive* or *Negative*. For instance, `"Symfony is the best"` is a *Positive* sentence, while `"No Symfony is bad"` is a *Negative* sentence. So given a new sentence, I want the classifier to return the type without me implementing new rules. 

From this, the basic setup for the classifier will be in a class called `Classifier` and it will contain a guess method. This method should get the new sentence as input and will return either *Positive* or *Negative*. The class would like something like so:
```php
class Classifier
{
    public function guess($statement)
    {}
}
```

Moreover, I do not like working with static string, so also define an Enum-like class called `Type` and it will contain `POSITIVE` and `NEGATIVE` constants which will be used for the output.

```php
class Type
{
    const POSITIVE = 'positive';
    const NEGATIVE = 'negative';
}
```

Setup done, time to create an algorithm that can make predictions!

## Naive Bayes
Naive Bayes works by looking at a training set and seeing how close your input resembles something it already knows and return that group. It does so using simple statistics and a bit of math. For example, when looking at the following training set consisting of 4 documents:

| Statement | Type |
|---|:---:|
| Symfony is the best | `Positive` |
| PhpStorm is great | `Positive` |
| Iltar complains a lot | `Negative` |
| No Symfony is bad | `Negative` |

If given the input `"Symfony is great"` you can intuitively say that this input is a *Positive* statement. You usually do this by looking at what was previously taught and make a decision on that historical information. This is what Naive Bayes also does: it looks at the training set and sees which type is more likely. 

### Definitions
Naive Bayes uses a bit of statistics to do this and to further explain this; a couple of definitions are needed. First of all, lets define the probability that the input is one of the given types also denoted with `P(Type)`. This is done by simply dividing the number of knows documents of a type, by the total of documents in the training set. A document is in this case an entry in the training set. For now, this method shall be called `totalP` and would look like so:
```php
function totalP($type)
{
    return ($this->documents[$type] + 1) / (array_sum($this->documents) + 1);
}
```
> Note here that `1` is added to both the numerator and denominator to prevent `0` probabilities.

In the given example, both *Positive* and *Negative* would result in `0.6` since there are 2 items each of the total 4 documents (so `(2 + 1) / (4 + 1)`).

The second definition is that of the probability a *word*, given a certain *Type*, formally this is defined as `P(word | Type)`. We do this by counting how often a word occurred in the training documents for the given `Type` and dividing that onto the total words in the documents for that `Type`. This method is called `p` and would look like so:
```php
function p($word, $type)
{
    $count = isset($this->words[$type][$word]) ? $this->words[$type][$word] : 0;

    return ($count + 1) / (array_sum($this->words[$type]) + 1);
}
```
In the training set, the word `is` for `Type` *Positive* would be `0.375` since it occurs twice in the total of 7 words for the `Positive` training set, which would result in `(2 + 1) / (7 + 1)`.

Lastly, the algorithm should only consider words and ignore anything else. A simple method to give a list of words from a string can be implemented as such:
```php
function getWords($string)
{
    return preg_split('/\s+/', preg_replace('/[^A-Za-z0-9\s]/', '', strtolower($string)));
}
```
All set, time to start implementing!

### Learning
Before the algorithm can do anything, the training set needs to be added so the classifier has the historic information. In order to do the work, the classifier must know two things: which word occurs how many times for each type, and how many documents are there per type. In this implementation I will store this in two arrays, one which will contain the word counts per type and one which contains the documents counts per type. All the other information I need can be aggregated from those arrays. An implementation would look like:

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
So with that set, the training set can be added and the algorithm can start making guesses.

### Guessing
In order to guess the `Type` of a sentence, the algorithm should calculate for each `Type` the probability given a sentence. Formally this would be written as `P(Type | sentence)`. The `Type` with the highest probability will be the result of the classification and returned by the algorithm. 

To calculate `P(Type | sentence)` Bayes' theorem can be used. Formally the theorem is defined as `P(Type | sentence) = P(Type) * P(sentence | Type) / P(sentence)` which means that the probability for the `Type` given a sentence is the same as the probability of the `Type` times the probability for the sentence given a `Type` divided by the probability of the sentence.

As you might have guessed, since the algorithm calculates each `P(Type | sentence)` for the same sentence, the `P(sentence)` is always the same. This means that it can be omitted since we only care about the highest probability, not the actual value. This means that calculation can be simplified to: `P(Type | sentence) = P(Type) * P(sentence | Type)`.

Finally, to calculate `P(sentence | Type)` we can apply the chain rule to each word in the sentence. So if there are `n` words in the sentence this is the same as `P(word_1 | Type) * P(word_2 | Type) * P(word_3 | Type) * ... * P(word_n | Type)`. The probability of each word can be calculated using the definition as seen earlier.

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
There you have it, even with a **very** small training set the algorithm can still return some correct results. In a more real world example you would have hundreds of learning records to give more accurate results. For example, [Naive Bayes has been proven to give decent results in sentiment analyses][nb-twitter-sentiment].

Moreover, Naive Bayes can be applied to more than just text. If you have other ways of calculating the probabilities of your metrics you can also plug those in and it will just as good.

Hopefully with this post you I have made the world of machine learning a bit more accessible to you. If you like this one, let me know in the comment below!

[naive bayes classifier]: https://en.wikipedia.org/wiki/Naive_Bayes_classifier
[github-classifier]: https://github.com/yannickl88/blog-articles/blob/master/src/machine-learning-naive-bayes/Classifier.php
[nb-twitter-sentiment]: http://www-nlp.stanford.edu/courses/cs224n/2009/fp/3.pdf
