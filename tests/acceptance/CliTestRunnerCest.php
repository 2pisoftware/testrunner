<?php
use \AcceptanceGuy;

class CliTestRunnerCest
{
	var $tmpStaging=DS.'tmp'.DS.'staging';
	var $path='';
    public function _before(AcceptanceGuy $I)
    {
		$this->path=getenv('testRunnerPath');
		require_once($this->path.DS."src".DS."FileSystemTools.php");
		require_once($this->path.DS."src".DS."TestConfig.php");
		require_once($this->path.DS."src".DS."TestRunner.php");
		require($this->path.DS.'composer'.DS.'vendor'.DS.'autoload.php');
		//FileSystemTools::rmdirRecursive($this->tmpStaging);
		@mkdir($this->tmpStaging,0777,true);
	}

    public function _after(AcceptanceGuy $I)
    {
	//	FileSystemTools::rmdirRecursive($this->tmpStaging);
	}

    // tests
    private function tryToTest(AcceptanceGuy $I)
    {
		// need a copy of test folder
		FileSystemTools::copyRecursive(TestConfig::getConfig('testPath'),$this->tmpStaging);
		$I->runShellCommand(TestConfig::getConfig('testRunnerPath').DS.'runtests.bat testStagingPath:'.$this->tmpStaging);
		
		$I->seeInShellOutput('Codeception PHP Testing Framework');
		//$I->seeInShellOutput('PhantomJS server stopped');
    }
}
