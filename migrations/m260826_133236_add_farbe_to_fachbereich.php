<?php

use yii\db\Migration;

class m260826_133236_add_farbe_to_fachbereich extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE fachbereich MODIFY id INT UNSIGNED NOT NULL AUTO_INCREMENT");

        $this->addForeignKey(
            'fk-produkt-fachbereich_id',
            'produkt',
            'fachbereich_id',
            'fachbereich',
            'id',
            'SET NULL'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-produkt-fachbereich_id', 'produkt');
        $this->alterColumn('fachbereich', 'id', $this->primaryKey());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260826_133236_add_farbe_to_fachbereich cannot be reverted.\n";

        return false;
    }
    */
}
