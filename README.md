# laboratorio-pedidos

![CI](https://github.com/jorge1100/mini-proyecto-laboratorio/actions/workflows/ci.yml/badge.svg)

Sistema de gestión de pedidos de un laboratorio de análisis clínicos.
**PHP 8.2+ Vanilla, sin frameworks.** Corre en Docker (recomendado)
o en XAMPP tal cual está. Composer se usa solo para las dependencias de
desarrollo (PHPUnit).

> El proyecto nació con deuda técnica sembrada a propósito (ver
> `docs/MAPA-DE-DEUDAS.md` y `docs/DEUDA-TECNICA.md`). Parte de esa deuda
> ya fue pagada aplicando patrones de diseño en ramas dedicadas
> (`feat/patron-*`). El código que queda con comentarios `❌ MAL APLICADO`
> es el que todavía está pendiente de refactor.

---

## 1. Requisitos

- **Docker** 24+ y **Docker Compose** v2 (`docker compose version`), **o**
- **XAMPP** (Apache + PHP 8.x). MySQL no es obligatorio: la persistencia está
  simulada en memoria para que el proyecto arranque sin configurar base de datos.
- **Composer** 2.x, solo para instalar PHPUnit y correr las pruebas.

---

## 2. Cómo levantarlo (Docker — recomendado)

```bash
cd mini-proyecto-laboratorio

# 1. Primera vez (o si cambiaste el Dockerfile)
docker compose up --build -d

# 2. Ver logs
docker compose logs -f app

# 3. Abrir en el navegador
# http://localhost:8080/index.php?accion=crear
# http://localhost:8080/index.php?accion=listar
# http://localhost:8080/index.php?accion=reporte
```

El volumen `./:/var/www/html` hace que cada cambio en tu editor se vea al
recargar, sin rebuild.

### MySQL (opcional)

El servicio `db` queda levantado y sano para cuando se aplique el Singleton
con PDO real:

- Host dentro de Docker: `db:3306`
- Host desde tu PC: `localhost:3307`
- Usuario: `root` / Clave: `root` / Base: `laboratorio`

Las credenciales salen de `docker-compose.yml → environment:` y se leen en
`config/database.php` con `getenv()`. Nunca van hardcodeadas en `src/`.

### Apagar / limpiar

```bash
docker compose down        # apaga y conserva el volumen de MySQL
docker compose down -v     # apaga y BORRA la base (empezar de cero)
```

---

## 3. Cómo levantarlo (XAMPP)

1. Copiar la carpeta dentro de `C:\xampp\htdocs\`.
2. Iniciar Apache desde el panel de XAMPP (MySQL no hace falta).
3. Abrir: `http://localhost/mini-proyecto-laboratorio/public/index.php`

---

## 4. Acciones disponibles

| URL | Qué hace |
|---|---|
| `public/index.php?accion=crear` | Crea un pedido pasando por toda la lógica |
| `public/index.php?accion=listar` | Lista pedidos desde la vista |
| `public/index.php?accion=reporte` | Genera un reporte combinando decoradores |

---

## 5. Pruebas unitarias e integración continua

Las pruebas cubren el código refactorizado (Strategy, Factory, Decorator y
Adapter) y corren con PHPUnit. No necesitan MySQL ni servidor de mail.

```bash
composer install     # instala PHPUnit en vendor/
composer lint        # php -l en src/, tests/ y public/
composer test        # ejecuta la suite con PHPUnit
```

Los casos de prueba documentados (identificador, precondiciones, datos de
entrada, pasos y resultado esperado) están en `docs/CASOS-DE-PRUEBA.md`.

Cada `push` y cada Pull Request disparan el workflow de GitHub Actions
(`.github/workflows/ci.yml`), que en PHP 8.2 y 8.4 instala dependencias,
corre el lint y ejecuta las pruebas.

---

## 6. Configuración local (sin credenciales versionadas)

El repositorio versiona `config/database.example.php` con valores vacíos.
El archivo real `config/database.php` está en `.gitignore` y **nunca** se
versiona.

```bash
cp config/database.example.php config/database.php
# editar config/database.php con los valores de tu entorno
```

En Docker no hace falta: `config/database.php` se genera a partir de las
variables de entorno del `docker-compose.yml`.

---

## 7. Estructura del proyecto

```
mini-proyecto-laboratorio/
├── composer.json              dependencias de desarrollo (PHPUnit)
├── phpunit.xml                configuración de la suite
├── config/
│   ├── database.example.php   (versionado, valores vacíos)
│   └── database.php           (local, ignorado por git)
├── public/
│   ├── index.php              (punto de entrada: autoload + config + rutas)
│   └── .htaccess
├── src/
│   ├── Database/              Connection (Singleton pendiente)
│   ├── Models/                Order (Repository + Strategy pendientes)
│   ├── Pricing/               PricingStrategy + estrategias (Strategy aplicado)
│   ├── Notifications/         Notification + factory (Factory aplicado)
│   ├── Legacy/                LegacyNotifier + Adapter (Adapter aplicado)
│   ├── Reports/               Report + decoradores (Decorator aplicado)
│   ├── Events/                OrderEvents (Observer pendiente)
│   ├── Services/              OrderService (Facade pendiente)
│   └── Controllers/           OrderController (MVC pendiente)
├── tests/
│   └── Unit/                  pruebas de Strategy, Factory, Decorator y Adapter
├── views/
│   └── orders.php
├── docs/
│   ├── CONSIGNA-TP.md         consigna del TP integrador
│   ├── MAPA-DE-DEUDAS.md      mapa de deudas de la clase práctica
│   ├── DEUDA-TECNICA.md       diagnóstico con evidencia
│   ├── CASOS-DE-PRUEBA.md     casos de prueba documentados
│   └── DOCKER.md              guía de Docker
└── .github/
    ├── PULL_REQUEST_TEMPLATE.md
    └── workflows/ci.yml       integración continua (lint + pruebas)
```

---

## 8. Patrones aplicados

| Patrón | Categoría | Dónde | Rama |
|---|---|---|---|
| Strategy | Comportamiento | `src/Pricing/*` | `feat/patron-strategy` |
| Factory Method | Creacional | `src/Notifications/NotificationFactory.php` | `feat/patron-factory` |
| Decorator | Estructural | `src/Reports/*Decorator.php` | `feat/patron-decorator` |
| Adapter | Estructural | `src/Legacy/LegacyNotifierAdapter.php` | `feat/patron-adapter` |

Cada patrón tiene su rama y su commit con la convención
`feat:` / `refactor:`. Ver `docs/DEUDA-TECNICA.md` para la justificación y
la consecuencia asumida de cada uno.

Los cuatro patrones tienen su suite de pruebas en `tests/Unit/`; la
trazabilidad caso ↔ test está en `docs/CASOS-DE-PRUEBA.md`.

---

## 9. Historial y flujo de trabajo

- Nadie escribe directamente en `main`.
- Una rama por patrón: `feat/patron-<nombre>`.
- Un PR por rama con las tres preguntas: qué deuda, qué patrón y por qué,
  qué consecuencia negativa.
- Cada PR revisado y aprobado por otro integrante.

Convención de commits:

```
feat:     funcionalidad nueva
fix:      corrección de un error
refactor: cambia la estructura sin cambiar el comportamiento
docs:     documentación
```

Ejemplo: `refactor(pricing): reemplazar switch por PricingStrategy`.

---

## 10. Troubleshooting

| Síntoma | Causa probable | Fix |
|---|---|---|
| `Accion no encontrada` | URL sin `?accion=` | Usá las 3 URLs de la sección 4 |
| Puerto 8080 ocupado | Otro Apache/XAMPP | Cambiá a `"8081:80"` en `docker-compose.yml` o apagá XAMPP |
| Cambios no se ven | Navegador cacheó | Recarga fuerte Ctrl+Shift+R |
| `db` unhealthy | Primera descarga de `mysql:8.0` lenta | Esperá 30s, `docker compose logs db` |
| Clase no encontrada | Falta el archivo en `src/` | El autoload busca por nombre de clase; verificá el nombre del archivo |
| `vendor/bin/phpunit` no existe | No se corrió `composer install` | Ejecutá `composer install` |
| Credenciales inválidas | Falta `config/database.php` | Copiá desde `config/database.example.php` |

---

## 11. La medida de la deuda

El descuento de obra social (`0.7`) aparecía en **5 archivos**:

```
src/Models/Order.php
src/Pricing/PriceCalculator.php   (dos veces)
src/Services/OrderService.php
src/Controllers/OrderController.php
views/orders.php
```

Después del refactor con Strategy, el valor vive en un único lugar:
`src/Pricing/InsuranceStrategy.php`. Esa diferencia es el trabajo hecho.
