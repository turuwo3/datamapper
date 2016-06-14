<?php

$config = require '../config.php';

$c = $config['MySql'];

$pdo = new PDO($c['dns'], $c['user'], $c['password']);

$s = $pdo->prepare("SELECT u.id FROM users as u LEFT JOIN comments as c ON c.user_id = 1");

$s->bindValue(':c0', 1);
$s->execute();

//print_r($s->fetchAll());

//$s = $pdo->prepare("select * from users where id = :c0");

//$s->bindValue(':c0', 1);

//$r = $s->execute();

//print_r($r->fetchAll());


$arr['t1'] = ['c'=>[], 'v'=>''];
$arr['t2'] = ['c'=>['p'=>'dfs', 'v'=>2, 't'=>'f'], 'v'=>''];

foreach($arr as $t => $s){
print_r($t);
}



