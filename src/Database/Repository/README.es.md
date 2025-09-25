# Ondine - Capa Repository

Este directorio contiene la capa Repository del módulo Database de Ondine. Los repositorios proporcionan una abstracción sobre las operaciones de base de datos, implementando el patrón Repository para encapsular la lógica de acceso a datos.

## Resumen

El patrón Repository separa la lógica que recupera datos de la lógica de negocio que los usa. En Ondine, los repositorios:

- Proporcionan una API limpia para operaciones CRUD
- Manejan excepciones específicas de base de datos y las convierten a excepciones de dominio
- Encapsulan consultas complejas y joins
- Mantienen la separación entre acceso a datos y lógica de negocio

## Repositorios Incluidos

### UserRepository (`UserRepository.php`)
Proporciona operaciones de acceso a datos para la tabla `users`.

**Métodos principales:**
- `all()`: Recuperar todos los usuarios
- `find($id)`: Encontrar usuario por ID
- `create($data)`: Crear nuevo usuario
- `update($id, $data)`: Actualizar usuario existente
- `delete($id)`: Eliminar usuario por ID
- `findByUsername($username)`: Encontrar usuario por nombre de usuario
- `findWithProfile($id)`: Encontrar usuario con información de perfil unida

**Características:**
- Maneja hash de contraseñas (espera contraseñas pre-hasheadas)
- Convierte violaciones de restricciones de BD a `DuplicateUsernameException`
- Soporta actualizaciones dinámicas (solo actualiza campos proporcionados)
- Incluye unión de perfiles con parsing de permisos JSON

## Excepciones

### DuplicateUsernameException (`DuplicateUsernameException.php`)
Se lanza cuando se intenta crear un usuario con un nombre de usuario que ya existe.

**Uso:**
```php
try {
    $userId = $userRepository->create($userData);
} catch (DuplicateUsernameException $e) {
    // Manejar nombre de usuario duplicado
    return ['error' => 'El nombre de usuario ya existe'];
}
```

## Ejemplo de Uso

```php
<?php
use Ondine\Database\Repository\UserRepository;

// Asumiendo que $pdo es tu conexión de base de datos
$userRepo = new UserRepository($pdo);

// Crear un nuevo usuario
$userData = [
    'first_name' => 'Juan',
    'last_name' => 'Pérez',
    'username' => 'juanperez',
    'password' => password_hash('password123', PASSWORD_DEFAULT),
    'profile_id' => 1
];

try {
    $userId = $userRepo->create($userData);
    echo "Usuario creado con ID: $userId";
} catch (DuplicateUsernameException $e) {
    echo "El nombre de usuario ya existe";
}

// Encontrar usuario con perfil
$user = $userRepo->findWithProfile($userId);
if ($user) {
    echo "Usuario: " . $user['first_name'] . " " . $user['last_name'];
    echo "Perfil: " . $user['profile_name'];
    echo "Permisos: " . json_encode($user['profile_permissions']);
}
```

## Patrones de Diseño

- **Patrón Repository**: Abstrae el acceso a datos detrás de una interfaz limpia
- **Traducción de Excepciones**: Convierte excepciones PDO de bajo nivel a excepciones específicas de dominio
- **Objetos de Transferencia de Datos**: Los métodos retornan arrays asociativos representando objetos de dominio

## Mejores Prácticas

- Los repositorios no deben contener lógica de negocio
- Usa repositorios en controladores o servicios, no directamente en vistas
- Maneja excepciones apropiadamente en la capa de aplicación
- Mantén repositorios enfocados en una sola entidad/tabla
- Prueba repositorios con pruebas de integración usando bases de datos reales

## Extensiones Futuras

- Agregar más repositorios para otras entidades (Profiles, Sessions)
- Implementar decoradores de caché para repositorios
- Agregar soporte de paginación a métodos de colección
- Crear clase base de repositorio para operaciones CRUD comunes

---

Esta capa de repositorio proporciona una base para acceso limpio a datos en aplicaciones Ondine. Para más información sobre el módulo de base de datos, consulta el `README.md` padre.