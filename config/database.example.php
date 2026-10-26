<?php
// Plantilla versionada. Copiar a database.php y completar con valores locales.
// Ese archivo SÍ está en .gitignore (nunca se versiona).
return [
    'host' => getenv('DB_HOST') ?: 'db',
    'name' => getenv('DB_NAME') ?: 'laboratorio',
    'user' => getenv('DB_USER') ?: 'root',
    'pass' => getenv('DB_PASS') ?: 'root',
    'port' => getenv('DB_PORT') ?: '3306',
];
