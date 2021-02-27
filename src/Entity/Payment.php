<?php

namespace App\Entity;

use App\Repository\PaymentRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PaymentRepository::class)
 */
class Payment
{

    const TYPE_DEBIT = 1; // оплата, положительный счёт
    const TYPE_CUSTOM = 2; // отрицательный счёт для клиента
    const TYPE_ORDER = 3; // отрицательный счёт для клиента - заказ услуги

    const STATUS_NEW = 1;
    const STATUS_PAID = 10;
    const STATUS_CANCELLED = 9;

    const RUB = 'RUB';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="ID")
     */
    private $ID;

    /**
     * @ORM\Column(type="string", length=255, name="ExternalGUID", nullable=true)
     */
    private $ExternalGUID;


    /**
     * @ORM\Column(type="string", length=255, name="Name")
     */
    private $Name;

    /**
     * @ORM\Column(type="integer", name="User_ID")
     */
    private $User_ID;

    /**
     * @ORM\Column(type="integer", name="Order_ID")
     */
    private $Order_ID;

    /**
     * @ORM\Column(type="string", length=255, name="UserName")
     */
    private $UserName;

    /**
     * @ORM\Column(type="smallint", name="Type")
     */

    private $Type;

    /**
     * @ORM\Column(type="smallint", name="Factor")
     */
    private $Factor;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, name="Total")
     */
    private $Total;

    /**
     * @ORM\Column(type="string", name="Currency")
     */
    private $Currency = self::RUB;

    /**
     * @ORM\Column(type="smallint", name="Status")
     */
    private $Status;

    /**
     * @ORM\Column(type="string", name="Notes", nullable=true)
     */
    private $Notes;

    /**
     * @ORM\Column(type="datetime", name="CreatedDate")
     */
    private $CreatedDate;

    /**
     * @ORM\Column(type="integer", name="CreatedBy")
     */
    private $CreatedBy;

    /**
     * @ORM\Column(type="datetime", name="ChangedDate")
     */
    private $ChangedDate;

    /**
     * @return mixed
     */
    public function getChangedDate()
    {
        return $this->ChangedDate;
    }

    /**
     * @param mixed $ChangedDate
     */
    public function setChangedDate($ChangedDate): void
    {
        $this->ChangedDate = $ChangedDate;
    }

    /**
     * @return mixed
     */
    public function getChangedBy()
    {
        return $this->ChangedBy;
    }

    /**
     * @param mixed $ChangedBy
     */
    public function setChangedBy($ChangedBy): void
    {
        $this->ChangedBy = $ChangedBy;
    }

    /**
     * @ORM\Column(type="integer", name="ChangedBy")
     */
    private $ChangedBy;

    public function getId(): ?int
    {
        return $this->ID;
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
    public function setName(string $Name): void
    {
        $this->Name = $Name;
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
    public function setUserID(int $User_ID): void
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
    public function setUserName(string $UserName): void
    {
        $this->UserName = $UserName;
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
    public function setType(int $Type): void
    {
        $this->Type = $Type;
    }

    /**
     * @return mixed
     */
    public function getFactor()
    {
        return $this->Factor;
    }

    /**
     * @param mixed $Factor
     */
    public function setFactor(int $Factor): void
    {
        $this->Factor = $Factor;
    }

    /**
     * @return mixed
     */
    public function getCurrency()
    {
        return $this->Currency;
    }

    /**
     * @param mixed $Currency
     */
    public function setCurrency(string $Currency): void
    {
        $this->Currency = $Currency;
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
    public function setStatus(int $Status): void
    {
        $this->Status = $Status;
    }

    /**
     * @return mixed
     */
    public function getNotes()
    {
        return $this->Notes;
    }

    /**
     * @param mixed $Notes
     */
    public function setNotes($Notes): void
    {
        $this->Notes = $Notes;
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

    /**
     * @return mixed
     */
    public function getCreatedBy()
    {
        return $this->CreatedBy;
    }

    /**
     * @param mixed $CreatedBy
     */
    public function setCreatedBy($CreatedBy): void
    {
        $this->CreatedBy = $CreatedBy;
    }


    public static function getTypes(): array
    {
        return [
            self::TYPE_ORDER => self::getTypeText(self::TYPE_ORDER),
            self::TYPE_CUSTOM => self::getTypeText(self::TYPE_CUSTOM),
            self::TYPE_DEBIT => self::getTypeText(self::TYPE_DEBIT)
        ];
    }

    public static function getTypeText(int $Type): string
    {
        switch ($Type) {
            case self::TYPE_DEBIT:
                return 'Оплата';
            case self::TYPE_CUSTOM:
                return 'Клиентский счёт';
            case self::TYPE_ORDER:
                return 'Заказ услуги';
        }
    }

    public static function getFactorByType(int $Type): string
    {
        switch ($Type) {
            case self::TYPE_DEBIT:
                return 1;
            case self::TYPE_CUSTOM:
                return -1;
            case self::TYPE_ORDER:
                return -1;
        }
    }

    public static function getStatusText($Status): string
    {
        switch ($Status) {
            case self::STATUS_NEW:
                return "Новый";
            case self::STATUS_CANCELLED:
                return "Отменён";
            case self::STATUS_PAID:
                return "Оплачен";
        }
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_NEW => self::getStatusText(self::STATUS_NEW),
            self::STATUS_PAID => self::getStatusText(self::STATUS_PAID),
            self::STATUS_CANCELLED => self::getStatusText(self::STATUS_CANCELLED),
        ];
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
    public function getExternalGUID()
    {
        return $this->ExternalGUID;
    }

    /**
     * @param mixed $ExternalGUID
     */
    public function setExternalGUID($ExternalGUID): void
    {
        $this->ExternalGUID = $ExternalGUID;
    }

    /**
     * @return mixed
     */
    public function getOrderID()
    {
        return $this->Order_ID;
    }

    /**
     * @param mixed $Order_ID
     */
    public function setOrderID($Order_ID): void
    {
        $this->Order_ID = $Order_ID;
    }


}
