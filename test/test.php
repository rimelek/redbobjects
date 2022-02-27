<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Rimelek\REDBObjects\MySQL\IsMySQLClass;
use Rimelek\REDBObjects\MySQL\IsMySQLListClass;
use Rimelek\REDBObjects\REDBObjects;

$db = REDBObjects::createDatabaseConnection('mysql:host=db;port=3306;dbname=app;charset=utf8', 'app', 'password');
REDBObjects::setConnection($db);

$rankFields = ['prefix_ranks as ranks' => ['*']];

$owner = new IsMySQLClass($rankFields);
$owner->init(['rankid' => 1]);
echo "$owner->name\n";

$admin = new IsMySQLClass($rankFields);
$admin->init("prefix_ranks as ranks where varname = 'admin'");
echo "$admin->name\n";

$ranks = new IsMySQLListClass($rankFields);
$test = new IsMySQLClass($rankFields);
$test->init(['varname' => 'test']);

if (empty($test->varname)) {
    $test->varname = 'test';
    $test->name = 'Test';
    $ranks->add($test);
}

echo "$test->name\n";

$test->name = "Test Rank";
echo "$test->name\n";
$test->update(false);
echo "$test->name\n";

// Change the name back so the next update with DB refresh can detect the change
$test->name = "Test";
$test->update();

$test->name = "Test Rank";
$test->update();
echo "$test->name\n";

$ranks->delete('varname', 'test');

$ranks->init('prefix_ranks as ranks order by name');

echo "\nList ranks: \n";
foreach ($ranks as $rank) {
    echo $rank->name . "\n";
}


