#!/bin/bash
gid=$1
pi=$2
user=$3

ssh root@core-server.igb.illinois.edu "mkcoredir -g $gid -p $pi -u $user"