#!/usr/bin/env php
<?php
namespace be\bastelstu\wcf\chat;
/**
 * Builds the Chat
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.chat
 */
$packageXML = file_get_contents('package.xml');
preg_match('/<version>(.*?)<\/version>/', $packageXML, $matches);
echo "Building Tims Chat $matches[1]\n";
echo str_repeat("=", strlen("Building Tims Chat $matches[1]"))."\n";

echo <<<EOT
Cleaning up
-----------

EOT;
	if (file_exists('package.xml.old')) {
		file_put_contents('package.xml', file_get_contents('package.xml.old'));
		unlink('package.xml.old');
	}
	if (file_exists('file.tar')) unlink('file.tar');
	if (file_exists('template.tar')) unlink('template.tar');
	if (file_exists('acptemplate.tar')) unlink('acptemplate.tar');
	foreach (glob('file/acp/be.bastelstu.chat.nodePush/lib/*.js') as $nodeFile) unlink($nodeFile);
	foreach (glob('file/js/*.js') as $jsFile) unlink($jsFile);
	if (file_exists('be.bastelstu.chat.tar')) unlink('be.bastelstu.chat.tar');
echo <<<EOT

Building JavaScript
-------------------

EOT;
foreach (glob('file/js/*.{litcoffee,coffee}', GLOB_BRACE) as $coffeeFile) {
	echo $coffeeFile."\n";
	passthru('coffee -cb '.escapeshellarg($coffeeFile), $code);
	if ($code != 0) exit($code);
}
foreach (glob('file/acp/be.bastelstu.chat.nodePush/lib/*.{litcoffee,coffee}', GLOB_BRACE) as $coffeeFile) {
	echo $coffeeFile."\n";
	passthru('coffee -cb '.escapeshellarg($coffeeFile), $code);
	if ($code != 0) exit($code);
}
echo <<<EOT

Checking PHP for Syntax Errors
------------------------------

EOT;
	chdir('file');
	$check = null;
	$check = function ($folder) use (&$check) {
		if (is_file($folder)) {
			if (substr($folder, -4) === '.php') {
				passthru('php -l '.escapeshellarg($folder), $code);
				if ($code != 0) exit($code);
			}
			
			return;
		}
		$files = glob($folder.'/*');
		foreach ($files as $file) {
			$check($file);
		}
	};
	$check('.');
echo <<<EOT

Building file.tar
-----------------

EOT;
	passthru('tar cvf ../file.tar * --exclude=*.coffee --exclude=*.scss --exclude=.sass-cache --exclude=node_modules', $code);
	if ($code != 0) exit($code);
echo <<<EOT

Building template.tar
---------------------

EOT;
	chdir('../template');
	passthru('tar cvf ../template.tar *', $code);
	if ($code != 0) exit($code);
echo <<<EOT

Building acptemplate.tar
------------------------

EOT;
	chdir('../acptemplate');
	passthru('tar cvf ../acptemplate.tar *', $code);
	if ($code != 0) exit($code);
echo <<<EOT

Building be.bastelstu.chat.tar
------------------------------

EOT;
	chdir('..');
	file_put_contents('package.xml.old', file_get_contents('package.xml'));
	file_put_contents('package.xml', preg_replace('~<date>\d{4}-\d{2}-\d{2}</date>~', '<date>'.date('Y-m-d').'</date>', file_get_contents('package.xml')));
	file_put_contents('package.xml', str_replace('</version>', '</version><!-- git id '.trim(shell_exec('git describe --always')).' -->', file_get_contents('package.xml')));
	passthru('tar cvf be.bastelstu.chat.tar * --exclude=*.old --exclude=file --exclude=template --exclude=acptemplate --exclude=contrib', $code);
	if (file_exists('package.xml.old')) {
		file_put_contents('package.xml', file_get_contents('package.xml.old'));
		unlink('package.xml.old');
	}
	if ($code != 0) exit($code);

if (file_exists('file.tar')) unlink('file.tar');
if (file_exists('template.tar')) unlink('template.tar');
if (file_exists('acptemplate.tar')) unlink('acptemplate.tar');
foreach (glob('file/acp/be.bastelstu.chat.nodePush/lib/*.js') as $nodeFile) unlink($nodeFile);
foreach (glob('file/js/*.js') as $jsFile) unlink($jsFile);
