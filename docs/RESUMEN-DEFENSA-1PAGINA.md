# Chuleta de defensa (1 página) — laboratorio-pedidos

**2° parcial · Patrones + Pruebas + CI · 10 minutos · 3 ponentes**
Versión imprimible en una A4: `docs/RESUMEN-DEFENSA-1PAGINA.pdf`

---

## Los números, de memoria

| | | | |
|---|---|---|---|
| Patrones aplicados | **9** | Ejercicios guiados | **8 / 8** |
| Pruebas unitarias | **81** | Casos + / − | **49 / 32** |
| Funcionalidades probadas | **14** | Assertions | **128** |
| PHP en el CI | **8.2 y 8.4** | Ramas con CI verde | **6** |

> **La métrica:** cambiar el descuento de obra social **1 archivo** (antes 5).
> Agregar un tipo de paciente **2 archivos**: la estrategia y una línea en la fábrica.
> *«El `0.7` no aparece más en ningún archivo del código.»*

## Los 9 patrones: deuda y consecuencia

| Patrón | Deuda que resolvió | Consecuencia asumida |
|---|---|---|
| **Strategy** | El precio estaba en un `switch` | 5 clases por 10 líneas de código |
| **Factory Method** | `if` por tipo repetido en 3 archivos | La fábrica sigue siendo el único punto a tocar |
| **Decorator** | Banderas booleanas (boolean trap) | N archivos y el orden del encadenado importa |
| **Adapter** | Clase de terceros modificada + copia y pega | Una capa extra de indirección |
| **Observer** | Avisos encadenados a clases concretas | Se pierde trazabilidad; no deduplica observadores |
| **Facade** | Un método con 7 responsabilidades | Puede volver a ser la clase que hace de todo |
| **Repository** | El modelo persistía y calculaba precios | Modelo anémico y más wiring en la composición |
| **MVC + SRP** | SQL + negocio + HTML en el controlador | Más capas; hay que sostener la disciplina |
| **Singleton** | Conexión nueva por consulta, errores tragados | Estado global: se filtra entre tests |

## Las 3 respuestas que siempre van a pedir

1. **¿Por qué Facade y no un buen servicio?** El problema no era la longitud: eran
   7 responsabilidades y `new` adentro. La fachada coordina servicios inyectados
   y quedó en 5 statements.
2. **¿Qué pasa si un observer falla?** Los demás reciben el aviso igual y el error
   se relanza al final: no queda estado inconsistente ni se silencia el problema.
3. **¿Qué encontraron los tests?** Tres bugs reales: la fachada aplicaba siempre
   el 30% de obra social, un archivo con dos clases rompía el autoload, y el
   nombre del paciente no se escapaba en el SQL.

## Preguntas frecuentes (respuesta de una línea)

| Pregunta | Respuesta |
|---|---|
| ¿Por qué no un ORM o un framework? | Es un proyecto vanilla sin frameworks: el trabajo es resolver el diseño con patrones. Composer solo aporta PHPUnit, que es herramienta de desarrollo. |
| ¿Y la base real? | El `Repository` es el único que habla con la conexión: conectar MySQL es cambiar 1 archivo, no el modelo ni los servicios. |
| ¿Los tests usan la base? | No. Son unitarios: sin MySQL, sin mail, sin navegador. Las salidas se capturan. |
| ¿Cómo se arrancan? | `composer install` → `composer lint` → `composer test`. El CI hace exactamente eso. |
| ¿Si el CI falla? | El PR queda bloqueado. La matriz 8.2 / 8.4 también detecta problemas de versión. |
| ¿El Singleton es mala idea? | Para guardar el pedido actual o el usuario, sí: es una variable global disfrazada. Para la conexión, tiene sentido; el costo es el estado global. |

## Los tres patrones que pagamos ahora

- **Observer** — `OrderEvents` encadenaba tres clases concretas y si una fallaba
  las otras no se enteraban. Ahora el sujeto conoce la interfaz: agregar un aviso
  es agregar una clase.
- **Facade** — `OrderService::procesarPedidoCompleto()`: 63 líneas y 7
  responsabilidades, imposible de probar. Ahora `OrderFacade`: 5 statements, sin `new`.
- **MVC + Repository + Singleton** — el modelo no persiste ni calcula, el
  controlador no tiene SQL ni `echo`, la vista escapa la salida y la conexión es
  única con errores que se propagan.

## Frases ancla

> «Cada patrón resuelve una fila concreta del mapa de deudas, no la consigna.»
> «El enunciado pide justificar, y para eso están las consecuencias asumidas.»
> «No entregamos solo el código: entregamos las pruebas que lo sostienen y el CI que lo valida solo.»

## Relámpago: qué hacer

| | |
|---|---|
| **Reparto** | **Jorge** slides 1–4 · **Facundo** 5–7 · **Roxana** 8–10 (y el demo entre la 7 y la 8) |
| **Demo (45 s)** | `?accion=crear` → `?accion=crear&monto=-5` → `?accion=listar`. Si se traba: seguí con la slide 8. |
| **Terminal** | `composer test` → `OK (81 tests, 128 assertions)` |
| **Abrir** | PowerPoint en F5, las 3 URLs en pestañas, `docs/CASOS-DE-PRUEBA.md` a mano |
| **Si sobra tiempo** | Mostrar `git log --oneline --graph --all`: una rama y un PR por patrón |
| **Si falta tiempo** | Saltá la slide 7 y andá directo a la 8. Nunca saltes la 10. |

---

*laboratorio-pedidos · Jorge, Facundo y Roxana · Metodología de Sistemas II · UTN FRRe*
