<?php


class TestRunnerTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitGuy
     */
    protected $guy;

	public $tmpPath1=DS.'tmp'.DS.'testing1';
	public $tmpPath2=DS.'tmp'.DS.'testing2';

    protected function _before()
    {
		FileSystemTools::rmdirRecursive($this->tmpPath1);
		FileSystemTools::rmdirRecursive($this->tmpPath2);
    }

    protected function _after()
    {
		FileSystemTools::rmdirRecursive($this->tmpPath1);
		FileSystemTools::rmdirRecursive($this->tmpPath2);
    }

    // tests
    /**
     * 
	runTests
	findTestFolders
	isTestSuiteFolder
	isTestFile
	*/
    public function testRunTests()
    {
		$this->assertTrue(false); // FAIL
		$this->guy->createTestFolderTree($this->tmpPath1);
		$this->assertTrue($this->guy->isTestFolderTree($this->tmpPath1));
    }
    
    public function testFindTestFolders()
    {
		$this->assertTrue(false); // FAIL
		$this->guy->createTestFolderTree($this->tmpPath1);
		$this->assertTrue($this->guy->isTestFolderTree($this->tmpPath1));
    }
    
    public function testIsTestSuiteFolder()
    {
		$this->guy->createTestFolderTree($this->tmpPath1);
		$this->assertTrue($this->guy->isTestFolderTree($this->tmpPath1));
		
		$this->assertFalse(TestRunner::isTestSuiteFolder($this->tmpPath1.DS.'testFolder'));
		$this->assertTrue(TestRunner::isTestSuiteFolder($this->tmpPath1.DS.'testFolder'.DS.'subFolder1'));
		$this->assertTrue(TestRunner::isTestSuiteFolder($this->tmpPath1.DS.'testFolder'.DS.'subFolder2'));
		
		$this->assertFalse(TestRunner::isTestSuiteFolder($this->tmpPath1));
		$this->assertFalse(TestRunner::isTestSuiteFolder($this->tmpPath1.DS.'nonExistentFolder'));
    }
    
    public function testIsTestFile()
    {
		$this->guy->createTestFolderTree($this->tmpPath1);
		$this->assertTrue($this->guy->isTestFolderTree($this->tmpPath1));
		// real tests
		$this->assertTrue(TestRunner::isTestFile($this->tmpPath1.DS.'subFolder1'.DS.'unit'.DS.'funTest.php'));
		$this->assertTrue(TestRunner::isTestFile($this->tmpPath1.DS.'subFolder2'.DS.'unit'.DS.'boringTestCest.php'));
		$this->assertTrue(TestRunner::isTestFile($this->tmpPath1.DS.'subFolder2'.DS.'unit'.DS.'interestingTestCept.php'));
		// not real tests
		$this->assertFalse(TestRunner::isTestFile($this->tmpPath1.DS.'README.txt'));
		$this->assertFalse(TestRunner::isTestFile($this->tmpPath1.DS.'nonExistentFile.txt'));
	}
}
