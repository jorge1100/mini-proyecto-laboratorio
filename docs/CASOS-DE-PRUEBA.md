# Casos de prueba — Unidades 4 y 5 (Pruebas + Integración Continua)

**Proyecto:** `laboratorio-pedidos` · **Instancia:** 2° parcial (entrega + defensa)
**Alcance:** pruebas unitarias sobre el código refactorizado (Strategy, Factory,
Decorator y Adapter), ejecutables con PHPUnit y validadas en GitHub Actions en
cada `push` y `pull_request`.

---

## 1. Alcance y enfoque

Se prueban **las cuatro funcionalidades que el grupo ya refactorizó**:

| Funcionalidad | Patrón | Código |
|---|---|---|
| Cálculo de precio por tipo de paciente | Strategy | `src/Pricing/*` |
| Creación y envío de notificaciones | Factory Method | `src/Notifications/*` |
| Generación y composición de reportes | Decorator | `src/Reports/*` |
| Adaptación del sistema heredado | Adapter | `src/Legacy/LegacyNotifier.php` |
| Avisos al crear un pedido | Observer | `src/Events/*` |
| Invariantes del pedido | Modelo con invariantes | `src/Models/Order.php` |
| Guardado y consulta de pedidos | Repository | `src/Repositories/*` |
| Orquestación del pedido completo | Facade | `src/Services/OrderFacade.php` |
| Calculador según el tipo de paciente | Factory Method | `src/Pricing/PriceCalculatorFactory.php` |
| Renderizado de vistas y escapado de la salida | MVC | `src/Views/View.php`, `views/*` |
| Orquestación de la entrada y la respuesta | MVC | `src/Controllers/OrderController.php` |
| Instancia única de conexión | Singleton | `src/Database/Connection.php` |

**Fuera de alcance:** los flujos de HTTP/HTML de punta a punta, que no son
código refactorizado y requerirían dobles de base de datos y salida web.

Las pruebas son **unitarias**: no usan MySQL, ni servidor de mail, ni el
navegador. Solo cargan las clases con el autoloader de Composer y verifican el
comportamiento de cada unidad.

---

## 2. Cómo ejecutar

```bash
# 1. Instalar dependencias (PHPUnit queda en vendor/)
composer install

# 2. Lint: verifica la sintaxis de todos los archivos PHP
composer lint

# 3. Pruebas unitarias
composer test
```

Salida esperada al final: `OK (81 tests, 128 assertions)`.

La integración continua ejecuta exactamente los pasos 2 y 3 en GitHub Actions
(`.github/workflows/ci.yml`) sobre PHP 8.2 y 8.4.

La misma suite se puede correr dentro del contenedor (PHP 8.2) para
reproducir el CI localmente:

```bash
docker compose exec app php vendor/bin/phpunit
```

El paso a paso completo está en `docs/GUIA-EJECUCION.md`.

---

## 3. Convenciones

- **Identificador:** `CP-xx` para casos positivos y `CN-xx` para negativos.
- **Precondiciones:** las mismas para todos: PHP 8.2+, dependencias instaladas
  con Composer y las clases de `src/` cargadas por el autoloader.
- **Datos de entrada:** valores concretos, no genéricos.
- **Resultado esperado:** el valor o la salida exactos que produce el código.
- Cada caso tiene **un test** en `tests/Unit/`; la trazabilidad está en la
  sección 6.

---

## 4. Casos de prueba

### F1. Cálculo de precio por tipo de paciente (Strategy)

| ID | Descripción | Precondiciones | Datos de entrada | Pasos | Resultado esperado | Tipo |
|---|---|---|---|---|---|---|
| CP-01 | Paciente particular paga el monto completo | Autoloader cargado | `monto = 15000`, `PrivatePatientStrategy` | Instanciar `PriceCalculator` con `PrivatePatientStrategy` y llamar `calculate(15000)` | `15000.0` | Positivo |
| CP-02 | Obra social aplica 30% de descuento por defecto | Autoloader cargado | `monto = 15000`, `InsuranceStrategy()` | Instanciar `PriceCalculator` con `InsuranceStrategy` y llamar `calculate(15000)` | `10500.0` | Positivo |
| CP-03 | El descuento de obra social se puede configurar | Autoloader cargado | `monto = 15000`, `InsuranceStrategy(0.25)` | Instanciar con descuento del 25% y llamar `calculate(15000)` | `11250.0` | Positivo |
| CP-04 | Jubilado aplica 50% de descuento por defecto | Autoloader cargado | `monto = 15000`, `RetiredStrategy()` | Instanciar `PriceCalculator` con `RetiredStrategy` y llamar `calculate(15000)` | `7500.0` | Positivo |
| CP-05 | Prepaga aplica 15% de descuento por defecto | Autoloader cargado | `monto = 15000`, `PrepaidStrategy()` | Instanciar `PriceCalculator` con `PrepaidStrategy` y llamar `calculate(15000)` | `12750.0` | Positivo |
| CP-06 | El calculador delega en la estrategia inyectada y no decide el algoritmo | Autoloader cargado | Estrategia de prueba que devuelve `42.0` y registra el monto recibido | Inyectar la estrategia en `PriceCalculator` y llamar `calculate(999)` | La estrategia recibe `999.0`, se marca como llamada y el resultado es `42.0` | Positivo |
| CN-01 | Monto cero es una frontera válida y no rompe el cálculo | Autoloader cargado | `monto = 0`, `InsuranceStrategy()` | Llamar `calculate(0)` | `0.0` | Negativo (frontera) |
| CN-02 | Si la estrategia lanza, el calculador propaga la excepción | Autoloader cargado | Estrategia que lanza `RuntimeException('La estrategia fallo')` | Lanzar `calculate(1000)` | Se propaga `RuntimeException` con el mensaje `La estrategia fallo` | Negativo |

