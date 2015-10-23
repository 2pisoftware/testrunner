@echo off
rem search for environment.%1.csv in current directory
rem otherwise search for %1 as a file 
if exist "%~dp0\environment.%1.csv" (
	FOR /F "tokens=1,2 delims=," %%G IN (%~dp0\environment.%1.csv) DO (
		rem current environment
	 	set %%G=%%H
		rem persistent global environment
	 	setx /M %%G %%H
	)
	rem these don't work when run from php
	rem force IIS to read updated environment variables
	rem elevate c:\windows\system32\iisreset.exe
	%~dp0\bin\psexec.exe -h c:\windows\system32\iisreset.exe
	rem TODO apache restart ??
) else if exist %1 (
	FOR /F "tokens=1,2 delims=," %%G IN (%1) DO (
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
) else (
	echo "No matching environment file matching  - %1"
)
