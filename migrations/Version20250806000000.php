<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Fix schema synchronization issues on production
 */
final class Version20250806000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix schema synchronization issues - ensure all FK and indexes are properly created';
    }

    public function up(Schema $schema): void
    {
        // Check if tables exist before making changes
        $connection = $this->connection;
        $schemaManager = $connection->createSchemaManager();
        
        // Fix bank_account FK if missing
        if ($schemaManager->tablesExist(['bank_account', 'supplier'])) {
            $bankAccountFKs = $schemaManager->listTableForeignKeys('bank_account');
            $hasBankAccountFK = false;
            foreach ($bankAccountFKs as $fk) {
                if ($fk->getName() === 'FK_53A23E0A2ADD6D8C') {
                    $hasBankAccountFK = true;
                    break;
                }
            }
            
            if (!$hasBankAccountFK) {
                $this->addSql('ALTER TABLE bank_account ADD CONSTRAINT FK_53A23E0A2ADD6D8C FOREIGN KEY (supplier_id) REFERENCES supplier (id)');
            }
        }
        
        // Fix communication FK if they have wrong names
        if ($schemaManager->tablesExist(['communication'])) {
            $communicationFKs = $schemaManager->listTableForeignKeys('communication');
            $existingFKs = [];
            foreach ($communicationFKs as $fk) {
                $existingFKs[] = $fk->getName();
            }
            
            // Drop old FK with custom names and create new ones with Doctrine names
            if (in_array('FK_COMM_USER', $existingFKs)) {
                $this->addSql('ALTER TABLE communication DROP FOREIGN KEY FK_COMM_USER');
                $this->addSql('ALTER TABLE communication ADD CONSTRAINT FK_F9AFB5EBA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
            }
            
            if (in_array('FK_COMM_SUPPLIER', $existingFKs)) {
                $this->addSql('ALTER TABLE communication DROP FOREIGN KEY FK_COMM_SUPPLIER');
                $this->addSql('ALTER TABLE communication ADD CONSTRAINT FK_F9AFB5EB2ADD6D8C FOREIGN KEY (supplier_id) REFERENCES supplier (id)');
            }
            
            if (in_array('FK_COMM_CLIENT', $existingFKs)) {
                $this->addSql('ALTER TABLE communication DROP FOREIGN KEY FK_COMM_CLIENT');
                $this->addSql('ALTER TABLE communication ADD CONSTRAINT FK_F9AFB5EB19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
            }
            
            if (in_array('FK_COMM_SERVICE', $existingFKs)) {
                $this->addSql('ALTER TABLE communication DROP FOREIGN KEY FK_COMM_SERVICE');
                $this->addSql('ALTER TABLE communication ADD CONSTRAINT FK_F9AFB5EBED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id)');
            }
            
            if (in_array('FK_COMM_INVOICE', $existingFKs)) {
                $this->addSql('ALTER TABLE communication DROP FOREIGN KEY FK_COMM_INVOICE');
                $this->addSql('ALTER TABLE communication ADD CONSTRAINT FK_F9AFB5EB2989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id)');
            }
            
            // Fix indexes
            $communicationIndexes = $schemaManager->listTableIndexes('communication');
            if (isset($communicationIndexes['idx_comm_user'])) {
                $this->addSql('ALTER TABLE communication RENAME INDEX idx_comm_user TO IDX_F9AFB5EBA76ED395');
            }
            if (isset($communicationIndexes['idx_comm_supplier'])) {
                $this->addSql('ALTER TABLE communication RENAME INDEX idx_comm_supplier TO IDX_F9AFB5EB2ADD6D8C');
            }
            if (isset($communicationIndexes['idx_comm_client'])) {
                $this->addSql('ALTER TABLE communication RENAME INDEX idx_comm_client TO IDX_F9AFB5EB19EB6921');
            }
            if (isset($communicationIndexes['idx_comm_service'])) {
                $this->addSql('ALTER TABLE communication RENAME INDEX idx_comm_service TO IDX_F9AFB5EBED5CA9E6');
            }
            if (isset($communicationIndexes['idx_comm_invoice'])) {
                $this->addSql('ALTER TABLE communication RENAME INDEX idx_comm_invoice TO IDX_F9AFB5EB2989F1FD');
            }
        }
    }

    public function down(Schema $schema): void
    {
        // Reverse the changes if needed
        $this->addSql('ALTER TABLE bank_account DROP FOREIGN KEY FK_53A23E0A2ADD6D8C');
        
        $this->addSql('ALTER TABLE communication DROP FOREIGN KEY FK_F9AFB5EBA76ED395');
        $this->addSql('ALTER TABLE communication DROP FOREIGN KEY FK_F9AFB5EB2ADD6D8C');
        $this->addSql('ALTER TABLE communication DROP FOREIGN KEY FK_F9AFB5EB19EB6921');
        $this->addSql('ALTER TABLE communication DROP FOREIGN KEY FK_F9AFB5EBED5CA9E6');
        $this->addSql('ALTER TABLE communication DROP FOREIGN KEY FK_F9AFB5EB2989F1FD');
    }
}
