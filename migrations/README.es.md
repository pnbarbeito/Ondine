# Ondine - Migraciones

Este directorio contiene archivos de migración de base de datos para el framework Ondine. Las migraciones se utilizan para gestionar cambios en el esquema de base de datos de manera versionada y repetible.

## Estructura de Migraciones

Cada archivo de migración es un archivo PHP que retorna un array con operaciones de migración:

```php
<?php
return [
  'up' => function ($pdo) {
    // Código para aplicar la migración
    $pdo->exec("CREATE TABLE ejemplo (id INTEGER PRIMARY KEY)");
  },
  'down' => function ($pdo) {
    // Código para revertir la migración (opcional)
    $pdo->exec("DROP TABLE ejemplo");
  }
];
```

## Convención de Nomenclatura

Los archivos de migración siguen el formato: `YYYYMMDD_HHMM_descripcion.php`

- `YYYYMMDD`: Fecha en formato año-mes-día
- `HHMM`: Hora en formato hora-minuto
- `descripcion`: Breve descripción de lo que hace la migración

Ejemplo: `20250922_0001_create_profiles_users.php`

## Cómo Funcionan las Migraciones

- Las migraciones se ejecutan en orden cronológico basado en el nombre del archivo
- La clase `Ondine\Database\Migrator` gestiona la ejecución de migraciones
- Se llama a la función `up` de cada migración para aplicar cambios
- La función `down` (si se proporciona) puede usarse para revertir cambios

## Compatibilidad de Base de Datos

Las migraciones deben escribirse para funcionar tanto con SQLite como con MariaDB/MySQL:

- Usa `INTEGER PRIMARY KEY AUTOINCREMENT` para IDs auto-incrementales (funciona en ambos)
- Usa `TEXT` para campos de cadena
- Usa `DATETIME` para timestamps
- Verifica la variable de entorno `DB_DRIVER` si necesitas código específico del driver

## Ejecutando Migraciones

Usa el script de migración desde la raíz del proyecto:

```bash
# Aplicar todas las migraciones pendientes
php scripts/migrate.php migrate

# Revertir la última migración
php scripts/migrate.php rollback
```

## Mejores Prácticas

- Mantén las migraciones pequeñas y enfocadas en un solo cambio
- Prueba las migraciones tanto en SQLite como en MariaDB
- Incluye funcionalidad de rollback cuando sea posible
- Usa nombres descriptivos para los archivos de migración
- Evita modificar migraciones existentes después de que se hayan aplicado en producción

## Migraciones Actuales

- `20250922_0001_create_profiles_users.php`: Crea las tablas profiles y users
- `20250922_0002_unique_user.php`: Agrega restricciones únicas
- `20250922_0003_create_sessions.php`: Crea la tabla sessions

---

Para más información sobre el módulo de base de datos, consulta `src/Database/README.md`.