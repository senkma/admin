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
        // Add foreign key constraints for service table
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD2A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD22ADD6D8C FOREIGN KEY (supplier_id) REFERENCES supplier (id)');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD219EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD212CB990C FOREIGN KEY (bank_account_id) REFERENCES bank_account (id)');
        
        // Add foreign key constraint for service_item table
        $this->addSql('ALTER TABLE service_item ADD CONSTRAINT FK_3D0B2DDED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id)');

        // Add indexes for better performance
        $this->addSql('CREATE INDEX IDX_E19D9AD2A76ED395 ON service (user_id)');
        $this->addSql('CREATE INDEX IDX_E19D9AD22ADD6D8C ON service (supplier_id)');
        $this->addSql('CREATE INDEX IDX_E19D9AD219EB6921 ON service (client_id)');
        $this->addSql('CREATE INDEX IDX_E19D9AD212CB990C ON service (bank_account_id)');
        $this->addSql('CREATE INDEX IDX_3D0B2DDED5CA9E6 ON service_item (service_id)');
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
