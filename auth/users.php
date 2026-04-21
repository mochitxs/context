<?php

$users = ['jorge', 'marti', 'diana', 'lucas', 'alan', 'nicole'];

echo "<pre>";
echo "INSERT INTO users (username, email, password_hash, role)\nVALUES\n";

$rows = [];

foreach ($users as $u) {
    $email = $u . "@gmail.com";
    $plainPassword = $u . "5";
    $hash = password_hash($plainPassword, PASSWORD_DEFAULT);

    $rows[] = "('$u', '$email', '$hash', 'user')";
}

echo implode(",\n", $rows) . ";";
echo "</pre>";