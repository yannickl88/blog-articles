Machine learning is for me an interesting topic since it is slowly becomming ubiquitous in our daily lives. From thermostats which know when you will be home, to smarter cars and the phone we have in our pocket. It seems like it is everywhere and that makes it an intresting field to explore, but what is machine learning? In general terms, it is a way for a system to learn and make predictions. This can be as simple as predicting relevent shopping items to as complex as a digital assistant.

With this blog post I will try to give an introduction into classification using the [Naive Bayes classifier algorithm][naive bayes classifier]. It is an easy algorithm to implement while giving faily decent results but it will require some statistics, so bear with me. Hopefully by the end of it you might see some applications and even try to implement it yourself!

## Setup
So, what does the classifier want to achieve? Well, it should be able to guess if a sentence is *Positive* or *Negative*. For instance, `"Symfony is the best"` is a *Positive* sentence, while `"No Symfony is bad"` is a *Negative* sentence. So given a new sentence, I want the classifier to return the type without me implementing new rules. 

So from this, the basic setup for the classifier will be in a class called `Classifier` and it will contain a guess method. This method should get the new sentence as input and will return either *Positive* or *Negative*. The class would like something like so:
```php
class Classifier
{
    public function guess($statement)
    {}
}
```

Moreover, I do not like woring with static string, so also define an Enum-like class called `Type` and it will contain `POSITIVE` and `NEGATIVE` constants which will be used for the output.

```php
class Type
{
    const POSITIVE = 'positive';
    const NEGATIVE = 'negative';
}
```

Setup done, time to create an algorithm that can make predictions!

## Naive Bayes
Naive Bayes works by looking at a training set and seeing how close your input resembels something it already knows and return that group. It does so using simple statistics and a bit of math. For example, when looking at the following training set consisting of 4 documents:

| Statement | Type |
|---|:---:|
| Symfony is the best | `Positive` |
| PhpStorm is great | `Positive` |
| Iltar complains a lot | `Negative` |
| No Symfony is bad | `Negative` |

If given the input `"Symfony is great"` you can intuitively say that this input is a *Positive* statement. You usually do this by looking at what was perviously taught and make a desicion on that historical information. This is what Naive Bayes also does: it looks at the training set and sees which type is more likely. 

### Definitions
Naive Bayes uses a bit of statistics to do this and to further explain this, a couple of definitions are needed. First of all, lets define the probability that the input is one of the given types also denoted with `P(Type)`. This is done by simply dividing the number of knows documents of a type, by the total of documents in the training set. A document is in this case an entry in the training set. For now, this method shall be called `totalP` and would look like so:
```php
function totalP($type)
{
    return ($this->documents[$type] + 1) / (array_sum($this->documents) + 1);
}
```
> Note here that `1` is added to both the numerator and denominator to prevent `0` probabilities.

In the given example, both *Positive* and *Negative* would result in `0.6` since there are 2 items each of the total 4 documents (so `(2 + 1) / (4 + 1)`).

The second definition is that of the probability a word is part of the type, we denote this with `P(word | Type)`. We do this by counting how often a word occurred in the training documents for the given `Type` and dividing that onto the total words in the documents for that `Type`. This method is called `p` and would look like so:
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
Before the algorithm can do anything, the training set needs to be added so the classifier has the historic information. In order to do the work, the classifier must know two things: which word occures how many times for each type, and how many documents are there per type. In this implementation I will store this in two arrays, one which will contain the word counts per type and one which contains the documents counts per type. All the other information I need can be aggregated from those arrays. An implemenation would look like:

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
So with that set, the training set can be added and the alorithm can start making guesses.

### Guessing
The guessing uses Bayes' theorem for calculating the probability for a `Type` given a sentence. Formally we would write it as `P(Type | sentence) = P(Type) * P(sentence | Type) / P(sentence)`. This can actually be simplyfied a bit, since the `P(sentence)` is constant for our calculations, we are only interested in the `Type`. Every term not dependended on `Type` can thus be removed. The result is then `P(Type | sentence) = P(Type) * P(sentence | Type)`. Using the chain rule we can even further simplify this into `P(Type | sentence) = P(Type) * P(word_1 | Type) * ... * P(word_n | Type)` where we multiply all the individual probabilities for the words to get that of the sentence. You should now see only familiair terms, which means it can now be calculated.

Lastly before actually showing the implementation, the alorithm should calculate `P(Type | sentence)` for every `Type` and pick the one which give the highest likelihood. The one with the highest likelihood will be the result of the classification. So without further ado, an implementation could look like:
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

[naive bayes classifier]: https://en.wikipedia.org/wiki/Naive_Bayes_classifier
