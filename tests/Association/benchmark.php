<?php
require '../../vendor/autoload.php';

use TRW\DataMapper\Database\Driver\MySql;
use TRW\DataMapper\MapperRegistry;

$config = require '../config.php';
$config['MySql']['dns'] = 'mysql:dbname=datamapper_test;host=localhost;charset=utf8;';
$d = new MySql($config['MySql']);
MapperRegistry::driver($d);
MapperRegistry::register()->defaultNamespace(null);

$d->query("DELETE FROM grandfathers");
$d->query("DELETE FROM parents");
$d->query("DELETE FROM childs");
$d->query("DELETE FROM grandsons");
$d->query("DELETE FROM greatgrandchilds");

$max = 10000;
$grandfathers = [];
$parents = [];
$childs = [];
$grandsons = [];
$greatgrandchilds = [];

$parentprofiles = [];

for($i=0;$i<$max;$i++){
	$grandfathers[] = "({$i}, 'grandfather{$i}')";
	$parents[] = "({$i}, 'parents{$i}', {$i})";
	$childs[] = "({$i}, 'childs{$i}', {$i})";
	$grandsons[] = "({$i}, 'grandsons{$i}', {$i})";
	$greatgrandchilds[] = "({$i}, 'grandchilds{$i}', {$i})";
	
	$parentprofiles[] = "({$i}, 'profile{$i}', {$i})";
}
$gf = implode(',', $grandfathers);
$p = implode(',', $parents);
$c = implode(',', $childs);
$g = implode(',', $grandsons);
$gg = implode(',', $greatgrandchilds);

$pp = implode(',', $parentprofiles);

$d->query("INSERT INTO grandfathers(id, name) VALUES{$gf}");
$d->query("INSERT INTO parents(id, name, grandfather_id) VALUES{$p}");
$d->query("INSERT INTO childs(id, name, parent_id) VALUES{$c}");
$d->query("INSERT INTO grandsons(id, name, child_id) VALUES{$g}");
$d->query("INSERT INTO greatgrandchilds(id, name, grandson_id) VALUES{$gg}");


$d->query("DELETE FROM parentprofiles");
$d->query("INSERT INTO parentprofiles(id, body, parent_id) VALUES{$pp}");















