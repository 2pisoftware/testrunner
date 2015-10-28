@echo off
IF [%1]==[] GOTO error


rem check for admin permissions
%~dp0\bin\psexec.exe -h ipconfig
set isAdmin=0
echo ERR %ERRORLEVEL%
IF %ERRORLEVEL% EQU 0 (
	goto adminsetlocalenvironmentvariables
)
echo "Need admin permissions to set global environment variables"
echo "Just setting local environment for this session"
goto setlocalenvironmentvariables


:adminsetlocalenvironmentvariables
rem search for environment.%1.csv in current directory
rem otherwise search for %1 as a file 
IF exist "%~dp0\environment.%1.csv" (
	FOR /F "tokens=1,2 delims=," %%G IN (%~dp0\environment.%1.csv) DO (
		rem current environment
	 	echo "set %%G=%%H"
	 	set %%G=%%H
		rem persistent global environment
		setx /M %%G %%H
	)
	rem these don't work when run from php
	rem force IIS to read updated environment variables
	rem elevate c:\windows\system32\iisreset.exe
	%~dp0\bin\psexec.exe -h c:\windows\system32\iisreset.exe
	rem TODO apache restart ??
) ELSE (
	IF exist "%1" (
		FOR /F "tokens=1,2 delims=," %%G IN (%1) DO (
			echo "set %%G=%%H"
	 		rem current environment
			set %%G=%%H
			rem persistent global environment
			setx /M %%G %%H
		)
		rem these don't work when run from php
		rem force IIS to read updated environment variables
		rem elevate c:\windows\system32\iisreset.exe
		bin\psexec.exe -h c:\windows\system32\iisreset.exe
		rem TODO apache restart ??
	) ELSE (
		echo "No matching environment file matching  - "
		echo %1
	)
)
GOTO end
:setlocalenvironmentvariables
IF exist "%~dp0\environment.%1.csv" (
	FOR /F "tokens=1,2 delims=," %%G IN (%~dp0\environment.%1.csv) DO (
	 	echo "set %%G=%%H"
	 	set %%G=%%H
	)
) ELSE (
	IF exist "%1" (
		FOR /F "tokens=1,2 delims=," %%G IN (%1) DO (
			echo "set %%G=%%H"
			set %%G=%%H
		)
	) ELSE (
		echo "No matching environment file matching  - "
		echo %1
	)
)
GOTO end


:error:
echo "Usage: setenvironment.bat <environment>"
:end
