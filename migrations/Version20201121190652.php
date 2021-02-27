<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201121190652 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE service CHANGE Category_ID Category_ID INT DEFAULT NULL, CHANGE ResellerPrice ResellerPrice NUMERIC(10, 0) UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD23A30165F FOREIGN KEY (Category_ID) REFERENCES category (ID)');
        $this->addSql('CREATE INDEX IDX_E19D9AD23A30165F ON service (Category_ID)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE service DROP FOREIGN KEY FK_E19D9AD23A30165F');
        $this->addSql('DROP INDEX IDX_E19D9AD23A30165F ON service');
        $this->addSql('ALTER TABLE service CHANGE ResellerPrice ResellerPrice INT UNSIGNED NOT NULL, CHANGE Category_ID Category_ID INT UNSIGNED NOT NULL');
    }
}
