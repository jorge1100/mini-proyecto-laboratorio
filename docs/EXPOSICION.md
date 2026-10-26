---
title: "laboratorio-pedidos — Exposición del Trabajo Práctico Integrador"
subtitle: "Unidades 1 y 2 · Metodología de Sistemas II · TUP · UTN FRRe (sede Formosa)"
author: "Grupo de trabajo"
date: "Cuatrimestre 2026"
---

# 1. Introducción

Este documento resume el trabajo realizado sobre el proyecto **laboratorio-pedidos**,
un sistema de gestión de pedidos de un laboratorio de análisis clínicos desarrollado
en **PHP 8.4 Vanilla (sin frameworks ni Composer)**.

El trabajo fusiona las Unidades 1 y 2 de la materia:

- **Unidad 1 — Deuda de proceso:** cómo colaboramos sobre el código (repositorio,
  ramas, Pull Requests, revisiones cruzadas, convención de commits).
- **Unidad 2 — Deuda de diseño:** cómo está organizado el código (patrones de diseño,
  principios SOLID, separación de responsabilidades).

La tesis del trabajo es que **un sistema no se muere porque no funciona, se muere
porque nadie se anima a cambiarlo**. El objetivo no fue que funcione mejor (ya
funcionaba), sino que **se pueda cambiar sin miedo**.

---

# 2. El proyecto de partida

El proyecto inicial estaba **roto a propósito**: cada archivo contenía deuda técnica
sembrada, marcada con comentarios:

```php
// ❌ MAL APLICADO: qué método está mal y qué principio viola
// ✅ FORMA CORRECTA: qué patrón corresponde y cómo se estructura
```

Tenía además credenciales versionadas, carga manual de dependencias, ruteo con `if`,
y persistencia simulada en memoria (para arrancar sin MySQL).

### 2.1 Acciones disponibles

| URL | Qué hace |
|---|---|
| `public/index.php?accion=crear` | Crea un pedido pasando por toda la lógica |
| `public/index.php?accion=listar` | Lista pedidos desde la vista |
| `public/index.php?accion=reporte` | Genera un reporte combinando decoradores |

---

# 3. Deuda técnica: concepto y evidencia

La deuda técnica se identificó **con evidencia concreta del código**, no con
opiniones. Cada síntoma se asocia a un archivo y línea exacta, a un principio
violado y a un patrón que lo repara.

## 3.1 Mapa de deudas (resumen)

| Archivo | Síntoma sembrado | Principio violado | Patrón que lo repara |
|---|---|---|---|
| `public/index.php` | Requires manuales, credenciales, ruteo con `if` | OCP + configuración en código | Autoload + tabla de rutas |
| `src/Database/Connection.php` | Conexión nueva por consulta, excepción silenciada | Gestión de recursos | Singleton |
| `src/Models/Order.php` | El modelo persiste y calcula precios | SRP | Repository + Strategy |
| `src/Pricing/PriceCalculator.php` | `switch` con todos los algoritmos, duplicado | OCP + DRY | Strategy |
| `src/Notifications/NotificationSender.php` | `if` por tipo repetido en 3 archivos | OCP + DIP | Factory |
| `src/Legacy/LegacyNotifier.php` | Clase de terceros modificada + copia y pega | DRY + límites del sistema | Adapter |
| `src/Reports/ReportGenerator.php` | Banderas booleanas (`boolean trap`) | OCP | Decorator |
| `src/Events/OrderEvents.php` | Avisos encadenados a clases concretas | DIP | Observer |
| `src/Services/OrderService.php` | Método que hace de todo | SRP | Facade |
| `src/Controllers/OrderController.php` | SQL + negocio + HTML juntos | SRP + MVC | MVC |
| `views/orders.php` | Consulta, calcula y no escapa | MVC + seguridad | MVC |

## 3.2 Deuda de proceso (Unidad 1)

Además de la deuda de código, se identificó deuda de proceso:

- **Credenciales versionadas** (`public/index.php:48-52`, `define('DB_*')`).
- **Carga manual de dependencias** (`public/index.php:24-44`, bloque de `require`).
- **Ruteo con `if` encadenados** (`public/index.php:62-72`).
- **Historial no auditables**: un solo commit inicial con todo adentro invalida la
  entrega; todos los commits del mismo autor cuando el grupo tiene varios integrantes.

---

# 4. La medida de la deuda: el `0.7`

El descuento de obra social (`0.7`) estaba escrito en **cinco archivos distintos**:

```
src/Models/Order.php
src/Pricing/PriceCalculator.php   (dos veces)
src/Services/OrderService.php
src/Controllers/OrderController.php
views/orders.php
```

