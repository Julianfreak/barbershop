# BuzzCut BarberShop

Aplicación web de gestión para una barbería, desarrollada con HTML, CSS, JavaScript, PHP y MySQL. El entorno de desarrollo se ejecuta con Docker Compose y Apache.

> **Estado:** proyecto de aprendizaje. La gestión de usuarios y el flujo de registro/consulta de ventas tienen endpoints PHP. Las pantallas de inventario, compras y reportes están presentes en el frontend, pero deben conectarse y probarse antes de considerarse funcionalidades completas.

## Tecnologías

- HTML5, CSS3 y JavaScript.
- PHP 8.2 con Apache y extensión `mysqli`.
- MySQL 5.7 (versión configurada actualmente en Docker Compose).
- Docker y Docker Compose.

## Estructura relevante

```text
barbershop/
├── backend/
│   ├── conexion.php              # Configuración local, no versionar
│   ├── conexion.example.php      # Ejemplo de configuración
│   ├── crear_usuario.php
│   ├── actualizar_usuarios.php
│   ├── eliminar_usuarios.php
│   ├── obtener_usuarios.php
│   ├── login.php
│   ├── crear_venta.php
│   └── obtener_ventas.php
├── database/
│   └── schema.sql                # Esquema y datos de catálogo iniciales
├── html/
├── images/
├── js/
├── .env.example
├── Dockerfile
├── docker-compose.yml
├── index.html
└── style.css
```

## Requisitos

- Docker Engine.
- Docker Compose v2 (`docker compose`).
- Git, si vas a clonar el repositorio.

## Instalación local con Docker

### 1. Clonar el repositorio

```bash
git clone <URL_DEL_REPOSITORIO>
cd barbershop
```

Si ya tienes el proyecto, entra a su carpeta raíz.

### 2. Crear la configuración local

```bash
cp .env.example .env
cp backend/conexion.example.php backend/conexion.php
```

Los valores de `.env.example` son solo para desarrollo local. No uses estas credenciales en producción ni subas `.env` o `backend/conexion.php` al repositorio.

### 3. Iniciar los contenedores

```bash
docker compose up -d --build
```

Abre la aplicación en <http://localhost:8888>.

Comprobar el estado:

```bash
docker compose ps
```

Consultar registros:

```bash
docker compose logs -f web mysql
```

La aplicación PHP se conecta a MySQL usando el host `mysql` y el puerto interno `3306`. Desde el equipo anfitrión, MySQL se publica en el puerto `3307`.

### 4. Inicialización de la base de datos

El archivo `database/schema.sql` crea las tablas `ROL`, `USUARIO`, `PRODUCTO`, `VENTA` y `VENTA_PRODUCTO`, y carga los roles `admin` y `empleado` junto con un catálogo de productos de ejemplo.

**Importante:** la imagen oficial de MySQL ejecuta los scripts de `/docker-entrypoint-initdb.d` únicamente cuando inicializa un directorio de datos vacío. Si ya habías iniciado el proyecto y existe el volumen `db_data`, agregar el script no recreará ni actualizará las tablas automáticamente.

Para conservar los datos existentes, realiza primero un respaldo y luego importa el esquema manualmente si corresponde. Para reiniciar completamente una base de datos local descartable, puedes ejecutar `docker compose down -v`, pero **este comando elimina el volumen y todos los datos almacenados en él**. No lo ejecutes si necesitas conservar información.

### 5. Crear el primer usuario administrador

El esquema no crea un usuario administrador ni incluye contraseñas predeterminadas. Registra el primer usuario usando el flujo de creación de usuarios una vez que la aplicación y la base de datos estén funcionando, o crea un hash seguro mediante PHP y almacénalo en la columna `USUARIO.contrasena`.

El campo `USUARIO.contrasena` debe contener el resultado de `password_hash()`, no una contraseña en texto plano.

## Modelo de datos

- `ROL`: roles disponibles para los usuarios.
- `USUARIO`: datos de acceso y perfil; cada usuario pertenece a un rol.
- `PRODUCTO`: catálogo, código, precio y stock.
- `VENTA`: empleado, servicio, nombres de productos, total y fecha.
- `VENTA_PRODUCTO`: relación entre ventas y productos, con precio unitario registrado al momento de la venta.

Los precios de servicios se definen actualmente en `backend/crear_venta.php`. Los precios de productos se consultan desde `PRODUCTO`; modifica el catálogo para reflejar los precios reales del negocio.

## Endpoints PHP identificados

| Endpoint | Propósito |
|---|---|
| `backend/login.php` | Validar credenciales e iniciar sesión PHP |
| `backend/crear_usuario.php` | Crear usuarios |
| `backend/obtener_usuarios.php` | Consultar usuarios |
| `backend/actualizar_usuarios.php` | Actualizar usuarios |
| `backend/eliminar_usuarios.php` | Eliminar usuarios |
| `backend/crear_venta.php` | Registrar una venta y sus productos en una transacción |
| `backend/obtener_ventas.php` | Consultar ventas |

## Pruebas manuales recomendadas

1. Levanta los contenedores y verifica que estén en estado `running`.
2. Comprueba la conexión a MySQL desde `backend/test_conexion.php`.
3. Registra un usuario y confirma que aparece en la tabla de usuarios.
4. Inicia sesión y confirma que la sesión PHP se mantiene al hacer solicitudes con `credentials: 'include'`.
5. Registra una venta con servicio, con productos y con ambos; verifica los datos en `VENTA` y `VENTA_PRODUCTO`.
6. Intenta registrar una venta sin sesión y confirma que el backend la rechaza.
7. Revisa que el total lo calcule el backend usando los precios almacenados, no el valor enviado por el navegador.

## Seguridad y limitaciones

- Las credenciales de desarrollo incluidas en `.env.example` son de ejemplo; no son apropiadas para producción.
- No publiques `.env`, `backend/conexion.php`, respaldos ni datos reales de clientes.
- El frontend no es una frontera de seguridad: los endpoints que cambian datos deben validar la sesión y los permisos en el servidor.
- El módulo de usuarios debe proteger las operaciones de administración con autorización del lado del servidor.
- Los módulos de inventario, compras y reportes necesitan implementación e integración del backend, además de pruebas.
- MySQL 5.7 es una versión antigua; evalúa actualizar a una versión soportada después de verificar compatibilidad.

## Objetivo del proyecto

Practicar el desarrollo de una aplicación web de extremo a extremo, incluyendo frontend, backend PHP, persistencia relacional, autenticación, contenedorización y documentación reproducible.