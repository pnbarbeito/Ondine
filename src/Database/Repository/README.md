# Ondine - Repository Layer

This directory contains the Repository layer for the Ondine Database module. Repositories provide an abstraction over database operations, implementing the Repository pattern to encapsulate data access logic.

## Overview

The Repository pattern separates the logic that retrieves data from the business logic that uses it. In Ondine, repositories:

- Provide a clean API for CRUD operations
- Handle database-specific exceptions and convert them to domain exceptions
- Encapsulate complex queries and joins
- Maintain separation between data access and business logic

## Included Repositories

### UserRepository (`UserRepository.php`)
Provides data access operations for the `users` table.

**Key methods:**
- `all()`: Retrieve all users
- `find($id)`: Find user by ID
- `create($data)`: Create new user
- `update($id, $data)`: Update existing user
- `delete($id)`: Delete user by ID
- `findByUsername($username)`: Find user by username
- `findWithProfile($id)`: Find user with joined profile information

**Features:**
- Handles password hashing (expects pre-hashed passwords)
- Converts database constraint violations to `DuplicateUsernameException`
- Supports dynamic updates (only updates provided fields)
- Includes profile joining with JSON permission parsing

## Exceptions

### DuplicateUsernameException (`DuplicateUsernameException.php`)
Thrown when attempting to create a user with a username that already exists.

**Usage:**
```php
try {
    $userId = $userRepository->create($userData);
} catch (DuplicateUsernameException $e) {
    // Handle duplicate username
    return ['error' => 'Username already exists'];
}
```

## Usage Example

```php
<?php
use Ondine\Database\Repository\UserRepository;

// Assuming $pdo is your database connection
$userRepo = new UserRepository($pdo);

// Create a new user
$userData = [
    'first_name' => 'John',
    'last_name' => 'Doe',
    'username' => 'johndoe',
    'password' => password_hash('password123', PASSWORD_DEFAULT),
    'profile_id' => 1
];

try {
    $userId = $userRepo->create($userData);
    echo "User created with ID: $userId";
} catch (DuplicateUsernameException $e) {
    echo "Username already exists";
}

// Find user with profile
$user = $userRepo->findWithProfile($userId);
if ($user) {
    echo "User: " . $user['first_name'] . " " . $user['last_name'];
    echo "Profile: " . $user['profile_name'];
    echo "Permissions: " . json_encode($user['profile_permissions']);
}
```

## Design Patterns

- **Repository Pattern**: Abstracts data access behind a clean interface
- **Exception Translation**: Converts low-level PDO exceptions to domain-specific exceptions
- **Data Transfer Objects**: Methods return associative arrays representing domain objects

## Best Practices

- Repositories should not contain business logic
- Use repositories in controllers or services, not directly in views
- Handle exceptions appropriately at the application layer
- Keep repositories focused on a single entity/table
- Test repositories with integration tests using real databases

## Future Extensions

- Add more repositories for other entities (Profiles, Sessions)
- Implement caching decorators for repositories
- Add pagination support to collection methods
- Create base repository class for common CRUD operations

---

This repository layer provides a foundation for clean data access in Ondine applications. For more information about the database module, see the parent `README.md`.