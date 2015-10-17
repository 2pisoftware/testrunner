FOR /F "tokens=1,2 delims=," %%G IN (environment.csv) DO (
	rem current environment
	set %%G=%%H
	rem persistent global environment
	setx /M %%G %%H
)

rem force IIS to read updated environment variables
elevate c:\windows\system32\iisreset.exe
