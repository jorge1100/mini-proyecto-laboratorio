# Guía de defensa — 2° parcial (Patrones + Pruebas + CI)

**Proyecto:** `laboratorio-pedidos` · **Momento:** defensa presencial, 10 minutos
**Integrantes:** Jorge · Facundo · Roxana
**Material de apoyo:** `docs/DEFENSA-2PARCIAL.pptx` (10 slides) ·
`docs/CASOS-DE-PRUEBA.md` · `docs/DEUDA-TECNICA.md` · `docs/GUIA-EJECUCION.md`

---

## 1. Cómo se reparte el tiempo

| Parte | Integrante | Slides | Minutos | Idea central |
|---|---|---|---|---|
| 1 | **Jorge** | 1 a 4 | ~3 min | De dónde partimos y cómo quedamos con los patrones |
| 2 | **Facundo** | 5 a 7 | ~3,5 min | Los patrones que aplicamos ahora (Observer, Facade, MVC…) |
| 3 | **Roxana** | 8 a 10 | ~3 min | Las pruebas, la integración continua y la métrica final |

Entre la slide 7 y la 8 hay un **demo de 45 segundos** (ver sección 4).

> Regla de oro: si un slide se está comiendo el tiempo, se pasa al siguiente.
> La slide 10 (métrica) y el demo no se saltean nunca.

---

## 2. Guion slide por slide

### Slide 1 — Portada · *Jorge* (~20 s)

> "Buen día. Somos el grupo de Metodología de Sistemas II y defendedos el
> trabajo práctico de patrones de diseño sobre `laboratorio-pedidos`, ampliado
> con lo de las Unidades 4 y 5: pruebas e integración continua."

No leer los nombres en voz alta: están en la slide.

### Slide 2 — De dónde partimos · *Jorge* (~60 s)

> "El proyecto venía con deuda sembrada por la cátedra: lo mapeamos en
> `docs/MAPA-DE-DEUDAS.md`. Lo más visible era que el descuento de obra social,
> el `0.7`, estaba escrito en **cinco archivos distintos**: el modelo, el
> calculador, el servicio, el controlador y la vista.
> Eso no era un bug: era la misma regla de negocio repetida en cinco capas.
> Y el único control de calidad era correr `php -l` a mano. No había ni una
> prueba ni una línea de CI."

**Qué mostrar**: la tabla del mapa de deudas (o la captura de la slide).

### Slide 3 — Qué hicimos · *Jorge* (~70 s)

> "Aplicamos **nueve patrones**, uno por rama, uno por Pull Request. Los cuatro
> que pidió el TP original —Strategy, Factory, Decorator y Adapter— más los que
> faltaban: Observer, Facade, Repository, MVC y Singleton.
> Cada patrón tiene su rama, su commit con la convención del curso y su PR con
> las tres preguntas: qué deuda, qué patrón y por qué ese, y qué consecuencia
> negativa tiene."

**Frase clave**: "No aplicamos un patrón porque lo pedía la consigna: cada uno
resuelve una fila concreta del mapa de deudas."

### Slide 4 — Strategy y Factory · *Jorge* (~60 s)

> "Con **Strategy**, cada algoritmo de precio es una clase. `PriceCalculator`
> ya no decide nada: delega en la estrategia que le inyectan.
> Para agregar un tipo de paciente no se toca el calculador, se agrega una
> clase.
> Con **Factory Method** pasa lo mismo con las notificaciones:
> `NotificationFactory` es el único lugar del sistema que conoce las clases
> concretas. `NotificationSender` y el servicio de pedidos no conocen ni una.
>
> **Consecuencia asumida**: la fábrica sigue siendo un punto único que hay que
> tocar para agregar un canal. Es un costo real: lo asumimos a propósito."

**Mostrar**: `src/Pricing/PriceCalculatorFactory.php` (el `match`).

### Slide 5 — Decorator y Adapter · *Facundo* (~60 s)

