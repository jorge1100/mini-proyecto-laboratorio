# Entorno Docker — laboratorio-pedidos

Todo el proyecto vive en **esta sola carpeta** (`mini-proyecto-laboratorio/`):

```
mini-proyecto-laboratorio/
├── Dockerfile
├── docker-compose.yml
├── .dockerignore
├── config/
│   ├── database.example.php  (versionado, valores vacíos)
│   └── database.php          (local, ignorado por git)
├── public/
│   ├── index.php             (punto de entrada, DocumentRoot)
│   └── .htaccess
├── src/                      (Database, Models, Pricing, Notifications, etc.)
├── views/
│   └── orders.php
└── docs/
    ├── CONSINGA-TP.md, MAPA-DE-DEUDAS.md
    └── DOCKER.md (este archivo)
```

No hay que mover nada a `htdocs` ni instalar XAMPP. El contenedor ya apunta
el DocumentRoot a `public/`.

## Requisitos

- Docker 24+ y Docker Compose v2 (`docker compose version`)

## Levantar

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

El `volumes: - .:/var/www/html` hace que cada cambio en tu editor se vea
al recargar, **sin rebuild**. Ideal para trabajar por ramas `feat/patron-*`.

## Apagar / limpiar

```bash
docker compose down        # apaga y conserva el volumen de MySQL
docker compose down -v     # apaga y BORRA la base (empezar de cero)
```

## MySQL (opcional)

Hoy el proyecto corre **sin MySQL** (persistencia simulada en memoria en
`src/Database/Connection.php`). El servicio `db` queda levantado y sano para
cuando apliquen el Singleton con PDO real:

- Host dentro de Docker: `db:3306`
- Host desde tu PC (Workbench, DBeaver): `localhost:3307`
- Usuario: `root` / Clave: `root` / Base: `laboratorio`

Las credenciales salen de `docker-compose.yml` → `environment:` y se leen en
`config/database.php` con `getenv()`. Nunca van hardcodeadas en `src/`.

## Troubleshooting

| Síntoma | Causa probable | Fix |
|---|---|---|
| `Accion no encontrada` | URL sin `?accion=` | Usá las 3 URLs de arriba |
| Puerto 8080 ocupado | Otro Apache/XAMPP | Cambiá a `"8081:80"` en `docker-compose.yml` o apagá XAMPP |
| Cambios no se ven | Navegador cacheó | Recarga fuerte Ctrl+Shift+R |
| `db` unhealthy | Primera descarga de `mysql:8.0` lenta | Esperá 30s, `docker compose logs db` |
