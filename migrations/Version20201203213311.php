<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201203213311 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` ADD QuantityRemains INT NOT NULL, CHANGE Service_ID Service_ID INT DEFAULT NULL');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398CEBAE5C FOREIGN KEY (Service_ID) REFERENCES service (ID)');
        $this->addSql('CREATE INDEX IDX_F5299398CEBAE5C ON `order` (Service_ID)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398CEBAE5C');
        $this->addSql('DROP INDEX IDX_F5299398CEBAE5C ON `order`');
        $this->addSql('ALTER TABLE `order` DROP QuantityRemains, CHANGE Service_ID Service_ID INT NOT NULL');
    }
}
