<?php
$output=[];
$output[]='cmFive Installer';

if (!defined('DS'))  define('DS', DIRECTORY_SEPARATOR);


//print_r( $argv);
//die();

$legalParameters=['cmFivePath','port','driver','hostname','username','password','database','adminUsername','adminPassword','adminFirstName','adminLastName','adminEmail'];
$requiredParameters=['cmFivePath','database','adminUsername','adminPassword','adminFirstName','adminLastName','adminEmail'];
$defaultParameters=['port'=>'','driver'=>'mysql','hostname'=>'localhost','username'=>'root','password'=>'','adminUsername'=>'admin','adminPassword'=>'admin','adminFirstName'=>'AdminFirst','adminLastName'=>'AdminLast','adminEmail'=>'admin@here.com'];

$config=$defaultParameters;  // combine environment variables with arguments keyed as argumentName:value

$testRunnerPath=dirname(__FILE__);

if (php_sapi_name() == 'cli') {
	foreach($legalParameters as $pKey =>$parameterName) {
		if (!empty(getenv($parameterName))) {
			$config[$parameterName]=getenv($parameterName);
		}
	}
	// set any legal parameters
	foreach($argv as $aKey =>$argument) {
		$argumentParts=explode(':',$argument);
		if (in_array(trim($argumentParts[0]),$legalParameters)) {
			$config[$argumentParts[0]]=implode(':',array_slice($argumentParts,1));
		}
	}
	$haveAllRequired=true;
	foreach ($requiredParameters as $rk => $parameterName) {
		if (empty($config[$parameterName])) {
			$output[]='Missing required argument '.$parameterName;
			$haveAllRequired=false;
		}
	}
	$output=array_merge($output,array(print_r($config)));
	
	if (!$haveAllRequired) {
		echo implode("\n",$output);
		return;
	}
	
	// now we have all required parameters, 
	
	
	chdir($config['cmFivePath']);
	
	require_once('system'.DS.'db.php');
	require_once('system'.DS.'modules'.DS.'install'.DS.'models'.DS.'InstallService.php');
	require_once('system'.DS.'modules'.DS.'auth'.DS.'models'.DS.'User.php');

	// map parameters
	$installConf=array();
	$installConf['application_name']='cmFive Test App';
	$installConf['company_name']='2PI Software';
	$installConf['company_url']='http://2pisoftware.com';
	$installConf['timezone']='Australia/Sydney';
	$installConf['db_hostname']=$config['hostname'];
	$installConf['db_username']=$config['username'];
	$installConf['db_password']=$config['password'];
	$installConf['db_database']=$config['database'];
	$installConf['db_driver']=$config['driver'];
	$installConf['email_layer']='smtp';
	$installConf['email_host']='';
	$installConf['email_port']='';
	$installConf['email_auth']='';
	$installConf['email_username']='';
	$installConf['email_password']='';
	$installConf['checkCSRF']=true;
	$installConf['allow_from_ip']='';
	$installConf['rest_api_key']='abcdefghijklmnopqrstuv';
	
// write config file and clear cache
	InstallService::writeConfig($installConf);
	unlink('cache'.DS.'config.cache');

	// run db install scripts
	$installResult=InstallService::runInstallSql(InstallService::getConnection($config['port'],$config['driver'],$config['hostname'],$config['username'],$config['password'],$config['database']));
	
	$createAdminUserResult=InstallService::createAdminUser(InstallService::getConnection($config['port'],$config['driver'],$config['hostname'],$config['username'],$config['password'],$config['database']),$config['adminUsername'],$config['adminPassword'],$config['adminFirstName'],$config['adminLastName'],$config['adminEmail']) ;
	
	$output=array_merge($output,$installResult['errors']);
	$output=array_merge($output,$installResult['output']);
	$output=array_merge($output,$createAdminUserResult['errors']);
	$output=array_merge($output,$createAdminUserResult['output']);


	
	// FINALLY RENDER OUTPUT
	echo implode("\n",$output);

}


