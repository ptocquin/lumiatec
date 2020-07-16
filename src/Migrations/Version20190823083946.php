<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190823083946 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP INDEX IDX_E5C56994F6D1A74B');
        $this->addSql('CREATE TEMPORARY TABLE __temp__cluster AS SELECT id, controller_id, label FROM cluster');
        $this->addSql('DROP TABLE cluster');
        $this->addSql('CREATE TABLE cluster (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, controller_id INTEGER DEFAULT NULL, label INTEGER NOT NULL, CONSTRAINT FK_E5C56994F6D1A74B FOREIGN KEY (controller_id) REFERENCES controller (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO cluster (id, controller_id, label) SELECT id, controller_id, label FROM __temp__cluster');
        $this->addSql('DROP TABLE __temp__cluster');
        $this->addSql('CREATE INDEX IDX_E5C56994F6D1A74B ON cluster (controller_id)');
        $this->addSql('DROP INDEX IDX_BFC1968BA76ED395');
        $this->addSql('DROP INDEX IDX_BFC1968BF6D1A74B');
        $this->addSql('CREATE TEMPORARY TABLE __temp__controller_user AS SELECT controller_id, user_id FROM controller_user');
        $this->addSql('DROP TABLE controller_user');
        $this->addSql('CREATE TABLE controller_user (controller_id INTEGER NOT NULL, user_id INTEGER NOT NULL, PRIMARY KEY(controller_id, user_id), CONSTRAINT FK_BFC1968BF6D1A74B FOREIGN KEY (controller_id) REFERENCES controller (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BFC1968BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO controller_user (controller_id, user_id) SELECT controller_id, user_id FROM __temp__controller_user');
        $this->addSql('DROP TABLE __temp__controller_user');
        $this->addSql('CREATE INDEX IDX_BFC1968BA76ED395 ON controller_user (user_id)');
        $this->addSql('CREATE INDEX IDX_BFC1968BF6D1A74B ON controller_user (controller_id)');
        $this->addSql('DROP INDEX IDX_43B9FE3C3EB8070A');
        $this->addSql('DROP INDEX IDX_43B9FE3C59D8A214');
        $this->addSql('CREATE TEMPORARY TABLE __temp__step AS SELECT id, program_id, recipe_id, type, rank, value FROM step');
        $this->addSql('DROP TABLE step');
        $this->addSql('CREATE TABLE step (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, program_id INTEGER NOT NULL, recipe_id INTEGER DEFAULT NULL, type VARCHAR(255) NOT NULL COLLATE BINARY, rank INTEGER NOT NULL, value VARCHAR(255) NOT NULL COLLATE BINARY, CONSTRAINT FK_43B9FE3C3EB8070A FOREIGN KEY (program_id) REFERENCES program (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_43B9FE3C59D8A214 FOREIGN KEY (recipe_id) REFERENCES recipe (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO step (id, program_id, recipe_id, type, rank, value) SELECT id, program_id, recipe_id, type, rank, value FROM __temp__step');
        $this->addSql('DROP TABLE __temp__step');
        $this->addSql('CREATE INDEX IDX_43B9FE3C3EB8070A ON step (program_id)');
        $this->addSql('CREATE INDEX IDX_43B9FE3C59D8A214 ON step (recipe_id)');
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
        $this->addSql('DROP INDEX IDX_6BAF7870B262EAC9');
        $this->addSql('DROP INDEX IDX_6BAF787059D8A214');
        $this->addSql('CREATE TEMPORARY TABLE __temp__ingredient AS SELECT id, led_id, recipe_id, level FROM ingredient');
        $this->addSql('DROP TABLE ingredient');
        $this->addSql('CREATE TABLE ingredient (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, led_id INTEGER DEFAULT NULL, recipe_id INTEGER DEFAULT NULL, level INTEGER DEFAULT NULL, CONSTRAINT FK_6BAF7870B262EAC9 FOREIGN KEY (led_id) REFERENCES led (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_6BAF787059D8A214 FOREIGN KEY (recipe_id) REFERENCES recipe (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO ingredient (id, led_id, recipe_id, level) SELECT id, led_id, recipe_id, level FROM __temp__ingredient');
        $this->addSql('DROP TABLE __temp__ingredient');
        $this->addSql('CREATE INDEX IDX_6BAF7870B262EAC9 ON ingredient (led_id)');
        $this->addSql('CREATE INDEX IDX_6BAF787059D8A214 ON ingredient (recipe_id)');
        $this->addSql('DROP INDEX IDX_8F3F68C5DC90A29E');
        $this->addSql('DROP INDEX IDX_8F3F68C5C36A3328');
        $this->addSql('CREATE TEMPORARY TABLE __temp__log AS SELECT id, cluster_id, luminaire_id, type, value, comment, time FROM log');
        $this->addSql('DROP TABLE log');
        $this->addSql('CREATE TABLE log (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, cluster_id INTEGER DEFAULT NULL, luminaire_id INTEGER DEFAULT NULL, type VARCHAR(255) NOT NULL COLLATE BINARY, value CLOB NOT NULL COLLATE BINARY --(DC2Type:json_array)
        , comment VARCHAR(255) DEFAULT NULL COLLATE BINARY, time DATETIME NOT NULL, remote_id INTEGER NOT NULL, CONSTRAINT FK_8F3F68C5C36A3328 FOREIGN KEY (cluster_id) REFERENCES cluster (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_8F3F68C5DC90A29E FOREIGN KEY (luminaire_id) REFERENCES luminaire (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO log (id, cluster_id, luminaire_id, type, value, comment, time) SELECT id, cluster_id, luminaire_id, type, value, comment, time FROM __temp__log');
        $this->addSql('DROP TABLE __temp__log');
        $this->addSql('CREATE INDEX IDX_8F3F68C5DC90A29E ON log (luminaire_id)');
        $this->addSql('CREATE INDEX IDX_8F3F68C5C36A3328 ON log (cluster_id)');
        $this->addSql('DROP INDEX IDX_92ED7784A76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__program AS SELECT id, user_id, label, description FROM program');
        $this->addSql('DROP TABLE program');
        $this->addSql('CREATE TABLE program (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER DEFAULT NULL, label VARCHAR(255) NOT NULL COLLATE BINARY, description CLOB DEFAULT NULL COLLATE BINARY, CONSTRAINT FK_92ED7784A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO program (id, user_id, label, description) SELECT id, user_id, label, description FROM __temp__program');
        $this->addSql('DROP TABLE __temp__program');
        $this->addSql('CREATE INDEX IDX_92ED7784A76ED395 ON program (user_id)');
        $this->addSql('DROP INDEX IDX_DA88B137A76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__recipe AS SELECT id, user_id, label, description FROM recipe');
        $this->addSql('DROP TABLE recipe');
        $this->addSql('CREATE TABLE recipe (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER DEFAULT NULL, label VARCHAR(255) NOT NULL COLLATE BINARY, description CLOB DEFAULT NULL COLLATE BINARY, CONSTRAINT FK_DA88B137A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO recipe (id, user_id, label, description) SELECT id, user_id, label, description FROM __temp__recipe');
        $this->addSql('DROP TABLE __temp__recipe');
        $this->addSql('CREATE INDEX IDX_DA88B137A76ED395 ON recipe (user_id)');
        $this->addSql('DROP INDEX IDX_BF3BAD1BF6D1A74B');
        $this->addSql('DROP INDEX IDX_BF3BAD1BC36A3328');
        $this->addSql('CREATE TEMPORARY TABLE __temp__luminaire AS SELECT id, controller_id, cluster_id, address, serial, ligne, colonne FROM luminaire');
        $this->addSql('DROP TABLE luminaire');
        $this->addSql('CREATE TABLE luminaire (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, controller_id INTEGER DEFAULT NULL, cluster_id INTEGER DEFAULT NULL, address INTEGER NOT NULL, serial VARCHAR(255) DEFAULT NULL COLLATE BINARY, ligne INTEGER DEFAULT NULL, colonne INTEGER DEFAULT NULL, CONSTRAINT FK_BF3BAD1BF6D1A74B FOREIGN KEY (controller_id) REFERENCES controller (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BF3BAD1BC36A3328 FOREIGN KEY (cluster_id) REFERENCES cluster (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO luminaire (id, controller_id, cluster_id, address, serial, ligne, colonne) SELECT id, controller_id, cluster_id, address, serial, ligne, colonne FROM __temp__luminaire');
        $this->addSql('DROP TABLE __temp__luminaire');
        $this->addSql('CREATE INDEX IDX_BF3BAD1BF6D1A74B ON luminaire (controller_id)');
        $this->addSql('CREATE INDEX IDX_BF3BAD1BC36A3328 ON luminaire (cluster_id)');
        $this->addSql('DROP INDEX IDX_33E4791FA76ED395');
        $this->addSql('DROP INDEX IDX_33E4791FDC90A29E');
        $this->addSql('CREATE TEMPORARY TABLE __temp__luminaire_user AS SELECT luminaire_id, user_id FROM luminaire_user');
        $this->addSql('DROP TABLE luminaire_user');
        $this->addSql('CREATE TABLE luminaire_user (luminaire_id INTEGER NOT NULL, user_id INTEGER NOT NULL, PRIMARY KEY(luminaire_id, user_id), CONSTRAINT FK_33E4791FDC90A29E FOREIGN KEY (luminaire_id) REFERENCES luminaire (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_33E4791FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO luminaire_user (luminaire_id, user_id) SELECT luminaire_id, user_id FROM __temp__luminaire_user');
        $this->addSql('DROP TABLE __temp__luminaire_user');
        $this->addSql('CREATE INDEX IDX_33E4791FA76ED395 ON luminaire_user (user_id)');
        $this->addSql('CREATE INDEX IDX_33E4791FDC90A29E ON luminaire_user (luminaire_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP INDEX IDX_A2F98E47DC90A29E');
        $this->addSql('DROP INDEX IDX_A2F98E47B262EAC9');
        $this->addSql('CREATE TEMPORARY TABLE __temp__channel AS SELECT id, luminaire_id, led_id, channel, i_peek FROM channel');
        $this->addSql('DROP TABLE channel');
        $this->addSql('CREATE TABLE channel (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, luminaire_id INTEGER NOT NULL, led_id INTEGER NOT NULL, channel INTEGER NOT NULL, i_peek INTEGER NOT NULL)');
        $this->addSql('INSERT INTO channel (id, luminaire_id, led_id, channel, i_peek) SELECT id, luminaire_id, led_id, channel, i_peek FROM __temp__channel');
        $this->addSql('DROP TABLE __temp__channel');
        $this->addSql('CREATE INDEX IDX_A2F98E47DC90A29E ON channel (luminaire_id)');
        $this->addSql('CREATE INDEX IDX_A2F98E47B262EAC9 ON channel (led_id)');
        $this->addSql('DROP INDEX IDX_E5C56994F6D1A74B');
        $this->addSql('CREATE TEMPORARY TABLE __temp__cluster AS SELECT id, controller_id, label FROM cluster');
        $this->addSql('DROP TABLE cluster');
        $this->addSql('CREATE TABLE cluster (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, controller_id INTEGER DEFAULT NULL, label INTEGER NOT NULL)');
        $this->addSql('INSERT INTO cluster (id, controller_id, label) SELECT id, controller_id, label FROM __temp__cluster');
        $this->addSql('DROP TABLE __temp__cluster');
        $this->addSql('CREATE INDEX IDX_E5C56994F6D1A74B ON cluster (controller_id)');
        $this->addSql('DROP INDEX IDX_BFC1968BF6D1A74B');
        $this->addSql('DROP INDEX IDX_BFC1968BA76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__controller_user AS SELECT controller_id, user_id FROM controller_user');
        $this->addSql('DROP TABLE controller_user');
        $this->addSql('CREATE TABLE controller_user (controller_id INTEGER NOT NULL, user_id INTEGER NOT NULL, PRIMARY KEY(controller_id, user_id))');
        $this->addSql('INSERT INTO controller_user (controller_id, user_id) SELECT controller_id, user_id FROM __temp__controller_user');
        $this->addSql('DROP TABLE __temp__controller_user');
        $this->addSql('CREATE INDEX IDX_BFC1968BF6D1A74B ON controller_user (controller_id)');
        $this->addSql('CREATE INDEX IDX_BFC1968BA76ED395 ON controller_user (user_id)');
        $this->addSql('DROP INDEX IDX_6BAF7870B262EAC9');
        $this->addSql('DROP INDEX IDX_6BAF787059D8A214');
        $this->addSql('CREATE TEMPORARY TABLE __temp__ingredient AS SELECT id, led_id, recipe_id, level FROM ingredient');
        $this->addSql('DROP TABLE ingredient');
        $this->addSql('CREATE TABLE ingredient (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, led_id INTEGER DEFAULT NULL, recipe_id INTEGER DEFAULT NULL, level INTEGER DEFAULT NULL)');
        $this->addSql('INSERT INTO ingredient (id, led_id, recipe_id, level) SELECT id, led_id, recipe_id, level FROM __temp__ingredient');
        $this->addSql('DROP TABLE __temp__ingredient');
        $this->addSql('CREATE INDEX IDX_6BAF7870B262EAC9 ON ingredient (led_id)');
        $this->addSql('CREATE INDEX IDX_6BAF787059D8A214 ON ingredient (recipe_id)');
        $this->addSql('DROP INDEX IDX_8F3F68C5C36A3328');
        $this->addSql('DROP INDEX IDX_8F3F68C5DC90A29E');
        $this->addSql('CREATE TEMPORARY TABLE __temp__log AS SELECT id, cluster_id, luminaire_id, type, value, comment, time FROM log');
        $this->addSql('DROP TABLE log');
        $this->addSql('CREATE TABLE log (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, cluster_id INTEGER DEFAULT NULL, luminaire_id INTEGER DEFAULT NULL, type VARCHAR(255) NOT NULL, value CLOB NOT NULL --(DC2Type:json_array)
        , comment VARCHAR(255) DEFAULT NULL, time DATETIME NOT NULL)');
        $this->addSql('INSERT INTO log (id, cluster_id, luminaire_id, type, value, comment, time) SELECT id, cluster_id, luminaire_id, type, value, comment, time FROM __temp__log');
        $this->addSql('DROP TABLE __temp__log');
        $this->addSql('CREATE INDEX IDX_8F3F68C5C36A3328 ON log (cluster_id)');
        $this->addSql('CREATE INDEX IDX_8F3F68C5DC90A29E ON log (luminaire_id)');
        $this->addSql('DROP INDEX IDX_BF3BAD1BF6D1A74B');
        $this->addSql('DROP INDEX IDX_BF3BAD1BC36A3328');
        $this->addSql('CREATE TEMPORARY TABLE __temp__luminaire AS SELECT id, controller_id, cluster_id, address, serial, ligne, colonne FROM luminaire');
        $this->addSql('DROP TABLE luminaire');
        $this->addSql('CREATE TABLE luminaire (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, controller_id INTEGER DEFAULT NULL, cluster_id INTEGER DEFAULT NULL, address INTEGER NOT NULL, serial VARCHAR(255) DEFAULT NULL, ligne INTEGER DEFAULT NULL, colonne INTEGER DEFAULT NULL)');
        $this->addSql('INSERT INTO luminaire (id, controller_id, cluster_id, address, serial, ligne, colonne) SELECT id, controller_id, cluster_id, address, serial, ligne, colonne FROM __temp__luminaire');
        $this->addSql('DROP TABLE __temp__luminaire');
        $this->addSql('CREATE INDEX IDX_BF3BAD1BF6D1A74B ON luminaire (controller_id)');
        $this->addSql('CREATE INDEX IDX_BF3BAD1BC36A3328 ON luminaire (cluster_id)');
        $this->addSql('DROP INDEX IDX_33E4791FDC90A29E');
        $this->addSql('DROP INDEX IDX_33E4791FA76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__luminaire_user AS SELECT luminaire_id, user_id FROM luminaire_user');
        $this->addSql('DROP TABLE luminaire_user');
        $this->addSql('CREATE TABLE luminaire_user (luminaire_id INTEGER NOT NULL, user_id INTEGER NOT NULL, PRIMARY KEY(luminaire_id, user_id))');
        $this->addSql('INSERT INTO luminaire_user (luminaire_id, user_id) SELECT luminaire_id, user_id FROM __temp__luminaire_user');
        $this->addSql('DROP TABLE __temp__luminaire_user');
        $this->addSql('CREATE INDEX IDX_33E4791FDC90A29E ON luminaire_user (luminaire_id)');
        $this->addSql('CREATE INDEX IDX_33E4791FA76ED395 ON luminaire_user (user_id)');
        $this->addSql('DROP INDEX IDX_46DC8952DC90A29E');
        $this->addSql('CREATE TEMPORARY TABLE __temp__pcb AS SELECT id, luminaire_id, crc, serial, n, type FROM pcb');
        $this->addSql('DROP TABLE pcb');
        $this->addSql('CREATE TABLE pcb (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, luminaire_id INTEGER NOT NULL, crc VARCHAR(6) NOT NULL, serial VARCHAR(10) NOT NULL, n INTEGER NOT NULL, type INTEGER NOT NULL)');
        $this->addSql('INSERT INTO pcb (id, luminaire_id, crc, serial, n, type) SELECT id, luminaire_id, crc, serial, n, type FROM __temp__pcb');
        $this->addSql('DROP TABLE __temp__pcb');
        $this->addSql('CREATE INDEX IDX_46DC8952DC90A29E ON pcb (luminaire_id)');
        $this->addSql('DROP INDEX IDX_92ED7784A76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__program AS SELECT id, user_id, label, description FROM program');
        $this->addSql('DROP TABLE program');
        $this->addSql('CREATE TABLE program (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER DEFAULT NULL, label VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL)');
        $this->addSql('INSERT INTO program (id, user_id, label, description) SELECT id, user_id, label, description FROM __temp__program');
        $this->addSql('DROP TABLE __temp__program');
        $this->addSql('CREATE INDEX IDX_92ED7784A76ED395 ON program (user_id)');
        $this->addSql('DROP INDEX IDX_DA88B137A76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__recipe AS SELECT id, user_id, label, description FROM recipe');
        $this->addSql('DROP TABLE recipe');
        $this->addSql('CREATE TABLE recipe (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER DEFAULT NULL, label VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL)');
        $this->addSql('INSERT INTO recipe (id, user_id, label, description) SELECT id, user_id, label, description FROM __temp__recipe');
        $this->addSql('DROP TABLE __temp__recipe');
        $this->addSql('CREATE INDEX IDX_DA88B137A76ED395 ON recipe (user_id)');
        $this->addSql('DROP INDEX IDX_43B9FE3C3EB8070A');
        $this->addSql('DROP INDEX IDX_43B9FE3C59D8A214');
        $this->addSql('CREATE TEMPORARY TABLE __temp__step AS SELECT id, program_id, recipe_id, type, rank, value FROM step');
        $this->addSql('DROP TABLE step');
        $this->addSql('CREATE TABLE step (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, program_id INTEGER NOT NULL, recipe_id INTEGER DEFAULT NULL, type VARCHAR(255) NOT NULL, rank INTEGER NOT NULL, value VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO step (id, program_id, recipe_id, type, rank, value) SELECT id, program_id, recipe_id, type, rank, value FROM __temp__step');
        $this->addSql('DROP TABLE __temp__step');
        $this->addSql('CREATE INDEX IDX_43B9FE3C3EB8070A ON step (program_id)');
        $this->addSql('CREATE INDEX IDX_43B9FE3C59D8A214 ON step (recipe_id)');
    }
}
