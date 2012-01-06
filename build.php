#!/usr/bin/env php
<?php
@unlink('file.tar');
@unlink('template.tar');
@unlink('timwolla.wcf.chat.tar');
exec('coffee -cb file/js/*.coffee');
chdir('file');
exec('tar cvf ../file.tar * --exclude=*.coffee');
chdir('..');
chdir('template');
exec('tar cvf ../template.tar *');
chdir('..');
exec('tar cvf timwolla.wcf.chat.tar * --exclude=file --exclude=template --exclude=build.php');
@unlink('file.tar');
@unlink('template.tar');
exec('rm file/js/*.js');