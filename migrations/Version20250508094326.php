<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250508094326 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE supplier (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, street VARCHAR(255) DEFAULT NULL, number VARCHAR(50) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, postal_code VARCHAR(20) DEFAULT NULL, telephone VARCHAR(20) DEFAULT NULL, invoice_email VARCHAR(255) DEFAULT NULL, dic VARCHAR(50) DEFAULT NULL, ico VARCHAR(50) DEFAULT NULL, description LONGTEXT DEFAULT NULL, bank_account VARCHAR(50) DEFAULT NULL, vat_payer TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP TABLE supplier
        SQL);
    }
}
