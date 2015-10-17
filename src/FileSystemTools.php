<?php 
/*****************************
 * Tools for working with the file system
 *****************************/

class FileSystemTools {
	/*****************************
	 * Recursively copy a folder to a destination path
	 * @return Array (of copied files)
	 *****************************/
	static function copyRecursive( $path,$dest) {
		@mkdir( $dest );
		$tests=array();
		if( is_dir($path) ) {
			$objects = scandir($path);
			if( sizeof($objects) > 0 ) {
				foreach( $objects as $file ) {
					if( $file == "." || $file == ".." )
						continue;
					// go on
					if( is_dir( $path.DS.$file ) ) {
						FileSystemTools::copyRecursive( $path.DS.$file ,$dest.DS.$file );
					} else {
						copy( $path.DS.$file, $dest.DS.$file );
					}
				}
			}
		} 
	}

	/*****************************
	 * Recursively delete a folder
	 *****************************/
	function rmdirRecursive($dir) { 
		if (is_dir($dir)) { 
			$files = array_diff(scandir($dir), array('.','..')); 
			foreach ($files as $file) { 
			  (is_dir("$dir/$file")) ? FileSystemTools::rmdirRecursive("$dir/$file") : unlink("$dir/$file"); 
			} 
			return rmdir($dir); 
		}
	}
	/*****************************
	 * Recursively delete everything inside a folder
	 *****************************/
	function prune($dir) { 
		if (is_dir($dir)) { 
			foreach(glob($dir . '/*') as $file)   { 
				if(is_dir($dir.DS.basename($file))) {
					FileSystemTools::rmdirRecursive($dir.DS.basename($file)); 
				} else {
					unlink($dir.DS.basename($file));
				}
			} 
	   } 
	}

	/**********************************
	 * Determine the path to the currently running php script
	 * Normalises the path to allow for the case where getcwd()
	 * returns the directory the script was called from rather than
	 * the actual path to the script.
	 **********************************/
	function getScriptFolder() {
		global $argv;
		// web server case
		$dir=getcwd();
		// cli case
		if (strlen(trim($argv[0]))>0) {
			$dir = dirname(getcwd() . DS . $argv[0]);
			$curDir = getcwd();
			chdir($dir);
			$dir = getcwd();
			chdir($curDir);
		}
		return $dir;
	}
	
}
