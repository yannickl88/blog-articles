<?php
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
     * @var DateTime
     */
    private $end_date;

    /**
     * @ORM\ManyToOne(targetEntity="VersionedContract", inversedBy="versions")
     * @ORM\JoinColumn()
     * @var VersionedContract
     */
    private $contract;

    public function __construct(VersionedContract $contract, \DateTime $end_date)
    {
        $this->contract = $contract;
        $this->end_date = $end_date;
    }

    public function getEndDate(): DateTime
    {
        return clone $this->end_date;
    }
}
