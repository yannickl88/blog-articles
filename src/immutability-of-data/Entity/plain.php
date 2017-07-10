<?php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Contract
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

    public function __construct(\DateTime $end_date)
    {
        $this->end_date = $end_date;
    }

    public function getEndDate(): \DateTime
    {
        return clone $this->end_date;
    }

    public function renew(\DateInterval $interval)
    {
        $this->end_date->add($interval);
    }
}
