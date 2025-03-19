# Proyecto de Automatización de Certificados de Firma Electrónica

## Descripción
Este proyecto tiene como objetivo la automatización de la creación de certificados de firma electrónica, optimizando y estandarizando el proceso. La aplicación permite generar, gestionar y almacenar certificados digitales de manera eficiente y segura.

## Tecnologías Utilizadas
- **Laravel** (Versión más reciente)
- **Inertia.js**
- **Vue 3**
- **MySQL/PostgreSQL** (Base de datos, según configuración)
- **Docker** (Opcional para despliegue y desarrollo)
- **GitLab CI/CD** (Para integración y despliegue continuo, si aplica)

## Características Principales
- **Generación Automática de Certificados**: Creación de certificados digitales con datos dinámicos.
- **Almacenamiento Seguro**: Gestor de almacenamiento con protección y cifrado.
- **Interfaz de Usuario Moderna**: Desarrollada con Vue 3 e Inertia.js para una experiencia fluida.
- **API REST**: Exposición de endpoints para integración con otros sistemas.
- **Gestor de Usuarios y Roles**: Control de acceso basado en permisos y roles.
- **Soporte para Firma Digital**: Implementación de firma digital según normativas establecidas.

## Instalación y Configuración
1. **Clonar el repositorio**:
   ```bash
   git clone https://github.com/tu_usuario/tu_repositorio.git
   cd tu_repositorio
   ```

2. **Instalar dependencias**:
   ```bash
   composer install
   npm install
   ```

3. **Configurar variables de entorno**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Modificar el archivo `.env` según la configuración de la base de datos y otros servicios.

4. **Ejecutar migraciones y seeders**:
   ```bash
   php artisan migrate --seed
   ```

5. **Iniciar el servidor**:
   ```bash
   php artisan serve
   npm run dev
   ```

## Uso
- Acceder a la aplicación a través de `http://localhost:8000`
- Iniciar sesión con las credenciales generadas o crear un usuario.
- Utilizar el dashboard para generar y gestionar certificados.

## Contribución
Si deseas contribuir:
1. Realiza un fork del proyecto.
2. Crea una rama con tu funcionalidad (`git checkout -b feature/nueva-funcionalidad`).
3. Realiza un commit (`git commit -m 'Agrega nueva funcionalidad'`).
4. Sube tus cambios (`git push origin feature/nueva-funcionalidad`).
5. Abre un Pull Request.

## Licencia
Este proyecto está bajo la licencia [MIT](LICENSE).
