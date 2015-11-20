
@echo off
	set objNote=->
	echo %objNote%
FOR /F "tokens=1 delims=" %%G IN (%1) DO (
		rem current environment
		echo -----------------------------------------
	 	echo SCAN FOR  %%G  in %cmFivePath%/system
	 	grep -c -r \>%%G %cmFivePath%/system/templates | grep -v system/tests | grep -v "system/docs"| grep -v system/composer |grep -v ":0$"
)	
