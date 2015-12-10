<?php
/**
 * Test module DbObject for testing 
 * 
 * @author Steve Ryan November 2015
 */
class TestmoduleFoodNoLabel extends DbObject {
	
	// object properties
	
	// public $id; <-- this is defined in the parent class
	public $data;
	public $d_last_known;
	public $t_killed;
	public $dt_born;
	public $s_data;
	private $_flagField=false;
	
	public function afterConstruct() {
		$this->_flagField=true;
	}
	
	public function canView() {
		return false;
	}

}
