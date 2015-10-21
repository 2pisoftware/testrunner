@echo off
if exist "environment.%1.csv" (
	FOR /F "tokens=1,2 delims=," %%G IN (environment.%1.csv) DO (
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
	echo "No matching environment file environment.%1.csv"
)



:end
