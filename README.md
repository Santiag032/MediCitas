# MediCitas

Sistema web para la gestión de citas médicas desarrollado con Laravel 12. Permite registrar citas, consultar información mediante una API y gestionar el acceso de los usuarios.

## Requisitos

- PHP compatible con Laravel 12.
- Composer.
- Laravel 12.
- SQLite para las pruebas automatizadas.
- MySQL o MariaDB si se configura una base de datos local diferente.

## Instalación

1. Clonar o descargar el repositorio.
2. Abrir una terminal en la carpeta del proyecto.
3. Instalar las dependencias:

   ```bash
   composer install
   ```

4. Crear el archivo de entorno:

   ```bash
   copy .env.example .env
   ```

5. Generar la clave de la aplicación:

   ```bash
   php artisan key:generate
   ```

6. Configurar la conexión a la base de datos en el archivo `.env`.
7. Ejecutar las migraciones:

   ```bash
   php artisan migrate
   ```

8. Iniciar el servidor local:

   ```bash
   php artisan serve
   ```

## Pruebas automatizadas

Ejecutar todas las pruebas con:

```bash
php artisan test
```

El entorno de pruebas está configurado en `phpunit.xml` para utilizar SQLite en memoria, de modo que las pruebas no dependan de los datos de la base de datos de desarrollo.

Las pruebas verifican:

- Registro de usuarios y autenticación mediante tokens.
- Protección de las rutas de la API.
- Consulta de citas propias y filtrado por estado.
- Registro de citas y persistencia en la base de datos.
- Envío de notificaciones por correo al médico.
- Autorización para actualizar o cancelar citas.
- Rendimiento de las consultas y prevención del problema N+1.
- Redirección de la página principal al módulo de citas.

## API disponible

Las rutas de la API se encuentran definidas en `routes/api.php`.

| Método | Endpoint | Función |
|---|---|---|
| POST | `/api/registro` | Registrar usuario |
| POST | `/api/login` | Iniciar sesión |
| GET | `/api/perfil` | Consultar perfil autenticado |
| POST | `/api/logout` | Cerrar sesión |
| GET | `/api/citas` | Consultar citas del paciente |
| POST | `/api/citas` | Registrar una cita |
| PUT | `/api/citas/{cita}` | Actualizar una cita autorizada |
| DELETE | `/api/citas/{cita}` | Cancelar una cita autorizada |

Las rutas protegidas requieren autenticación mediante Laravel Sanctum.

## Interfaz web

El módulo web permite visualizar las citas y acceder al formulario de registro.

## Notificaciones

Al registrar una cita mediante la API, el sistema intenta enviar una notificación por correo electrónico al médico asociado. La configuración de envío depende del servicio de correo definido en el entorno.

## Autor

Proyecto académico MediCitas.
