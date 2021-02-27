<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OrderRepository::class)
 * @ORM\Table(name="`order`")
 */
class Order
{

    const STATUS_BOOKED= 'Booked'; // 3абранировано - оплата ещё не пришла и заказ не отправлен брокеру
    const STATUS_PENDING    = 'Pending';
    const STATUS_PROCESSING = 'Processing';
    const STATUS_IN_PROGRESS = 'In progress';
    const STATUS_PARTIAL    = 'Partial';
    const STATUS_REFUNDED   = 'Refunded';
    const STATUS_CANCELED   = 'Canceled';
    const STATUS_COMPLETED  = 'Completed';
    const STATUS_DELETED    = 'Deleted';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @ORM\Column(type="integer", name="API_ID")
     */
    private $API_ID;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Service")
     * @ORM\JoinColumn(name="Service_ID", referencedColumnName="ID")
     */
    private $Service;

    /**
     * @return mixed
     */
    public function getService()
    {
        return $this->Service;
    }

    /**
     * @param mixed $Service
     */
    public function setService($Service): void
    {
        $this->Service = $Service;
    }

    /**
     * @ORM\Column(type="integer", name="User_ID")
     */
    private $User_ID;

    /**
     * @ORM\Column(type="string", length=255, name="UserName")
     */
    private $UserName;

    /**
     * @ORM\Column(type="integer", name="Quantity")
     */
    private $Quantity;

    /**
     * @ORM\Column(type="integer", name="QuantityRemains")
     */
    private $QuantityRemains = 0;

    /**
     * @return mixed
     */
    public function getQuantityRemains()
    {
        return $this->QuantityRemains;
    }

    /**
     * @param mixed $QuantityRemains
     */
    public function setQuantityRemains($QuantityRemains): void
    {
        $this->QuantityRemains = $QuantityRemains;
    }


    /**
     * @ORM\Column(type="string", length=255, name="Link")
     */
    private $Link;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, name="Total")
     */
    private $Total;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $Status;

    /**
     * @ORM\Column(type="integer", name="StartCount")
     */
    private $StartCount = 0;


    /**
     * @ORM\Column(type="integer", name="Type")
     */
    private $Type = 0;

    /**
     * @ORM\Column(type="integer", name="Additional")
     */
    private $Additional = 0;

    /**
     * @ORM\Column(type="datetime", name="CreatedDate")
     */
    private $CreatedDate;


    public function __construct()
    {
        $this->CreatedDate = new \DateTime(date('Y-m-d H:i:s'));
    }

    /**
     * @return mixed
     */
    public function getAPIID()
    {
        return $this->API_ID;
    }

    /**
     * @param mixed $API_ID
     */
    public function setAPIID($API_ID): void
    {
        $this->API_ID = $API_ID;
    }

    /**
     * @return mixed
     */
    public function getUserID()
    {
        return $this->User_ID;
    }

    /**
     * @param mixed $User_ID
     */
    public function setUserID($User_ID): void
    {
        $this->User_ID = $User_ID;
    }

    /**
     * @return mixed
     */
    public function getUserName()
    {
        return $this->UserName;
    }

    /**
     * @param mixed $UserName
     */
    public function setUserName($UserName): void
    {
        $this->UserName = $UserName;
    }

    /**
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->Quantity;
    }

    /**
     * @param mixed $Quantity
     */
    public function setQuantity($Quantity): void
    {
        $this->Quantity = $Quantity;
    }

    /**
     * @return mixed
     */
    public function getLink()
    {
        return $this->Link;
    }

    /**
     * @param mixed $Link
     */
    public function setLink($Link): void
    {
        $this->Link = $Link;
    }

    /**
     * @return mixed
     */
    public function getTotal()
    {
        return $this->Total;
    }

    /**
     * @param mixed $Total
     */
    public function setTotal($Total): void
    {
        $this->Total = $Total;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->Status;
    }

    /**
     * @param mixed $Status
     */
    public function setStatus($Status): void
    {
        $this->Status = $Status;
    }

    /**
     * @return mixed
     */
    public function getStartCount()
    {
        return $this->StartCount;
    }

    /**
     * @param mixed $StartCount
     */
    public function setStartCount($StartCount): void
    {
        $this->StartCount = $StartCount;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->Type;
    }

    /**
     * @param int $Type
     */
    public function setType(int $Type): void
    {
        $this->Type = $Type;
    }

    /**
     * @return int
     */
    public function getAdditional(): int
    {
        return $this->Additional;
    }

    /**
     * @param int $Additional
     */
    public function setAdditional(int $Additional): void
    {
        $this->Additional = $Additional;
    }

    /**
     * @return mixed
     */
    public function getCreatedDate()
    {
        return $this->CreatedDate;
    }

    /**
     * @param mixed $CreatedDate
     */
    public function setCreatedDate($CreatedDate): void
    {
        $this->CreatedDate = $CreatedDate;
    }

}
