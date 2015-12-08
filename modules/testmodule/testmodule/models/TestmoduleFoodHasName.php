<?php
/**
 * Test module DbObject for testing 
 * 
 * @author Steve Ryan November 2015
 */
class TestmoduleFoodHasName extends DbObject {
	
	// object properties
	public $_db_table='patch_testmodule_food_has_name';
	// public $id; <-- this is defined in the parent class
	public $data;
	public $name;
	
	static $_title_ui_select_lookup_code = ""; //"states";
	
}