### F2. Cálculo con IVA (PriceCalculator)

| ID | Descripción | Precondiciones | Datos de entrada | Pasos | Resultado esperado | Tipo |
|---|---|---|---|---|---|---|
| CP-07 | El IVA por defecto (21%) se aplica sobre el total ya descontado | Autoloader cargado | `monto = 15000`, `InsuranceStrategy()`, `vat = 1.21` | Llamar `calculateWithVat(15000)` | `12705.0` (10500 × 1.21) | Positivo |
| CP-08 | Se puede indicar una tasa de IVA distinta | Autoloader cargado | `monto = 15000`, `PrivatePatientStrategy()`, `vat = 1.10` | Llamar `calculateWithVat(15000, 1.10)` | `16500.0` | Positivo |
| CN-03 | Si la estrategia lanza, `calculateWithVat` también propaga | Autoloader cargado | Estrategia que lanza `RuntimeException('La estrategia fallo')` | Llamar `calculateWithVat(1000)` | Se propaga `RuntimeException` con el mensaje `La estrategia fallo` | Negativo |

### F3. Creación de notificaciones (Factory)

| ID | Descripción | Precondiciones | Datos de entrada | Pasos | Resultado esperado | Tipo |
|---|---|---|---|---|---|---|
| CP-09 | Crea una notificación de email | Autoloader cargado | `type = 'email'`, `destination = 'paciente@mail.com'` | Llamar `NotificationFactory::create('email', 'paciente@mail.com')` | Instancia de `EmailNotification` | Positivo |
| CP-10 | Crea una notificación de SMS | Autoloader cargado | `type = 'sms'`, `destination = '3704000000'` | Llamar `NotificationFactory::create('sms', '3704000000')` | Instancia de `SmsNotification` | Positivo |
| CP-11 | Crea una notificación de WhatsApp | Autoloader cargado | `type = 'whatsapp'`, `destination = '3704000000'` | Llamar `NotificationFactory::create('whatsapp', '3704000000')` | Instancia de `WhatsAppNotification` | Positivo |
| CN-04 | Rechaza un tipo de canal inexistente | Autoloader cargado | `type = 'telegram'` | Llamar `NotificationFactory::create('telegram', 'destino')` | `InvalidArgumentException` con el mensaje `Tipo de notificacion no soportado: telegram` | Negativo |
| CN-05 | Rechaza un tipo de canal vacío | Autoloader cargado | `type = ''` | Llamar `NotificationFactory::create('', 'destino')` | `InvalidArgumentException` | Negativo |
| CN-06 | El tipo es sensible a mayúsculas | Autoloader cargado | `type = 'Email'` | Llamar `NotificationFactory::create('Email', 'destino')` | `InvalidArgumentException` | Negativo |

### F4. Envío de notificaciones (NotificationSender)

| ID | Descripción | Precondiciones | Datos de entrada | Pasos | Resultado esperado | Tipo |
|---|---|---|---|---|---|---|
| CP-12 | Envía por email con el formato del canal | Autoloader cargado | `tipo = 'email'`, `destino = 'paciente@mail.com'`, `mensaje = 'Pedido 1 creado'` | Llamar `enviar(...)` y capturar la salida | `[EMAIL] para paciente@mail.com: Pedido 1 creado<br>` | Positivo |
| CP-13 | Envía por SMS con el formato del canal | Autoloader cargado | `tipo = 'sms'`, `destino = '3704000000'`, `mensaje = 'Pedido 1 creado'` | Llamar `enviar(...)` y capturar la salida | `[SMS] a 3704000000: Pedido 1 creado<br>` | Positivo |
| CP-14 | Envía por WhatsApp con el formato del canal | Autoloader cargado | `tipo = 'whatsapp'`, `destino = '3704000000'`, `mensaje = 'Pedido 1 creado'` | Llamar `enviar(...)` y capturar la salida | `[WHATSAPP] a 3704000000: Pedido 1 creado<br>` | Positivo |
| CN-07 | Un canal no soportado no se traga en silencio | Autoloader cargado | `tipo = 'telegram'`, `destino = 'destino'`, `mensaje = 'Pedido 1 creado'` | Llamar `enviar(...)` | Se propaga `InvalidArgumentException` de la fábrica | Negativo |

