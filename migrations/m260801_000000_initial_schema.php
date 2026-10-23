<?php

use yii\db\Migration;

/**
 * Initial schema (replaces inventur.sql as the source of truth).
 *
 * Idempotent: on existing installations the tables already exist and are skipped,
 * so this can be applied safely everywhere with `php yii migrate`.
 */
class m260801_000000_initial_schema extends Migration
{
    public function safeUp()
    {
        $options = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB'
            : null;

        if (!$this->tableExists('benutzer')) {
            $this->createTable('benutzer', [
                'id' => 'INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
                'entra_oid' => $this->string(100)->notNull()->unique(),
                'firstname' => $this->string(150)->notNull(),
                'lastname' => $this->string(150)->notNull(),
                'email' => $this->string(255)->notNull(),
                'rolle' => "ENUM('admin', 'user') NOT NULL DEFAULT 'user'",
                'aktiv' => $this->tinyInteger(1)->notNull()->defaultValue(1),
                'erstellt_am' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            ], $options);
        }

        if (!$this->tableExists('fachbereich')) {
            $this->createTable('fachbereich', [
                'id' => 'INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
                'bezeichnung' => $this->string(100)->notNull()->unique(),
                'farbe' => $this->string(7)->null(),
            ], $options);
        }

        if (!$this->tableExists('produkt')) {
            $this->createTable('produkt', [
                'id' => 'INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
                'name' => $this->string(150)->notNull(),
                'barcode' => $this->string(64)->null()->unique(),
                'standort' => $this->string(150)->null(),
                'fachbereich_id' => 'INT UNSIGNED NULL',
                'quantitaet' => $this->integer()->notNull()->defaultValue(0),
                'mindestbestand' => $this->integer()->notNull()->defaultValue(0),
                'erstellt_am' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
                'aktualisiert_am' => 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            ], $options);
            // FK produkt -> fachbereich is added by m260826_133236_add_farbe_to_fachbereich
        }

        if (!$this->tableExists('bestand_bewegung')) {
            $this->createTable('bestand_bewegung', [
                'id' => 'INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
                'produkt_id' => 'INT UNSIGNED NOT NULL',
                'benutzer_id' => 'INT UNSIGNED NOT NULL',
                'delta' => $this->integer()->notNull(),
                'bestand_nach' => $this->integer()->notNull(),
                'gebucht_am' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            ], $options);

            $this->addForeignKey('fk-bestand_bewegung-produkt_id', 'bestand_bewegung', 'produkt_id', 'produkt', 'id', 'RESTRICT');
            $this->addForeignKey('fk-bestand_bewegung-benutzer_id', 'bestand_bewegung', 'benutzer_id', 'benutzer', 'id', 'RESTRICT');
            $this->createIndex('idx_produkt_zeit', 'bestand_bewegung', ['produkt_id', 'gebucht_am']);
            $this->createIndex('idx_benutzer_zeit', 'bestand_bewegung', ['benutzer_id', 'gebucht_am']);
        }
    }

    public function safeDown()
    {
        echo "m260801_000000_initial_schema cannot be reverted (would drop all inventory data).\n";
        return false;
    }

    private function tableExists(string $name): bool
    {
        return $this->db->getTableSchema($name, true) !== null;
    }
}
