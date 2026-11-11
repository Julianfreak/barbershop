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
│   ├── auth.php                   # Helper de autenticación y autorización
│   ├── conexion.php              # Configuración local, no versionar
│   ├── conexion.example.php      # Ejemplo de configuración
│   ├── crear_usuario.php
│   ├── actualizar_usuarios.php
│   ├── eliminar_usuarios.php
│   ├── obtener_usuarios.php
│   ├── login.php
│   ├── crear_venta.php
│   ├── obtener_ventas.php
│   └── test_conexion.php          # Verificación de conexión (requiere sesión)
├── database/
│   └── schema.sql                # Esquema y datos de catálogo iniciales
├── html/
├── images/
├── js/
├── .env.example
├── .htaccess                      # Protege archivos sensibles (.env, .git, .sql)
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

| Endpoint | Propósito | Autenticación |
|---|---|---|
| `backend/login.php` | Validar credenciales e iniciar sesión PHP | Pública |
| `backend/logout.php` | Cerrar sesión y destruir la sesión PHP | Sesión requerida |
| `backend/auth.php` | Helper de autenticación (incluido por otros endpoints) | — |
| `backend/crear_usuario.php` | Crear usuarios | Solo admin |
| `backend/obtener_usuarios.php` | Consultar usuarios | Solo admin |
| `backend/actualizar_usuarios.php` | Actualizar usuarios | Solo admin |
| `backend/eliminar_usuarios.php` | Eliminar usuarios | Solo admin |
| `backend/crear_venta.php` | Registrar una venta y sus productos en una transacción | Sesión requerida |
| `backend/obtener_ventas.php` | Consultar ventas | Sesión requerida |
| `backend/test_conexion.php` | Verificar conexión a MySQL | Sesión requerida |

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
- El frontend no es una frontera de seguridad: los endpoints que cambian datos validan la sesión y los permisos en el servidor mediante `backend/auth.php`.
- Los endpoints de gestión de usuarios requieren rol admin (`requerir_admin()`); las ventas y consulta de ventas requieren sesión activa (`requerir_sesion()`).
- El login solo acepta contraseñas hasheadas con `password_hash()` y regenera el ID de sesión en cada inicio.
- El archivo `.htaccess` protege `.env`, `.git/` y archivos `.sql` de ser servidos por Apache.
- `display_errors` está desactivado en producción para no filtrar errores PHP al cliente.
- Los módulos de inventario, compras y reportes necesitan implementación e integración del backend, además de pruebas.
- MySQL 5.7 es una versión antigua; evalúa actualizar a una versión soportada después de verificar compatibilidad.

## Correcciones aplicadas

### Seguridad

- **login.php**: Eliminado el fallback de contraseñas en texto plano (`|| $password === $hashAlmacenado`). Ahora solo se aceptan hashes de `password_hash()`.
- **login.php**: Añadido `session_regenerate_id(true)` para prevenir fijación de sesión.
- **login.php**: Eliminada la enumeración de usuarios (mensaje genérico "Usuario o contraseña incorrectos").
- **login.php**: Desactivado `display_errors` para no filtrar errores PHP al cliente.
- **login.php**: Eliminado el código muerto comentado (implementación anterior).
- **auth.php**: Nuevo helper de autenticación compartido. `requerir_sesion()` verifica sesión activa; `requerir_admin()` verifica rol admin.
- **crear_usuario.php, obtener_usuarios.php, actualizar_usuarios.php, eliminar_usuarios.php**: Añadida verificación de sesión y rol admin. Antes cualquiera podía crear/listar/editar/eliminar usuarios sin autenticarse.
- **eliminar_usuarios.php**: Impedida la autoeliminación. Corregido el formato de respuesta inconsistente (ahora usa `success` en todos los casos).
- **crear_usuario.php, actualizar_usuarios.php**: Añadida validación de longitud mínima de contraseña (6 caracteres) y formato de correo.
- **test_conexion.php**: Añadida verificación de sesión. Antes exponía el estado de la conexión a cualquier visitante.
- **.htaccess**: Nuevo archivo que bloquea el acceso HTTP a `.env`, `.git/`, archivos `.sql` y `conexion.php`.
- **Dockerfile**: Desactivado `display_errors` en PHP. Habilitado `mod_rewrite` y `mod_headers` para soporte de `.htaccess`.

