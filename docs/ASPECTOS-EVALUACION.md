# Aspectos de Evaluación — TP Integrador (Unidades 1 y 2)

## laboratorio-pedidos · Metodología de Sistemas II · TUP · UTN FRRe (sede Formosa)

**Modalidad:** Grupal · **Tipo de instancia:** Formativa (criterios de logro, sin nota numérica) · **Fecha de entrega:** 25/09/2026

---

## 0. Introducción

Este documento detalla, con ejemplos y checklists, los cuatro aspectos que la cátedra evalúa en el Trabajo Práctico Integrador de las Unidades 1 y 2, aplicados sobre el proyecto **laboratorio-pedidos** (PHP 8.4 Vanilla, sin frameworks ni Composer).

La tesis del trabajo: **un sistema no se muere porque no funciona, se muere porque nadie se anima a cambiarlo**. Por eso se evalúa no solo que el código funcione, sino **cómo colaboramos sobre él** (proceso) y **cómo está organizado** (diseño).

Las dos capas de deuda que se pagan son:

- **Deuda de proceso (Unidad 1):** cómo colaboramos sobre el código (repositorio, ramas, Pull Requests, revisiones cruzadas, convención de commits).
- **Deuda de diseño (Unidad 2):** cómo está organizado el código (patrones de diseño, principios SOLID, separación de responsabilidades).

> **Criterio de aprobación global:** otro grupo debe poder levantar el proyecto siguiendo solo el `README.md`, y el historial debe mostrar ramas, PR y revisiones cruzadas reales entre integrantes.

---

## 1. Resumen de los aspectos evaluados

| # | Aspecto | Capa | Qué se verifica | Evidencia esperada |
|---|---|---|---|---|
| 1 | Clonado del proyecto | Proceso | Trabajar sobre el proyecto del laboratorio, incluyendo los casos que deben refactorizarse | Repositorio público/privado con acceso a la cátedra, historial real |
| 2 | Buenas prácticas y patrones de diseño | Diseño | Aplicar correctamente buenas prácticas y patrones de diseño | 4 patrones mínimo (1 estructural), con justificación y consecuencia negativa |
| 3 | Commits individuales | Proceso | Cada integrante realiza, como mínimo, un commit | Historial con autores distintos y convención de mensajes |
| 4 | Convención de ramas | Proceso | Respetar la convención de nombres y el uso de ramas visto en clase | Ramas `feat/patron-*`, PR por rama, revisión cruzada |

---

# 2. Aspecto 1 — Clonado del proyecto

## 2.1 Qué significa

"Clonado del proyecto" no es solo hacer `git clone`. Significa **trabajar sobre el proyecto del laboratorio**, incluyendo los **casos que deben refactorizarse** (los 8 ejercicios guiados del `docs/MAPA-DE-DEUDAS.md`). No se acepta empezar de cero ni entregar un proyecto distinto sin justificación; la Opción B (proyecto propio) requiere elaborar primero un mapa de deudas propio.

## 2.2 Requisitos del repositorio

- **Público**, o **privado con acceso para la cátedra**.
- **Historial real:** no se acepta un único commit inicial con todo adentro.
- **Sin credenciales versionadas:** `config/` en `.gitignore` y un `config/database.example.php` con valores vacíos.
- Debe incluir el proyecto del laboratorio con sus deudas sembradas (comentarios `❌ MAL APLICADO` / `✅ FORMA CORRECTA`).

## 2.3 Pasos para clonar y preparar el entorno

```bash
# 1. Fork del repositorio de la cátedra (laboratorio-pedidos) a la cuenta del grupo
# 2. Clonar el fork
git clone https://github.com/<grupo>/mini-proyecto-laboratorio.git
cd mini-proyecto-laboratorio

# 3. Verificar que se vea el historial base
git log --oneline --all --graph

# 4. Configurar identidad (cada integrante con su propio email)
git config user.name  "Nombre Apellido"
git config user.email "email@dominio.com"

# 5. Preparar configuración local (sin versionarla)
cp config/database.example.php config/database.php
# editar config/database.php con valores del entorno

# 6. Levantar con Docker (recomendado)
docker compose up --build -d
# http://localhost:8080/index.php?accion=crear
# http://localhost:8080/index.php?accion=listar
# http://localhost:8080/index.php?accion=reporte
```

