<?php

/**
 * Bootstrap de las pruebas.
 *
 * 1. Carga el autoloader de Composer (dependencias de desarrollo).
 * 2. Registra el autoload del proyecto: el MISMO que usa la app.
 *
 * Así, agregar una clase nueva en src/ no requiere regenerar ningún mapa:
 * el autoload resuelve por nombre de archivo.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Autoloader.php';

Autoloader::register(__DIR__ . '/../src');
