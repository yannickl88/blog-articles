[//]: # (TITLE: Mutations in data over time)
[//]: # (TAGS: entities, data)

When dealing with enterprise software, your data is often the most valuable part. It contains all your customer information, contracts, invoices and much more. So what are you doing to make sure the data is being dealt with correctly? A bug in your code can have a high impact on the validity of your data. If the bug is causing unwanted changes in your data, fixing the damage might prove to be quite a big challenge.

With this post I would like to show how data immutability can help designing a more robust system. One that is less susceptible to bugs that might change your data unwanted.

## The contradiction
At first it might seems like a contradiction. Immutability and mutating data seems like to ends of the same spectrum. But these two concepts can actually complement each other when used together. Think of it like versioning your data. Each version is immutable by definition and a change will not update a version but create a new one.

For example: consider you have contracts and each contract has an end date. The contracts can be renewed for a new period, this will extend the end date. 

A naive approach to implement this would be to update the end date each time you renew the contract. And this is what most people would do. But, you will lose the previous end date when you do so since it is overwritten. So instead, each renew can also be seen as a new version of the contract, but with a different end date. This way, when there was a bug that might have caused incorrect renewals it is easy to rollback to an old version. Without it, it can be a big challenge to restore the old data.

In essence, this is the core principal of data immutability and most solutions are a variant of this. What usually differs is if you want to access the older versions from your domain model. So the database can have the old versions but they are never accessabel in the code. This allows you to query them when needed in the case of a bug and keeps the complexity lower.

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
You can see that the end date is updated when renewing the contract. The only data here that can change is the end date, so that would be something to version. Moreover, in this example this is the most important data in the contract. A versioned contract would look something like so:
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
> Note: all the ORM stuff is omitted, a full Doctrine version can be seen here.

Both `Contract` and `VersionedContract` have the same public API and behavior. Yet, one's data is immutable and the other is not. In this case the versions are present in the domain model. If not needed, you can change the `VersionedContract` to only have the latest version instead of a list. The data will still be in the database but not accessabel in the domain model.