## 2.4 Qué casos deben refactorizarse (8 ejercicios)

| # | Archivo | Consigna | Patrón |
|---|---|---|---|
| 1 | `src/Pricing/PriceCalculator.php` | Implementar `PrepaidStrategy` sin modificar `PriceCalculator` | Strategy |
| 2 | `src/Notifications/NotificationSender.php` | Agregar WhatsApp tocando un solo archivo además de la clase nueva | Factory |
| 3 | `src/Legacy/LegacyNotifier.php` | Eliminar el método agregado a la clase de terceros y la copia duplicada | Adapter |
| 4 | `src/Reports/ReportGenerator.php` | Reemplazar las banderas booleanas por decoradores encadenables | Decorator |
| 5 | `src/Events/OrderEvents.php` | Agregar `SmsObserver` sin tocar el sujeto | Observer |
| 6 | `src/Services/OrderService.php` | Dejar el método en menos de 10 líneas sin perder funcionalidad | Facade |
| 7 | `src/Controllers/OrderController.php` | Dejar `create()` sin SQL, sin reglas de negocio y sin `echo` | MVC + SRP |
| 8 | `views/orders.php` | Dejar la vista sin lógica y con la salida escapada | MVC |

> La consigna exige **mínimo 4 de los 8** para la entrega; los 8 son el proyecto completo.

## 2.5 Checklist — Clonado

- [ ] El repositorio es el fork del laboratorio (o proyecto propio con mapa de deudas).
- [ ] `git clone` funciona y el proyecto levanta con Docker o XAMPP.
- [ ] No hay credenciales versionadas (`config/database.php` en `.gitignore`, existe `config/database.example.php`).
- [ ] El historial tiene más de un commit y no es un único commit inicial con todo.
- [ ] Se incluyen los archivos con deuda sembrada (`❌ MAL APLICADO`).
- [ ] Los endpoints `?accion=crear`, `?accion=listar`, `?accion=reporte` funcionan.

---

# 3. Aspecto 2 — Buenas prácticas y patrones de diseño

## 3.1 Principios que se evalúan

| Principio | Significado | Violación típica en el proyecto |
|---|---|---|
| **SRP** | Una clase, un motivo de cambio | `OrderService::procesarPedidoCompleto()` hace validar, calcular, cobrar, guardar, notificar, reportar e imprimir |
| **OCP** | Abierto a extensión, cerrado a modificación | `switch` en `PriceCalculator` y `if` en `NotificationSender` |
| **DIP** | Depender de abstracciones, no de implementaciones concretas | Avisos encadenados a clases concretas en `OrderEvents` |
| **DRY** | El mismo conocimiento no se escribe dos veces | El `0.7` del descuento en 5 archivos |
| **MVC** | Separación modelo/vista/controlador | SQL + negocio + HTML en `OrderController::create()` |
| **Seguridad** | Escapar la salida (XSS) | `views/orders.php` no escapa la salida |

## 3.2 Patrones aplicados (mínimo 4, al menos uno estructural)

Se aplicaron **4 patrones**, cada uno en su propia rama y con su propio commit. Al menos dos son estructurales (Decorator y Adapter).

### 3.2.1 Strategy — `feat/patron-strategy` (Comportamiento)

- **Problema:** un `switch` concentraba todos los algoritmos de precio. Cada tipo de paciente nuevo obligaba a modificar la clase (violaba OCP y SRP). Los porcentajes estaban escritos a mano y repetidos (violaba DRY).
- **Solución:** interfaz `PricingStrategy` + una clase por algoritmo + composición.

