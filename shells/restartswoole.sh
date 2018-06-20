#!/bin/bash

project_dir=$(cd "$(dirname "$0")"; cd "../"; pwd)
count=`ps -fe |grep "tcpStart" | grep -v "grep" | wc -l`

if [ $count -gt 0 ];
then
/usr/local/Cellar/php70/7.0.15_8/bin/php $project_dir/think tcpStop
sleep 2
ulimit -c unlimited
fi

if [ $count -gt 0 ];
then
ps -eaf |grep "tcpStart" | grep -v "grep"| awk '{print $2}'|xargs kill -9
else
echo "["$(date +%Y-%m-%d_%H:%M:%S)"]"" TCP SERVER stop success";
fi

/usr/local/Cellar/php70/7.0.15_8/bin/php $project_dir/think tcpStart
echo "["$(date +%Y-%m-%d_%H:%M:%S)"]"" TCP SERVER restart success";
echo $(date +%Y-%m-%d_%H:%M:%S) >>/data/logs/swoole/restart.log