<?php

session_start(); 

// Datos de conexión
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'practicaweb';

// Creamos la conexión
$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

// Comprobamos errores
if ($mysqli->connect_errno) {
    die('Error de conexión MySQL: ' . $mysqli->connect_error);
}

// Fijar charset
$mysqli->set_charset('utf8');
?>