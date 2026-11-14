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

// 1. Autoload: un solo recorrido de clases, compartido con las pruebas.
require_once __DIR__ . '/../src/Autoloader.php';
Autoloader::register(__DIR__ . '/../src');

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

// 3. Composición: se arman las dependencias y se inyectan.
//    El punto de entrada es el único lugar que conoce las clases concretas.
$conexion   = Connection::getInstance();
$repository = new OrderRepository($conexion);
$calculators = new PriceCalculatorFactory();

// Avisos al crear un pedido (Observer).
$eventos = new OrderSubject();
$eventos->subscribe(new EmailObserver());
$eventos->subscribe(new SmsObserver());
$eventos->subscribe(new DashboardObserver());

// Fachada: coordina los servicios cohesivos.
$facade = new OrderFacade(
    $calculators,
    $repository,
    $eventos,
    new NotificationSender()
);

// Datos de demostración (mismo camino que un pedido real).
(new DemoOrderSeeder($repository, $calculators))->seed();

// Presentación: el controlador devuelve HTML, el punto de entrada lo muestra.
$view = new View(__DIR__ . '/../views');

$controller = new OrderController($facade, $repository, $view, new ReportBuilder());

$rutas = [
    'crear'   => fn() => $controller->create(),
    'listar'  => fn() => $controller->index(),
    'reporte' => fn() => $controller->report(),
];

$accion = $_GET['accion'] ?? 'crear';

if (isset($rutas[$accion])) {
    echo $rutas[$accion]();
} else {
    http_response_code(404);
    echo 'Accion no encontrada';
}
