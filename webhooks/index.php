<?php
if (!function_exists('getallheaders')) 
{ 
    function getallheaders() 
    { 
           $headers = ''; 
       foreach ($_SERVER as $name => $value) 
       { 
           if (substr($name, 0, 5) == 'HTTP_') 
           { 
               $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value; 
           } 
       } 
       return $headers; 
    } 
} 

@mkdir ("/tmp/docker-manager/jobs",0777,true);

// DECODE THE BODY AND HEADERS FROM THE WEBHOOK
$repo='';
$user='';
$gitId='';
$b=json_decode(@file_get_contents('php://input'));
$h=getallheaders();

if (array_key_exists('X-Github-Event',$h) && $h['X-Github-Event']==='push') {
	$repo=$b->repository->name;
	$user=$b->pusher->email;
	$gitId=$b->after;
	
} else if (array_key_exists('X-Event-Key',$h) &&  $h['X-Event-Key']==='repo:push') {
	$repo=$b->repository->name;
	$user=''; //syntithenai@gmail.com';
	if (!empty($b) &&  !empty($b->push) && is_array($b->push->changes)) {
		foreach ($b->push->changes as $change) {
			foreach ($change->commits as $commit) {
				$user=$commit->author->raw;
			}
		}
	}
} else {
	echo "not enough data to run";
	//readfile('hooklog.txt');
}   
//print_r(['REPO',$repo,'USER',$user,'git',$gitId]);

// NOW WRITE THE JOB FILE
if (!empty($repo) && !empty($user) && !empty($gitId))  {
	try {
		$a=time();
		sleep(1);
		file_put_contents("/tmp/docker-manager/jobs/job".$a,'test	'.$repo.'	'.$gitId.'	'.$user."\n");
	} catch (Exception $e) {
		var_dump($e);
	}
}
