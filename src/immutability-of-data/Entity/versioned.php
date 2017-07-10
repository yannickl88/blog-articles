<?php
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class ContractVersion
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $end_date;

    /**
     * @ORM\ManyToOne(targetEntity="VersionedContract", inversedBy="versions")
     * @ORM\JoinColumn()
     */
    private $contract;

    public function __construct(VersionedContract $contract, \DateTime $end_date)
    {
        $this->contract = $contract;
        $this->end_date = $end_date;
    }

    public function getEndDate(): \DateTime
    {
        return clone $this->end_date;
    }
}

/**
 * @ORM\Entity()
 */
class VersionedContract
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="ContractVersion", mappedBy="contract", cascade={"persist"})
     */
    private $versions;

    public function __construct(\DateTime $end_date)
    {
        $this->versions = new ArrayCollection([new ContractVersion($this, $end_date)]);
    }

    public function getEndDate(): \DateTime
    {
        return $this->getCurrentVersion()->getEndDate();
    }

    private function getCurrentVersion(): ContractVersion
    {
        return $this->versions->last();
    }

    public function renew(\DateInterval $interval)
    {
        $this->versions->add(new ContractVersion($this, $this->getEndDate()->add($interval)));
    }
}
