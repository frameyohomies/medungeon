<?php

use yii\db\Migration;

/**
 * Soft delete for products: products with stock history must never be deleted,
 * otherwise traceability is lost (and the FK from bestand_bewegung blocks it anyway).
 */
class m261023_000000_add_aktiv_to_produkt extends Migration
{
    public function safeUp()
    {
        $schema = $this->db->getTableSchema('produkt', true);
        if ($schema !== null && !isset($schema->columns['aktiv'])) {
            $this->addColumn('produkt', 'aktiv', $this->tinyInteger(1)->notNull()->defaultValue(1)->after('mindestbestand'));
        }
    }

    public function safeDown()
    {
        $this->dropColumn('produkt', 'aktiv');
    }
}
