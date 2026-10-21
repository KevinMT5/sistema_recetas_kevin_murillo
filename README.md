# Sistema de recetas

Aplicación con API en Laravel 9 y frontend en Vue 3 para registrar recetas médicas.

## Ejecutar con Docker (recomendado)

Docker permite ejecutar este proyecto con PHP 8.2 y MySQL 8.4 sin modificar ni desinstalar el PHP 5.6 que utiliza SIS.

Desde la raíz del proyecto:

```bash
docker compose up --build
```

Después abrir:

- Aplicación: `http://localhost:8080`
- API: `http://localhost:8001/api`
- phpMyAdmin: `http://localhost:8082`

Se utiliza el puerto 8001 en el equipo para evitar interferir con otros proyectos que ya ocupan el 8000; dentro del contenedor Laravel continúa usando el 8000.

La primera ejecución descarga las imágenes e instala las dependencias. Las siguientes son más rápidas. MySQL crea la base `sistema_recetas`, y Laravel ejecuta automáticamente las migraciones y los datos iniciales. Los datos quedan guardados en el volumen persistente `mysql_data`.

Acceso de desarrollo a MySQL y phpMyAdmin:

- Servidor desde phpMyAdmin: `mysql`
- Usuario: `recetas_user`
- Contraseña: `recetas_password`
- Base: `sistema_recetas`
- Puerto desde el equipo: `3307`

Si aparece `permission denied` al acceder a `/var/run/docker.sock`, el usuario actual todavía no pertenece al grupo `docker`. Se puede ejecutar temporalmente con `sudo docker compose up --build` o habilitar Docker sin `sudo` (requiere cerrar y volver a iniciar sesión):

```bash
sudo usermod -aG docker "$USER"
```

Para detener los servicios sin borrar los datos:

```bash
docker compose down
```

No se debe agregar `-v` al comando anterior salvo que se quiera borrar también la base de datos guardada en Docker.

## Base de datos

El proyecto usa MySQL 8.4 dentro de Docker. No es necesario instalar MySQL en el equipo. Las migraciones crean las tablas `users`, `establecimientos`, `medicos`, `pacientes`, `medicamentos`, `recetas`, `receta_detalles` y las tablas internas de Laravel.

Las migraciones de Laravel son la fuente principal del esquema y funcionan directamente con MySQL.
También se incluye [backend/database/schema.mysql.sql](backend/database/schema.mysql.sql) como respaldo importable desde phpMyAdmin.

Los datos de demostración incluyen establecimientos, médicos, pacientes, medicamentos, una receta y este acceso:

- Correo: `admin@recetas.test`
- Contraseña: `password`

## Instalación del backend

La instalación manual requiere PHP 8.0.2 o posterior, Composer y MySQL. Docker es la opción recomendada porque no altera el PHP 5.6 del equipo.

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

La API quedará disponible en `http://127.0.0.1:8000/api`.

Si ya existe una base creada con las migraciones incompletas anteriores, reconstruirla en desarrollo con:

```bash
php artisan migrate:fresh --seed
```

## Instalación del frontend

En otra terminal:

```bash
cd frontend-recetas
npm install
npm run serve
```

Abrir la dirección que muestre Vue (normalmente `http://localhost:8080`).