### F5. Generación y composición de reportes (Decorator)

| ID | Descripción | Precondiciones | Datos de entrada | Pasos | Resultado esperado | Tipo |
|---|---|---|---|---|---|---|
| CP-15 | El reporte base genera solo el contenido | Autoloader cargado | `contenido = 'Pedidos del dia'` | Llamar `generate()` sobre `BasicReport` | `Reporte: Pedidos del dia` | Positivo |
| CP-16 | El decorador de firma digital agrega su sufijo | Autoloader cargado | `BasicReport('Pedidos del dia')` | Envolver con `DigitalSignatureDecorator` y llamar `generate()` | `Reporte: Pedidos del dia + firma digital` | Positivo |
| CP-17 | El decorador de PDF agrega su marca | Autoloader cargado | `BasicReport('Pedidos del dia')` | Envolver con `PdfReportDecorator` y llamar `generate()` | `Reporte: Pedidos del dia + PDF` | Positivo |
| CP-18 | El decorador de marca de agua agrega su sufijo | Autoloader cargado | `BasicReport('Pedidos del dia')` | Envolver con `WatermarkDecorator` y llamar `generate()` | `Reporte: Pedidos del dia + marca de agua` | Positivo |
| CP-19 | Se pueden combinar los tres decoradores | Autoloader cargado | `BasicReport('Pedidos del dia')` | Envolver en orden firma → PDF → marca de agua y llamar `generate()` | `Reporte: Pedidos del dia + firma digital + PDF + marca de agua` | Positivo |
| CP-20 | El orden de encadenado define el resultado | Autoloader cargado | `BasicReport('Pedidos del dia')` | Envolver en orden marca de agua → PDF y llamar `generate()` | `Reporte: Pedidos del dia + marca de agua + PDF` | Positivo |
| CN-08 | Rechaza una dependencia que no cumple el contrato `Report` | Autoloader cargado | Objeto `stdClass` como reporte interno | Instanciar `DigitalSignatureDecorator(new stdClass())` | `TypeError` | Negativo |
| CN-09 | Si el reporte interno lanza, el decorador propaga | Autoloader cargado | `Report` cuyo `generate()` lanza `RuntimeException('El reporte interno fallo')` | Envolver con `DigitalSignatureDecorator` y llamar `generate()` | Se propaga `RuntimeException` con el mensaje `El reporte interno fallo` | Negativo |

### F6. Adaptación del sistema heredado (Adapter)

| ID | Descripción | Precondiciones | Datos de entrada | Pasos | Resultado esperado | Tipo |
|---|---|---|---|---|---|---|
| CP-21 | El adaptador cumple el contrato `Notification` | Autoloader cargado | `LegacyNotifier` real | Instanciar `LegacyNotifierAdapter(new LegacyNotifier())` | `instanceof Notification` es `true` | Positivo |
| CP-22 | El adaptador delega en `sendMessage()` del heredado | Autoloader cargado | `LegacyNotifier` espía que registra los mensajes | Llamar `send('Pedido 1 creado')` | El espía recibe exactamente `['Pedido 1 creado']` | Positivo |
| CP-23 | La salida mantiene el formato del proveedor | Autoloader cargado | `LegacyNotifier` real, mensaje `Pedido 1 creado` | Llamar `send(...)` y capturar la salida | `[LEGACY] Pedido 1 creado<br>` | Positivo |
| CN-10 | Si el proveedor lanza, el adaptador propaga | Autoloader cargado | `LegacyNotifier` que lanza `RuntimeException('El proveedor esta caido')` | Llamar `send('Pedido 1 creado')` | Se propaga `RuntimeException` con el mensaje `El proveedor esta caido` | Negativo |
| CN-11 | Rechaza una dependencia que no es `LegacyNotifier` | Autoloader cargado | Objeto `stdClass` | Instanciar `LegacyNotifierAdapter(new stdClass())` | `TypeError` | Negativo |

### F7. Avisos al crear un pedido (Observer)

