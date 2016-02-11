<?php 
require_once('lib'.DIRECTORY_SEPARATOR.'Diff.php');
/*****************************
 * Tools for working with the file system
 *****************************/

class FileSystemTools {
	
	static $webServerUser='www-data';
	
	static function setPermissionsAllowEveryone($path) {
	//	echo TestConfig::getConfig('testRunnerPath').DS.'fixfilepermissions.bat '.$path;
		//die();
	//	print_r(array('FIX FILE PERMS',$path,exec(TestConfig::getConfig('testRunnerPath').DS.'fixfilepermissions.bat '.$path)));
		
	}
	
	/***************************************
	 * Return any lines that have been added since the snapshot was taken
	 * @param $snapshot  - last 30 lines of original file contents
	 * @param $filepath - $filename to compare contents
	 ***************************************/
	static function checkChangesToFile($snapshot,$filepath) {
		$lines=array();
		if (is_file($filepath)) {
			$fileNow=FileSystemTools::tail($filepath,30);
			if (strlen($fileNow)>0 && strlen($snapshot)>0) {
				$diff = Diff::compare($snapshot,$fileNow);
				if (count($diff[Diff::INSERTED])>0) {
					foreach($diff as $dk =>$dv) {
						if ($dv[1]==Diff::INSERTED) {
							if (strlen(trim($dv[0]))>0) $lines[]=$dv[0];
						}
					}
				}
			}	
		}
		return $lines;
	}
	
	
	/***************************************
	 * Return tailing lines from a file
	 * @param $filepath - path to file
	 * @param $lines - maximum number of  lines to return
	 * @param $adaptive - adapt the size of each chunk read from a file based on the number of lines requested
	 * 
	 ****************************************/
	static function tail($filepath, $lines = 1, $adaptive = true) {
		if (file_exists($filepath))  {
			// Open file
			$f = @fopen($filepath, "rb");
			if ($f === false) return false;
			// Sets buffer size
			if (!$adaptive) $buffer = 4096;
			else $buffer = ($lines < 2 ? 64 : ($lines < 10 ? 512 : 4096));
			// Jump to last character
			fseek($f, -1, SEEK_END);
			// Read it and adjust line number if necessary
			// (Otherwise the result would be wrong if file doesn't end with a blank line)
			if (fread($f, 1) != "\n") $lines -= 1;
			// Start reading
			$output = '';
			$chunk = '';
			// While we would like more
			while (ftell($f) > 0 && $lines >= 0) {
				// Figure out how far back we should jump
				$seek = min(ftell($f), $buffer);
				// Do the jump (backwards, relative to where we are)
				fseek($f, -$seek, SEEK_CUR);
				// Read a chunk and prepend it to our output
				$output = ($chunk = fread($f, $seek)) . $output;
				// Jump back to where we started reading
				fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);
				// Decrease our line counter
				$lines -= substr_count($chunk, "\n");
			}
			// While we have too many lines
			// (Because of buffer size we might have read too many)
			while ($lines++ < 0) {
				// Find first newline and remove all text before that
				$output = substr($output, strpos($output, "\n") + 1);
			}
			// Close file and return
			fclose($f);
			return trim($output);
		}
	}
	
	/*****************************
	 * Recursively copy a folder to a destination path
	 * @return Array (of copied files)
	 *****************************/
	static function copyRecursive( $path,$dest,$perms=null) {
	//	echo "COPY REC ".$path." to ".$dest."\n";
		@mkdir( $dest ,0777,true);
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
						if (!empty($perms)) chmod($dest.DS.$file,$perms);
						@chgrp($dest.DS.$file,FileSystemTools::$webServerUser);
					}
				}
			}
		} 
	}

	/*****************************
	 * Recursively delete a folder
	 *****************************/
	static function rmdirRecursive($dir) { 
	//	echo "RMDIR REC ".$dir."\n";
		if (is_dir($dir)) { 
			FileSystemTools::setPermissionsAllowEveryone($dir);
		
	//		echo "RMDIR REC ISDIR  ".$dir."\n";
			$files = array_diff(scandir($dir), array('.','..')); 
	//		echo "RMDIR REC FILES"."\n";
	//		print_r($files);
			foreach ($files as $file) { 
	//			echo "RMDIR REC ".$file."\n"; 
				if (is_dir($dir.DS.basename($file))) {
	//				echo "RMDIR REC ITER IS DIR ".$file."\n";
					FileSystemTools::rmdirRecursive($dir.DS.basename($file)) ;
				} else { 
	//				echo "RMDIR REC ITER IS FILE ".$file."\n";
					unlink($dir.DS.basename($file)); 
				}
			} 
	//		echo "RMDIR NOW REMOVE ".$dir."\n";
			return rmdir($dir); 
		}
	}
/*****************************
	 * Recursively delete everything inside a folder
	 *****************************/
	static function prune($dir) { 
	//	echo "PRUNE ".$dir."\n";
		if (is_dir($dir)) { 
		//	echo "PRUNE IS DIR ".$dir."\n";
			foreach(glob($dir . '/*') as $file)   { 
			//	echo "PRUNE INNER ".$file."\n";
				if(is_dir($dir.DS.basename($file))) {
				//	echo "PRUNE ISDIR  ".$dir.DS.basename($file)."\n";
					FileSystemTools::rmdirRecursive($dir.DS.basename($file)); 
				} else {
					//echo "PRUNE IS FILE".$dir.DS.basename($file)."\n";
					unlink($dir.DS.basename($file));
				}
			} 
	   } 
	}
	/**
	 * 
	 * Find the relative file system path between two file system paths
	 *
	 * @param  string  $frompath  Path to start from
	 * @param  string  $topath    Path we want to end up in
	 *
	 * @return string             Path leading from $frompath to $topath
	 */
	static function findRelativePath ( $frompath, $topath ) {
		$from = explode( DIRECTORY_SEPARATOR, $frompath ); // Folders/File
		$to = explode( DIRECTORY_SEPARATOR, $topath ); // Folders/File
		$relpath = '';

		$i = 0;
		// Find how far the path is the same
		while ( isset($from[$i]) && isset($to[$i]) ) {
			if ( $from[$i] != $to[$i] ) break;
			$i++;
		}
		$j = count( $from ) - 1;
		// Add '..' until the path is the same
		while ( $i <= $j ) {
			if ( !empty($from[$j]) ) $relpath .= '..'.DIRECTORY_SEPARATOR;
			$j--;
		}
		// Go to folder from where it starts differing
		while ( isset($to[$i]) ) {
			if ( !empty($to[$i]) ) $relpath .= $to[$i].DIRECTORY_SEPARATOR;
			$i++;
		}
		
		// Strip last separator
		return substr($relpath, 0, -1);
	}
}
