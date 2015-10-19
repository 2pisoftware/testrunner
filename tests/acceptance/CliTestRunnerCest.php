<?php
use \AcceptanceGuy;

class CliTestRunnerCest
{
	var $tmpStaging=DS.'tmp'.DS.'staging';
    public function _before(AcceptanceGuy $I)
    {
		//FileSystemTools::rmdirRecursive($this->tmpStaging);
		@mkdir($this->tmpStaging); //,0777,true);
    }

    public function _after(AcceptanceGuy $I)
    {
	//	FileSystemTools::rmdirRecursive($this->tmpStaging);
	}

    // tests
    public function tryToTest(AcceptanceGuy $I)
    {
		// need a copy of test folder
		//FileSystemTools::copyRecursive(TestConfig::getConfig('testPath'),$this->tmpStaging);
		
		//echo "<hr>";
		//echo TestConfig::getConfig('codeception');
		//echo "<hr>";
		//echo TestConfig::getConfig('testStagingPath');
		//echo "<hr>";
		//echo dirname(TestConfig::getConfig('testStagingPath'));
		//echo "<hr>";
		//echo 'php -f '.TestConfig::getConfig('codeception').' run ';
		//echo "<pre>";
		TestConfig::init();
		TestConfig::$config['testStagingPath']=$this->tmpStaging;
		//$I->runShellCommand('php -f '.TestConfig::getConfig('codeception').' run '.$this->tmpStaging);
		codecept_debug(TestConfig::$config);
		//die();
	
		//$I->runShellCommand(TestConfig::getConfig('codeception').' run '.dirname(TestConfig::getConfig('testStagingPath')));
		//$I->seeInShellOutput('Codeception PHP Testing Framework');
		//$I->seeInShellOutput('PhantomJS server stopped');
    }
}