| ID | Descripción | Precondiciones | Datos de entrada | Pasos | Resultado esperado | Tipo |
|---|---|---|---|---|---|---|
| CP-24 | El observador suscrito recibe el pedido | Autoloader cargado | Observador espía, `Order(1, 'Juan Perez', 15000, 'obra_social')` | Suscribir el observador en `OrderSubject` y llamar `notify($order)` | El observador recibe exactamente el mismo objeto `Order` | Positivo |
| CP-25 | Todos los observadores suscritos reciben el aviso | Autoloader cargado | Dos observadores espía | Suscribir ambos y llamar `notify($order)` | Ambos reciben el pedido | Positivo |
| CP-26 | El aviso por SMS se agrega sin tocar el sujeto | Autoloader cargado | `SmsObserver`, `Order(7, 'Ana Gomez', 22000, 'particular')` | Suscribir `SmsObserver` en un `OrderSubject` nuevo y llamar `notify()` | La salida contiene `[OBSERVER-SMS] Pedido 7 creado` | Positivo |
| CP-27 | El observador de email escribe su aviso | Autoloader cargado | `EmailObserver`, `Order(3, 'Ana Gomez', 22000, 'particular')` | Llamar `update($order)` y capturar la salida | La salida contiene `[OBSERVER-EMAIL] Pedido 3 creado` | Positivo |
| CP-28 | El observador de SMS escribe su aviso | Autoloader cargado | `SmsObserver`, `Order(4, 'Luis Diaz', 9000, 'jubilado')` | Llamar `update($order)` y capturar la salida | La salida contiene `[OBSERVER-SMS] Pedido 4 creado` | Positivo |
| CP-29 | El observador de dashboard escribe su aviso | Autoloader cargado | `DashboardObserver`, `Order(5, 'Maria Lopez', 12000, 'prepaga')` | Llamar `update($order)` y capturar la salida | La salida contiene `[DASHBOARD] actualizado para el pedido 5` | Positivo |
| CN-12 | Un observador que falla no impide el aviso a los demás | Autoloader cargado | Observador que lanza `RuntimeException('El aviso cayo')` + un observador sano | Suscribir ambos y llamar `notify($order)` | Se propaga `RuntimeException` **y** el observador sano también recibió el pedido | Negativo |
| CN-13 | El mismo observador suscrito dos veces recibe dos avisos | Autoloader cargado | Un observador espía suscrito dos veces | Suscribirlo dos veces y llamar `notify($order)` | El observador registra 2 avisos: el sujeto no deduplica | Negativo |
| CN-14 | Un observador no suscrito no recibe el aviso | Autoloader cargado | Observador espía sin suscribir | Crear el `OrderSubject` y llamar `notify($order)` sin suscribir | El observador no registra ningún aviso | Negativo |

### F8. Modelo Order (invariantes del pedido)

| ID | Descripción | Precondiciones | Datos de entrada | Pasos | Resultado esperado | Tipo |
|---|---|---|---|---|---|---|
| CP-30 | El modelo conserva los datos del pedido | Autoloader cargado | `Order(1, 'Juan Perez', 15000.0, 'obra_social')` | Construir el pedido y leer sus propiedades | `id=1`, `patient='Juan Perez'`, `amount=15000.0`, `patientType='obra_social'` | Positivo |
| CN-15 | Rechaza un id no positivo | Autoloader cargado | `Order(0, 'Juan Perez', 15000.0, 'particular')` | Construir el pedido | `InvalidArgumentException` con mensaje `El id del pedido debe ser mayor a cero` | Negativo |
| CN-16 | Rechaza un paciente vacío | Autoloader cargado | `Order(1, '   ', 15000.0, 'particular')` | Construir el pedido | `InvalidArgumentException` con mensaje `El paciente es obligatorio` | Negativo |
| CN-17 | Rechaza un importe negativo | Autoloader cargado | `Order(1, 'Juan Perez', -15000.0, 'particular')` | Construir el pedido | `InvalidArgumentException` con mensaje `El importe no puede ser negativo` | Negativo |
| CN-18 | Rechaza un importe cero | Autoloader cargado | `Order(1, 'Juan Perez', 0.0, 'particular')` | Construir el pedido | `InvalidArgumentException` | Negativo |

### F9. Repositorio de pedidos

