DIR=`dirname $0`
php -f $DIR/index.php $@
echo "EXIT CODE: $?"
exit $?
