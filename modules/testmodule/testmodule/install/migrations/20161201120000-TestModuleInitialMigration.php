<?php

use \Phinx\Db\Adapter\MysqlAdapter;

class TestModuleInitialMigration extends CmfiveMigration {

	public function up() {
		$column = parent::Column();
		$column->setName('id')
				->setType('biginteger')
				->setIdentity(true);

		/**
		 * testmodule_data TABLE
		 */
		if (!$this->hasTable('testmodule_data')) {
			$this->table('testmodule_data', [
						'id' => false,
						'primary_key' => 'id'
					])->addColumn($column)
					->addColumn('title', 'string', ['limit' => 255])
					->addColumn('data', 'string', ['limit' => 255])
					->addColumn('s_data', 'string', ['limit' => 255])
					->addColumn('d_last_known', 'date')
					->addColumn('t_killed', 'time')
					->addColumn('dt_born', 'datetime')
					->addCmfiveParameters([])
					->create();
		}

		/**
		 * patch_testmodule_food_has_title TABLE
		 */
		if (!$this->hasTable('patch_testmodule_food_has_title')) {
			$this->table('patch_testmodule_food_has_title', [
						'id' => false,
						'primary_key' => 'id'
					])->addColumn($column)
					->addColumn('title', 'string', ['limit' => 255])
					->create();
		}
		
		/**
		 * patch_testmodule_food_has_name TABLE
		 */
		if (!$this->hasTable('patch_testmodule_food_has_name')) {
			$this->table('patch_testmodule_food_has_name', [
						'id' => false,
						'primary_key' => 'id'
					])->addColumn($column)
					->addColumn('name', 'string', ['limit' => 255])
					->create();
		}
		
		/**
		 * testmodule_food_no_label  TABLE
		 */
		if (!$this->hasTable('testmodule_food_no_label')) {
			$this->table('testmodule_food_no_label', [
						'id' => false,
						'primary_key' => 'id'
					])->addColumn($column)
					->addColumn('data', 'string', ['limit' => 255])
					->create();
		}
		
	}

	public function down() {
		$this->hasTable('testmodule_data') ? $this->dropTable('testmodule_data') : null;
		$this->hasTable('patch_testmodule_food_has_title') ? $this->dropTable('patch_testmodule_food_has_title') : null;
		$this->hasTable('patch_testmodule_food_has_name') ? $this->dropTable('patch_testmodule_food_has_name') : null;
		$this->hasTable('patch_testmodule_food_no_label') ? $this->dropTable('patch_testmodule_food_no_label') : null;
		
	}

}
