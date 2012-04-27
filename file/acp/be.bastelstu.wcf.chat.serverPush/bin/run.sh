#!/bin/sh

cd `dirname $0`
if [ -d "bin" ]; then
	cd "../"
fi

if [ "$(id -u)" -eq 0 ]; then
	echo "You may not start the Push-Server as root!"
	exit 1
fi

echo "Installing dependencies"
npm install

cd "../lib"

/usr/bin/env node server.js