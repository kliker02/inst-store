<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201124203243 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE payment (ID INT AUTO_INCREMENT NOT NULL, Name VARCHAR(255) NOT NULL, GUID VARCHAR(6) NOT NULL, User_ID INT NOT NULL, UserName VARCHAR(6) NOT NULL, Type SMALLINT NOT NULL, Factor SMALLINT NOT NULL, Currency VARCHAR(255) NOT NULL, Status SMALLINT NOT NULL, Notes VARCHAR(255) NOT NULL, CreatedDate DATETIME NOT NULL, CreatedBy INT NOT NULL, PRIMARY KEY(ID)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE payment');
    }
}
