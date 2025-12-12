<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250115000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add bank_account_id to invoice table';
    }

    public function up(Schema $schema): void
    {
        // Create bank_account table first
        $connection = $this->connection;
        $schemaManager = $connection->createSchemaManager();
        $tables = $schemaManager->listTableNames();

        // Only create bank_account table if supplier table exists
        if (in_array('supplier', $tables) && !in_array('bank_account', $tables)) {
            $this->addSql('CREATE TABLE bank_account (id INT AUTO_INCREMENT NOT NULL, supplier_id INT NOT NULL, account_number VARCHAR(50) NOT NULL, bank_code VARCHAR(10) DEFAULT NULL, bank_name VARCHAR(100) DEFAULT NULL, iban VARCHAR(34) DEFAULT NULL, swift VARCHAR(11) DEFAULT NULL, is_default TINYINT(1) NOT NULL, INDEX IDX_53A23E0A2ADD6D8C (supplier_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            $this->addSql('ALTER TABLE bank_account ADD CONSTRAINT FK_53A23E0A2ADD6D8C FOREIGN KEY (supplier_id) REFERENCES supplier (id)');
        }

        // Check if invoice table exists and column doesn't exist yet
        if (in_array('invoice', $tables) && in_array('bank_account', $tables)) {
            $columns = $schemaManager->listTableColumns('invoice');

            if (!isset($columns['bank_account_id'])) {
                $this->addSql('ALTER TABLE invoice ADD bank_account_id INT DEFAULT NULL');
                $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_9065174412CB990C FOREIGN KEY (bank_account_id) REFERENCES bank_account (id)');
                $this->addSql('CREATE INDEX IDX_9065174412CB990C ON invoice (bank_account_id)');
            }
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_9065174412CB990C');
        $this->addSql('DROP INDEX IDX_9065174412CB990C ON invoice');
        $this->addSql('ALTER TABLE invoice DROP bank_account_id');

        // Drop bank_account table
        $this->addSql('ALTER TABLE bank_account DROP FOREIGN KEY FK_53A23E0A2ADD6D8C');
        $this->addSql('DROP TABLE bank_account');
    }
}
