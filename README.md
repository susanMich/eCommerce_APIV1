# API de la plataforma eCommerce

Este repositorio contiene una API de la plataforma eCommerce con CodeIgniter.
En esta primera versión está incluída la gestión de productos construida , que incluye:
- Recursos CRUD para productos.
- Validación de login mediante JWT.
- Validación de tokens JWT para proteger rutas.

## Requisitos

- PHP 7.4 o superior
- Composer
- CodeIgniter 4
- MySQL o cualquier base de datos compatible con CodeIgniter

## Instalación

1. Clona el repositorios:

    ```bash
    git clone https://github.com/cristianzambrano/eCommerce_APIV1.git
    cd repo
    ```

2. Instala las dependencias:

    ```bash
    composer install
    ```


## Endpoints de la API

### 1. **Home**

- **`GET /`**
  - **Descripción:** Carga la página de inicio o la acción principal del controlador `Home`.
  - **Controlador:** `Home::index`
  - **Funcionalidad:** Muestra la página principal de la aplicación o un mensaje de bienvenida.

### 2. **Autenticación**

- **`POST /login/`**
  - **Descripción:** Permite a los usuarios autenticarse proporcionando sus credenciales.
  - **Controlador:** `Auth::login`
  - **Funcionalidad:** Valida las credenciales y, si son correctas, devuelve un token JWT para autenticar solicitudes futuras.

- **`POST /veriftoken/`**
  - **Descripción:** Verifica la validez de un token JWT.
  - **Controlador:** `Auth::validateToken`
  - **Funcionalidad:** Asegura que el token JWT es válido y no ha expirado, lo que protege rutas y verifica la autenticidad del usuario.

### 3. **Recursos de Productos**

La API proporciona una serie de endpoints CRUD para gestionar productos a través del controlador `Producto`.

- **`GET /productos`**
  - **Descripción:** Recupera una lista de todos los productos disponibles.
  - **Controlador:** `Producto::index`
  - **Funcionalidad:** Devuelve un array de productos existentes en la base de datos.

- **`GET /productos/{id}`**
  - **Descripción:** Recupera los detalles de un producto específico utilizando su ID.
  - **Controlador:** `Producto::show`
  - **Funcionalidad:** Devuelve los detalles de un producto particular si existe en la base de datos.

- **`POST /productos`**
  - **Descripción:** Crea un nuevo producto en la base de datos.
  - **Controlador:** `Producto::create`
  - **Funcionalidad:** Agrega un nuevo producto. Los datos del producto se envían en el cuerpo de la solicitud en formato JSON.

- **`PUT /productos/{id}`**
  - **Descripción:** Actualiza un producto existente en la base de datos.
  - **Controlador:** `Producto::update`
  - **Funcionalidad:** Modifica los detalles de un producto ya existente. Se requiere el ID del producto en la URL y los nuevos datos en el cuerpo de la solicitud.

- **`DELETE /productos/{id}`**
  - **Descripción:** Elimina un producto específico de la base de datos.
  - **Controlador:** `Producto::delete`
  - **Funcionalidad:** Borra un producto identificado por su ID.
