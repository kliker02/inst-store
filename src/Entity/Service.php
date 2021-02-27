<?php

namespace App\Entity;

use App\Repository\ServiceRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Category as Category;

/**
 * @ORM\Entity(repositoryClass=ServiceRepository::class)
 */
class Service
{

    CONST STATUS_ACTIVE = 1;
    CONST STATUS_DISABLE = 0;

    const BROKER_INSTAGRAM777 = 1;


    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="ID")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", name="API_ID")
     */
    private $API_ID = 0;

    /**
     * @return int
     */
    public function getAPIID(): int
    {
        return $this->API_ID;
    }

    /**
     * @param int $API_ID
     */
    public function setAPIID(int $API_ID): void
    {
        $this->API_ID = $API_ID;
    }

    /**
     * @ORM\Column(type="blob", name="Name")
     */
    private $Name;

    /**
     * @ORM\Column(type="blob", name="Description")
     */
    private $Description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category")
     * @ORM\JoinColumn(name="Category_ID", referencedColumnName="ID")
     */
    private $Category;

    /**
     * @ORM\Column(type="text", name="API")
     */
    private $API;

    /**
     * @ORM\Column(type="text", name="OrderAPI")
     */
    private $OrderAPI;

    /**
     * @ORM\Column(type="smallint", name="Type", options={"unsigned"=true})
     */
    private $Type = 0;

    /**
     * @ORM\Column(type="decimal", name="Price", precision=8, scale=2)
     */
    private $Price;

    /**
     * @ORM\Column(type="integer", name="MinQuantity", options={"unsigned"=true})
     */
    private $MinQuantity;

    /**
     * @ORM\Column(type="integer", name="MaxQuantity", options={"unsigned"=true})
     */
    private $MaxQuantity;

    /**
     * @ORM\Column(type="decimal", name="ResellerPrice", options={"unsigned"=true})
     */
    private $ResellerPrice;

    /**
     * @ORM\Column(type="integer", name="Status", options={"unsigned"=true})
     */
    private $Status;

    /**
     * @ORM\Column(type="datetime", name="CreatedDate", nullable=false)
     */
    private $CreatedDate;

    /**
     * @ORM\Column(type="integer", name="Broker", nullable=false)
     */
    private $Broker = 0;

    /**
     * @return int
     */
    public function getBroker(): int
    {
        return $this->Broker;
    }

    /**
     * @param int $Broker
     */
    public function setBroker(int $Broker): void
    {
        $this->Broker = $Broker;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->Name;
    }

    /**
     * @param mixed $Name
     */
    public function setName($Name): void
    {
        $this->Name = $Name;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->Description;
    }

    /**
     * @param mixed $Description
     */
    public function setDescription($Description): void
    {
        $this->Description = $Description;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->Category;
    }

    /**
     * @param mixed $Category
     */
    public function setCategory($Category): void
    {
        $this->Category = $Category;
    }

    /**
     * @return mixed
     */
    public function getAPI()
    {
        return $this->API;
    }

    /**
     * @param mixed $API
     */
    public function setAPI($API): void
    {
        $this->API = $API;
    }

    /**
     * @return mixed
     */
    public function getOrderAPI()
    {
        return $this->OrderAPI;
    }

    /**
     * @param mixed $OrderAPI
     */
    public function setOrderAPI($OrderAPI): void
    {
        $this->OrderAPI = $OrderAPI;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->Type;
    }

    /**
     * @param mixed $Type
     */
    public function setType($Type): void
    {
        $this->Type = $Type;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->Price;
    }

    /**
     * @param mixed $Price
     */
    public function setPrice($Price): void
    {
        $this->Price = $Price;
    }

    /**
     * @return mixed
     */
    public function getMinQuantity()
    {
        return $this->MinQuantity;
    }

    /**
     * @param mixed $MinQuantity
     */
    public function setMinQuantity($MinQuantity): void
    {
        $this->MinQuantity = $MinQuantity;
    }

    /**
     * @return mixed
     */
    public function getMaxQuantity()
    {
        return $this->MaxQuantity;
    }

    /**
     * @param mixed $MaxQuantity
     */
    public function setMaxQuantity($MaxQuantity): void
    {
        $this->MaxQuantity = $MaxQuantity;
    }

    /**
     * @return mixed
     */
    public function getResellerPrice()
    {
        return $this->ResellerPrice;
    }

    /**
     * @param mixed $ResellerPrice
     */
    public function setResellerPrice($ResellerPrice): void
    {
        $this->ResellerPrice = $ResellerPrice;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->Status;
    }

    /**
     * @param int $Status
     */
    public function setStatus($Status): void
    {
        $this->Status = $Status;
    }

    /**
     * @param int $Status
     */
    public function getStatusText(int $Status, string $unknown = 'Неизвестно'): string
    {
        switch ($Status) {
            case self::STATUS_ACTIVE: return 'Активна';
            case self::STATUS_DISABLE: return 'Неактивна';
            default: return $unknown;
        }
    }

    /**
     * @return mixed
     */
    public function getСreatedDate()
    {
        return $this->CreatedDate;
    }

    /**
     * @param mixed $CreateDate
     */
    public function setСreatedDate($CreateDate): void
    {
        $this->CreatedDate = $CreateDate;
    }

    public function readName(){
        $Name = '';
        while(!feof($this->getName())){
            $Name.= fread($this->getName(), 1024);
        }
        rewind($this->getName());
        return $Name;
    }

    public function readDescription(){
        $Description = '';
        while(!feof($this->getDescription())){
            $Description.= fread($this->getDescription(), 1024);
        }
        rewind($this->getDescription());
        return $Description;
    }


    public static function getStatuses(): array
    {
        return array(
            self::STATUS_ACTIVE => self::getStatusText(self::STATUS_ACTIVE),
            self::STATUS_DISABLE => self::getStatusText(self::STATUS_DISABLE),
        );
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
