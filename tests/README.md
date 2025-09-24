# Ondine - Tests

Este archivo documenta cómo ejecutar y contribuir al suite de pruebas del proyecto.

## Ejecutar tests

El proyecto usa PHPUnit. Desde la raíz del proyecto en Windows PowerShell:

```powershell
# Ejecutar todos los tests
.\vendor\bin\phpunit --colors=always
```

El proyecto incluye `tests/BaseTestCase.php` que crea una DB SQLite temporal para cada test y corre migraciones automáticamente.


## Configuración de entorno para tests

Los tests esperan variables de entorno que indican usar SQLite. `BaseTestCase` configura internamente:

- `DB_DRIVER=sqlite`
- `DB_SQLITE_PATH` apuntando a un archivo temporal en `data/` creado por el test case.

No es necesario cambiar la configuración del archivo `config/config.php` para correr los tests locales.


## Patrones de tests

- Tests unitarios: instanciar dependencias (repositorios, servicios) y mockear adaptadores externos.
- Tests de integración: usar `BaseTestCase` para crear una BD sqlite aislada y correr migraciones.
- Tests críticos incluidos:
  - `AuthRefreshFlowTest.php` — cubre login, refresh y logout.
  - `SessionRepositoryTest.php` — cubre persistencia, revocación y purga.
  - `RateLimitTest.php` — cubre límite de peticiones y headers `Retry-After`.


## Reglas prácticas

- Evitar estados compartidos entre tests: cada test crea su propia DB.
- No usar funciones que envíen cabeceras o impriman directamente en tests; trabajar con objetos `Response`.


---

Archivo generado automáticamente por la herramienta de desarrollo.
