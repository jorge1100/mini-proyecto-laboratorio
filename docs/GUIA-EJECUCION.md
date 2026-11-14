# Guía de ejecución y verificación — laboratorio-pedidos

**Cómo levantar el proyecto, cómo correr las pruebas y qué valida el CI.**
Documento complementario de:

| Documento | Qué cubre |
|---|---|
| `README.md` | Vista general del proyecto |
| `docs/DOCKER.md` | El entorno Docker en detalle |
| `docs/CASOS-DE-PRUEBA.md` | Catálogo de casos de prueba (qué se prueba y por qué) |
| `docs/GUIA-EJECUCION.md` | **Este archivo**: cómo se ejecuta y se verifica todo |

---

## 1. Requisitos

| Herramienta | Versión | Para qué |
|---|---|---|
| Docker + Compose | 24+ / v2 | Levantar la app (recomendado) |
| PHP | 8.2+ | Correr PHPUnit en la máquina |
| Composer | 2.x | Instalar PHPUnit |

No hace falta MySQL para ver la app: la persistencia está simulada en memoria.
El servicio `db` queda levantado y sano para cuando se aplique el Singleton
con PDO real.

---

## 2. Levantar el proyecto

### 2.1 Con Docker (recomendado)

```bash
cd mini-proyecto-laboratorio

docker compose up -d --build     # primera vez o si cambiaste el Dockerfile
docker compose ps                # verificar
docker compose logs -f app       # ver la salida del server
```

Salida esperada de `docker compose ps`:

```
NAME              IMAGE                           SERVICE   STATUS
lab-pedidos-app   mini-proyecto-laboratorio-app   app       Up (healthy)
lab-pedidos-db    mysql:8.0                       db        Up (healthy)
```

- App: **http://localhost:8080**
- MySQL: `localhost:3307` (root / root / base `laboratorio`)

### 2.2 Sin Docker (servidor embebido de PHP)

```bash
php -S localhost:8080 -t public
```

Rutas identical a Docker: `http://localhost:8080/index.php?accion=...`.

> En un clon nuevo hay que crear la configuración local primero:
> `cp config/database.example.php config/database.php`

### 2.3 Con XAMPP

Copiar la carpeta en `htdocs/` y abrir
`http://localhost/mini-proyecto-laboratorio/public/index.php`.

### 2.4 Apagar

```bash
docker compose down        # apaga y conserva el volumen de MySQL
docker compose down -v     # apaga y BORRA la base (empezar de cero)
```

> El volumen monta `./:/var/www/html`: **cada cambio en el editor se ve al
> recargar, sin rebuild**. Es lo que permite mostrar en vivo los efectos de un
> refactor durante la defensa.

---

## 3. Verificación de la aplicación

Salidas reales verificadas de esta versión:

| URL | Respuesta |
|---|---|
| `?accion=crear` | `[EMAIL] para paciente@mail.com: Pedido 1 creado` + `<h1>Pedido creado</h1>` + `<p>Total: $ 10500</p>` |
| `?accion=listar` | Tabla con `Juan Perez → 10500` y `Ana Gomez → 22000` |
| `?accion=reporte` | `Reporte: Pedidos del dia + firma digital + PDF + marca de agua` |
| `?accion=xyz` (inválida) | `HTTP 404` + `Accion no encontrada` |

```bash
# Comprobación rápida desde la terminal
curl -s "http://localhost:8080/index.php?accion=reporte"
```

Si `?accion=crear` muestra `Total: $ 10500`, la cadena completa funciona:
`OrderController` → `Connection` → `NotificationSender` → `NotificationFactory`
→ `BasicReport` + decoradores.

---

## 4. Pruebas y lint

### 4.1 En la máquina (PHP 8.4)

```bash
composer install     # instala PHPUnit en vendor/ (una sola vez)
composer lint        # php -l sobre src/, tests/ y public/
composer test        # ejecuta la suite
```

Salida final esperada:

```
OK (81 tests, 128 assertions)
```

### 4.2 Dentro del contenedor (PHP 8.2, la misma del CI)

```bash
docker compose exec app php vendor/bin/phpunit
```

También devuelve `OK (81 tests, 128 assertions)`. Esto permite **verificar la
compatibilidad con 8.2 antes de que corra GitHub Actions**.

### 4.3 Qué cubre la suite

| Suite (`tests/Unit/`) | Patrón | Tests |
|---|---|---|
| `Pricing/PriceCalculatorTest.php` | Strategy | 11 |
| `Pricing/PriceCalculatorFactoryTest.php` | Factory Method | 6 |
| `Notifications/NotificationFactoryTest.php` | Factory Method | 6 |
| `Notifications/NotificationSenderTest.php` | Factory Method | 4 |
| `Reports/ReportDecoratorTest.php` | Decorator | 8 |
| `Legacy/LegacyNotifierAdapterTest.php` | Adapter | 5 |
| `Events/OrderSubjectTest.php` | Observer | 6 |
| `Events/OrderObserverOutputTest.php` | Observer | 3 |
| `Models/OrderTest.php` | Modelo con invariantes | 5 |
| `Repositories/OrderRepositoryTest.php` | Repository | 5 |
| `Repositories/DemoOrderSeederTest.php` | Repository | 1 |
| `Services/OrderFacadeTest.php` | Facade | 6 |
| `Controllers/OrderControllerTest.php` | MVC | 5 |
| `Views/ViewTest.php` | MVC | 5 |
| `Database/ConnectionTest.php` | Singleton | 5 |
| **Total** | | **81** |

