<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190816141844 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TABLE luminaire_group (luminaire_id INTEGER NOT NULL, group_id INTEGER NOT NULL, PRIMARY KEY(luminaire_id, group_id))');
        $this->addSql('CREATE INDEX IDX_EF76C7ABDC90A29E ON luminaire_group (luminaire_id)');
        $this->addSql('CREATE INDEX IDX_EF76C7ABFE54D947 ON luminaire_group (group_id)');
        $this->addSql('CREATE TABLE controller_group (controller_id INTEGER NOT NULL, group_id INTEGER NOT NULL, PRIMARY KEY(controller_id, group_id))');
        $this->addSql('CREATE INDEX IDX_1898B519F6D1A74B ON controller_group (controller_id)');
        $this->addSql('CREATE INDEX IDX_1898B519FE54D947 ON controller_group (group_id)');
        $this->addSql('DROP TABLE controller_user');
        $this->addSql('DROP INDEX IDX_BF3BAD1BF6D1A74B');
        $this->addSql('CREATE TEMPORARY TABLE __temp__luminaire AS SELECT id, controller_id, address, serial, ligne, colonne FROM luminaire');
        $this->addSql('DROP TABLE luminaire');
        $this->addSql('CREATE TABLE luminaire (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, controller_id INTEGER DEFAULT NULL, address INTEGER NOT NULL, serial VARCHAR(255) DEFAULT NULL COLLATE BINARY, ligne INTEGER DEFAULT NULL, colonne INTEGER DEFAULT NULL, CONSTRAINT FK_BF3BAD1BF6D1A74B FOREIGN KEY (controller_id) REFERENCES controller (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO luminaire (id, controller_id, address, serial, ligne, colonne) SELECT id, controller_id, address, serial, ligne, colonne FROM __temp__luminaire');
        $this->addSql('DROP TABLE __temp__luminaire');
        $this->addSql('CREATE INDEX IDX_BF3BAD1BF6D1A74B ON luminaire (controller_id)');
        $this->addSql('DROP INDEX IDX_A4C98D39A76ED395');
        $this->addSql('DROP INDEX IDX_A4C98D39FE54D947');
        $this->addSql('CREATE TEMPORARY TABLE __temp__group_user AS SELECT group_id, user_id FROM group_user');
        $this->addSql('DROP TABLE group_user');
        $this->addSql('CREATE TABLE group_user (group_id INTEGER NOT NULL, user_id INTEGER NOT NULL, PRIMARY KEY(group_id, user_id), CONSTRAINT FK_A4C98D39FE54D947 FOREIGN KEY (group_id) REFERENCES "group" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_A4C98D39A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO group_user (group_id, user_id) SELECT group_id, user_id FROM __temp__group_user');
        $this->addSql('DROP TABLE __temp__group_user');
        $this->addSql('CREATE INDEX IDX_A4C98D39A76ED395 ON group_user (user_id)');
        $this->addSql('CREATE INDEX IDX_A4C98D39FE54D947 ON group_user (group_id)');
        $this->addSql('DROP INDEX IDX_46DC8952DC90A29E');
        $this->addSql('CREATE TEMPORARY TABLE __temp__pcb AS SELECT id, luminaire_id, crc, serial, n, type FROM pcb');
        $this->addSql('DROP TABLE pcb');
        $this->addSql('CREATE TABLE pcb (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, luminaire_id INTEGER NOT NULL, crc VARCHAR(6) NOT NULL COLLATE BINARY, serial VARCHAR(10) NOT NULL COLLATE BINARY, n INTEGER NOT NULL, type INTEGER NOT NULL, CONSTRAINT FK_46DC8952DC90A29E FOREIGN KEY (luminaire_id) REFERENCES luminaire (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO pcb (id, luminaire_id, crc, serial, n, type) SELECT id, luminaire_id, crc, serial, n, type FROM __temp__pcb');
        $this->addSql('DROP TABLE __temp__pcb');
        $this->addSql('CREATE INDEX IDX_46DC8952DC90A29E ON pcb (luminaire_id)');
        $this->addSql('DROP INDEX IDX_A2F98E47B262EAC9');
        $this->addSql('DROP INDEX IDX_A2F98E47DC90A29E');
        $this->addSql('CREATE TEMPORARY TABLE __temp__channel AS SELECT id, luminaire_id, led_id, channel, i_peek FROM channel');
        $this->addSql('DROP TABLE channel');
        $this->addSql('CREATE TABLE channel (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, luminaire_id INTEGER NOT NULL, led_id INTEGER NOT NULL, channel INTEGER NOT NULL, i_peek INTEGER NOT NULL, CONSTRAINT FK_A2F98E47DC90A29E FOREIGN KEY (luminaire_id) REFERENCES luminaire (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_A2F98E47B262EAC9 FOREIGN KEY (led_id) REFERENCES led (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO channel (id, luminaire_id, led_id, channel, i_peek) SELECT id, luminaire_id, led_id, channel, i_peek FROM __temp__channel');
        $this->addSql('DROP TABLE __temp__channel');
        $this->addSql('CREATE INDEX IDX_A2F98E47B262EAC9 ON channel (led_id)');
        $this->addSql('CREATE INDEX IDX_A2F98E47DC90A29E ON channel (luminaire_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TABLE controller_user (controller_id INTEGER NOT NULL, user_id INTEGER NOT NULL, PRIMARY KEY(controller_id, user_id))');
        $this->addSql('CREATE INDEX IDX_BFC1968BA76ED395 ON controller_user (user_id)');
        $this->addSql('CREATE INDEX IDX_BFC1968BF6D1A74B ON controller_user (controller_id)');
        $this->addSql('DROP TABLE luminaire_group');
        $this->addSql('DROP TABLE controller_group');
        $this->addSql('DROP INDEX IDX_A2F98E47DC90A29E');
        $this->addSql('DROP INDEX IDX_A2F98E47B262EAC9');
        $this->addSql('CREATE TEMPORARY TABLE __temp__channel AS SELECT id, luminaire_id, led_id, channel, i_peek FROM channel');
        $this->addSql('DROP TABLE channel');
        $this->addSql('CREATE TABLE channel (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, luminaire_id INTEGER NOT NULL, led_id INTEGER NOT NULL, channel INTEGER NOT NULL, i_peek INTEGER NOT NULL)');
        $this->addSql('INSERT INTO channel (id, luminaire_id, led_id, channel, i_peek) SELECT id, luminaire_id, led_id, channel, i_peek FROM __temp__channel');
        $this->addSql('DROP TABLE __temp__channel');
        $this->addSql('CREATE INDEX IDX_A2F98E47DC90A29E ON channel (luminaire_id)');
        $this->addSql('CREATE INDEX IDX_A2F98E47B262EAC9 ON channel (led_id)');
        $this->addSql('DROP INDEX IDX_A4C98D39FE54D947');
        $this->addSql('DROP INDEX IDX_A4C98D39A76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__group_user AS SELECT group_id, user_id FROM group_user');
        $this->addSql('DROP TABLE group_user');
        $this->addSql('CREATE TABLE group_user (group_id INTEGER NOT NULL, user_id INTEGER NOT NULL, PRIMARY KEY(group_id, user_id))');
        $this->addSql('INSERT INTO group_user (group_id, user_id) SELECT group_id, user_id FROM __temp__group_user');
        $this->addSql('DROP TABLE __temp__group_user');
        $this->addSql('CREATE INDEX IDX_A4C98D39FE54D947 ON group_user (group_id)');
        $this->addSql('CREATE INDEX IDX_A4C98D39A76ED395 ON group_user (user_id)');
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
