USAGE='Usage: source setenvironment.sh <environmentFileOrReference>'
DIR=`dirname $0`
echo $DIR
oldifs=$IFS
IFS=,
if [ -z "$1" ] 
then
	echo $USAGE
else
	if [ -f "$DIR/environment.$1.csv" ]
	then
		echo "here"
		while read var val
		do
				#current environment
				echo "export $var=$val"
				export $var=$val
		done < environment.$1.csv
		#rem these don't work when run from php
		#rem force IIS to read updated environment variables
		#rem elevate c:\windows\system32\iisreset.exe
		#
		#%~dp0\bin\psexec.exe -h c:\windows\system32\iisreset.exe
		#rem TODO apache restart ??
	else 
		if [ -f $1 ]
		then
			while read var val
			do
				#current environment
				echo "export $var=$val"
				set $var=$val
				export $var=$val
			done < $1
		else
			echo "No matching environment file matching  - "
			echo $1
			echo $USAGE
		fi
	fi
fi
IFS=$oldifs
