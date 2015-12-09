<?php
ob_start();
echo "<pre>";

echo "<hr>";
echo "<hr>";
echo  $_SERVER['SERVER_NAME'].' at '.date('l jS \of F Y h:i:s A',$_SERVER['REQUEST_TIME']);
//print_r($_SERVER);
echo "<hr>";
echo "<hr>";
$b=json_decode(@file_get_contents('php://input'));
$h=getallheaders();
print_r($b);
echo "<hr>";
print_r($h);
echo "</pre>";

$repo='';
$user='';
$doCheckout=false;
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
	ob_end_clean();
	readfile('hooklog.txt');
}   
if ($doCheckout) {
	print_r(['REPO',$repo,'USER',$user]);
	// git update local 
	require_once(__DIR__.'/Git.php');
	$repos = Git::open('/var/www/projects/'.$repo."/dev");
	echo "pull"; 
	print_r([$repos->pull()]);

	// run tests
	// prep environment
	$envLines=file('/var/www/projects/'.$repo."/environment.csv");
	foreach ($envLines as $line) {
		$parts=explode(",",$line);
		if (count($line) && strlen(trim($line[0]))>0 && strlen(trim($line[1]))>0)  {
			putenv($line[0]."=".$line[1]);
		}
	}

	//print_r([exec('php '.__DIR__.'/../index.php')]);
	require(__DIR__.'/../index.php');
	// on failure of tests send email to repository owner and 
}
// on failure of tests send email to repository owner and 
$content=ob_get_contents();
ob_end_clean();
file_put_contents('hooklog.txt',$content,FILE_APPEND);

?>
