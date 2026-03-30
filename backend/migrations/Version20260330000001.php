<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Inicjalna migracja VENOM CRM:
 * - Tabela roles (moduł Access)
 * - Tabela users (moduł Admin) — powiązana z roles
 * - Tabela customers (moduł Customers) — powiązana z users
 */
final class Version20260330000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Inicjalna struktura bazy danych VENOM CRM: roles, users, customers';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE roles (
                id          INT AUTO_INCREMENT NOT NULL,
                name        VARCHAR(100) NOT NULL,
                description VARCHAR(255) DEFAULT NULL,
                UNIQUE INDEX UNIQ_B63E2EC75E237E06 (name),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE users (
                id         INT AUTO_INCREMENT NOT NULL,
                role_id    INT NOT NULL,
                email      VARCHAR(180) NOT NULL,
                password   VARCHAR(255) NOT NULL,
                first_name VARCHAR(100) NOT NULL,
                last_name  VARCHAR(100) NOT NULL,
                is_active  TINYINT(1) NOT NULL DEFAULT 1,
                created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email),
                INDEX IDX_1483A5E9D60322AC (role_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE customers (
                id             INT AUTO_INCREMENT NOT NULL,
                assigned_to_id INT DEFAULT NULL,
                name           VARCHAR(255) NOT NULL,
                email          VARCHAR(180) DEFAULT NULL,
                phone          VARCHAR(30) DEFAULT NULL,
                nip            VARCHAR(15) DEFAULT NULL,
                address        VARCHAR(255) DEFAULT NULL,
                city           VARCHAR(100) DEFAULT NULL,
                zip_code       VARCHAR(10) DEFAULT NULL,
                country        VARCHAR(100) DEFAULT NULL,
                notes          LONGTEXT DEFAULT NULL,
                status         VARCHAR(20) NOT NULL DEFAULT 'active',
                created_at     DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                updated_at     DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                INDEX IDX_62534E21F4BD7827 (assigned_to_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE users
                ADD CONSTRAINT FK_1483A5E9D60322AC
                FOREIGN KEY (role_id) REFERENCES roles (id)
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE customers
                ADD CONSTRAINT FK_62534E21F4BD7827
                FOREIGN KEY (assigned_to_id) REFERENCES users (id) ON DELETE SET NULL
        SQL);

        // Dane startowe — trzy domyślne role
        $this->addSql(<<<'SQL'
            INSERT INTO roles (name, description) VALUES
                ('Administrator',              'Pełny dostęp do wszystkich funkcji systemu CRM.'),
                ('Pracownik administracyjny',  'Dostęp do zarządzania kontrahentami i raportów.'),
                ('Handlowiec',                 'Dostęp do swoich kontrahentów i szans sprzedaży.')
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE customers DROP FOREIGN KEY FK_62534E21F4BD7827');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E9D60322AC');
        $this->addSql('DROP TABLE customers');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE roles');
    }
}