### Bugs

- **crear_venta.php**: Añadida verificación de stock disponible y decremento del inventario dentro de la transacción al vender productos.
- **js/ventas.js**: Descomentada la llamada a `cargarVentas()` para que la tabla de ventas cargue al abrir la página.
- **html/inventario.html, compras.html, reportes.html**: Corregida la ruta del script de `../login.js` (inexistente) a `../js/autenticacion.js`.
- **html/reportes.html**: Corregido el valor duplicado `value="mes"` en las opciones de periodo ("Esta Semana" ahora usa `value="semana"`).
- **html/menu.html**: Corregido el logout que limpiaba `localStorage['role']` (clave incorrecta). Ahora limpia `sessionStorage` y `localStorage` completamente.

### Infraestructura

- **.gitignore**: Corregida la regla `*.sql` que excluía `database/schema.sql` del control de versiones. Ahora solo se ignoran dumps (`dump-*.sql`, `backup-*.sql`).
- **.gitignore**: Especificada la ruta completa `backend/conexion.php` en lugar de `conexion.php` para mayor claridad.
- **docker-compose.yml**: Añadido healthcheck para MySQL con `condition: service_healthy` en `depends_on` para evitar race conditions en el arranque.
- **Dockerfile**: Añadido `EXPOSE 80` y healthcheck.
- **.env.example**: Creado (el README lo referenciaba pero no existía).
- **backend/conexion.example.php**: Creado (el README lo referenciaba pero no existía).
- **database/schema.sql**: Ahora versionado en git (antes estaba excluido por `*.sql`).

### Backend

- **crear_usuario.php, obtener_usuarios.php**: Eliminada la introspección innecesaria `column_exists()` (el esquema siempre tiene las columnas `telefono` y `correo`).
- **crear_usuario.php, actualizar_usuarios.php**: Añadido manejo de error de duplicado (errno 1062) con mensaje claro.
- **obtener_usuarios.php**: Añadido `ORDER BY u.id ASC` para orden consistente.
- **logout.php** (nuevo): Endpoint que destruye la sesión PHP y borra la cookie de sesión.
- **js/login.js**: Añadido `credentials: 'include'` al fetch de login. Eliminado el código muerto comentado.
- **js/usuarios.js**: Añadido `credentials: 'include'` a las 3 peticiones fetch (crear, editar, eliminar). Sin esto, la cookie de sesión PHP no viajaba al backend y todas las operaciones devolvían 401.
- **html/menu.html**: El logout ahora llama a `backend/logout.php` (antes llamaba a `login.php` que no maneja logout).

### Modelo de autorización

El sistema implementa control de acceso basado en roles (RBAC) en el servidor:

1. **Autenticación**: El usuario inicia sesión en `login.php`. El servidor valida la contraseña con `password_verify()` y crea una sesión PHP almacenando `id`, `nombre_usuario` y `rol_id`.
2. **Autorización**: Cada endpoint incluye `auth.php` y llama a:
   - `requerir_sesion()`: cualquier usuario autenticado (ventas, consulta de ventas, test de conexión).
   - `requerir_admin()`: solo rol admin (crear, editar, eliminar, listar usuarios).
3. **Transporte de sesión**: El navegador envía la cookie `PHPSESSID` con cada petición. El flag `credentials: 'include'` en fetch garantiza que la cookie llegue al backend.
4. **Asignación de roles**: Solo un admin autenticado puede crear usuarios y asignar roles. Un usuario nuevo recibe el rol que el admin le asigne y no puede cambiarlo por sí mismo.
5. **UI como conveniencia**: El frontend oculta opciones según el rol (`menu.html`), pero la seguridad real la impone el backend. Manipular el frontend no permite acceso no autorizado.

## Objetivo del proyecto

Practicar el desarrollo de una aplicación web de extremo a extremo, incluyendo frontend, backend PHP, persistencia relacional, autenticación, contenedorización y documentación reproducible.