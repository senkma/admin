<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250700000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix foreign keys, indexes and column definitions';
    }

    public function up(Schema $schema): void
    {
        // Check if constraints and indexes already exist before adding them
        $connection = $this->connection;
        $schemaManager = $connection->createSchemaManager();
        
        // Add service_item foreign key and index if not exists
        $serviceItemForeignKeys = $schemaManager->listTableForeignKeys('service_item');
        $hasServiceItemFK = false;
        foreach ($serviceItemForeignKeys as $fk) {
            if ($fk->getName() === 'FK_D15891F2ED5CA9E6') {
                $hasServiceItemFK = true;
                break;
            }
        }
        
        if (!$hasServiceItemFK) {
            $this->addSql('ALTER TABLE service_item ADD CONSTRAINT FK_D15891F2ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id)');
        }
        
        $serviceItemIndexes = $schemaManager->listTableIndexes('service_item');
        if (!isset($serviceItemIndexes['idx_d15891f2ed5ca9e6'])) {
            $this->addSql('CREATE INDEX IDX_D15891F2ED5CA9E6 ON service_item (service_id)');
        }
        
        // Add invoice foreign key and index if not exists and bank_account_id column exists
        if ($schemaManager->tablesExist(['invoice'])) {
            $invoiceColumns = $schemaManager->listTableColumns('invoice');

            // Only proceed if bank_account_id column exists
            if (isset($invoiceColumns['bank_account_id'])) {
                $invoiceForeignKeys = $schemaManager->listTableForeignKeys('invoice');
                $hasInvoiceFK = false;
                foreach ($invoiceForeignKeys as $fk) {
                    if ($fk->getName() === 'FK_9065174412CB990C') {
                        $hasInvoiceFK = true;
                        break;
                    }
                }

                if (!$hasInvoiceFK) {
                    $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_9065174412CB990C FOREIGN KEY (bank_account_id) REFERENCES bank_account (id)');
                }

                $invoiceIndexes = $schemaManager->listTableIndexes('invoice');
                if (!isset($invoiceIndexes['idx_9065174412cb990c'])) {
                    $this->addSql('CREATE INDEX IDX_9065174412CB990C ON invoice (bank_account_id)');
                }
            }
        }
        
        // Update client due_days column if it exists
        if ($schemaManager->tablesExist(['client'])) {
            $clientColumns = $schemaManager->listTableColumns('client');
            if (isset($clientColumns['due_days'])) {
                $this->addSql('ALTER TABLE client CHANGE due_days due_days INT DEFAULT 14 NOT NULL');
            }
        }
        
        // Drop bank_account column from supplier if exists
        $supplierColumns = $schemaManager->listTableColumns('supplier');
        if (isset($supplierColumns['bank_account'])) {
            $this->addSql('ALTER TABLE supplier DROP bank_account');
        }
        
        // Update service columns with comments
        $this->addSql('ALTER TABLE service CHANGE invoice_day invoice_day INT NOT NULL COMMENT \'Den v měsíci kdy se vytvoří faktura (1-31)\'');
        $this->addSql('ALTER TABLE service CHANGE due_days due_days INT NOT NULL COMMENT \'Počet dní od vytvoření do splatnosti\'');
        $this->addSql('ALTER TABLE service CHANGE frequency frequency VARCHAR(20) NOT NULL COMMENT \'Frekvence: monthly, quarterly, yearly\'');
        $this->addSql('ALTER TABLE service CHANGE last_invoice_date last_invoice_date DATE DEFAULT NULL COMMENT \'Datum posledního vygenerování faktury\'');
        $this->addSql('ALTER TABLE service CHANGE start_date start_date DATE DEFAULT NULL COMMENT \'Datum kdy služba začíná\'');
        $this->addSql('ALTER TABLE service CHANGE end_date end_date DATE DEFAULT NULL COMMENT \'Datum kdy služba končí (null = nekonečně)\'');
        
        // Add service foreign key and index if not exists and bank_account_id column exists
        if ($schemaManager->tablesExist(['service'])) {
            $serviceColumns = $schemaManager->listTableColumns('service');

            // Only proceed if bank_account_id column exists
            if (isset($serviceColumns['bank_account_id'])) {
                $serviceForeignKeys = $schemaManager->listTableForeignKeys('service');
                $hasServiceFK = false;
                foreach ($serviceForeignKeys as $fk) {
                    if ($fk->getName() === 'FK_E19D9AD212CB990C') {
                        $hasServiceFK = true;
                        break;
                    }
                }

                if (!$hasServiceFK) {
                    $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD212CB990C FOREIGN KEY (bank_account_id) REFERENCES bank_account (id)');
                }

                $serviceIndexes = $schemaManager->listTableIndexes('service');
                if (!isset($serviceIndexes['idx_e19d9ad212cb990c'])) {
                    $this->addSql('CREATE INDEX IDX_E19D9AD212CB990C ON service (bank_account_id)');
                }

                // Rename indexes if they exist with old names
                if (isset($serviceIndexes['fk_e19d9ad2a76ed395'])) {
                    $this->addSql('ALTER TABLE service RENAME INDEX fk_e19d9ad2a76ed395 TO IDX_E19D9AD2A76ED395');
                }

                if (isset($serviceIndexes['fk_e19d9ad22add6d8c'])) {
                    $this->addSql('ALTER TABLE service RENAME INDEX fk_e19d9ad22add6d8c TO IDX_E19D9AD22ADD6D8C');
                }

                if (isset($serviceIndexes['fk_e19d9ad219eb6921'])) {
                    $this->addSql('ALTER TABLE service RENAME INDEX fk_e19d9ad219eb6921 TO IDX_E19D9AD219EB6921');
                }
            }
        }

    }

    public function down(Schema $schema): void
    {
        // Reverse the changes
        $this->addSql('ALTER TABLE service_item DROP FOREIGN KEY FK_D15891F2ED5CA9E6');
        $this->addSql('DROP INDEX IDX_D15891F2ED5CA9E6 ON service_item');
        
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_9065174412CB990C');
        $this->addSql('DROP INDEX IDX_9065174412CB990C ON invoice');
        
        $this->addSql('ALTER TABLE client CHANGE due_days due_days INT DEFAULT NULL');
        
        $this->addSql('ALTER TABLE supplier ADD bank_account VARCHAR(50) DEFAULT NULL');
        
        $this->addSql('ALTER TABLE service CHANGE invoice_day invoice_day INT NOT NULL');
        $this->addSql('ALTER TABLE service CHANGE due_days due_days INT NOT NULL');
        $this->addSql('ALTER TABLE service CHANGE frequency frequency VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE service CHANGE last_invoice_date last_invoice_date DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE service CHANGE start_date start_date DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE service CHANGE end_date end_date DATE DEFAULT NULL');
        
        $this->addSql('ALTER TABLE service DROP FOREIGN KEY FK_E19D9AD212CB990C');
        $this->addSql('DROP INDEX IDX_E19D9AD212CB990C ON service');
    }
}
