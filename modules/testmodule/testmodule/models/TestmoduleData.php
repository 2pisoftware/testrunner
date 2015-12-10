<?php
/**
 * Test module DbObject for testing 
 * 
 * @author Steve Ryan November 2015
 */
class TestmoduleData extends DbObject {
	
	// object properties
	
	// public $id; <-- this is defined in the parent class
	public $title;
	public $data;
	public $d_last_known;
	public $t_killed;
	public $dt_born;
	public $s_data;
	private $_flagField=false;
	
	static $_title_ui_select_strings = []; //array("option1","option2");
	
	public static $_validation = [];
	
	public function afterConstruct() {
		$this->_flagField=true;
	}
	
	public function addToIndex() {
		$content='about <b>NOW</bb>'."\n\n*&^%$".'interestingly The   thErN     TESTMODULEDATA::';
		$content.=$this->id;
		$content.=' above';
		return $content;
	}
}
