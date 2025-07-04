<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250704120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create communication table and add sendEmail field to service table';
    }

    public function up(Schema $schema): void
    {
        // Create communication table
        $this->addSql('CREATE TABLE IF NOT EXISTS communication (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            supplier_id INT DEFAULT NULL,
            client_id INT DEFAULT NULL,
            service_id INT DEFAULT NULL,
            invoice_id INT DEFAULT NULL,
            email VARCHAR(255) NOT NULL,
            message LONGTEXT NOT NULL,
            status VARCHAR(20) DEFAULT "pripraveno" NOT NULL,
            created_at DATETIME NOT NULL,
            sent_at DATETIME DEFAULT NULL,
            error_message LONGTEXT DEFAULT NULL,
            PRIMARY KEY(id),
            INDEX IDX_COMM_USER (user_id),
            INDEX IDX_COMM_SUPPLIER (supplier_id),
            INDEX IDX_COMM_CLIENT (client_id),
            INDEX IDX_COMM_SERVICE (service_id),
            INDEX IDX_COMM_INVOICE (invoice_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Add foreign key constraints
        $this->addSql('ALTER TABLE communication ADD CONSTRAINT FK_COMM_USER FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE communication ADD CONSTRAINT FK_COMM_SUPPLIER FOREIGN KEY (supplier_id) REFERENCES supplier (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE communication ADD CONSTRAINT FK_COMM_CLIENT FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE communication ADD CONSTRAINT FK_COMM_SERVICE FOREIGN KEY (service_id) REFERENCES service (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE communication ADD CONSTRAINT FK_COMM_INVOICE FOREIGN KEY (invoice_id) REFERENCES invoice (id) ON DELETE SET NULL');

        // Add sendEmail field to service table if it doesn't exist
        $this->addSql('ALTER TABLE service ADD send_email TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // Remove foreign key constraints
        $this->addSql('ALTER TABLE communication DROP FOREIGN KEY FK_COMM_USER');
        $this->addSql('ALTER TABLE communication DROP FOREIGN KEY FK_COMM_SUPPLIER');
        $this->addSql('ALTER TABLE communication DROP FOREIGN KEY FK_COMM_CLIENT');
        $this->addSql('ALTER TABLE communication DROP FOREIGN KEY FK_COMM_SERVICE');
        $this->addSql('ALTER TABLE communication DROP FOREIGN KEY FK_COMM_INVOICE');

        // Drop communication table
        $this->addSql('DROP TABLE IF EXISTS communication');

        // Remove sendEmail field from service table
        $this->addSql('ALTER TABLE service DROP COLUMN send_email');
    }
}
