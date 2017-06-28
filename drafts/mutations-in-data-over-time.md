[//]: # (TITLE: Mutations in data over time)
[//]: # (TAGS: entities, data)

When dealing with enterprise software, your data is often the most valuable part. It contains all your customer information, contracts, invoices and much more. So what are you doing to make sure the data is being dealt with correctly? A bug in your code can have a high impact on the validity of your data. If the bug is causing unwanted changes in your data, fixing the damage might prove to be quite a big challenge.

With this post I would like to show how data immutability can help designing a more robust system. One that is less susceptible to bugs that might change your data unwanted.

## The contradiction
At first it might seems like a contradiction. Immutability and mutating data seems like to ends of the same spectrum. But consider this: Instead of changing the data, why not make a new version of the previous data and update that?

Now it makes more sense and the two concepts can actually complement each other. Not only can we still change the data, we actually get a full revisions as a bonus. So tracing back a change is as easy as looking at past revisions. Of course, you do not actually need to do this for your whole data object, you can do this for partial data too.

From a code perspective, not a whole lot needs to change to make this work if you are using an ORM like doctrine. You can create an abstraction in such a way that the underlying data is not visible. For example, consider these two classes.

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

    public function renew(\DateInterval $interval)
    {
        $this->end_date->add($interval);
    }
}
```

```php
<?php
class ContractVersion
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
}

class VersionedContract
{
    private $versions = [];

    public function __construct(\DateTime $end_date)
    {
        $this->versions[] = new ContractVersion($end_date);
    }

    public function getEndDate(): DateTime
    {
        return $this->getCurrentVersion()->getEndDate();
    }

    private function getCurrentVersion(): ContractVersion
    {
        return end($this->versions);
    }

    public function renew(\DateInterval $interval)
    {
        $this->versions[] = new ContractVersion($this->getCurrentVersion()->getEndDate()->add($interval));
    }
}
```
Both `Contract` and `VersionedContract` have the same public API and behavior. Yet, one's data is immutable and the other is not. And yes, I agree that it requires more code and complexity is higher, but in return you get versioning of your data. 

For instance, what if you have a bug in the renewal process that calculates the wrong interval. In the old model you will lose the previous data, making puzzling back the old data difficult. In the immutable case you have it easier since you can view the previous data. In such cases, recovery is easy and straightforward. You can even remove the new versions and run the renewal process again.
