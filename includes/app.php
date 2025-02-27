<?php

use Model\ActiveRecord;

require 'funciones.php';
require 'database.php';
require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__); // Creamos una instancia de Dotenv
$dotenv->safeLoad(); // Cargamos las variables de entorno

// Conectarnos a la base de datos
ActiveRecord::setDB($db);
