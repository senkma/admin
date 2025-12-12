<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250606145512 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Check if tables exist before making changes
        $connection = $this->connection;
        $schemaManager = $connection->createSchemaManager();
        $tables = $schemaManager->listTableNames();
        
        // Create bank_account table only if it doesn't exist and supplier exists
        if (in_array('supplier', $tables) && !in_array('bank_account', $tables)) {
            $this->addSql('CREATE TABLE bank_account (id INT AUTO_INCREMENT NOT NULL, supplier_id INT NOT NULL, account_number VARCHAR(50) NOT NULL, bank_code VARCHAR(10) DEFAULT NULL, bank_name VARCHAR(100) DEFAULT NULL, iban VARCHAR(34) DEFAULT NULL, swift VARCHAR(11) DEFAULT NULL, is_default TINYINT(1) NOT NULL, INDEX IDX_53A23E0A2ADD6D8C (supplier_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            $this->addSql('ALTER TABLE bank_account ADD CONSTRAINT FK_53A23E0A2ADD6D8C FOREIGN KEY (supplier_id) REFERENCES supplier (id)');
        }
        
        // Drop bank_account column from supplier if it exists
        if (in_array('supplier', $tables)) {
            $columns = $schemaManager->listTableColumns('supplier');
            if (isset($columns['bank_account'])) {
                $this->addSql('ALTER TABLE supplier DROP bank_account');
            }
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bank_account DROP FOREIGN KEY FK_53A23E0A2ADD6D8C');
        $this->addSql('DROP TABLE bank_account');
        $this->addSql('ALTER TABLE supplier ADD bank_account VARCHAR(50) DEFAULT NULL');
    }
}
