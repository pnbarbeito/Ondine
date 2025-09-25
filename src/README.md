# Ondine - Core Files in src/

This README documents the main PHP files located directly in the `src/` directory. These files constitute the core of the Ondine framework, providing essential functionalities for web application handling.

## Core Files

### App.php
**Purpose**: Main application class that coordinates routing, middleware, and request execution.

**Key functionalities**:
- HTTP route management (GET, POST, PUT, DELETE) through the internal router.
- Sequential middleware system for request processing.
- `run()` method that processes the global request and executes the middleware and controller chain.

**Dependencies**: `Router.php`, `Request.php`, middleware classes.

**Typical usage**:
```php
$app = new Ondine\App();
$app->get('/api/users', [UserController::class, 'index']);
$app->addMiddleware(new AuthMiddleware());
$app->run();
```

### autoload.php
**Purpose**: Implements a PSR-4 autoloader for the `Ondine` namespace.

**Key functionalities**:
- Registers an autoloading function with `spl_autoload_register()`.
- Converts class names from the `Ondine\` namespace to relative file paths.
- Automatic class loading from the `src/` directory.

**Dependencies**: None external, uses PHP SPL functions.

**Notes**: Must be included at the beginning of the application for autoloading to work.

### Bootstrap.php
**Purpose**: Provides static methods to automatically register authentication and profile routes.

**Key functionalities**:
- `registerAuthRoutes()`: Registers standard login, refresh, logout, me, and profile routes.
- Support for customizable route prefixes.
- Compatible with `App` or `Router` instances.

**Dependencies**: `App.php`, `Router.php`, authentication and profile controllers.

**Typical usage**:
```php
Bootstrap::registerAuthRoutes($app, ['prefix' => '/api']);
```

### compat.php
**Purpose**: Provides backward compatibility for legacy code that references global classes.

**Key functionalities**:
- Creates class aliases using `class_alias()` for classes in the `Ondine` namespace.
- Currently includes alias for `Env` as `\Env`.

**Dependencies**: Classes in the `Ondine` namespace.

**Notes**: Useful for gradual migrations of code using global references.

### Env.php
**Purpose**: Handles environment variables and loading of `.env` files.

**Key functionalities**:
- Automatic loading of `.env` files from configurable paths.
- `get()` method to obtain environment variables with default values.
- Support for quoted variable values.

**Dependencies**: PHP file functions.

**Typical usage**:
```php
Env::setDefaultEnvPath('/path/to/.env');
$dbHost = Env::get('DB_HOST', 'localhost');
```

### Request.php
**Purpose**: Represents an incoming HTTP request with all its components.

**Key functionalities**:
- Construction from PHP global variables (`$_SERVER`, `$_GET`, etc.).
- Automatic JSON parsing in the body for `application/json` content-type.
- Storage of additional attributes (used by middleware).

**Dependencies**: PHP functions for header handling and URI parsing.

**Main properties**:
- `method`, `path`, `headers`, `query`, `body`, `parsedBody`
- `user`, `token_payload` (populated by AuthMiddleware)
- `attributes` (for middleware data)

### Response.php
**Purpose**: Represents an HTTP response with status code, data, and headers.

**Key functionalities**:
- Construction with status, data, and optional headers.
- Methods to modify headers and status.
- Automatic HTTP response sending (with protection against "headers already sent").

**Dependencies**: PHP HTTP functions.

**Typical usage**:
```php
$response = new Response(200, ['data' => 'ok']);
$response->setHeader('Content-Type', 'application/json');
return $response;
```

### Router.php
**Purpose**: Handles routing of URLs to specific handlers.

**Key functionalities**:
- Route registration with HTTP methods and path patterns.
- Route matching with support for named parameters (e.g., `/users/{id}`).
- Automatic parameter extraction from the URL.

**Dependencies**: PHP regular expression functions.

**Typical usage**:
```php
$router = new Router();
$router->add('GET', '/users/{id}', [UserController::class, 'show']);
$match = $router->match('GET', '/users/123');
// Returns: ['handler' => [...], 'params' => ['id' => '123']]
```

### Validation.php
**Purpose**: Provides data validation with configurable rules.

**Key functionalities**:
- Validation of data arrays according to defined rules.
- Supported rules: `required`, `min:N`, `max:N`, `int`, `in:val1,val2`, `email`.
- Return of structured errors by field.

**Dependencies**: PHP validation and string functions.

**Typical usage**:
```php
$rules = [
    'email' => ['required', 'email'],
    'age' => ['int', 'min:18']
];
$errors = Validation::validate($_POST, $rules);
if (!empty($errors)) {
    // handle errors
}
```

## General Architecture

These files form the core of the Ondine framework following principles of simplicity and modularity:

- **App.php** coordinates the entire application flow.
- **Request/Response** handle HTTP communication.
- **Router** directs requests to appropriate controllers.
- **Middleware** (through App) allows cross-cutting processing.
- **Validation** ensures data integrity.
- **Env** handles external configuration.
- **Bootstrap** facilitates quick setup.
- **autoload.php** and **compat.php** support the class infrastructure.

For documentation of specific modules (Auth, Cache, Controllers, etc.), check the READMEs in their respective subdirectories.

---

This file documents the technical information of the core files in `src/`. For more details, review the source code of each file.