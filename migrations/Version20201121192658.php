<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201121192658 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE service DROP FOREIGN KEY FK_E19D9AD23A30165F');
        $this->addSql('DROP INDEX IDX_E19D9AD23A30165F ON service');
        $this->addSql('ALTER TABLE service CHANGE Category_ID Category_ID INT NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE service CHANGE Category_ID Category_ID INT DEFAULT NULL');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD23A30165F FOREIGN KEY (Category_ID) REFERENCES category (ID)');
        $this->addSql('CREATE INDEX IDX_E19D9AD23A30165F ON service (Category_ID)');
    }
}
