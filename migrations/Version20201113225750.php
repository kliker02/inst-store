<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201113225750 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE service (ID INT AUTO_INCREMENT NOT NULL, Name LONGBLOB NOT NULL, Description LONGBLOB NOT NULL, Category_ID INT UNSIGNED NOT NULL, API LONGTEXT NOT NULL, OrderAPI LONGTEXT NOT NULL, Type SMALLINT UNSIGNED NOT NULL, Price NUMERIC(8, 2) NOT NULL, MinQuantity INT UNSIGNED NOT NULL, MaxQuantity INT UNSIGNED NOT NULL, ResellerPrice INT UNSIGNED NOT NULL, Active INT UNSIGNED NOT NULL, CreateDate DATETIME NOT NULL, PRIMARY KEY(ID)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE service');
    }
}
