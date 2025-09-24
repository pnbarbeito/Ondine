# Ondine - Módulo Middleware (ES)

Este README documenta el módulo de Middleware de Ondine: qué es un middleware en el marco, los middleware incluidos, cómo escribir, registrar y probar middleware, y recomendaciones de diseño.

## Propósito

El sistema de middleware proporciona una forma uniforme de interceptar y procesar peticiones antes y/o después de que lleguen a los controladores. Permite:

- Aplicar políticas transversales (CORS, autenticación, rate-limiting).
- Añadir logging, transformaciones de request/response, caching.
- Componer comportamientos reutilizables en la tubería de ejecución de la aplicación.

(Contenido en español equivalente al `src/Middleware/README.md` en inglés.)
