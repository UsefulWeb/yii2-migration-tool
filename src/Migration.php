<?php 

namespace UsefulWeb\MigrationTool


class Migration extends \yii\db\Migration
{
  
  public function getTables() {
    return [];
  }

  public function up()
  {
      $tableOptions = null;
      if ($this->db->driverName === 'mysql') {
          // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
          $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
      }

      
      $tables = $this->getTables();

      $foreign_keys = [

      ];

      foreach ($tables as $table_name => $columns) {
          foreach ($columns as $column_name => $column_data) {
              
              $index = strpos($column_name, '_id');

              if ($index !== false) {

                  $ref_table = substr($column_name, 0, $index);

                  $ref_column_name = in_array($column_name, ['site_id', 'block_id']) ? 'id' : 'block_id';

                  $foreign_keys[] = [$table_name, $column_name, $ref_table, $ref_column_name];
              }
          }
      }


      // var_dump($foreign_keys);

      foreach ($tables as $table_name => $columns) {

          $table = '{{%'.$table_name.'}}';

          if ($this->getDb()->getSchema()->getTableSchema($table) === null) {
              $this->createTable($table, $columns, $tableOptions);
          }
      }

      foreach ($foreign_keys as $key => $item) {
          try {
              $this->addForeignKey('FK_'.$item[0].'_'.$item[2],'{{%'.$item[0].'}}', $item[1], '{{%'.$item[2].'}}',$item[3]);   
          } catch (Exception $e) {
              return false;
          }
      }
  }

  public function down()
  {
     $tables = $this->getTables();

     $this->db->createCommand()->checkIntegrity(false)->execute();
     $prefix = $this->db->tablePrefix;
     
     foreach ($tables as $table_name => $table_data) {
         try {
             $tableSchema = $this->getDb()->getSchema()->getTableSchema('{{%'.$table_name.'}}');

             if ($tableSchema) {
                 
                 $this->dropTable('{{%'.$table_name.'}}');
             }

         } catch (Exception $e) {
             return false;
         }
     }

     $this->db->createCommand()->checkIntegrity(true)->execute();

     return true;
  }

}