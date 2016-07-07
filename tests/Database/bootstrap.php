<?php

$pdo = new PDO('sqlite:test.sqlite');

$pdo->query("CREATE TABLE users (id integer primary key, name text)");
$pdo->query("CREATE TABLE profiles (id integer primary key, body text, user_id int)");
$pdo->query("CREATE TABLE comments (id integer primary key, body text, user_id int)");
$pdo->query("CREATE TABLE tags (id integer primary key, name text)");
$pdo->query("CREATE TABLE posts (id integer primary key, body text)");
$pdo->query("CREATE TABLE posts_tags (id integer primary key, post_id int, tag_id int)");
