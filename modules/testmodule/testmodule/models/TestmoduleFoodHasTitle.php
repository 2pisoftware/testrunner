<?php
/**
 * Test module DbObject for testing 
 * 
 * @author Steve Ryan November 2015
 */
class TestmoduleFoodHasTitle extends DbObject {
	
	// object properties
	public static $_db_table='patch_testmodule_food_has_title';
	// public $id; <-- this is defined in the parent class
	public $data;
	public $title;

	static $_title_ui_select_objects_class = ""; //"Contact";
	static $_title_ui_select_objects_filter = []; //array("is_deleted"=>0);
	

}
