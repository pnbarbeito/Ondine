Ejemplo de uso local del paquete Ondine como librería.

Este ejemplo muestra cómo consumir la librería desde otro proyecto usando la instalación por path en Composer.

Pasos:

1. En otro proyecto, agregar en `composer.json`:

{
  "repositories": [
    {
      "type": "path",
      "url": "../Ondine"
    }
  ],
  "require": {
    "pbarbeito/ondine": "dev-main"
  }
}

2. Ejecutar `composer require pbarbeito/ondine:dev-main`.
3. Ver el ejemplo `public/index.php` en este directorio para ver cómo arrancar la app.
