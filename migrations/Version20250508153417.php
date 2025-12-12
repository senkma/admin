<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250508153417 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Check if user_id column already exists
        $connection = $this->connection;
        $schemaManager = $connection->createSchemaManager();

        if ($schemaManager->tablesExist(['client'])) {
            $columns = $schemaManager->listTableColumns('client');

            if (!isset($columns['user_id'])) {
                $this->addSql('ALTER TABLE client ADD user_id INT NOT NULL');
                $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
                $this->addSql('CREATE INDEX IDX_C7440455A76ED395 ON client (user_id)');
            }
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE client DROP FOREIGN KEY FK_C7440455A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_C7440455A76ED395 ON client
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client DROP user_id
        SQL);
    }
}
