<?php

class FileSystemToolsTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitGuy
     */
    protected $guy;
    
    public $tmpPath1='/tmp/testing1';
    public $tmpPath2='/tmp/testing2';
    
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
    public function testCopyRecursive()  {
		$this->guy->createTestFolderTree($this->tmpPath1);
		$this->assertTrue($this->guy->isTestFolderTree($this->tmpPath1));
		FileSystemTools::copyRecursive($this->tmpPath1,$this->tmpPath2);
		$this->assertTrue($this->guy->isTestFolderTree($this->tmpPath2));
    }
    
    public function testRmDirRecursive() {
		$this->guy->createTestFolderTree($this->tmpPath1);
		$this->assertTrue($this->guy->isTestFolderTree($this->tmpPath1));
		FileSystemTools::rmDirRecursive($this->tmpPath1);
		$this->assertFalse(is_dir($this->tmpPath1));
	}
	
	
	public function testPrune() {
		$this->guy->createTestFolderTree($this->tmpPath1);
		$this->assertTrue($this->guy->isTestFolderTree($this->tmpPath1));
		FileSystemTools::prune($this->tmpPath1);
		$this->assertTrue(is_dir($this->tmpPath1));
		$this->assertFalse($this->guy->isTestFolderTree($this->tmpPath1));
	}

	public function testGetScriptFolder() {
		
	}
	
}
