<?php
//echo "<pre>";

//echo "<hr>";
//echo "<hr>";
//echo  $_SERVER['SERVER_NAME'].' at '.date('l jS \of F Y h:i:s A',$_SERVER['REQUEST_TIME']);
//print_r($_SERVER);
//echo "<hr>";
//echo "<hr>";
//print_r($b);
//echo "<hr>";
//print_r($h);
//echo "</pre>";
$doCheckout=false;


$repo='';
$user='';
$b=json_decode(@file_get_contents('php://input'));
$h=getallheaders();
	
if (array_key_exists('X-GitHub-Event',$h) && $h['X-GitHub-Event']==='push') {
	$repo=$b->repository->name;
	$user=$b->pusher->email;
	$user='syntithenai@gmail.com';
	$doCheckout=true;
} else if (array_key_exists('X-Event-Key',$h) &&  $h['X-Event-Key']==='repo:push') {
	$repo=$b->repository->name;
	$user='syntithenai@gmail.com';
	$doCheckout=true;
} else {
	//readfile('hooklog.txt');
}   

ob_start();
if (false && $doCheckout) {
	try {
		print_r(['REPO',$repo,'USER',$user]);
		// git update local 
		//require_once(__DIR__.'/Git.php');
		//$repos = Git::open('/var/www/projects/'.$repo."/dev");
		//echo "pull"; 
		//print_r([$repos->pull()]);
		// run tests by placing job file
		$a=time();
		sleep(1);
		file_put_contents('/var/www/tools/testrunner/webhooks/jobs/'.$a,$repo.'	'.$user);
	} catch (Exception $e) {
		var_dump($e);
	}

}
var_dump($b);
// on failure of tests send email to repository owner and 
$content=ob_get_contents();
ob_end_clean();

file_put_contents('hooklog.txt','this is it:'.$content."\n",FILE_APPEND);


// for log 
//	 tail -f /var/www/tools/testrunner/webhooks/hooklog.txt & tail -f /var/log/apache2/error.log &
// as root
// cd /root/testrepository_bitbucket; echo "dd" >> readme.txt; git add .; git commit -m eek ; git push
?>