> "Con **Decorator** sacamos las banderas booleanas: `generate(contenido, true,
> true, false)`. Ahora el reporte base y cada agregado implementan el mismo
> contrato `Report` y se encadenan. El detalle que hay que decir es que **el
> orden del encadenado define el resultado**: es la consecuencia negativa que
> asumimos.
> Con **Adapter** dejamos intacta la clase de terceros: el laboratorio no la
> puede modificar. El adapter traduce nuestro `send()` al `sendMessage()` del
> proveedor. Toda la incompatibilidad queda en un archivo."

**Mostrar**: el encadenamiento en `src/Reports/ReportBuilder.php`.

### Slide 6 — Observer y Facade · *Facundo* (~70 s)

> "El patrón que más dolor causaba era el de los avisos: `OrderEvents`
> encadenaba llamadas a tres clases concretas. Agregar un aviso obligaba a
> modificar esa clase, y si uno fallaba los siguientes no se enteraban.
> Con **Observer** el sujeto conoce la interfaz, no las clases: agregar un
> aviso es agregar una clase y suscribirla. Y lo resolvimos mejor que el
> enunciado: si un observador falla, los demás **igual** se enteran y el error
> se relanza al final. No se corta la cadena en silencio.
> Con **Facade** dejamos de tener un método que hacía de todo. `OrderService`
> validaba, calculaba, guardaba, notificaba, reportaba e imprimía HTML: 63
> líneas, siete responsabilidades, `new` adentro, imposible de probar.
> Ahora `OrderFacade::procesarPedidoCompleto()` tiene cinco statements y
> coordina servicios cohesivos."

**Mostrar**: `git show --stat` del commit de la fachada, o el antes/después.

### Slide 7 — Repository, MVC y Singleton · *Facundo* (~70 s)

> "**Repository**: el modelo ya no persiste ni calcula precios. `Order` es un
> modelo con invariantes: properties de solo lectura y validación en el
> constructor. El SQL vive en un único archivo.
> **MVC**: el controlador no tiene SQL, ni reglas de negocio, ni `echo`; la
> vista no consulta la base ni calcula totales y escapa toda la salida. Eso
> cerró el XSS que tenía la vista original.
> **Singleton**: `Connection` tenía un constructor público y `obtener()`
> devolvía una conexión nueva en cada llamada, además de tragarse las
> excepciones en silencio. Ahora el constructor es privado y los errores se
> propagan.
> **Consecuencia**: el estado de la conexión es global y se filtra entre
> tests; por eso existe `Connection::reset()`."

### Slide 8 — Las pruebas · *Roxana* (~60 s)

> "Tenemos **81 pruebas unitarias** con PHPUnit: 49 casos positivos y 32
> negativos, sobre 14 funcionalidades. Cada caso está documentado con
> identificador, descripción, precondiciones, datos de entrada, pasos y
> resultado esperado, y hay trazabilidad entre el caso y el test.
> Y lo mejor: **los tests encontraron tres bugs reales** que a ojo no se
> veían. El primero: la fachada aplicaba siempre el descuento de obra social,
> aunque el paciente fuera particular. El segundo: un archivo con dos clases,
> que el autoload no encontraba. El tercero: el nombre del paciente se
> escapaba en el SQL."

**Mostrar**: la salida `OK (81 tests, 128 assertions)`.

### Slide 9 — La integración continua · *Roxana* (~50 s)

> "El workflow está en `.github/workflows/ci.yml` y se dispara en **cada push y
> en cada Pull Request**. Hace tres pasos: instala dependencias, corre el lint
> y ejecuta las pruebas. Corre en matriz sobre PHP 8.2 y 8.4.
> Las cinco ramas del trabajo quedaron en verde.
> **Consecuencia asumida**: el CI agrega tiempo y depende de un servicio
> externo: si GitHub Actions se cae, el PR queda bloqueado."

**Mostrar**: la pestaña de Actions con los 5 runs en verde.

### Slide 10 — La métrica final y cierre · *Roxana* (~50 s)

> "Volvemos a la pregunta de cierre de la consigna: ¿cuántos archivos hay que
> tocar? Para cambiar el descuento de obra social, antes cinco, **hoy uno**.
> Para agregar un tipo de paciente nuevo, **dos**: la estrategia y una línea en
> la fábrica.
> El `0.7` no aparece más en ningún archivo del código.
> Y lo del 2° parcial: no entregamos solo el código, entregamos las pruebas
> que lo sostienen, los casos documentados y el CI que lo valida solo.
> Gracias."

---

## 3. Qué tener abierto antes de empezar

| Pestaña / archivo | Para qué |
|---|---|
| El PowerPoint (10 slides) | La exposición |
| `http://localhost:8080/index.php?accion=crear` | El demo |
| `src/Pricing/PriceCalculatorFactory.php` | La tabla de tipos de paciente |
| `src/Services/OrderFacade.php` | La fachada en 5 statements |
| `src/Controllers/OrderController.php` | El controlador sin SQL ni `echo` |
| `src/Database/Connection.php` | El constructor privado |
| `docs/CASOS-DE-PRUEBA.md` | La evidencia de los casos documentados |
| La terminal con `composer test` | Mostrar `OK (81 tests, 128 assertions)` |

