# Guía de contribución — laboratorio-pedidos

## Flujo de trabajo

1. Crear una rama descriptiva desde `main`:
   `git checkout -b feat/patron-<nombre>` o `fix/<descripcion>`.
2. Hacer commits pequeños con convención:
   `feat:`, `fix:`, `refactor:`, `docs:`.
3. Abrir un Pull Request que responda:
   - qué deuda encontré (archivo y línea exacta),
   - qué patrón apliqué y por qué ese (y cuál descarté),
   - qué consecuencia negativa tiene mi solución.
4. Otro integrante revisa y aprueba. Nunca el autor.
5. Resolver conflictos en Git y documentar cómo. No pisar archivos.

## Reglas

- Nadie escribe directamente en `main`.
- Un patrón por rama. No mezclar cambios.
- Se borra el código viejo. No dejar métodos "por las dudas".
- Sin credenciales versionadas: `config/database.php` queda en `.gitignore`.
- Todo patrón aplicado debe tener su consecuencia negativa documentada.

## Reportar deuda nueva

Agregar una fila en `docs/DEUDA-TECNICA.md` con evidencia `archivo:línea`.