| ID | Descripción | Precondiciones | Datos de entrada | Pasos | Resultado esperado | Tipo |
|---|---|---|---|---|---|---|
| CP-31 | Guarda el pedido con el total ya calculado | Autoloader cargado | `Order(5, 'Ana Gomez', 22000.0, 'particular')`, total `22000.0` | `save($order, $total)` y luego `find(5)` | El pedido guardado tiene el paciente y el total | Positivo |
| CP-32 | `all()` devuelve todos los pedidos guardados | Autoloader cargado | Dos pedidos con totales `10500.0` y `22000.0` | Guardar los dos y llamar `all()` | Devuelve 2 pedidos; el primero tiene total `10500.0` | Positivo |
| CP-33 | Los datos de demostración usan los totales de las estrategias | Autoloader cargado | Datos de demo (obra social y particular) | `DemoOrderSeeder::seed()` | 2 pedidos: `Juan Perez → 10500.0` y `Ana Gomez → 22000.0` | Positivo |
| CN-19 | `find()` de un id inexistente devuelve null | Autoloader cargado | Id `999` | `find(999)` | `null` | Negativo |
| CN-20 | Un repositorio vacío no devuelve pedidos | Autoloader cargado | Sin pedidos | `all()` | `[]` | Negativo |
| CN-21 | El nombre del paciente se escapa en la consulta SQL | Autoloader cargado | Paciente `O'Neil` | `save()` y revisar la consulta ejecutada | La consulta contiene `'O\'Neil'` y **no** `'O'Neil'` | Negativo |

### F10. Fachada de pedidos

| ID | Descripción | Precondiciones | Datos de entrada | Pasos | Resultado esperado | Tipo |
|---|---|---|---|---|---|---|
| CP-34 | Devuelve el total calculado por la estrategia | Autoloader cargado | `Order(1, 'Juan Perez', 15000.0, 'obra_social')` | `procesarPedidoCompleto($order)` | `10500.0` | Positivo |
| CP-35 | Guarda el pedido con el total calculado | Autoloader cargado | `Order(5, 'Ana Gomez', 22000.0, 'particular')` | `procesarPedidoCompleto($order)` y `find(5)` | El repositorio tiene el pedido 5 con total `22000.0` | Positivo |
| CP-36 | Avisa a los observadores suscritos | Autoloader cargado | Observador espía suscrito al sujeto | `procesarPedidoCompleto($order)` | El observador registra 1 aviso | Positivo |
| CP-37 | Envía la notificación al paciente | Autoloader cargado | Destino `paciente@mail.com` | `procesarPedidoCompleto($order)` capturando la salida | La salida contiene `[EMAIL] para paciente@mail.com: Pedido 1 por $ 10500` | Positivo |
| CN-22 | Un tipo de paciente no soportado no guarda el pedido | Autoloader cargado | Tipo `xyz` | `procesarPedidoCompleto($order)` | `InvalidArgumentException` con mensaje `no soportado` y el repositorio queda vacío | Negativo |
| CN-23 | Si un observador falla, el error se propaga | Autoloader cargado | Observador que lanza `RuntimeException('El aviso cayo')` | `procesarPedidoCompleto($order)` | Se propaga `RuntimeException` | Negativo |

### F11. Fábrica de calculadores por tipo de paciente

| ID | Descripción | Precondiciones | Datos de entrada | Pasos | Resultado esperado | Tipo |
|---|---|---|---|---|---|---|
| CP-38 | El paciente particular paga el monto completo | Autoloader cargado | `for('particular')`, monto `22000` | Calcular con el calculador devuelto | `22000.0` | Positivo |
| CP-39 | La obra social usa la estrategia de seguro | Autoloader cargado | `for('obra_social')`, monto `15000` | Calcular con el calculador devuelto | `10500.0` | Positivo |
| CP-40 | El jubilado usa la estrategia de jubilado | Autoloader cargado | `for('jubilado')`, monto `15000` | Calcular con el calculador devuelto | `7500.0` | Positivo |
| CP-41 | El prepaga usa la estrategia de prepaga | Autoloader cargado | `for('prepaga')`, monto `15000` | Calcular con el calculador devuelto | `12750.0` | Positivo |
| CN-24 | Rechaza un tipo de paciente no soportado | Autoloader cargado | `for('xyz')` | Pedir el calculador | `InvalidArgumentException` con mensaje `Tipo de paciente no soportado: xyz` | Negativo |
| CN-25 | El tipo de paciente es sensible a mayúsculas | Autoloader cargado | `for('Obra_Social')` | Pedir el calculador | `InvalidArgumentException` | Negativo |

---

## 5. Resumen por funcionalidad

| Funcionalidad | Positivos | Negativos | Tests |
|---|---|---|---|
| F1. Precio por tipo de paciente (Strategy) | 6 | 2 | 8 |
| F2. Cálculo con IVA | 2 | 1 | 3 |
| F3. Creación de notificaciones (Factory) | 3 | 3 | 4 |
| F4. Envío de notificaciones (Sender) | 3 | 1 | 4 |
| F5. Reportes y decoradores (Decorator) | 6 | 2 | 8 |
| F6. Adaptación del heredado (Adapter) | 3 | 2 | 5 |
| F7. Avisos al crear un pedido (Observer) | 6 | 3 | 9 |
| F8. Modelo Order (invariantes) | 1 | 4 | 5 |
| F9. Repositorio y datos de demo | 3 | 3 | 6 |
| F10. Fachada de pedidos | 4 | 2 | 6 |
| F11. Fábrica de calculadores | 4 | 2 | 6 |
| F12. Renderizado de vistas | 3 | 2 | 5 |
| F13. Controlador de pedidos (MVC) | 3 | 2 | 5 |
| F14. Conexión a la base (Singleton) | 2 | 3 | 5 |
| **Total** | **49** | **32** | **81** |

