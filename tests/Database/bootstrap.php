<?php

$config = require '../test.config.php';

$pdo = new PDO($config['Sqlite']);

$pdo->query("CREATE TABLE users (id integer primary key, name text)");
$pdo->query("CREATE TABLE profiles (id integer primary key, body text, user_id integer)");
$pdo->query("CREATE TABLE comments (id integer primary key, body text, user_id integer, approved integer)");
$pdo->query("CREATE TABLE tags (id integer primary key, name text)");
$pdo->query("CREATE TABLE posts (id integer primary key, body text)");
$pdo->query("CREATE TABLE posts_tags (id integer primary key, post_id integer, tag_id integer)");

$pdo->query("CREATE TABLE grandfathers (id integer primary key, name text)");
$pdo->query("CREATE TABLE parents (id integer primary key, name text, grandfather_id integer)");
$pdo->query("CREATE TABLE parentprofiles (id integer primary key, body text, parent_id integer)");
$pdo->query("CREATE TABLE childs (id integer primary key, name text, parent_id integer)");
$pdo->query("CREATE TABLE grandsons (id integer primary key, name text, child_id integer)");
$pdo->query("CREATE TABLE greatgrandchilds (id integer primary key, name text, grandson_id integer)");


$pdo = new PDO($config['MySql']);

$pdo->query("CREATE TABLE users (id integer primary key, name text)");
$pdo->query("CREATE TABLE profiles (id integer primary key, body text, user_id integer)");
$pdo->query("CREATE TABLE comments (id integer primary key, body text, user_id integer, approved integer)");
$pdo->query("CREATE TABLE tags (id integer primary key, name text)");
$pdo->query("CREATE TABLE posts (id integer primary key, body text)");
$pdo->query("CREATE TABLE posts_tags (id integer primary key, post_id integer, tag_id integer)");

$pdo->query("CREATE TABLE grandfathers (id integer primary key, name text)");
$pdo->query("CREATE TABLE parents (id integer primary key, name text, grandfather_id integer)");
$pdo->query("CREATE TABLE parentprofiles (id integer primary key, body text, parent_id integer)");
$pdo->query("CREATE TABLE childs (id integer primary key, name text, parent_id integer)");
$pdo->query("CREATE TABLE grandsons (id integer primary key, name text, child_id integer)");
$pdo->query("CREATE TABLE greatgrandchilds (id integer primary key, name text, grandson_id integer)");
