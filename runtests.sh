DIR=`dirname $0`
php -f $DIR/installCmFive.php $@
php -f $DIR/index.php $@
echo "EXIT CODE: $?"
exit $?