Tip: dejá `git log --oneline --graph --all` a mano por si preguntan por el
historial de ramas y PRs.

---

## 4. Demo en vivo (45 segundos)

Lo hace quien esté en pantalla, entre la slide 7 y la 8.

```bash
docker compose up -d          # si el stack no está arriba
```

1. **`?accion=crear`** → muestra el recorrido completo: los tres observers
   avisan, se manda la notificación y se calcula el total con la estrategia.
   *"El total de 10500 es 15000 menos el 30% de la estrategia de obra social."*
2. **`?accion=crear&monto=-5`** → la vista de error: *"El importe no puede ser
   negativo"*. Demuestra el caso negativo.
3. **`?accion=listar`** → la tabla con los totales ya calculados: la vista no
   calcula nada.

> Si el demo se traba (no hay Docker, no levanta), no lo fuerces: seguí con la
> slide 8 y mostrá la salida de `composer test` en la terminal.

---

## 5. Preguntas que puede hacer la cátedra (y cómo responder)

**1. ¿Por qué `Facade` y no simplemente un buen servicio?**
Porque el problema no era la longitud del método sino que tenía siete
responsabilidades y creaba sus dependencias con `new`. La fachada coordina
servicios cohesivos e inyectados; el método quedó en cinco statements.

**2. ¿Qué consecuencia negativa tiene la fachada?**
Que puede convertirse en la nueva "clase que hace de todo" si no se cuida. Por
eso no tiene `new` adentro y no decide reglas de negocio: el tipo de paciente
lo resuelve la fábrica de calculadores.

**3. ¿Qué pasa si un observer falla?**
Los demás observers igual reciben el aviso y al final se relanza el primer
error. Así no queda estado inconsistente ni se silencia el problema. Cero
lógica de reintentos: si un aviso crítico falla, el pedido tiene que saberlo.

**4. ¿Dónde quedó el `0.7`?**
En un solo lugar: `src/Pricing/InsuranceStrategy.php`, en el constructor
(`$discount = 0.30`). El `0.7` literal no aparece más en ningún archivo.

**5. ¿Y si el laboratorio cambia el descuento a 25%?**
Se toca **un** archivo: `InsuranceStrategy`. La fábrica no cambia porque mapea
tipos de paciente, no porcentajes.

**6. ¿Agregar un tipo de paciente cuesta mucho?**
Dos archivos: la clase `PricingStrategy` nueva y una línea en
`PriceCalculatorFactory`. Ni el modelo, ni la vista, ni el controlador, ni las
pruebas de los otros tipos se tocan.

