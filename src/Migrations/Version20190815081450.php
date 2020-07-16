<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190815081450 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TABLE controller_user (controller_id INTEGER NOT NULL, user_id INTEGER NOT NULL, PRIMARY KEY(controller_id, user_id))');
        $this->addSql('CREATE INDEX IDX_BFC1968BF6D1A74B ON controller_user (controller_id)');
        $this->addSql('CREATE INDEX IDX_BFC1968BA76ED395 ON controller_user (user_id)');
        $this->addSql('DROP INDEX IDX_BF3BAD1BF6D1A74B');
        $this->addSql('CREATE TEMPORARY TABLE __temp__luminaire AS SELECT id, controller_id, address, serial, ligne, colonne FROM luminaire');
        $this->addSql('DROP TABLE luminaire');
        $this->addSql('CREATE TABLE luminaire (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, controller_id INTEGER DEFAULT NULL, address INTEGER NOT NULL, serial VARCHAR(255) DEFAULT NULL COLLATE BINARY, ligne INTEGER DEFAULT NULL, colonne INTEGER DEFAULT NULL, CONSTRAINT FK_BF3BAD1BF6D1A74B FOREIGN KEY (controller_id) REFERENCES controller (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO luminaire (id, controller_id, address, serial, ligne, colonne) SELECT id, controller_id, address, serial, ligne, colonne FROM __temp__luminaire');
        $this->addSql('DROP TABLE __temp__luminaire');
        $this->addSql('CREATE INDEX IDX_BF3BAD1BF6D1A74B ON luminaire (controller_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP TABLE controller_user');
        $this->addSql('DROP INDEX IDX_BF3BAD1BF6D1A74B');
        $this->addSql('CREATE TEMPORARY TABLE __temp__luminaire AS SELECT id, controller_id, address, serial, ligne, colonne FROM luminaire');
        $this->addSql('DROP TABLE luminaire');
        $this->addSql('CREATE TABLE luminaire (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, controller_id INTEGER DEFAULT NULL, address INTEGER NOT NULL, serial VARCHAR(255) DEFAULT NULL, ligne INTEGER DEFAULT NULL, colonne INTEGER DEFAULT NULL)');
        $this->addSql('INSERT INTO luminaire (id, controller_id, address, serial, ligne, colonne) SELECT id, controller_id, address, serial, ligne, colonne FROM __temp__luminaire');
        $this->addSql('DROP TABLE __temp__luminaire');
        $this->addSql('CREATE INDEX IDX_BF3BAD1BF6D1A74B ON luminaire (controller_id)');
    }
}