El detalle de cada caso (identificador, precondiciones, datos de entrada,
pasos y resultado esperado) está en `docs/CASOS-DE-PRUEBA.md`.

### 4.4 Comandos útiles de PHPUnit

```bash
./vendor/bin/phpunit --testdox                    # nombres legibles de cada caso
./vendor/bin/phpunit --filter testCalculateWithVat # un solo test
./vendor/bin/phpunit --testsuite Unit              # una suite puntual
./vendor/bin/phpunit --coverage-text               # cobertura (requiere xdebug/pcov)
```

---

## 5. Integración continua

Archivo: `.github/workflows/ci.yml`.

**Disparadores:** cada `push` (cualquier rama) y cada `pull_request`.

**Matriz:** PHP 8.2 y 8.4.

**Pasos de cada job:**

1. `composer install --no-interaction --prefer-dist` (instalación de dependencias).
2. `composer lint` (lint de sintaxis).
3. `composer test` (ejecución de las pruebas).

### 5.1 Verlo desde la interfaz

- Pestaña **Actions** del repo → lista de ejecuciones.
- En el PR, el check aparece abajo a la izquierda: `PHP 8.2 · lint + pruebas`
  y `PHP 8.4 · lint + pruebas`.
- Mientras esté en rojo, el PR no se puede mergear.

### 5.2 Si el CI falla

| Síntoma | Causa probable | Fix |
|---|---|---|
| `composer install` falla | `composer.lock` desactualizado | `composer update` y commitear el lock |
| `php -l` falla | Error de sintaxis | Revisar el archivo que nombre el log |
| `PHPUnit` no encontrado | `composer install` no corrió en ese job | Revisar que el paso exista en el workflow |
| La matriz se queda en rojo solo en 8.2 | Código que usa una función nueva | Ejecutar la suite dentro del contenedor: `docker compose exec app php vendor/bin/phpunit` |

### 5.3 Paridad local ↔ CI

| Versión | Cómo se replica localmente |
|---|---|
| PHP 8.4 | `composer test` en la máquina |
| PHP 8.2 | `docker compose exec app php vendor/bin/phpunit` |

---

## 6. Publicar el trabajo (paso a paso manual)

### 6.1 Autenticación

El remoto es HTTPS. Git pide un **token**, no la contraseña de la cuenta.

1. GitHub → **Settings → Developer settings → Personal access tokens → Fine-grained tokens → Generate new token**
2. Repository access → solo `jorge1100/mini-proyecto-laboratorio`
3. Permisos: **Contents → Read and write**, **Pull requests → Read and write**
4. Cuando Git pregunte: `Username` = `jorge1100`, `Password` = el token.

> El token no se versiona ni se escribe en ningún archivo del proyecto.

### 6.2 Push

```bash
git push origin main            # los 19 commits del grupo
git push -u origin feat/tests-ci
```

### 6.3 Pull Request

`https://github.com/jorge1100/mini-proyecto-laboratorio/compare/main...feat/tests-ci`

- Título: `test: agregar pruebas unitarias PHPUnit y CI con GitHub Actions`
- Cuerpo: las tres preguntas de la plantilla (deuda, qué se aplicó y por qué,
  consecuencia negativa) + checklist.
- Reviewer: otro integrante (revisión cruzada obligatoria; nunca el autor).

### 6.4 Merge

Con el CI en verde y la aprobación del otro integrante: **Merge pull request →
Create a merge commit**. Después:

```bash
git checkout main
git pull origin main
git branch -d feat/tests-ci
```

---

## 7. Checklist antes de la entrega

- [ ] `composer test` en verde local (81 tests).
- [ ] Suite verificada también en PHP 8.2 dentro del contenedor.
- [ ] `git push origin main` ejecutado: `origin/main` tiene los commits del grupo.
- [ ] `git push -u origin feat/tests-ci` ejecutado.
- [ ] PR abierto con las tres preguntas respondidas.
- [ ] CI en verde en los dos jobs (8.2 y 8.4).
- [ ] PR mergeado a `main`.
- [ ] `git log --oneline -5` muestra el commit de pruebas arriba.
- [ ] Link del repositorio subido en la tarea **antes** de la defensa.

---

## 8. Troubleshooting general

| Síntoma | Causa probable | Fix |
|---|---|---|
| `Accion no encontrada` | URL sin `?accion=` | Usá las 3 URLs de la sección 3 |
| Puerto 8080 ocupado | Otro Apache/XAMPP | `"8081:80"` en `docker-compose.yml` o apagá XAMPP |
| Cambios no se ven | Caché del navegador | Recarga fuerte `Ctrl+Shift+R` |
| `db` unhealthy | Primera descarga de `mysql:8.0` | Esperar 30 s; `docker compose logs db` |
| Clase no encontrada | Falta el archivo en `src/` | El autoload busca por nombre de clase |
| `vendor/bin/phpunit` no existe | No se corrió `composer install` | `composer install` |
| Credenciales inválidas | Falta `config/database.php` | `cp config/database.example.php config/database.php` |
