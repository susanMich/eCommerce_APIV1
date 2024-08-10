# API de Gestión de Productos

Este repositorio contiene una API de gestión de productos construida con CodeIgniter, que incluye:
- Recursos CRUD para productos.
- Validación de login mediante JWT.
- Validación de tokens JWT para proteger rutas.

## Requisitos

- PHP 7.4 o superior
- Composer
- CodeIgniter 4
- MySQL o cualquier base de datos compatible con CodeIgniter

## Instalación

1. Clona el repositorio:

    ```bash
    git clone https://github.com/cristianzambrano/eCommerce_APIV1.git
    cd repo
    ```

2. Instala las dependencias:

    ```bash
    composer install
    ```


## Uso

### Recursos de Productos

La API proporciona endpoints para gestionar productos. A continuación se describen los endpoints principales:

- **Crear un producto (POST /productos):**
  
  ```json
  POST /productos
  {
      "nombre": "Producto A",
      "descripcion": "Descripción del Producto A",
      "precio": 100.00,
      "stock": 50
  }
