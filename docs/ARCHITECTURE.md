# Arquitectura — laboratorio-pedidos

## Capas

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

## Flujo de una creación de pedido (`?accion=crear`)

1. `index.php` resuelve la ruta `crear` y llama a `OrderController::create()`.
2. El controlador lee `$_GET`, calcula el total (deuda: debería delegar en
   `PriceCalculator` + `PricingStrategy`), persiste vía `Connection`, notifica
   vía `NotificationSender` y genera un reporte con decoradores.
3. Imprime HTML (deuda: debería delegar en una vista).

## Reglas de dependencia

- El controlador **no** debe contener SQL, reglas de negocio ni `echo` de HTML.
- El servicio **no** debe imprimir HTML.
- La vista **no** debe consultar la BD ni calcular totales.
- Las dependencias se inyectan por constructor; no se crean con `new` dentro
  de los métodos (para poder reemplazarlas en pruebas).

## Patrones por categoría

- **Creacionales**: Factory Method (notificaciones), Singleton (conexión).
- **Estructurales**: Adapter (sistema heredado), Decorator (reportes), Facade (servicio).
- **Comportamiento**: Strategy (precios), Observer (eventos).
