<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200512205541 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql('
            CREATE TABLE user (
                id INT AUTO_INCREMENT NOT NULL,
                phone_nr BIGINT NOT NULL,
                roles JSON NOT NULL,
                password VARCHAR(255) NOT NULL,
                api_token VARCHAR(255) DEFAULT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE INDEX UNIQ_8D93D64980E6005D (phone_nr),
                UNIQUE INDEX UNIQ_8D93D6497BA2F5EB (api_token),
                PRIMARY KEY(id)
              )
              DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');

        $this->addSql('
            CREATE TABLE contact (
                id INT AUTO_INCREMENT NOT NULL,
                user_id INT NOT NULL,
                phone_nr BIGINT NOT NULL,
                name VARCHAR(55) NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX IDX_4C62E6389D86650F (user_id),
                PRIMARY KEY(id)
            )
            DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');

        $this->addSql('
            CREATE TABLE share_request (
                id INT AUTO_INCREMENT NOT NULL,
                sender_id INT NOT NULL,
                receiver_id INT NOT NULL,
                phone_nr BIGINT NOT NULL,
                name VARCHAR(55) NOT NULL,
                status VARCHAR(10) NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX IDX_79FB25036061F7CF (sender_id),
                INDEX IDX_79FB2503BE20CAB0 (receiver_id),
                PRIMARY KEY(id)
            )
            DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');

        $this->addSql('
            ALTER TABLE contact
                ADD CONSTRAINT FK_4C62E6389D86650F FOREIGN KEY (user_id) REFERENCES user (id)
        ');

        $this->addSql('
            ALTER TABLE share_request
                ADD CONSTRAINT FK_79FB25036061F7CF FOREIGN KEY (sender_id) REFERENCES user (id)
        ');

        $this->addSql('
            ALTER TABLE share_request
                ADD CONSTRAINT FK_79FB2503BE20CAB0 FOREIGN KEY (receiver_id) REFERENCES user (id)
        ');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql('ALTER TABLE contact DROP FOREIGN KEY FK_4C62E6389D86650F');
        $this->addSql('ALTER TABLE share_request DROP FOREIGN KEY FK_79FB25036061F7CF');
        $this->addSql('ALTER TABLE share_request DROP FOREIGN KEY FK_79FB2503BE20CAB0');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE contact');
        $this->addSql('DROP TABLE share_request');
    }
}
