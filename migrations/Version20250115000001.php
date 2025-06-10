<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250115000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create service and service_item tables for automatic invoicing';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE service (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            supplier_id INT NOT NULL,
            client_id INT NOT NULL,
            bank_account_id INT DEFAULT NULL,
            name VARCHAR(255) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            invoice_day INT NOT NULL,
            due_days INT NOT NULL,
            frequency VARCHAR(20) NOT NULL,
            last_invoice_date DATE DEFAULT NULL,
            start_date DATE DEFAULT NULL,
            end_date DATE DEFAULT NULL,
            is_active TINYINT(1) DEFAULT 1 NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE service_item (
            id INT AUTO_INCREMENT NOT NULL,
            service_id INT NOT NULL,
            description VARCHAR(255) NOT NULL,
            quantity NUMERIC(10, 2) NOT NULL,
            unit VARCHAR(50) DEFAULT NULL,
            unit_price NUMERIC(10, 2) NOT NULL,
            vat_rate NUMERIC(5, 2) DEFAULT \'21.00\' NOT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD2A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD22ADD6D8C FOREIGN KEY (supplier_id) REFERENCES supplier (id)');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD219EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD212CB990C FOREIGN KEY (bank_account_id) REFERENCES bank_account (id)');
        $this->addSql('ALTER TABLE service_item ADD CONSTRAINT FK_3D0B2DDED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id)');

        $this->addSql('CREATE INDEX IDX_E19D9AD2A76ED395 ON service (user_id)');
        $this->addSql('CREATE INDEX IDX_E19D9AD22ADD6D8C ON service (supplier_id)');
        $this->addSql('CREATE INDEX IDX_E19D9AD219EB6921 ON service (client_id)');
        $this->addSql('CREATE INDEX IDX_E19D9AD212CB990C ON service (bank_account_id)');
        $this->addSql('CREATE INDEX IDX_3D0B2DDED5CA9E6 ON service_item (service_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE service DROP FOREIGN KEY FK_E19D9AD2A76ED395');
        $this->addSql('ALTER TABLE service DROP FOREIGN KEY FK_E19D9AD22ADD6D8C');
        $this->addSql('ALTER TABLE service DROP FOREIGN KEY FK_E19D9AD219EB6921');
        $this->addSql('ALTER TABLE service DROP FOREIGN KEY FK_E19D9AD212CB990C');
        $this->addSql('ALTER TABLE service_item DROP FOREIGN KEY FK_3D0B2DDED5CA9E6');
        $this->addSql('DROP TABLE service_item');
        $this->addSql('DROP TABLE service');
    }
}
