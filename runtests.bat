@echo off
php -f %~dp0\installCmFive.php %*
php -f %~dp0\index.php %*
echo "EXIT CODE:%ERRORLEVEL%"
