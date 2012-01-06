#!/usr/bin/env php
<?php
/**
 * Builds the Chat
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2012 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	timwolla.wcf.chat
 */
echo <<<EOT
Cleaning up
-----------

EOT;
	if (file_exists('file.tar')) unlink('file.tar');
	if (file_exists('template.tar')) unlink('template.tar');
	foreach (glob('file/js/*.js') as $jsFile) unlink($jsFile);
	foreach (glob('file/style/*.css') as $cssFile) unlink($cssFile);
	if (file_exists('timwolla.wcf.chat.tar')) unlink('timwolla.wcf.chat.tar');
echo <<<EOT

Building JavaScript
-------------------

EOT;
foreach (glob('file/js/*.coffee') as $coffeeFile) {
	echo $coffeeFile."\n";
	passthru('coffee -cb '.escapeshellarg($coffeeFile));
}
echo <<<EOT

Building CSS
------------

EOT;
foreach (glob('file/style/*.scss') as $sassFile) {
	echo $sassFile."\n";
	passthru('scss '.escapeshellarg($sassFile).' '.escapeshellarg(substr($sassFile, 0, -4).'css'));
}
echo <<<EOT

Building file.tar
-----------------

EOT;
	chdir('file');
	passthru('tar cvf ../file.tar * --exclude=*.coffee');
echo <<<EOT

Building template.tar
---------------------

EOT;
	chdir('../template');
	passthru('tar cvf ../template.tar *');
echo <<<EOT

Building timwolla.wcf.chat.tar
------------------------------

EOT;
	chdir('..');
	passthru('tar cvf timwolla.wcf.chat.tar * --exclude=file --exclude=template --exclude=build.php');

if (file_exists('file.tar')) unlink('file.tar');
if (file_exists('template.tar')) unlink('template.tar');
foreach (glob('file/js/*.js') as $jsFile) unlink($jsFile);
foreach (glob('file/style/*.css') as $cssFile) unlink($cssFile);
