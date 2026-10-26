<?php
/**
 * ============================================================================
 *  laboratorio-pedidos — PUNTO DE ENTRADA
 *  Metodología de Sistemas II — UTN FRRe (sede Formosa)
 * ============================================================================
 *
 *  Carga automática de clases (PSR-4 simplificada para clases sin namespace
 *  distribuidas en subdirectorios de src/). Configuración externa (sin
 *  credenciales versionadas). Ruteo por tabla de rutas.
 * ============================================================================
 */

// 1. Autoload: busca el archivo <Clase>.php dentro de src/ (cualquier subdirectorio).
spl_autoload_register(function (string $class): void {
    static $map = null;
    if ($map === null) {
        $map = [];
        $base = __DIR__ . '/../src';
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS)
        );
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $map[$file->getBasename('.php')] = $file->getPathname();
            }
        }
    }
    if (isset($map[$class]) && is_file($map[$class])) {
        require_once $map[$class];
    }
});

// 2. Configuración externa: nunca versionada. Si no existe el archivo local,
//    se usa la plantilla con valores vacíos/default.
$configFile = __DIR__ . '/../config/database.php';
if (!is_file($configFile)) {
    $configFile = __DIR__ . '/../config/database.example.php';
}
$config = require $configFile;

define('DB_HOST', $config['host'] ?? 'localhost');
define('DB_NAME', $config['name'] ?? 'laboratorio');
define('DB_USER', $config['user'] ?? 'root');
define('DB_PASS', $config['pass'] ?? '');
define('DB_PORT', $config['port'] ?? '3306');

// 3. Controlador y ruteo por tabla de rutas (abierto a extensión).
$controller = new OrderController();

$rutas = [
    'crear'   => fn() => $controller->create(),
    'listar'  => fn() => $controller->index(),
    'reporte' => fn() => $controller->report(),
];

$accion = $_GET['accion'] ?? 'crear';

if (isset($rutas[$accion])) {
    $rutas[$accion]();
} else {
    http_response_code(404);
    echo 'Accion no encontrada';
}