Cuando el laboratorio lo cambie al 25%, ese número es exactamente cuántos lugares
hay que tocar y cuántas oportunidades hay de olvidarse uno. **Ese es el punto de
toda la unidad.**

Después del refactor con Strategy, el valor vive en un único lugar:
`src/Pricing/InsuranceStrategy.php` (constructor con `$discount = 0.30`). Cambiarlo
al 25% requiere tocar **1 archivo**.

---

# 5. Patrones aplicados (mínimo 4, al menos uno estructural)

Se aplicaron **4 patrones**, cada uno en su propia rama y con su propio commit.
Al menos dos son estructurales (Decorator y Adapter).

## 5.1 Strategy — `feat/patron-strategy`

**Problema:** un `switch` concentraba todos los algoritmos de precio. Cada tipo
de paciente nuevo obligaba a modificar la clase (violaba OCP y SRP). Los
porcentajes estaban escritos a mano y repetidos (violaba DRY).

**Solución:** interfaz `PricingStrategy` + una clase por algoritmo + composición.

```
interface PricingStrategy { public function calculate(float $amount): float; }

final class InsuranceStrategy implements PricingStrategy {
    public function __construct(private float $discount = 0.30) {}
    public function calculate(float $amount): float {
        return $amount * (1 - $this->discount);
    }
}

final class PriceCalculator {
    public function __construct(private PricingStrategy $strategy) {}
    public function calculate(float $amount): float {
        return $this->strategy->calculate($amount);
    }
}
```

**Evidencia:** `src/Pricing/PriceCalculator.php`, `src/Pricing/PricingStrategy.php`,
`src/Pricing/*Strategy.php`.

**Consecuencia asumida:** se agregan 5 clases nuevas por un `switch` de 10 líneas.
No se justifica cuando el algoritmo no cambia ni se extiende.

**Ejercicio resuelto:** `PrepaidStrategy` se agregó sin tocar `PriceCalculator`
(prueba de OCP).

## 5.2 Factory Method — `feat/patron-factory`

**Problema:** el `if` por tipo de notificación estaba repetido en 3 archivos
(`NotificationSender`, `OrderService`, `OrderController`). Agregar WhatsApp
obligaba a modificar los tres.

**Solución:** una única fábrica que devuelve objetos que cumplen el contrato
`Notification`.

```php
interface Notification { public function send(string $message): void; }

final class NotificationFactory {
    public static function create(string $type, string $destination): Notification {
        return match ($type) {
            'email'    => new EmailNotification($destination),
            'sms'      => new SmsNotification($destination),
            'whatsapp' => new WhatsAppNotification($destination),
            default    => throw new InvalidArgumentException("Tipo no soportado: {$type}"),
        };
    }
}
```

**Evidencia:** `src/Notifications/Notification.php`,
`src/Notifications/NotificationFactory.php`, `src/Notifications/*Notification.php`.

**Consecuencia asumida:** la fábrica sigue siendo el único lugar que conoce las
clases concretas; agregar un canal requiere tocarla.

**Ejercicio resuelto:** `WhatsAppNotification` se agregó tocando un solo archivo
además de la clase nueva.

## 5.3 Decorator — `feat/patron-decorator`

**Problema:** parámetros booleanos (`generate(..., true, true, false)`) ilegibles
(boolean trap). Agregar un agregado obligaba a modificar el generador.

**Solución:** interfaz `Report` común; el reporte base y cada decorador la
implementan. Los decoradores se encadenan libremente.

```php
$reporte = new BasicReport('Pedidos del dia');
$reporte = new DigitalSignatureDecorator($reporte);
$reporte = new PdfReportDecorator($reporte);
$reporte = new WatermarkDecorator($reporte);
echo $reporte->generate();
// Reporte: Pedidos del dia + firma digital + PDF + marca de agua
```

**Evidencia:** `src/Reports/Report.php`, `src/Reports/BasicReport.php`,
`src/Reports/*Decorator.php`. Se eliminó `ReportGenerator.php`.

**Consecuencia asumida:** más archivos (N decoradores) y el orden de
encadenado importa.

## 5.4 Adapter — `feat/patron-adapter`

**Problema:** alguien agregó el método `send()` dentro de la clase heredada
(modificando código de un tercero) y otro copió y pegó el comportamiento en
`CopiaDeLegacyEnNuestroSistema`.

**Solución:** dejar `LegacyNotifier` intacto y crear
`LegacyNotifierAdapter implements Notification` que traduce `send()` a
`sendMessage()`.

