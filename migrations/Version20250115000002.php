<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250115000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add foreign key constraints to service tables';
    }

    public function up(Schema $schema): void
    {
        // Check if all required tables exist before adding foreign keys
        $connection = $this->connection;
        $schemaManager = $connection->createSchemaManager();
        $tables = $schemaManager->listTableNames();

        $requiredTables = ['service', 'service_item', 'users', 'supplier', 'client', 'bank_account'];
        $allTablesExist = true;
        foreach ($requiredTables as $table) {
            if (!in_array($table, $tables)) {
                $allTablesExist = false;
                break;
            }
        }

        if (!$allTablesExist) {
            return; // Skip if required tables don't exist
        }

        // Check if foreign keys already exist
        $serviceForeignKeys = $schemaManager->listTableForeignKeys('service');
        $serviceItemForeignKeys = $schemaManager->listTableForeignKeys('service_item');

        $existingServiceFKs = [];
        foreach ($serviceForeignKeys as $fk) {
            $existingServiceFKs[] = $fk->getName();
        }

        $existingServiceItemFKs = [];
        foreach ($serviceItemForeignKeys as $fk) {
            $existingServiceItemFKs[] = $fk->getName();
        }

        // Add foreign key constraints for service table only if they don't exist
        if (!in_array('FK_E19D9AD2A76ED395', $existingServiceFKs)) {
            $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD2A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        }
        if (!in_array('FK_E19D9AD22ADD6D8C', $existingServiceFKs)) {
            $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD22ADD6D8C FOREIGN KEY (supplier_id) REFERENCES supplier (id)');
        }
        if (!in_array('FK_E19D9AD219EB6921', $existingServiceFKs)) {
            $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD219EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        }
        if (!in_array('FK_E19D9AD212CB990C', $existingServiceFKs)) {
            $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD212CB990C FOREIGN KEY (bank_account_id) REFERENCES bank_account (id)');
        }

        // Add foreign key constraint for service_item table
        if (!in_array('FK_3D0B2DDED5CA9E6', $existingServiceItemFKs)) {
            $this->addSql('ALTER TABLE service_item ADD CONSTRAINT FK_3D0B2DDED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id)');
        }

        // Add indexes for better performance (check if they exist first)
        $serviceIndexes = $schemaManager->listTableIndexes('service');
        $serviceItemIndexes = $schemaManager->listTableIndexes('service_item');

        if (!isset($serviceIndexes['idx_e19d9ad2a76ed395'])) {
            $this->addSql('CREATE INDEX IDX_E19D9AD2A76ED395 ON service (user_id)');
        }
        if (!isset($serviceIndexes['idx_e19d9ad22add6d8c'])) {
            $this->addSql('CREATE INDEX IDX_E19D9AD22ADD6D8C ON service (supplier_id)');
        }
        if (!isset($serviceIndexes['idx_e19d9ad219eb6921'])) {
            $this->addSql('CREATE INDEX IDX_E19D9AD219EB6921 ON service (client_id)');
        }
        if (!isset($serviceIndexes['idx_e19d9ad212cb990c'])) {
            $this->addSql('CREATE INDEX IDX_E19D9AD212CB990C ON service (bank_account_id)');
        }
        if (!isset($serviceItemIndexes['idx_3d0b2dded5ca9e6'])) {
            $this->addSql('CREATE INDEX IDX_3D0B2DDED5CA9E6 ON service_item (service_id)');
        }
    }

    public function down(Schema $schema): void
    {
        // Drop foreign key constraints
        $this->addSql('ALTER TABLE service DROP FOREIGN KEY FK_E19D9AD2A76ED395');
        $this->addSql('ALTER TABLE service DROP FOREIGN KEY FK_E19D9AD22ADD6D8C');
        $this->addSql('ALTER TABLE service DROP FOREIGN KEY FK_E19D9AD219EB6921');
        $this->addSql('ALTER TABLE service DROP FOREIGN KEY FK_E19D9AD212CB990C');
        $this->addSql('ALTER TABLE service_item DROP FOREIGN KEY FK_3D0B2DDED5CA9E6');

        // Drop indexes
        $this->addSql('DROP INDEX IDX_E19D9AD2A76ED395 ON service');
        $this->addSql('DROP INDEX IDX_E19D9AD22ADD6D8C ON service');
        $this->addSql('DROP INDEX IDX_E19D9AD219EB6921 ON service');
        $this->addSql('DROP INDEX IDX_E19D9AD212CB990C ON service');
        $this->addSql('DROP INDEX IDX_3D0B2DDED5CA9E6 ON service_item');
    }
}
