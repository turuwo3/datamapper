<?php
$pdo = new PDO('sqlite:test.sqlite');
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$schema = $pdo->query("pragma table_info (users)");

print_R($schema->fetchAll());