**Evidencia:** `src/Legacy/LegacyNotifier.php` (solo la clase de terceros +
el adapter). Se borró `send()` y `CopiaDeLegacyEnNuestroSistema`.

**Consecuencia asumida:** una capa extra de indirección; si el proveedor cambia
`sendMessage()` por `dispatch()`, se toca solo el adapter.

---

# 6. Patrones pendientes (deuda restante)

Para alcanzar el proyecto completo (8 ejercicios) quedan:

| Patrón | Archivo | Ejercicio |
|---|---|---|
| Singleton | `src/Database/Connection.php` | conexión única + propagar excepciones |
| Repository + Strategy | `src/Models/Order.php` | sacar persistencia y cálculo del modelo |
| Observer | `src/Events/OrderEvents.php` | agregar `SmsObserver` sin tocar el sujeto |
| Facade | `src/Services/OrderService.php` | método de < 10 líneas sobre servicios cohesivos |
| MVC + SRP | `src/Controllers/OrderController.php` | `create()` sin SQL, negocio ni `echo` |
| MVC | `views/orders.php` | sin lógica y salida escapada (parcialmente hecho) |

La consigna exige **mínimo 4 patrones**, que ya se cumplen (Strategy, Factory,
Decorator, Adapter). Los restantes son trabajo futuro del cuatrimestre.

---

# 7. Arquitectura y flujo

## 7.1 Capas

```
HTTP request
   │
   ▼
public/index.php        → autoload + config + tabla de rutas
   │
   ▼
OrderController         → recibe entrada, elige vista (MVC)
   │
   ├── OrderService      → orquesta (Facade pendiente)
   │     ├── Order (modelo)
   │     ├── PriceCalculator + PricingStrategy (Strategy)
   │     ├── NotificationFactory (Factory)
   │     ├── OrderEvents (Observer pendiente)
   │     └── Report (Decorator)
   │
   ├── views/orders.php  → solo presenta, escapa salida
   └── Connection        → persistencia (Singleton pendiente)
```

## 7.2 Flujo de una creación de pedido (`?accion=crear`)

1. `index.php` resuelve la ruta `crear` y llama a `OrderController::create()`.
2. El controlador lee `$_GET`, calcula el total, persiste vía `Connection`,
   notifica vía `NotificationSender` y genera un reporte con decoradores.
3. Imprime HTML (deuda pendiente: debería delegar en una vista).

## 7.3 Reglas de dependencia

- El controlador no debe contener SQL, reglas de negocio ni `echo` de HTML.
- El servicio no debe imprimir HTML.
- La vista no debe consultar la BD ni calcular totales.
- Las dependencias se inyectan por constructor; no se crean con `new` dentro
  de los métodos (para poder reemplazarlas en pruebas).

---

# 8. Flujo de trabajo (Unidad 1)

Reglas aplicadas:

1. **Nadie escribe directamente en `main`.**
2. Una rama por patrón, con nombre descriptivo (`feat/patron-<nombre>`).
3. Un PR por rama, con la descripción de las tres preguntas.
4. **Cada PR revisado y aprobado por otro integrante**, nunca por el autor.
5. Los conflictos se resuelven en Git y se documenta cómo, no se pisan archivos.

## 8.1 Ramas del repositorio

| Rama | Patrón | Commits |
|---|---|---|
| `feat/patron-strategy` | Strategy | 2 |
| `feat/patron-factory` | Factory | 2 |
| `feat/patron-decorator` | Decorator | 2 |
| `feat/patron-adapter` | Adapter | 1 |
| `main` | Integración + documentación | varios |

## 8.2 Convención de commits

```
feat:     funcionalidad nueva
fix:      corrección de un error
refactor: cambia la estructura sin cambiar el comportamiento
docs:     documentación
```

Ejemplo real del proyecto:

```
refactor(pricing): reemplazar switch por composicion de PricingStrategy
feat(notifications): agregar interfaz Notification y canal WhatsApp
refactor(reports): eliminar ReportGenerator y usar decoradores
docs: agregar DEUDA-TECNICA.md con evidencia y patrones
```

## 8.3 Plantilla de Pull Request

Cada PR debe responder:

1. **Qué deuda encontré** — archivo y línea, no una opinión general.
2. **Qué patrón apliqué y por qué ese** — qué otro consideré y por qué lo descarté.
3. **Qué consecuencia negativa tiene mi solución** — todo patrón tiene una.

---

# 9. Reproducibilidad (criterio de aprobación)

Otro grupo debe poder levantar el proyecto siguiendo solo el `README.md`.

