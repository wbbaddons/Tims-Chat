#!/bin/sh

cd `dirname $0`
if [ -d "bin" ]; then
	cd "../"
fi

if [ "$(id -u)" -eq 0 ]; then
	echo "You may not start nodePush as root!"
	exit 1
fi

echo "Installing dependencies"
/usr/bin/env npm install

cd "../lib"

/usr/bin/env node server.js