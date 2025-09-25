# Ondine - Módulo Cache

Este directorio contiene implementaciones de caché para el framework Ondine. La caché ayuda a mejorar el rendimiento almacenando datos frecuentemente accedidos en memoria o disco.

## Resumen

Ondine proporciona soluciones de caché simples y basadas en archivos, adecuadas para aplicaciones pequeñas a medianas. Las implementaciones de caché son:

- Livianas y sin dependencias
- Almacenamiento basado en archivos con soporte TTL (Time To Live)
- Configuración mediante variables de entorno
- Creación automática del directorio de caché

## Clases Incluidas

### ProfileCache (`ProfileCache.php`)
Una caché mínima basada en archivos específicamente diseñada para almacenamiento de datos de perfil.

**Características principales:**
- Almacena entradas de caché como archivos JSON en `data/cache/profile_{key}.json`
- TTL configurable (Time To Live) en segundos
- Invalidación automática de caché basada en TTL
- Soporte para configuración de TTL mediante variables de entorno
- Saneamiento seguro de claves para compatibilidad con sistema de archivos

**Configuración:**
- Directorio de caché: Por defecto `data/cache` en la raíz del proyecto
- TTL: Por defecto 60 segundos, configurable vía variable de entorno `PROFILE_CACHE_TTL`

**Ejemplo de uso:**
```php
use Ondine\Cache\ProfileCache;

$cache = new ProfileCache();

// Almacenar datos de perfil
$profileData = ['id' => 1, 'name' => 'Juan Pérez', 'permissions' => ['read', 'write']];
$cache->set('profile_1', $profileData);

// Recuperar datos de perfil
$cachedProfile = $cache->get('profile_1');
if ($cachedProfile) {
    // Usar datos en caché
    echo $cachedProfile['name'];
}

// Limpiar caché de perfil específico
$cache->clearProfile(1);

// O eliminar por clave
$cache->delete('profile_1');
```

## Estructura de Caché

Los archivos de caché se almacenan en el siguiente formato:
```
data/cache/profile_{clave_saneada}.json
```

Cada archivo de caché contiene:
```json
{
  "ts": 1640995200,
  "value": { /* datos en caché */ }
}
```

Donde:
- `ts`: Timestamp Unix cuando los datos fueron almacenados en caché
- `value`: Los datos en caché reales

## Variables de Entorno

- `PROFILE_CACHE_TTL`: Tiempo en segundos para expiración de caché (por defecto: 60)

## Mejores Prácticas

- Usar valores TTL apropiados basados en la frecuencia de actualización de datos
- Limpiar caché cuando los datos subyacentes cambien
- Monitorear el tamaño del directorio de caché para aplicaciones grandes
- Considerar Redis u otros almacenes de caché externos para aplicaciones de alto tráfico

## Mejoras Futuras

- Agregar más implementaciones de caché (Redis, Memcached)
- Implementar estrategias de calentamiento de caché
- Agregar estadísticas y monitoreo de caché
- Soporte para etiquetas de caché y operaciones masivas

---

Este módulo de caché proporciona funcionalidad básica de caché para aplicaciones Ondine. Para necesidades de caché más avanzadas, considera integrar con almacenes de caché externos.