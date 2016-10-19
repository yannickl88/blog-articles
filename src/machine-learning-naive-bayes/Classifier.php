<?php
class Type
{
    const POSITIVE = 'positive';
    const NEGATIVE = 'negative';
}

class Classifier
{
    private $types     = [Type::POSITIVE, Type::NEGATIVE];
    private $words     = [Type::POSITIVE => [], Type::NEGATIVE => []];
    private $documents = [Type::POSITIVE => 0, Type::NEGATIVE => 0];

    public function guess($statement)
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

    public function learn($statement, $type)
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

    public function p($word, $type)
    {
        $count = 0;

        if (isset($this->words[$type][$word])) {
            $count = $this->words[$type][$word];
        }

        return ($count + 1) / (array_sum($this->words[$type]) + 1);
    }

    public function pTotal($type)
    {
        return ($this->documents[$type] + 1) / (array_sum($this->documents) + 1);
    }

    public function getWords($string)
    {
        return preg_split('/\s+/', preg_replace('/[^A-Za-z0-9\s]/', '', strtolower($string)));
    }
}

$classifier = new Classifier();
$classifier->learn('Symfony is the best', Type::POSITIVE);
$classifier->learn('PhpStorm is great', Type::POSITIVE);
$classifier->learn('Iltar complains a lot', Type::NEGATIVE);
$classifier->learn('No Symfony is bad', Type::NEGATIVE);

var_dump($classifier->guess('Symfony is great')); // string(8) "positive"
var_dump($classifier->guess('I complain a lot')); // string(8) "negative"
