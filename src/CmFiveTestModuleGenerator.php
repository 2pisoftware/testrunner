<?php 


/*****************************
 * Tools to help find and run tests in source code
 * Tests must be organised into suites where each suite is a folder
 * containing a *.suite.yml file (usually the same name as the folder)
 * The suite.yml file allows codeception modules to be enabled per suite
 * 
 * 
 *****************************/



class CmFiveTestModuleGenerator {
	
	var $ROOT_PATH='';

	// initialise path
	function __construct($path) {
		if (!is_dir($path)) throw new Exception('Invalid path creating module generator') ; 
		$this->ROOT_PATH=$path;
	}
	
	/*****************************************
	 * Return a list of files that cover all the permutations for templateExists
	 ****************************************/	
	function getTestTemplateFiles() {
		$paths=[
			'modules/testmodule/templates/submodule/testtemplate',
			'modules/testmodule/templates/submodule/get',
			'modules/testmodule/templates/submodule/edit',
			'modules/testmodule/templates/submodule/submodule',
			'modules/testmodule/templates/submodule/testmodule',
			
			'modules/testmodule/templates/testtemplate',
			'modules/testmodule/templates/get',
			'modules/testmodule/templates/edit',
			'modules/testmodule/templates/submodule',
			'modules/testmodule/templates/testmodule',
			
			'modules/testmodule/testtemplate',
			'modules/testmodule/get',
			'modules/testmodule/edit',
			'modules/testmodule/submodule',
			'modules/testmodule/testmodule',

			'system/modules/systestmodule/templates/submodule/testtemplate',
			'system/modules/systestmodule/templates/submodule/get',
			'system/modules/systestmodule/templates/submodule/edit',
			'system/modules/systestmodule/templates/submodule/submodule',
			'system/modules/systestmodule/templates/submodule/testmodule',
			
			'system/modules/systestmodule/templates/testtemplate',
			'system/modules/systestmodule/templates/get',
			'system/modules/systestmodule/templates/edit',
			'system/modules/systestmodule/templates/submodule',
			'system/modules/systestmodule/templates/testmodule',
			
			'system/modules/systestmodule/testtemplate',
			'system/modules/systestmodule/get',
			'system/modules/systestmodule/edit',
			'system/modules/systestmodule/submodule',
			'system/modules/systestmodule/testmodule',

			'templates/testmodule/testtemplate',
			'templates/testmodule/get',
			'templates/testmodule/edit',
			'templates/testmodule/submodule',
			'templates/testmodule/testmodule',

			'templates/testtemplate',
			'templates/get',
			'templates/edit',
			'templates/submodule',
			'templates/testmodule',

			'system/templates/testtemplate',
			'system/templates/get',
			'system/templates/edit',
			'system/templates/submodule',
			'system/templates/testmodule'
		];
		return $paths;
	}	