```php
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

- **Evidencia:** `src/Pricing/PriceCalculator.php`, `src/Pricing/PricingStrategy.php`, `src/Pricing/*Strategy.php`.
- **Consecuencia asumida:** se agregan 5 clases nuevas por un `switch` de 10 líneas. No se justifica cuando el algoritmo no cambia ni se extiende.
- **Ejercicio resuelto:** `PrepaidStrategy` se agregó sin tocar `PriceCalculator` (prueba de OCP).

### 3.2.2 Factory Method — `feat/patron-factory` (Creacional)

- **Problema:** el `if` por tipo de notificación estaba repetido en 3 archivos (`NotificationSender`, `OrderService`, `OrderController`). Agregar WhatsApp obligaba a modificar los tres.
- **Solución:** una única fábrica que devuelve objetos que cumplen el contrato `Notification`.

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

- **Evidencia:** `src/Notifications/Notification.php`, `src/Notifications/NotificationFactory.php`, `src/Notifications/*Notification.php`.
- **Consecuencia asumida:** la fábrica sigue siendo el único lugar que conoce las clases concretas; agregar un canal requiere tocarla.
- **Ejercicio resuelto:** `WhatsAppNotification` se agregó tocando un solo archivo además de la clase nueva.

### 3.2.3 Decorator — `feat/patron-decorator` (Estructural)

- **Problema:** parámetros booleanos (`generate(..., true, true, false)`) ilegibles (boolean trap). Agregar un agregado obligaba a modificar el generador.
- **Solución:** interfaz `Report` común; el reporte base y cada decorador la implementan. Los decoradores se encadenan libremente.

```php
$reporte = new BasicReport('Pedidos del dia');
$reporte = new DigitalSignatureDecorator($reporte);
$reporte = new PdfReportDecorator($reporte);
$reporte = new WatermarkDecorator($reporte);
echo $reporte->generate();
// Reporte: Pedidos del dia + firma digital + PDF + marca de agua
```

- **Evidencia:** `src/Reports/Report.php`, `src/Reports/BasicReport.php`, `src/Reports/*Decorator.php`. Se eliminó `ReportGenerator.php`.
- **Consecuencia asumida:** más archivos (N decoradores) y el orden de encadenado importa.

### 3.2.4 Adapter — `feat/patron-adapter` (Estructural)

- **Problema:** alguien agregó el método `send()` dentro de la clase heredada (modificando código de un tercero) y otro copió y pegó el comportamiento en `CopiaDeLegacyEnNuestroSistema`.
- **Solución:** dejar `LegacyNotifier` intacto y crear `LegacyNotifierAdapter implements Notification` que traduce `send()` a `sendMessage()`.
- **Evidencia:** `src/Legacy/LegacyNotifier.php` (solo la clase de terceros + el adapter). Se borró `send()` y `CopiaDeLegacyEnNuestroSistema`.
- **Consecuencia asumida:** una capa extra de indirección; si el proveedor cambia `sendMessage()` por `dispatch()`, se toca solo el adapter.

## 3.3 Patrones pendientes (deuda restante, opcionales)

| Patrón | Archivo | Ejercicio |
|---|---|---|
| Singleton | `src/Database/Connection.php` | conexión única + propagar excepciones |
| Repository + Strategy | `src/Models/Order.php` | sacar persistencia y cálculo del modelo |
| Observer | `src/Events/OrderEvents.php` | agregar `SmsObserver` sin tocar el sujeto |
| Facade | `src/Services/OrderService.php` | método de < 10 líneas sobre servicios cohesivos |
| MVC + SRP | `src/Controllers/OrderController.php` | `create()` sin SQL, negocio ni `echo` |
| MVC | `views/orders.php` | sin lógica y salida escapada (parcialmente hecho) |

## 3.4 Cómo aplicar correctamente (lo que se evalúa)

Cada patrón debe responder **tres preguntas** en la descripción del PR:

1. **Qué deuda encontré** — archivo y línea, no una opinión general.
2. **Qué patrón apliqué y por qué ese** — qué otro consideré y por qué lo descarté.
3. **Qué consecuencia negativa tiene mi solución** — todo patrón tiene una.

Además:

- El patrón debe **resolver el problema real**, no estar puesto para cumplir la consigna (sobreingeniería).
- Se **borra el código viejo**. No dejar métodos "por las dudas" comentados.
- Las dependencias se inyectan por constructor; no se crean con `new` dentro de los métodos (para poder reemplazarlas en pruebas).
- Reglas de dependencia:
  - El controlador **no** debe contener SQL, reglas de negocio ni `echo` de HTML.
  - El servicio **no** debe imprimir HTML.
  - La vista **no** debe consultar la BD ni calcular totales.

## 3.5 Checklist — Buenas prácticas y patrones

- [ ] Mínimo 4 patrones aplicados, al menos 1 estructural.
- [ ] Cada patrón en su propia rama y commit.
- [ ] Cada PR responde las 3 preguntas (deuda, patrón + alternativa descartada, consecuencia negativa).
- [ ] No hay código viejo comentado "por las dudas".
- [ ] No hay sobreingeniería (patrón solo si resuelve un problema real).
- [ ] Los archivos PHP pasan `php -l` (lint sin errores).
- [ ] Los endpoints siguen funcionando igual que antes (no se rompe funcionalidad).

---

# 4. Aspecto 3 — Commits individuales

## 4.1 Qué se evalúa

**Cada integrante del equipo debe realizar, como mínimo, un commit.** "Todos los commits del mismo autor cuando el grupo tiene varios integrantes" es un error que **invalida la entrega**.

## 4.2 Convención de mensajes (obligatoria)

```
feat:     funcionalidad nueva
fix:      corrección de un error
refactor: cambia la estructura sin cambiar el comportamiento
docs:     documentación
```

Formato recomendado: `tipo(scope): descripción corta en imperativo`.

```
❌ git commit -m "arreglo"
❌ git commit -m "cambios"
❌ git commit -m "update"

✅ git commit -m "refactor(pricing): reemplazar switch por PricingStrategy"
✅ git commit -m "feat(notifications): agregar interfaz Notification y canal WhatsApp"
✅ git commit -m "refactor(reports): eliminar ReportGenerator y usar decoradores"
✅ git commit -m "docs: agregar DEUDA-TECNICA.md con evidencia y patrones"
```

## 4.3 Cómo asegurar que cada integrante commitee

1. Asignar un patrón por integrante (cada uno crea su rama `feat/patron-<nombre>`).
2. Cada integrante hace sus propios commits en su rama.
3. Verificar antes de entregar:

```bash
# Ver autores distintos en el historial
git log --all --format='%an <%ae>' | sort -u

# Contar commits por autor
git shortlog -sn --all

# Ver que haya más de un autor (si el grupo tiene varios integrantes)
git log --all --format='%an' | sort -u | wc -l
```

4. Si un integrante solo revisó PRs pero no commiteó, debe hacer al menos un commit (por ejemplo un `docs:` o un `fix:` pequeño en su área).

## 4.4 Checklist — Commits individuales

- [ ] Cada integrante tiene al menos un commit con su propia identidad (`user.name`/`user.email`).
- [ ] Los mensajes siguen la convención `feat:`/`fix:`/`refactor:`/`docs:`.
- [ ] No hay un único commit con todo el proyecto.
- [ ] Los commits son pequeños y descriptivos (no "arreglo" ni "cambios").

---

# 5. Aspecto 4 — Convención de ramas

## 5.1 Naming convention

```
feat/patron-<nombre>     → un patrón nuevo (strategy, factory, decorator, adapter, observer, facade)
fix/<descripcion>       → corrección de un error
refactor/<descripcion>  → cambio de estructura sin cambio de comportamiento
docs/<descripcion>      → documentación
```

Ramas del proyecto (reales):

| Rama | Patrón | Commits |
|---|---|---|
| `feat/patron-strategy` | Strategy | 2 |
| `feat/patron-factory` | Factory | 2 |
| `feat/patron-decorator` | Decorator | 2 |
| `feat/patron-adapter` | Adapter | 1 |
| `main` | Integración + documentación | varios |

## 5.2 Flujo de trabajo obligatorio (Unidad 1)

```
main          ●───────────────●──────────────●
               \             / \            /
feat/strategy   ●───●───●───●   \          /
                                 \        /
feat/observer                     ●──●───●
```

Reglas:

1. **Nadie escribe directamente en `main`.**
2. Una rama por patrón, con nombre descriptivo.
3. Un PR por rama, con la descripción de las tres preguntas.
4. **Cada PR revisado y aprobado por otro integrante**, nunca por el autor.
5. Los conflictos se resuelven en Git y se documenta cómo, no se pisan archivos.

## 5.3 Cómo crear una rama y abrir un PR

```bash
# 1. Partir siempre de main actualizado
git checkout main
git pull origin main

# 2. Crear rama descriptiva
git checkout -b feat/patron-observer

# 3. Trabajar y commitear con convención
git add src/Events/...
git commit -m "feat(events): agregar SmsObserver sin tocar el sujeto"

# 4. Subir la rama
git push -u origin feat/patron-observer

# 5. Abrir Pull Request en GitHub (usar la plantilla .github/PULL_REQUEST_TEMPLATE.md)
# 6. Otro integrante revisa y aprueba (nunca el autor)
# 7. Merge a main (resolver conflictos en Git, documentar cómo)
```

## 5.4 Plantilla de Pull Request (obligatoria)

Cada PR debe responder:

1. **Qué deuda encontré** — archivo y línea exacta.
2. **Qué patrón apliqué y por qué ese** — otro patrón que consideré y por qué lo descarté.
3. **Qué consecuencia negativa tiene mi solución** — todo patrón tiene una.

Checklist del PR (ver `.github/PULL_REQUEST_TEMPLATE.md`):

- [ ] No se rompe funcionalidad (la app hace lo mismo antes y después).
- [ ] Se borró el código viejo.
- [ ] Sin credenciales versionadas.
- [ ] Commits con convención (`feat:`/`fix:`/`refactor:`/`docs:`).

## 5.5 Resolución de conflictos

- Resolver en Git (merge/rebase), **no pisar archivos**.
- Documentar cómo se resolvió (en el PR o en un commit `fix:`).
- Si dos ramas tocan el mismo archivo, coordinar antes de mergear.

## 5.6 Checklist — Convención de ramas

- [ ] Nadie commiteó directamente en `main`.
- [ ] Una rama por patrón con nombre descriptivo (`feat/patron-<nombre>`).
- [ ] Un PR por rama.
- [ ] Cada PR revisado y aprobado por otro integrante (no el autor).
- [ ] Conflictos resueltos en Git y documentados.

---

# 6. Criterios de logro (calificación formativa)

Cada criterio se califica como **logrado / en proceso / no logrado**. Un criterio "en proceso" se puede volver a presentar corregido antes del TP siguiente.

| Criterio | Se considera logrado cuando… | Evidencia en este proyecto |
|---|---|---|
| **Diagnóstico** | La deuda se identifica con evidencia concreta del código, no con opinión | `docs/DEUDA-TECNICA.md` (15 filas con `archivo:línea`) |
| **Aplicación** | El patrón resuelve el problema real; no está puesto para cumplir la consigna | 4 patrones aplicados sobre deudas reales |
| **Justificación** | El grupo explica la consecuencia negativa de su propia solución | Consecuencia asumida en cada patrón |
| **Colaboración** | El historial muestra ramas, PR y revisiones cruzadas reales entre integrantes | Ramas `feat/patron-*` + merges + commits convencionales |
| **Reproducibilidad** | Otro grupo levanta el proyecto siguiendo solo el README | `README.md` + `docs/DOCKER.md` |
| **Integración** | El trabajo cubre las dos capas: proceso y diseño | Deuda de proceso (autoload, config, rutas, commits, ramas) + diseño (patrones) |

---

# 7. Errores que invalidan la entrega

- Un solo commit con todo el proyecto.
- Todos los commits del mismo autor cuando el grupo tiene varios integrantes.
- Patrones aplicados sin que exista el problema que resuelven (sobreingeniería).
- Código viejo comentado "por las dudas" al lado del refactor.
- Credenciales versionadas.

---

# 8. Mapa de deudas del proyecto (resumen)

| Archivo | Síntoma sembrado | Principio violado | Patrón que lo repara | Estado |
|---|---|---|---|---|
| `public/index.php` | Requires manuales, credenciales, ruteo con `if` | OCP + configuración en código | Autoload + tabla de rutas | ✅ Aplicado (proceso) |
| `src/Database/Connection.php` | Conexión nueva por consulta, excepción silenciada | Gestión de recursos | Singleton | ⏳ Pendiente |
| `src/Models/Order.php` | El modelo persiste y calcula precios | SRP | Repository + Strategy | ⏳ Pendiente |
| `src/Pricing/PriceCalculator.php` | `switch` con todos los algoritmos, duplicado | OCP + DRY | Strategy | ✅ Aplicado |
| `src/Notifications/NotificationSender.php` | `if` por tipo repetido en 3 archivos | OCP + DIP | Factory | ✅ Aplicado |
| `src/Legacy/LegacyNotifier.php` | Clase de terceros modificada + copia y pega | DRY + límites del sistema | Adapter | ✅ Aplicado |
| `src/Reports/ReportGenerator.php` | Banderas booleanas (`boolean trap`) | OCP | Decorator | ✅ Aplicado |
| `src/Events/OrderEvents.php` | Avisos encadenados a clases concretas | DIP | Observer | ⏳ Pendiente |
| `src/Services/OrderService.php` | Método que hace de todo | SRP | Facade | ⏳ Pendiente |
| `src/Controllers/OrderController.php` | SQL + negocio + HTML juntos | SRP + MVC | MVC | ⏳ Pendiente |
| `views/orders.php` | Consulta, calcula y no escapa | MVC + seguridad | MVC | ⏳ Parcial |

## 8.1 Deuda de proceso (Unidad 1) — mínimo 2 filas en `DEUDA-TECNICA.md`

- **Credenciales versionadas** (`public/index.php:48-52`, `define('DB_*')`).
- **Carga manual de dependencias** (`public/index.php:24-44`, bloque de `require`).
- **Ruteo con `if` encadenados** (`public/index.php:62-72`).
- **Historial no auditable** (un solo commit inicial con todo adentro; todos los commits del mismo autor).

## 8.2 Tabla de deuda técnica (`docs/DEUDA-TECNICA.md`) — forma requerida

| Síntoma observado | Evidencia (archivo:línea) | Tipo de deuda | Patrón aplicado | Consecuencia asumida |
|---|---|---|---|---|
| El descuento está escrito en 5 archivos | `Order.php:62`, `PriceCalculator.php:24`, … | Diseño | Strategy | 5 clases nuevas por un `switch` de 10 líneas |

Mínimo **6 filas**, de las cuales al menos **2** deben ser deuda de proceso (Unidad 1) y no de código.

---

# 9. La medida de la deuda: el `0.7`

El descuento de obra social (`0.7`) estaba escrito en **cinco archivos distintos**:

```
src/Models/Order.php
src/Pricing/PriceCalculator.php   (dos veces)
src/Services/OrderService.php
src/Controllers/OrderController.php
views/orders.php
```

Cuando el laboratorio lo cambie al 25%, ese número es exactamente cuántos lugares hay que tocar y cuántas oportunidades hay de olvidarse uno. **Ese es el punto de toda la unidad.**

Después del refactor con Strategy, el valor vive en un único lugar: `src/Pricing/InsuranceStrategy.php` (constructor con `$discount = 0.30`). Cambiarlo al 25% requiere tocar **1 archivo**.

> **Pregunta de cierre:** ¿Cuántos archivos tienen que tocar para agregar un tipo de paciente nuevo? Si al terminar el TP ese número bajó y pueden explicarlo, el trabajo está logrado.

---

# 10. Reproducibilidad (criterio de aprobación)

Otro grupo debe poder levantar el proyecto siguiendo solo el `README.md`.

## 10.1 Con Docker (recomendado)

```bash
cd mini-proyecto-laboratorio
docker compose up --build -d
# http://localhost:8080/index.php?accion=crear
# http://localhost:8080/index.php?accion=listar
# http://localhost:8080/index.php?accion=reporte
```

## 10.2 Con XAMPP

1. Copiar la carpeta a `C:\xampp\htdocs\`.
2. Iniciar Apache (MySQL no hace falta).
3. Abrir `http://localhost/mini-proyecto-laboratorio/public/index.php`.

## 10.3 Configuración local (sin credenciales versionadas)

```bash
cp config/database.example.php config/database.php
# editar con valores del entorno
```

`config/database.php` está en `.gitignore` y nunca se versiona.

---

# 11. Evidencia de ejecución (salida real tras el refactor)

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

# 12. Estructura final del repositorio

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

# 13. Checklist final de entrega

## Clonado del proyecto
- [ ] Repositorio es fork del laboratorio (o proyecto propio con mapa de deudas).
- [ ] Proyecto levanta con Docker o XAMPP.
- [ ] Sin credenciales versionadas.
- [ ] Historial real (más de un commit).

## Buenas prácticas y patrones de diseño
- [ ] Mínimo 4 patrones, al menos 1 estructural.
- [ ] Cada patrón resuelve un problema real y tiene consecuencia negativa documentada.
- [ ] Código viejo borrado (no comentado "por las dudas").
- [ ] Archivos PHP pasan `php -l`.
- [ ] Endpoints funcionan igual que antes.

## Commits individuales
- [ ] Cada integrante tiene al menos un commit con su identidad.
- [ ] Mensajes con convención `feat:`/`fix:`/`refactor:`/`docs:`.
- [ ] No hay un único commit con todo.

## Convención de ramas
- [ ] Nadie commiteó directamente en `main`.
- [ ] Una rama por patrón con nombre descriptivo.
- [ ] Un PR por rama con las 3 preguntas.
- [ ] Cada PR revisado y aprobado por otro integrante.
- [ ] Conflictos resueltos en Git y documentados.

## Documentación
- [ ] `docs/DEUDA-TECNICA.md` con mínimo 6 filas (al menos 2 de proceso).
- [ ] `README.md` permite levantar el proyecto sin preguntar.
- [ ] Convención de commits y ramas documentada.

---

# 14. Anexo A — Comandos Git útiles

```bash
# Ver historial gráfico
git log --oneline --all --graph

# Ver autores distintos
git log --all --format='%an <%ae>' | sort -u

# Contar commits por autor
git shortlog -sn --all

# Crear rama y subirla
git checkout -b feat/patron-<nombre>
git push -u origin feat/patron-<nombre>

# Actualizar main y partir rama
git checkout main && git pull origin main

# Lint de PHP
php -l src/Pricing/PriceCalculator.php

# Ver diferencias entre ramas
git diff main..feat/patron-strategy
```

---

# 15. Anexo B — Plantilla de Pull Request

```markdown
## Qué deuda encontré

Archivo y línea exacta:

## Qué patrón apliqué y por qué ese

Otro patrón que consideré y por qué lo descarté:

## Qué consecuencia negativa tiene mi solución

## Checklist

- [ ] No se rompe funcionalidad (la app hace lo mismo antes y después).
- [ ] Se borró el código viejo.
- [ ] Sin credenciales versionadas.
- [ ] Commits con convención (`feat:`/`fix:`/`refactor:`/`docs:`).
```

---

*Documento generado a partir del repositorio `laboratorio-pedidos` y de la consigna del TP Integrador (Unidades 1 y 2).*
