<?php
require('composer\vendor\autoload.php');

use Symfony\Component\Yaml\Yaml;

if (!defined('DS'))  define('DS', DIRECTORY_SEPARATOR);

//print_r( $argv);
//die();

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
require(getScriptFolder().DS.'lib'.DS.'Spyc.php');



$array = Yaml::parse(file_get_contents(getScriptFolder().DS.'codeception.template.yml'));

print Yaml::dump($array);
