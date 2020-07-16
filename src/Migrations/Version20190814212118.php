<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190814212118 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TABLE controller (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, url VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE TABLE luminaire (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, controller_id INTEGER DEFAULT NULL, address INTEGER NOT NULL, serial VARCHAR(255) DEFAULT NULL, ligne INTEGER DEFAULT NULL, colonne INTEGER DEFAULT NULL)');
        $this->addSql('CREATE INDEX IDX_BF3BAD1BF6D1A74B ON luminaire (controller_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP TABLE controller');
        $this->addSql('DROP TABLE luminaire');
    }
}
