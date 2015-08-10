<?php

require('vendor/autoload.php');

$test = new Jaybizzle\DeployBot('API_KEY', 'ACCOUNT');

$r = $test->users(['query' => ['limit' => 1]]);

var_dump($r);