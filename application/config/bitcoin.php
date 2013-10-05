<?php

$config['ssl'] = false;			// Set up these!
$config['user'] = '';
$config['password'] = '';
$config['host'] = '';
$config['port'] = '';



// Leave these lines intact.
$config['ssl'] = ($config['ssl'] == TRUE) ? 'https://' : 'http://';
$config['url'] = $config['ssl'].$config['user'].':'.$config['password'].'@'.$config['host'].':'.$config['port'].'/';
