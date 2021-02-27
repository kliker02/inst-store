<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CategoryRepository::class)
 */
class Category
{

    const STATUS_ACTIVE = 1;
    const STATUS_DISABLE = 0;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="ID")
     */
    private $ID;

    /**
     * @ORM\Column(type="integer", name="API_ID", nullable=true)
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
     * @ORM\Column(type="string", name="Name")
     */
    private $Name;

    /**
     * @ORM\Column(type="string", name="Description")
     */
    private $Description;

    /**
     * @ORM\Column(type="integer", name="Status", options={"default": "1"})
     */
    private $Status = self::STATUS_ACTIVE;

    /**
     * @ORM\Column(type="datetime", name="CreateDate", nullable=false, options={"default": "CURRENT_TIMESTAMP"})
     */
    private $CreateDate;


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
    public function getCreateDate()
    {
        return $this->CreateDate;
    }

    /**
     * @param mixed $CreateDate
     */
    public function setCreateDate($CreateDate): void
    {
        $this->CreateDate = $CreateDate;
    }

    /**
     * @return mixed
     */
    public function getID()
    {
        return $this->ID;
    }

    /**
     * @param mixed $ID
     */
    public function setID($ID): void
    {
        $this->ID = $ID;
    }

    public static function getStatusText($Status): string
    {
        switch ($Status) {
            case self::STATUS_ACTIVE: return 'Активна';
            case self::STATUS_DISABLE: return 'Неактивна';
        }
    }

    public static function getStatuses(): array
    {
        return array(
            self::STATUS_ACTIVE => self::getStatusText(self::STATUS_ACTIVE),
            self::STATUS_DISABLE => self::getStatusText(self::STATUS_DISABLE),
        );
    }

}