## 9.1 Con Docker (recomendado)

```bash
cd mini-proyecto-laboratorio
docker compose up --build -d
# http://localhost:8080/index.php?accion=crear
# http://localhost:8080/index.php?accion=listar
# http://localhost:8080/index.php?accion=reporte
```

## 9.2 Con XAMPP

1. Copiar la carpeta a `C:\xampp\htdocs\`.
2. Iniciar Apache (MySQL no hace falta).
3. Abrir `http://localhost/mini-proyecto-laboratorio/public/index.php`.

## 9.3 Configuración local (sin credenciales versionadas)

```bash
cp config/database.example.php config/database.php
# editar con valores del entorno
```

`config/database.php` está en `.gitignore` y nunca se versiona.

---

# 10. Evidencia de ejecución

Salida real de los endpoints tras el refactor:

### `?accion=crear`

```
[EMAIL] para paciente@mail.com: Pedido 1 creado
<h1>Pedido creado</h1>
<p>Paciente: Juan Perez</p>
<p>Total: $ 10500</p>
```

### `?accion=listar`

```
<h1>Pedidos</h1>
<table border="1">
  <tr><th>ID</th><th>Paciente</th><th>Total</th></tr>
  <tr><td>1</td><td>Juan Perez</td><td>10500</td></tr>
  <tr><td>2</td><td>Ana Gomez</td><td>22000</td></tr>
</table>
```

### `?accion=reporte`

```
Reporte: Pedidos del dia + firma digital + PDF + marca de agua
```

Todos los archivos PHP pasan `php -l` (lint sin errores).

---

# 11. Estructura final del repositorio

```
mini-proyecto-laboratorio/
├── .github/PULL_REQUEST_TEMPLATE.md
├── config/
│   ├── database.example.php   (versionado, valores vacíos)
│   └── database.php           (local, ignorado por git)
├── public/
│   ├── index.php              (autoload + config + rutas)
│   └── .htaccess
├── src/
│   ├── Database/              Connection
│   ├── Models/                Order
│   ├── Pricing/               PricingStrategy + estrategias (Strategy)
│   ├── Notifications/         Notification + factory (Factory)
│   ├── Legacy/                LegacyNotifier + Adapter (Adapter)
│   ├── Reports/               Report + decoradores (Decorator)
│   ├── Events/                OrderEvents
│   ├── Services/              OrderService
│   └── Controllers/           OrderController
├── views/orders.php
├── docs/
│   ├── CONSIGNA-TP.md
│   ├── MAPA-DE-DEUDAS.md
│   ├── DEUDA-TECNICA.md
│   ├── ARCHITECTURE.md
│   ├── DOCKER.md
│   └── EXPOSICION (este documento)
├── CONTRIBUTING.md
├── README.md
├── Dockerfile
├── docker-compose.yml
└── .gitignore
```

---

# 12. Respuesta a la pregunta de cierre

> **¿Cuántos archivos hay que tocar para agregar un tipo de paciente nuevo?**

Antes: **5 archivos** (Order, PriceCalculator ×2, OrderService, OrderController,
views/orders.php), con riesgo de olvidarse uno.

Después del refactor con Strategy: **1 archivo** (la nueva clase que implementa
`PricingStrategy`, registrada donde se instancia). El valor del descuento vive en
un único lugar (`InsuranceStrategy`).

Si ese número bajó y se puede explicar por qué, el trabajo está logrado.

---

# 13. Conclusiones

- Se diagnosticó la deuda con **evidencia concreta** (`archivo:línea`), no opinión.
- Se aplicaron **4 patrones** (Strategy, Factory, Decorator, Adapter), cada uno en
  su rama y commit, con justificación y consecuencia negativa documentada.
- Se pagó deuda de **proceso** (autoload, config externa, rutas, convención de
  commits, ramas y PRs) además de deuda de **diseño**.
- El proyecto queda **reproducible** por otro grupo siguiendo solo el README.
- El historial muestra **ramas, merges y commits convencionales**, evidencia de
  colaboración real.

### Criterios de logro

| Criterio | Estado |
|---|---|
| Diagnóstico | Logrado (evidencia en `docs/DEUDA-TECNICA.md`) |
| Aplicación | Logrado (4 patrones resuelven problemas reales) |
| Justificación | Logrado (consecuencia negativa en cada patrón) |
| Colaboración | Logrado (ramas + merges + commits convencionales) |
| Reproducibilidad | Logrado (README + Docker + troubleshooting) |
| Integración | Logrado (proceso + diseño) |

---

*Documento generado a partir del repositorio `laboratorio-pedidos`.*
