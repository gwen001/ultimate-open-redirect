#!/bin/bash


bash_source=$(ls -ld ${BASH_SOURCE[0]})
source_dir=$(echo $bash_source | awk -F "-> " '{print $2}')
if [ ${#source_dir} -gt 0 ] ; then
  source_dir=$(dirname $source_dir)"/"
else
  source_dir=$(dirname $(echo $bash_source | awk -F " " '{print $(NF)}'))"/"
fi


function usage() {
    echo "Usage: "$0" <victim url> <hacker url>"
    if [ -n "$1" ] ; then
		echo "Error: "$1"!"
    fi
    exit
}


if [ ! $# -eq 2 ] ; then
    usage
fi

victim=$1
hacker=$2
tmpfile="/tmp/$(date +%s).dat"

php $source_dir"payloads_generator.php" $victim $hacker > $tmpfile
php $source_dir"test_open_redirect.php" $tmpfile $hacker

rm $tmpfile