---

## 6. Trazabilidad caso ↔ test

| ID | Test (`tests/Unit/...`) |
|---|---|
| CP-01 | `Pricing/PriceCalculatorTest::testPrivatePatientPaysFullAmount` |
| CP-02 | `Pricing/PriceCalculatorTest::testInsuranceAppliesDefault30PercentDiscount` |
| CP-03 | `Pricing/PriceCalculatorTest::testInsuranceAcceptsCustomDiscount` |
| CP-04 | `Pricing/PriceCalculatorTest::testRetiredAppliesDefault50PercentDiscount` |
| CP-05 | `Pricing/PriceCalculatorTest::testPrepaidAppliesDefault15PercentDiscount` |
| CP-06 | `Pricing/PriceCalculatorTest::testPriceCalculatorDelegatesToInjectedStrategy` |
| CN-01 | `Pricing/PriceCalculatorTest::testZeroAmountReturnsZero` |
| CN-02 | `Pricing/PriceCalculatorTest::testCalculatePropagatesStrategyException` |
| CP-07 | `Pricing/PriceCalculatorTest::testCalculateWithVatAppliesDefaultVatOverDiscountedTotal` |
| CP-08 | `Pricing/PriceCalculatorTest::testCalculateWithVatAcceptsCustomRate` |
| CN-03 | `Pricing/PriceCalculatorTest::testCalculateWithVatPropagatesStrategyException` |
| CP-09 | `Notifications/NotificationFactoryTest::testCreatesEmailNotification` |
| CP-10 | `Notifications/NotificationFactoryTest::testCreatesSmsNotification` |
| CP-11 | `Notifications/NotificationFactoryTest::testCreatesWhatsAppNotification` |
| CN-04 | `Notifications/NotificationFactoryTest::testThrowsOnUnsupportedType` (data set `tipo inexistente`) |
| CN-05 | `Notifications/NotificationFactoryTest::testThrowsOnUnsupportedType` (data set `tipo vacio`) |
| CN-06 | `Notifications/NotificationFactoryTest::testThrowsOnUnsupportedType` (data set `tipo en mayusculas`) |
| CN-07 | `Notifications/NotificationSenderTest::testPropagatesFactoryExceptionForUnsupportedType` |
| CP-12 | `Notifications/NotificationSenderTest::testSendsEmailThroughFactory` |
| CP-13 | `Notifications/NotificationSenderTest::testSendsSmsThroughFactory` |
| CP-14 | `Notifications/NotificationSenderTest::testSendsWhatsAppThroughFactory` |
| CP-15 | `Reports/ReportDecoratorTest::testBasicReportGeneratesBaseContent` |
| CP-16 | `Reports/ReportDecoratorTest::testDigitalSignatureAppendsSignature` |
| CP-17 | `Reports/ReportDecoratorTest::testPdfAppendsPdfMark` |
| CP-18 | `Reports/ReportDecoratorTest::testWatermarkAppendsWatermark` |
| CP-19 | `Reports/ReportDecoratorTest::testDecoratorsCanBeCombined` |
| CP-20 | `Reports/ReportDecoratorTest::testDecoratorOrderChangesResult` |
| CN-08 | `Reports/ReportDecoratorTest::testDecoratorRejectsDependencyThatIsNotAReport` |
| CN-09 | `Reports/ReportDecoratorTest::testDecoratorPropagatesInnerReportException` |
| CP-21 | `Legacy/LegacyNotifierAdapterTest::testAdapterImplementsNotificationContract` |
| CP-22 | `Legacy/LegacyNotifierAdapterTest::testAdapterDelegatesToLegacySendMessage` |
| CP-23 | `Legacy/LegacyNotifierAdapterTest::testAdapterOutputsLegacyFormat` |
| CN-10 | `Legacy/LegacyNotifierAdapterTest::testAdapterPropagatesLegacyException` |
| CN-11 | `Legacy/LegacyNotifierAdapterTest::testAdapterRejectsDependencyThatIsNotLegacyNotifier` |
| CP-24 | `Events/OrderSubjectTest::testNotifiesSubscribedObserverWithTheOrder` |
| CP-25 | `Events/OrderSubjectTest::testNotifiesAllObserversInSubscriptionOrder` |
| CP-26 | `Events/OrderSubjectTest::testSmsObserverIsAddedWithoutTouchingTheSubject` |
| CP-27 | `Events/OrderObserverOutputTest::testEmailObserverWritesItsNotice` |
| CP-28 | `Events/OrderObserverOutputTest::testSmsObserverWritesItsNotice` |
| CP-29 | `Events/OrderObserverOutputTest::testDashboardObserverWritesItsNotice` |
| CN-12 | `Events/OrderSubjectTest::testFailingObserverDoesNotStopTheOthersAndTheErrorIsRethrown` |
| CN-13 | `Events/OrderSubjectTest::testSameObserverSubscribedTwiceReceivesTwoNotices` |
| CN-14 | `Events/OrderSubjectTest::testDoesNotNotifyObserversThatWereNotSubscribed` |
| CP-30 | `Models/OrderTest::testKeepsTheOrderData` |
| CN-15 | `Models/OrderTest::testRejectsNonPositiveId` |
| CN-16 | `Models/OrderTest::testRejectsBlankPatient` |
| CN-17 | `Models/OrderTest::testRejectsNonPositiveAmount` |
| CN-18 | `Models/OrderTest::testRejectsZeroAmount` |
| CP-31 | `Repositories/OrderRepositoryTest::testSavesAndReturnsTheOrderWithItsTotal` |
| CP-32 | `Repositories/OrderRepositoryTest::testAllReturnsEverySavedOrder` |
| CP-33 | `Repositories/DemoOrderSeederTest::testSeedsTwoOrdersWithTheTotalsFromTheStrategies` |
| CN-19 | `Repositories/OrderRepositoryTest::testReturnsNullForAnUnknownId` |
| CN-20 | `Repositories/OrderRepositoryTest::testReturnsEmptyListWhenNothingWasSaved` |
| CN-21 | `Repositories/OrderRepositoryTest::testEscapesQuotesInThePatientName` |
| CP-34 | `Services/OrderFacadeTest::testReturnsTheTotalCalculatedByTheStrategy` |
| CP-35 | `Services/OrderFacadeTest::testSavesTheOrderWithTheCalculatedTotal` |
| CP-36 | `Services/OrderFacadeTest::testNotifiesTheObservers` |
| CP-37 | `Services/OrderFacadeTest::testSendsTheNotification` |
| CN-22 | `Services/OrderFacadeTest::testRejectsAnUnsupportedTypeAndDoesNotSaveTheOrder` |
| CN-23 | `Services/OrderFacadeTest::testPropagatesTheErrorOfAFailingObserver` |
| CP-38 | `Pricing/PriceCalculatorFactoryTest::testParticularPaysTheFullAmount` |
| CP-39 | `Pricing/PriceCalculatorFactoryTest::testObraSocialGetsTheInsuranceStrategy` |
| CP-40 | `Pricing/PriceCalculatorFactoryTest::testJubiladoGetsTheRetiredStrategy` |
| CP-41 | `Pricing/PriceCalculatorFactoryTest::testPrepagaGetsThePrepaidStrategy` |
| CN-24 | `Pricing/PriceCalculatorFactoryTest::testRejectsAnUnsupportedPatientType` |
| CN-25 | `Pricing/PriceCalculatorFactoryTest::testPatientTypeIsCaseSensitive` |
| CP-42 | `Views/ViewTest::testRendersTheTemplateWithTheGivenData` |
| CP-43 | `Views/ViewTest::testOrdersViewShowsTheTotalsItReceives` |
| CP-44 | `Views/ViewTest::testEscapeEscapesHtmlSpecialCharacters` |
| CN-26 | `Views/ViewTest::testThrowsWhenTheTemplateDoesNotExist` |
| CN-27 | `Views/ViewTest::testOrdersViewEscapesThePatientName` |
| CP-45 | `Controllers/OrderControllerTest::testCreateReturnsTheCreatedOrder` |
| CP-46 | `Controllers/OrderControllerTest::testIndexReturnsTheOrdersTable` |
| CP-47 | `Controllers/OrderControllerTest::testReportReturnsTheDecoratedReport` |
| CN-28 | `Controllers/OrderControllerTest::testCreateReturnsTheErrorViewForAnInvalidAmount` |
| CN-29 | `Controllers/OrderControllerTest::testCreateReturnsTheErrorViewForAnUnsupportedType` |
| CP-48 | `Database/ConnectionTest::testAlwaysReturnsTheSameInstance` |
| CP-49 | `Database/ConnectionTest::testKeepsTheExecutedQueries` |
| CN-30 | `Database/ConnectionTest::testConstructorIsPrivate` |
| CN-31 | `Database/ConnectionTest::testRejectsAnEmptyQuery` |
| CN-32 | `Database/ConnectionTest::testResetDiscardsTheInstance` |

