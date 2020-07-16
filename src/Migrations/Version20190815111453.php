<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190815111453 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP INDEX IDX_BFC1968BA76ED395');
        $this->addSql('DROP INDEX IDX_BFC1968BF6D1A74B');
        $this->addSql('CREATE TEMPORARY TABLE __temp__controller_user AS SELECT controller_id, user_id FROM controller_user');
        $this->addSql('DROP TABLE controller_user');
        $this->addSql('CREATE TABLE controller_user (controller_id INTEGER NOT NULL, user_id INTEGER NOT NULL, PRIMARY KEY(controller_id, user_id), CONSTRAINT FK_BFC1968BF6D1A74B FOREIGN KEY (controller_id) REFERENCES controller (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BFC1968BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO controller_user (controller_id, user_id) SELECT controller_id, user_id FROM __temp__controller_user');
        $this->addSql('DROP TABLE __temp__controller_user');
        $this->addSql('CREATE INDEX IDX_BFC1968BA76ED395 ON controller_user (user_id)');
        $this->addSql('CREATE INDEX IDX_BFC1968BF6D1A74B ON controller_user (controller_id)');
        $this->addSql('DROP INDEX IDX_46DC8952DC90A29E');
        $this->addSql('CREATE TEMPORARY TABLE __temp__pcb AS SELECT id, luminaire_id, crc, serial, n, type FROM pcb');
        $this->addSql('DROP TABLE pcb');
        $this->addSql('CREATE TABLE pcb (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, luminaire_id INTEGER NOT NULL, crc VARCHAR(6) NOT NULL COLLATE BINARY, serial VARCHAR(10) NOT NULL COLLATE BINARY, n INTEGER NOT NULL, type INTEGER NOT NULL, CONSTRAINT FK_46DC8952DC90A29E FOREIGN KEY (luminaire_id) REFERENCES luminaire (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO pcb (id, luminaire_id, crc, serial, n, type) SELECT id, luminaire_id, crc, serial, n, type FROM __temp__pcb');
        $this->addSql('DROP TABLE __temp__pcb');
        $this->addSql('CREATE INDEX IDX_46DC8952DC90A29E ON pcb (luminaire_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__channel AS SELECT id, channel, i_peek FROM channel');
        $this->addSql('DROP TABLE channel');
        $this->addSql('CREATE TABLE channel (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, luminaire_id INTEGER NOT NULL, channel INTEGER NOT NULL, i_peek INTEGER NOT NULL, CONSTRAINT FK_A2F98E47DC90A29E FOREIGN KEY (luminaire_id) REFERENCES luminaire (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO channel (id, channel, i_peek) SELECT id, channel, i_peek FROM __temp__channel');
        $this->addSql('DROP TABLE __temp__channel');
        $this->addSql('CREATE INDEX IDX_A2F98E47DC90A29E ON channel (luminaire_id)');
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

        $this->addSql('DROP INDEX IDX_A2F98E47DC90A29E');
        $this->addSql('CREATE TEMPORARY TABLE __temp__channel AS SELECT id, channel, i_peek FROM channel');
        $this->addSql('DROP TABLE channel');
        $this->addSql('CREATE TABLE channel (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, channel INTEGER NOT NULL, i_peek INTEGER NOT NULL)');
        $this->addSql('INSERT INTO channel (id, channel, i_peek) SELECT id, channel, i_peek FROM __temp__channel');
        $this->addSql('DROP TABLE __temp__channel');
        $this->addSql('DROP INDEX IDX_BFC1968BF6D1A74B');
        $this->addSql('DROP INDEX IDX_BFC1968BA76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__controller_user AS SELECT controller_id, user_id FROM controller_user');
        $this->addSql('DROP TABLE controller_user');
        $this->addSql('CREATE TABLE controller_user (controller_id INTEGER NOT NULL, user_id INTEGER NOT NULL, PRIMARY KEY(controller_id, user_id))');
        $this->addSql('INSERT INTO controller_user (controller_id, user_id) SELECT controller_id, user_id FROM __temp__controller_user');
        $this->addSql('DROP TABLE __temp__controller_user');
        $this->addSql('CREATE INDEX IDX_BFC1968BF6D1A74B ON controller_user (controller_id)');
        $this->addSql('CREATE INDEX IDX_BFC1968BA76ED395 ON controller_user (user_id)');
        $this->addSql('DROP INDEX IDX_BF3BAD1BF6D1A74B');
        $this->addSql('CREATE TEMPORARY TABLE __temp__luminaire AS SELECT id, controller_id, address, serial, ligne, colonne FROM luminaire');
        $this->addSql('DROP TABLE luminaire');
        $this->addSql('CREATE TABLE luminaire (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, controller_id INTEGER DEFAULT NULL, address INTEGER NOT NULL, serial VARCHAR(255) DEFAULT NULL, ligne INTEGER DEFAULT NULL, colonne INTEGER DEFAULT NULL)');
        $this->addSql('INSERT INTO luminaire (id, controller_id, address, serial, ligne, colonne) SELECT id, controller_id, address, serial, ligne, colonne FROM __temp__luminaire');
        $this->addSql('DROP TABLE __temp__luminaire');
        $this->addSql('CREATE INDEX IDX_BF3BAD1BF6D1A74B ON luminaire (controller_id)');
        $this->addSql('DROP INDEX IDX_46DC8952DC90A29E');
        $this->addSql('CREATE TEMPORARY TABLE __temp__pcb AS SELECT id, luminaire_id, crc, serial, n, type FROM pcb');
        $this->addSql('DROP TABLE pcb');
        $this->addSql('CREATE TABLE pcb (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, luminaire_id INTEGER NOT NULL, crc VARCHAR(6) NOT NULL, serial VARCHAR(10) NOT NULL, n INTEGER NOT NULL, type INTEGER NOT NULL)');
        $this->addSql('INSERT INTO pcb (id, luminaire_id, crc, serial, n, type) SELECT id, luminaire_id, crc, serial, n, type FROM __temp__pcb');
        $this->addSql('DROP TABLE __temp__pcb');
        $this->addSql('CREATE INDEX IDX_46DC8952DC90A29E ON pcb (luminaire_id)');
    }
}
