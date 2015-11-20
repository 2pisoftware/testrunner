@echo off

FOR /F "tokens=1 delims=" %%G IN (%1) DO (
		rem current environment
		echo -----------------------------------------
	 	echo SCAN FOR  %%G  in %cmFivePath%/system and %cmFivePath%/modules
	 	
	 	grep -c -r "%%G" %cmFivePath%/system | grep -v system/tests | grep -v "system/docs"| grep -v system/composer | grep -v "system/templates" |grep -v ":0$"
	 	
	 	grep -c -r "%%G" %cmFivePath%/modules | grep -v system/tests | grep -v "system/docs"| grep -v system/composer | grep -v "system/templates" |grep -v ":0$"
)	