### F14. Conexión a la base (Singleton)

| ID | Descripción | Precondiciones | Datos de entrada | Pasos | Resultado esperado | Tipo |
|---|---|---|---|---|---|---|
| CP-48 | Siempre devuelve la misma instancia | Autoloader cargado | — | Pedir la conexión dos veces | Las dos referencias apuntan al mismo objeto | Positivo |
| CP-49 | Guarda las consultas ejecutadas | Autoloader cargado | `SELECT 1` y `SELECT 2` | `ejecutar()` dos veces y llamar `consultas()` | Devuelve `['SELECT 1', 'SELECT 2']` | Positivo |
| CN-30 | El constructor es privado | Autoloader cargado | Clase `Connection` | Inspeccionar el constructor por reflexión | Es privado: nadie puede hacer `new Connection()` | Negativo |
| CN-31 | Una consulta vacía lanza en vez de tragarse el error | Autoloader cargado | Consulta `'   '` | `ejecutar('   ')` | `InvalidArgumentException` con mensaje `La consulta no puede estar vacia` | Negativo |
| CN-32 | `reset()` descarta la instancia | Autoloader cargado | Una conexión ya creada | Pedir la conexión, llamar `reset()` y volver a pedirla | Se obtiene una instancia distinta | Negativo |

### F12. Renderizado de vistas