	function createTestTemplateFiles() {
		// prime from module template directories
		FileSystemTools::copyRecursive(getenv('thisTestRun_testRunnerPath').'/system/modules/systestmodule','system/modules');
		FileSystemTools::copyRecursive(getenv('thisTestRun_testRunnerPath').'/modules/testmodule','modules');
		
		
		$paths=$this->getTestTemplateFiles();
		foreach ($paths as $path) {
			$file=$this->ROOT_PATH."/".$path.".tpl.php";
			if (!is_dir(dirname($file))) {
				mkdir(dirname($file),0777,true);
			}
			file_put_contents($file,':::TEMPLATE:::'.$file."::");
		}
		// write module config
		file_put_contents($this->ROOT_PATH."/system/modules/systestmodule/config.php",'<'.'?php Config::set("systestmodule",["testing"=>"fred","active"=>true,"topmenu"=>false,"path"=>"system/modules","hooks"=>["systestmodule","core_web","core_dbobject"]]);');
		file_put_contents($this->ROOT_PATH."/modules/testmodule/config.php",'<'.'?php Config::set("testmodule",["testing"=>"fred","active"=>true,"topmenu"=>false,"path"=>"modules","hooks"=>["systestmodule","core_web","core_dbobject"]]);');
		// hooks file
		$content='<'.'?php';
		// web hooks
		foreach ([
		'testmodule_core_web_testhooks',
		'testmodule_core_web_testhooks_ping',
		'testmodule_core_web_testhooks_ping_testmodule',
		'testmodule_core_web_testhooks_ping_testmodule_submodule',
		'testmodule_core_web_testhooks_ping_testmodule_submodule_sleep',
		'testmodule_core_web_testhooks_ping_testmodule_sleep',
		] as $hookFunction) {
			$content.=' function '.$hookFunction.'($w,$a) { echo ":::HOOK:::'.$hookFunction.':::"; }'."\n";
		}
		// db hooks
		foreach ([
		'testmodule_core_dbobject_before_insert',
		'testmodule_core_dbobject_after_insert',
		'testmodule_core_dbobject_before_update',
		'testmodule_core_dbobject_after_update',
		'testmodule_core_dbobject_before_delete',
		'testmodule_core_dbobject_after_delete',
		
		'testmodule_core_dbobject_before_insert_TestmoduleData',
		'testmodule_core_dbobject_after_insert_TestmoduleData',
		'testmodule_core_dbobject_before_update_before_TestmoduleData',
		'testmodule_core_dbobject_after_update_TestmoduleData',
		'testmodule_core_dbobject_before_delete_TestmoduleData',
		'testmodule_core_dbobject_after_delete_TestmoduleData',
		'testmodule_core_dbobject_indexChange_TestmoduleData',
		] as $hookFunction) {
			$content.=' function '.$hookFunction.'($w,$a) { echo ":::DBHOOK:::'.$hookFunction.':::"; }'."\n";
		}
		// write hooks file
		 $content.=' function systestmodule_systestmodule_dostuff($w,$a) {echo ":::".$w->_module.":::".$a.":::stuff done";}'."\n";
		
		file_put_contents($this->ROOT_PATH."/system/modules/systestmodule/systestmodule.hooks.php",$content);
		// partial in testmodule
		@mkdir($this->ROOT_PATH."/modules/testmodule/mypartials");
		@mkdir($this->ROOT_PATH."/modules/testmodule/mypartials/actions");
		@mkdir($this->ROOT_PATH."/modules/testmodule/mypartials/templates");
		file_put_contents($this->ROOT_PATH."/modules/testmodule/mypartials/actions/testpartial.php",'<'.'?php '. 
		'function testpartial_ALL(Web $w,$params) { $w->ctx("partialvalue","thepartialvalue".$params["paramsvalue"]);} ;'
		);
		file_put_contents($this->ROOT_PATH."/modules/testmodule/mypartials/templates/testpartial.tpl.php",'<'.'?php '. 
		'echo "testpartial:::".$partialvalue.":::";'
		);
		
		// action and template for sys test module
		@mkdir($this->ROOT_PATH."/system/modules/systestmodule/actions");
		file_put_contents($this->ROOT_PATH."/system/modules/systestmodule/actions/fail.php",'<'.'?php '. 
		'function fail_ALL(Web $w) { $w->ctx("testactionvalue","failedthetest");} ;'
		);
		
		
		// action and template for test module
		@mkdir($this->ROOT_PATH."/modules/testmodule/actions");
		@mkdir($this->ROOT_PATH."/modules/testmodule/templates");
		file_put_contents($this->ROOT_PATH."/modules/testmodule/actions/testaction.php",'<'.'?php '. 
		'function testaction_ALL(Web $w) { $w->ctx("testactionvalue","thevalue");} ;'
		);
		file_put_contents($this->ROOT_PATH."/modules/testmodule/templates/testaction.tpl.php",'<'.'?php '. 
		'echo "testaction:::".$testactionvalue.":::";'
		);
		// minimum layout template
		file_put_contents($this->ROOT_PATH."/templates/minilayout.tpl.php",'<'.'?php '. 'echo "MINILAYOUT||"; echo !empty($body) ? $body : ""; echo "||"; ');
		
		
		
	}

	/*****************************
	 * Delete template files
	 *****************************/
	function removeTestTemplateFiles() {
		FileSystemTools::rmdirRecursive($this->ROOT_PATH."/system/modules/systestmodule");
		FileSystemTools::rmdirRecursive($this->ROOT_PATH."/modules/testmodule");
		foreach ([
			'templates/testmodule/testtemplate',
			'templates/testmodule/get',
			'templates/testmodule/edit',
			'templates/testmodule/submodule',
			'templates/testmodule/testmodule',

			'templates/testtemplate',
			'templates/get',
			'templates/edit',
			'templates/submodule',
			'templates/testmodule',

			'system/templates/testtemplate',
			'system/templates/get',
			'system/templates/edit',
			'system/templates/submodule',
			'system/templates/testmodule'] as $toRemove) {
				if (file_exists($this->ROOT_PATH.DIRECTORY_SEPARATOR.$toRemove.'.tpl.php')) {
					unlink($this->ROOT_PATH.DIRECTORY_SEPARATOR.$toRemove.'.tpl.php');
				}
		}
		if (file_exists($this->ROOT_PATH."/templates/minilayout.tpl.php"))  {
			unlink($this->ROOT_PATH."/templates/minilayout.tpl.php");
		}
	}	


	
}
