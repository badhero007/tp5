#!/bin/bash
project_dir=$(cd "$(dirname "$0")"; cd "../"; pwd)
#扫描注册过滤器
php $project_dir/think tcpReload