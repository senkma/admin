<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250115000003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add due_days column to client table';
    }

    public function up(Schema $schema): void
    {
        // Check if client table exists and due_days column doesn't exist
        $connection = $this->connection;
        $schemaManager = $connection->createSchemaManager();
        $tables = $schemaManager->listTableNames();

        if (in_array('client', $tables)) {
            $columns = $schemaManager->listTableColumns('client');

            if (!isset($columns['due_days'])) {
                $this->addSql('ALTER TABLE client ADD due_days INT DEFAULT 14 NOT NULL COMMENT \'Počet dní splatnosti pro faktury tohoto klienta\'');
            }
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE client DROP due_days');
    }
}
