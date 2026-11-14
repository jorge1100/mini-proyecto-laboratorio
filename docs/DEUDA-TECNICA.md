# Deuda técnica — laboratorio-pedidos

Diagnóstico de la deuda encontrada en el proyecto, con evidencia concreta
(archivo:línea), tipo, patrón aplicado y consecuencia asumida.

## Tabla de deuda

| # | Síntoma observado | Evidencia (archivo:línea) | Tipo de deuda | Patrón aplicado | Consecuencia asumida |
|---|---|---|---|---|---|
| 1 | El descuento de obra social (0.7) está escrito en 5 archivos distintos | `Order.php:62`, `PriceCalculator.php:24`, `PriceCalculator.php:38`, `OrderService.php:46`, `OrderController.php:40`, `views/orders.php:46` | Diseño (DRY/OCP) | Strategy | 5 clases nuevas (interfaz + 4 estrategias) por un `switch` de 10 líneas |
| 2 | `switch` con todos los algoritmos de precio concentrados en una clase | `src/Pricing/PriceCalculator.php:18-33` | Diseño (OCP/SRP) | Strategy | Hay que conocer la jerarquía de estrategias para agregar un tipo |
| 3 | `if` por tipo de notificación repetido en 3 archivos | `NotificationSender.php:24-40`, `OrderService.php:60-72`, `OrderController.php:46-48` | Diseño (OCP/DIP) | Factory | La fábrica sigue siendo el único lugar que conoce las clases concretas |
| 4 | Banderas booleanas (`boolean trap`) en la generación de reportes | `OrderService.php:84` (ex `ReportGenerator::generate(..., true, true, false)`) | Diseño (OCP) | Decorator | Más archivos (N decoradores) y orden de encadenado importa |
| 5 | Clase de terceros modificada (`send()` agregado) + copia y pega del comportamiento | `Legacy/LegacyNotifier.php:30-35` (método `send`), `Legacy/LegacyNotifier.php:49-56` (`CopiaDeLegacyEnNuestroSistema`) | Diseño (DRY/límites del sistema) | Adapter | Una capa extra de indirección; si cambia el proveedor se toca solo el adapter |
| 6 | El modelo se persiste y calcula precios (mezcla dominio y datos) | `Order.php:48-56` (`guardar()`), `Order.php:60-76` (`calcularTotal()`) | Diseño (SRP) | Repository + Strategy | Más clases y wiring; el modelo queda anémico |
| 7 | Conexión nueva por consulta y excepción silenciada | `Database/Connection.php:24-27` (`obtener()`), `Database/Connection.php:33-38` (`ejecutar()`) | Diseño (recursos) | Singleton | Estado global compartido; cuidado con concurrencia y tests |
| 8 | Avisos encadenados a mano a clases concretas | `Events/OrderEvents.php:20-32` (`pedidoCreado()`) | Diseño (DIP) | Observer | Se pierde trazabilidad de quién se entera del evento |
| 9 | Método que hace de todo (validar, calcular, cobrar, guardar, notificar, reportar, imprimir) | `Services/OrderService.php:24-86` (`procesarPedidoCompleto()`) | Diseño (SRP) | Facade | La fachada puede convertirse en la nueva "clase que hace todo" si no se cuida |
| 10 | SQL + negocio + HTML en el controlador | `Controllers/OrderController.php:28-55` (`create()`) | Diseño (SRP/MVC) | MVC | Más archivos y capas; requiere disciplina del equipo |
| 11 | Vista consulta la base, calcula totales y no escapa la salida (XSS) | `views/orders.php:24-48` | Diseño (MVC/seguridad) | MVC | El controlador asume la carga de preparar los datos |
| 12 | Carga manual de dependencias con `require` | `public/index.php:24-44` (bloque de requires) | **Proceso** (configuración en código) | Autoload (PSR-4) | Resolución en runtime; errores de carga se vuelven silenciosos si no se cuida |
| 13 | Credenciales de base de datos escritas en el código y versionadas | `public/index.php:48-52` (`define('DB_*')`) | **Proceso** (secretos en repo) | Config externa (.gitignore) | Cada entorno necesita su archivo local; riesgo de olvidar crearlo |
| 14 | Ruteo resuelto con cadena de `if` | `public/index.php:62-72` | **Proceso** (OCP en código) | Tabla de rutas | Un mapa crece; hay que mantenerlo actualizado |
| 15 | Todos los commits del mismo autor / un solo commit inicial invalida la entrega | historial del repositorio (Unidad 1) | **Proceso** (colaboración) | Flujo de ramas + PR + revisión cruzada | Más ceremonia; requiere disciplina del equipo |

## Métrica del proyecto

El descuento de obra social (`0.7`) aparecía en **5 archivos**.
Después del refactor con Strategy, el valor vive en un único lugar:
`src/Pricing/InsuranceStrategy.php` (constructor con `$discount = 0.30`).
Cambiarlo al 25% requiere tocar **1 archivo**.

## Estado de los patrones aplicados

| Patrón | Rama | Estado |
|---|---|---|
| Strategy | `feat/patron-strategy` | Integrado en `main` |
| Factory | `feat/patron-factory` | Integrado en `main` |
| Decorator | `feat/patron-decorator` | Integrado en `main` |
| Adapter | `feat/patron-adapter` | Integrado en `main` |
| Observer | `feat/patron-observer` | Integrado en `main` |
| Facade | `feat/patron-facade` | Integrado en `main` |
| Repository | `feat/patron-facade` | Integrado en `main` |

## Evidencia de la deudaObserver pagada

Síntoma original: `src/Events/OrderEvents.php:20-32` (`pedidoCreado()`) llamaba
a `EmailNotification` y `SmsNotification` **por nombre de clase**, encadenadas.
Consecuencia directa: agregar un aviso obligaba a modificar esa clase, y si
uno fallaba, los siguientes no se ejecutaban.

Solución aplicada:

- `src/Events/OrderObserver.php` — interfaz del observador.
- `src/Events/OrderSubject.php` — sujeto con colección de interfaces.
- `src/Events/EmailObserver.php`, `SmsObserver.php`, `DashboardObserver.php` —
  avisos intercambiables.
- `src/Events/OrderEvents.php` — **eliminado**: no queda código viejo.

Agregar un aviso nuevo es agregar una clase y suscribirla. `OrderSubject` no
se tocó (ejercicio 5 del TP).