| ID | Descripción | Precondiciones | Datos de entrada | Pasos | Resultado esperado | Tipo |
|---|---|---|---|---|---|---|
| CP-42 | Renderiza la plantilla con los datos recibidos | Autoloader cargado | Plantilla `pedido-creado` con paciente y total | `View::render('pedido-creado', $datos)` | El HTML contiene `<h1>Pedido creado</h1>`, el paciente, el total y el reporte | Positivo |
| CP-43 | La vista de pedidos muestra los totales que recibe | Autoloader cargado | Un pedido con total `10500.0` | `View::render('orders', ['pedidos' => [...]])` | El HTML contiene `10500` y el nombre del paciente | Positivo |
| CP-44 | `escape()` escapa los caracteres especiales de HTML | Autoloader cargado | `<script>alert("xss")</script>` | `View::escape($valor)` | Devuelve `&lt;script&gt;...` sin `<script>` | Positivo |
| CN-26 | Una plantilla inexistente lanza un error | Autoloader cargado | Nombre de vista `inexistente` | `View::render('inexistente')` | `RuntimeException` con mensaje `No existe la vista: inexistente` | Negativo |
| CN-27 | La vista escapa el nombre del paciente (XSS) | Autoloader cargado | Paciente `<script>alert("xss")</script>` | Renderizar la vista `orders` | El HTML **no** contiene `<script>`; sí `&lt;script&gt;` | Negativo |

### F13. Controlador de pedidos (MVC)

| ID | Descripción | Precondiciones | Datos de entrada | Pasos | Resultado esperado | Tipo |
|---|---|---|---|---|---|---|
| CP-45 | `create()` devuelve el HTML del pedido creado | Autoloader cargado | `id=5`, `paciente=Ana Gomez`, `monto=22000`, `tipo=particular` | `OrderController::create()` | El HTML contiene `<h1>Pedido creado</h1>`, `Ana Gomez` y `22000` | Positivo |
| CP-46 | `index()` devuelve la tabla de pedidos | Autoloader cargado | Repositorio con los 2 pedidos de demostración | `OrderController::index()` | El HTML contiene `<h1>Pedidos</h1>`, `Juan Perez` y `10500` | Positivo |
| CP-47 | `report()` devuelve el reporte con decoradores | Autoloader cargado | Contenido `Pedidos del dia` | `OrderController::report()` | `Reporte: Pedidos del dia + firma digital + PDF + marca de agua` | Positivo |
| CN-28 | `create()` con un importe inválido devuelve la vista de error | Autoloader cargado | `monto=-5` | `OrderController::create()` | El HTML contiene `<h1>Pedido rechazado</h1>` y el mensaje del error | Negativo |
| CN-29 | `create()` con un tipo no soportado devuelve la vista de error | Autoloader cargado | `tipo=xyz` | `OrderController::create()` | El HTML contiene `<h1>Pedido rechazado</h1>` y `no soportado` | Negativo |

---

## 7. Integración continua

`.github/workflows/ci.yml` se ejecuta en **cada `push` y en cada `pull_request`**,
en PHP 8.2 y 8.4, con tres pasos:

1. **Instalar dependencias:** `composer install --no-interaction --prefer-dist`.
2. **Lint:** `composer lint` → `php -l` sobre `src/`, `tests/` y `public/`.
3. **Pruebas:** `composer test` → `phpunit`.

Si algún paso falla, el workflow queda en rojo y el PR no se puede mergear.