**7. ¿Por qué no usaron un ORM o un framework?**
El enunciado es un proyecto PHP vanilla sin frameworks, y el trabajo consiste
en resolver el diseño con patrones, no en sumar dependencias. Con Composer
solo agregamos PHPUnit, que es herramienta de desarrollo.

**8. ¿Qué pasa con la base de datos real?**
El proyecto simula la persistencia en memoria (`Connection`), como venía. El
`Repository` ya es el único que habla con la conexión, así que conectar MySQL
de verdad es cambiar **un** archivo, no el modelo ni los servicios. Está
documentado en `docs/DEUDA-TECNICA.md` como deuda pendiente.

**9. ¿Los tests usan la base de datos?**
No. Son unitarios: no usan MySQL, ni servidor de mail, ni navegador. Las
notificaciones y los observers se verifican capturando la salida.

**10. ¿Qué pasa si el CI falla?**
El PR queda bloqueado hasta que se arregle. El workflow corre la matriz de PHP
8.2 y 8.4, así que también detecta problemas de compatibilidad de versión.

**11. ¿Cómo arrancan las pruebas en limpio?**
`composer install`, `composer lint`, `composer test`. Está en el README y en
`docs/GUIA-EJECUCION.md`. El CI hace exactamente esos tres pasos.

**12. ¿Por qué el Singleton les parece una mala idea?**
Lo es para guardar el pedido actual o el usuario logueado: ahí es una variable
global disfrazada. Para una conexión tiene sentido porque el recurso es único.
El costo real es el estado global compartido, que se filtra entre tests; por
eso existe `Connection::reset()`.

**13. ¿Qué tests encontraron bugs?**
Tres: la estrategia fija de obra social en la fachada, un archivo con dos
clases que rompía el autoload, y el nombre del paciente sin escapar en el SQL.
El primero apareció por un `assertSame` que dio `15399.999999999998` en vez de
`22000`.

**14. ¿Quién hizo qué?**
El historial lo muestra: ramas y commits por patrón, con revisiones cruzadas.
Cada patrón es una rama y un PR revisado por otro integrante.

---

## 6. Lo que no hay que decir

- **No** digas "usamos el patrón Observer porque lo pedía la consigna". Decí
  qué deuda resolvía.
- **No** prometas el Singleton para más cosas: la consecuencia negativa está
  documentada y la conocemos.
- **No** digas que los tests cubren todo: cubren los patrones refactorizados.
  Los flujos HTTP de punta a punta quedan fuera, y está escrito.
- **No** discutas con la cátedra sobre si un patrón "era necesario": el
  enunciado pide justificar, y para eso están las consecuencias asumidas.

---

## 7. Checklist de los 5 minutos previos

- [ ] `docker compose ps` → el stack arriba (o `docker compose up -d`)
- [ ] `composer test` en verde en la terminal
- [ ] PowerPoint abierto en pantalla completa (F5), en la slide 1
- [ ] El navegador con las tres URLs abiertas en pestañas
- [ ] `docs/CASOS-DE-PRUEBA.md` y `docs/DEUDA-TECNICA.md` a mano por si preguntan
- [ ] Celular en silencio
- [ ] Repartido los tiempos: 3 / 3,5 / 3 minutos

---

## 8. Los números, de memoria

| Dato | Valor |
|---|---|
| Patrones aplicados | **9** |
| Ejercicios guiados resueltos | **8 de 8** |
| Pruebas | **81** (49 positivas, 32 negativas) |
| Funcionalidades probadas | **14** |
| Assertions | **128** |
| Versiones de PHP en el CI | **8.2 y 8.4** |
| Archivos que hay que tocar para cambiar el descuento | **1** (antes: 5) |
| Archivos para agregar un tipo de paciente | **2** (antes: el `switch` + todos los `if`) |
| Ramas / PRs | 9 ramas con patrón + 1 de pruebas y CI |
