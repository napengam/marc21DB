<?php

$dbHost = ""; //Hostname des Servers
$dbUser = ""; //Benutzername
$dbPass = ""; //Passwort
$dbName = ""; //Name der Datenbank
$dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8";
$opt = array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_PERSISTENT => true,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
);
$connect_pdo = new PDO($dsn, $dbUser, $dbPass, $opt);
$connect_pdo->exec("set names utf8");
$connect_pdo->exec("set sql_mode=''");
