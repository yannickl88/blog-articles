[//]: # (TITLE: Immutability of Data)
[//]: # (TAGS: php, entity, doctrine, data, data integrity)
[doctrine-example]: https://github.com/yannickl88/blog-articles/tree/master/src/immutability-of-data/Entity

When dealing with enterprise software, your data is often the most valuable part. It contains all your customer information, contracts, invoices and much more. So what are you doing to do to make sure the data is being dealt with correctly? A bug in your code can have a high impact on the integrity of your data. If the bug is causing unwanted changes in your data, fixing the damage might prove to be quite a big challenge.

With this post I would like to show how data immutability can help designing a more robust system. One that is less susceptible to bugs that might change your data unwanted.

## The contradiction
At first it might seems like a contradiction. Immutability and mutating data seems like to ends of the same spectrum. But these two concepts can actually complement each other when used together. Think of it like versioning your data. Each version is immutable by definition and a change will not update a version but create a new one.

For example: consider you have contracts and each contract has an end date. The contracts can be renewed for a new period, this will extend the end date. 

A naive approach to implement this would be to update the end date each time you renew the contract. And this is what most people would do. But, you will lose the previous end date when you do so since it is overwritten. So instead, each renew can also be seen as a new version of the contract, but with a different end date. This way, when there was a bug that might have caused incorrect renewals it is easy to rollback to an old version. Without it, it can be a big challenge to restore the old data.

In essence, this is the core principal of data immutability and most solutions are a variant of this. What usually differs is if you want to access the older versions from your domain model. So alternatively the database can have the old versions but they are never accessabel in the code. This allows you to query them when needed in the case of a bug and keeps the complexity lower.

## Example

So, how do you implement versioning of the data? Let us go back to the example of the contracts and renewing them. The naive solution here would look something like so:

```php
<?php
class Contract
{
    private $end_date;

    public function __construct(\DateTime $end_date)
    {
        $this->end_date = $end_date;
    }

    public function getEndDate(): DateTime
    {
        return clone $this->end_date;
    }

    public function renew(\DateInterval $interval): void
    {
        $this->end_date->add($interval);
    }
}
```
You can see that the end date is updated when renewing the contract, loosing the old data. 

To created a versioned contract, all data needs to be extracted to a version. The resulting contract will then only contain the versioning logic. Additionally, you can implement the public methods which call the current version. This allows you to prevent exposure of the underlying data structure. A versioned contract would look something like so:
```php
<?php
class ContractVersion
{
    private $end_date;

    public function __construct(\DateTime $end_date)
    {
        $this->end_date = $end_date;
    }

    public function getEndDate(): \DateTime
    {
        return clone $this->end_date;
    }
}

class VersionedContract
{
    private $versions = [];

    public function __construct(\DateTime $end_date)
    {
        $this->versions[] = new ContractVersion($end_date);
    }

    public function getEndDate(): \DateTime
    {
        return $this->getCurrentVersion()->getEndDate();
    }

    private function getCurrentVersion(): ContractVersion
    {
        return end($this->versions);
    }

    public function renew(\DateInterval $interval)
    {
        $this->versions[] = new ContractVersion($this->getEndDate()->add($interval));
    }
}
```
> Note: all the ORM stuff is omitted, a [full Doctrine version can be seen here][doctrine-example].

Both `Contract` and `VersionedContract` have the same public API and behavior. Yet, one's data is immutable and the other is not. So what does mean for your data. If you would execute the following code:

```php
$contract = new Contract(new \DateTime('2017-10-10 10:10:00'));
$contract->renew(new \DateInterval('P6M'));
$contract->renew(new \DateInterval('P6M'));
$entityManager->persist($contract);

$contract = new VersionedContract(new \DateTime('2017-10-10 10:10:00'));
$contract->renew(new \DateInterval('P6M'));
$contract->renew(new \DateInterval('P6M'));
$entity_manager->persist($contract);

$entity_manager->flush();
```
> Note: this example is using doctrine, but any ORM should be able to do this.

The resulting data is as follows:

*Contract*

| id | end_date            |
|----|---------------------|
| 1  | 2018-10-10 10:10:00 |

*VersionedContract*

| id |
|----|
| 1  |

*ContractVersion*

| id | end_date            | contract_id |
|----|---------------------|-------------|
| 1  | 2017-10-10 10:10:00 | 1           |
| 2  | 2018-04-10 10:10:00 | 1           |
| 3  | 2018-10-10 10:10:00 | 1           |

As you can see, in the `VersionedContract`'s case, all the data ever present in the `end_date` field is there. This is in the form of `ContractVersion` records. While for the regular `Contract` we only have the most recent data.

## Wrapping up
As a developer it is your responsibility to design systems well. Doing the same for your data is just as important (if not more) as your application. You might be a small startup now, but after a couple of years you can grow into a large company. Designing your software as well as your data right from the start will save you later down the line.

So, should you now run of and version all your data? Well no, there are some serious performance penalties for doing so. Hydration of your domain model now requires querying two tables. Moreover, you have introduced a collection you need to check with for every function call. Granted, you can optimize this somewhat with smart queries, but it will always be slower than the plain example.

So, should you version your sensitive data? Absolutely! Data related to clients, contracts, invoices, etc. are good examples for versioning. You want to be able to see when the data changes and what the values were. 

If done right, and somebody asks: "What happend here 2 years ago?" You can answer that question.
