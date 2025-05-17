# UpTask - Gestor de Proyectos

UpTask es una aplicación web desarrollada en PHP utilizando el patrón de arquitectura MVC. Permite a los usuarios gestionar proyectos y tareas de manera eficiente, ofreciendo una interfaz intuitiva y funcionalidades clave para la organización del trabajo.

## 🚀 Características

- **Gestión de proyectos**: Crea, edita y elimina proyectos según tus necesidades.
- **Gestión de tareas**: Añade, actualiza y elimina tareas dentro de cada proyecto.
- **Interfaz dinámica**: Utiliza Fetch API para interacciones asincrónicas sin recargar la página.
- **Diseño responsive**: Adaptado para dispositivos móviles y de escritorio.
- **Automatización de tareas**: Compilación de SASS y minificación de archivos con Gulp.

## 🛠️ Tecnologías utilizadas

- **Backend**: PHP
- **Frontend**: SCSS, JavaScript
- **Herramientas**: Gulp, Composer, NPM
- **Base de datos**: MySQL
- **Otras**: Fetch API, Virtual DOM

## 📁 Estructura del proyecto

- `classes/`: Clases auxiliares y utilidades.
- `controllers/`: Controladores que manejan la lógica de negocio.
- `models/`: Modelos que interactúan con la base de datos.
- `views/`: Vistas y plantillas HTML.
- `public/`: Archivos públicos accesibles desde el navegador.
- `src/`: Recursos como SASS y JavaScript.
- `includes/`: Archivos compartidos y configuraciones.
- `Router.php`: Sistema de enrutamiento personalizado.

## ⚙️ Instalación

Sigue los siguientes pasos para instalar y ejecutar el proyecto en tu entorno local:

1. Clona el repositorio:

   ```bash
   git clone https://github.com/Julian-Sandoval-x/upTask.git
   cd upTask

   ```

2. Instalar dependencias de PHP

   ```bash
   composer install
   ```

3. Instalar dependencias de JavaScript

   ```bash
   npm install
   ```

4. Configura el archivo .env tomando de base el archivo .env.example

5. Compilar SASS y JavaScript

   ```bash
   npm run dev
   ```

6. Arrancar el servidor
   ```bash
   php -S lcoalhost:3000
   ```

## 📄 Licencia

Este proyecto está bajo la licencia MIT.

## 🙋‍♂️ Autor

Desarrollado por [Julian Sandoval](https://github.com/Julian-Sandoval-x)
Estudiante de Ingeniería en Sistemas Computacionales
Apasionado por el desarrollo backend y en constante aprendizaje.
