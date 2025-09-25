# Ondine - Archivos Principales en src/

Este README documenta los archivos PHP principales ubicados directamente en el directorio `src/`. Estos archivos constituyen el núcleo del framework Ondine, proporcionando funcionalidades esenciales para el manejo de aplicaciones web.

## Archivos Principales

### App.php
**Propósito**: Clase principal de la aplicación que coordina el enrutamiento, middleware y ejecución de solicitudes.

**Funcionalidades clave**:
- Gestión de rutas HTTP (GET, POST, PUT, DELETE) a través del router interno.
- Sistema de middleware secuencial para procesamiento de solicitudes.
- Método `run()` que procesa la solicitud global y ejecuta la cadena de middleware y controlador.

**Dependencias**: `Router.php`, `Request.php`, clases de middleware.

**Uso típico**:
```php
$app = new Ondine\App();
$app->get('/api/users', [UserController::class, 'index']);
$app->addMiddleware(new AuthMiddleware());
$app->run();
```

### autoload.php
**Propósito**: Implementa un autoloader PSR-4 para el namespace `Ondine`.

**Funcionalidades clave**:
- Registra una función de autoloading con `spl_autoload_register()`.
- Convierte nombres de clases del namespace `Ondine\` a rutas de archivos relativas.
- Carga automática de clases desde el directorio `src/`.

**Dependencias**: Ninguna externa, usa funciones SPL de PHP.

**Notas**: Debe incluirse al inicio de la aplicación para que funcione el autoloading.

### Bootstrap.php
**Propósito**: Proporciona métodos estáticos para registrar rutas de autenticación y perfiles de forma automática.

**Funcionalidades clave**:
- `registerAuthRoutes()`: Registra rutas estándar de login, refresh, logout, me, y perfiles.
- Soporte para prefijos de ruta personalizables.
- Compatible con instancias de `App` o `Router`.

**Dependencias**: `App.php`, `Router.php`, controladores de autenticación y perfiles.

**Uso típico**:
```php
Bootstrap::registerAuthRoutes($app, ['prefix' => '/api']);
```

### compat.php
**Propósito**: Proporciona compatibilidad hacia atrás para código legacy que referencia clases globales.

**Funcionalidades clave**:
- Crea aliases de clase usando `class_alias()` para clases del namespace `Ondine`.
- Actualmente incluye alias para `Env` como `\Env`.

**Dependencias**: Clases del namespace `Ondine`.

**Notas**: Útil para migraciones graduales de código que usa referencias globales.

### Env.php
**Propósito**: Maneja variables de entorno y carga de archivos `.env`.

**Funcionalidades clave**:
- Carga automática de archivos `.env` desde rutas configurables.
- Método `get()` para obtener variables de entorno con valores por defecto.
- Soporte para comillas en valores de variables.

**Dependencias**: Funciones de archivo de PHP.

**Uso típico**:
```php
Env::setDefaultEnvPath('/path/to/.env');
$dbHost = Env::get('DB_HOST', 'localhost');
```

### Request.php
**Propósito**: Representa una solicitud HTTP entrante con todos sus componentes.

**Funcionalidades clave**:
- Construcción desde variables globales de PHP (`$_SERVER`, `$_GET`, etc.).
- Parsing automático de JSON en el body para content-type `application/json`.
- Almacenamiento de atributos adicionales (usado por middleware).

**Dependencias**: Funciones de PHP para manejo de headers y parsing de URI.

**Propiedades principales**:
- `method`, `path`, `headers`, `query`, `body`, `parsedBody`
- `user`, `token_payload` (poblados por AuthMiddleware)
- `attributes` (para datos de middleware)

### Response.php
**Propósito**: Representa una respuesta HTTP con código de estado, datos y headers.

**Funcionalidades clave**:
- Construcción con estado, datos y headers opcionales.
- Métodos para modificar headers y estado.
- Envío automático de respuesta HTTP (con protección contra "headers already sent").

**Dependencias**: Funciones HTTP de PHP.

**Uso típico**:
```php
$response = new Response(200, ['data' => 'ok']);
$response->setHeader('Content-Type', 'application/json');
return $response;
```

### Router.php
**Propósito**: Maneja el enrutamiento de URLs a handlers específicos.

**Funcionalidades clave**:
- Registro de rutas con métodos HTTP y patrones de path.
- Matching de rutas con soporte para parámetros nombrados (ej. `/users/{id}`).
- Extracción automática de parámetros de la URL.

**Dependencias**: Funciones de expresiones regulares de PHP.

**Uso típico**:
```php
$router = new Router();
$router->add('GET', '/users/{id}', [UserController::class, 'show']);
$match = $router->match('GET', '/users/123');
// Retorna: ['handler' => [...], 'params' => ['id' => '123']]
```

### Validation.php
**Propósito**: Proporciona validación de datos con reglas configurables.

**Funcionalidades clave**:
- Validación de arrays de datos según reglas definidas.
- Reglas soportadas: `required`, `min:N`, `max:N`, `int`, `in:val1,val2`, `email`.
- Retorno de errores estructurados por campo.

**Dependencias**: Funciones de validación y string de PHP.

**Uso típico**:
```php
$rules = [
    'email' => ['required', 'email'],
    'age' => ['int', 'min:18']
];
$errors = Validation::validate($_POST, $rules);
if (!empty($errors)) {
    // manejar errores
}
```

## Arquitectura General

Estos archivos forman el núcleo del framework Ondine siguiendo principios de simplicidad y modularidad:

- **App.php** coordina todo el flujo de la aplicación.
- **Request/Response** manejan la comunicación HTTP.
- **Router** dirige las solicitudes a los controladores apropiados.
- **Middleware** (a través de App) permite procesamiento transversal.
- **Validation** asegura integridad de datos.
- **Env** maneja configuración externa.
- **Bootstrap** facilita configuración rápida.
- **autoload.php** y **compat.php** soportan la infraestructura de clases.

Para documentación de módulos específicos (Auth, Cache, Controllers, etc.), consulta los README en sus respectivos subdirectorios.

---

Este archivo documenta la información técnica de los archivos principales en `src/`. Para más detalles, revisa el código fuente de cada archivo.