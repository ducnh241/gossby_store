#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

if [ ! $1 ]
then
    echo "Please provide container folder"
    exit 1
fi

if [ ! -e $1 ]
then
    echo "The container folder is not exists"
    exit 1
fi

while IFS= read line
do
    line=$( echo $line| tr -d '\r' )
    if [[ "$line" =~ ^.+/([^\/]+)$ ]];
	then
		wget $line -O $1/${BASH_REMATCH[1]}
	fi    
done <"$1/label.txt"

#mogrify -resize 1000x $1/*.png
convert $1/*.png $1/output.pdf

echo "DONE"
