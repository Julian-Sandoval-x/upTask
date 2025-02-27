<?php

namespace Controllers;

use MVC\Router;
use Model\Proyecto;
use Model\Usuario;

class DashboardController
{
    public static function index(Router $router)
    {
        session_start();
        // Verificamos que el usuario este autenticado
        isAuth();

        // Obtener los proyectos del usuario
        $proyectos = Proyecto::belongsTo('propietarioId', $_SESSION['id']);
        $router->render('dashboard/index', [
            'titulo' => 'Proyectos',
            'proyectos' => $proyectos
        ]);
    }

    public static function crear_proyecto(Router $router)
    {
        session_start();
        // Verificamos que el usuario este autenticado
        isAuth();

        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Instanciamos el modelo de proyecto
            $proyecto = new Proyecto($_POST);

            // Validacion del nombre del proyecto
            $alertas = $proyecto->validarProyecto();

            if (empty($alertas)) {
                // Generamos una URL unica
                $proyecto->url = md5(uniqid());

                // Almacenamos el creador del proyecto
                $proyecto->propietarioId = $_SESSION['id'];

                //Guardamos el proyecto en la base de datos
                $proyecto->guardar();

                // Redireccionamos al usuario
                header('Location: /proyecto?id=' . $proyecto->url);
            }
        }


        $router->render('dashboard/crear-proyecto', [
            'titulo' => 'Crear Proyecto',
            'alertas' => $alertas
        ]);
    }

    public static function proyecto(Router $router)
    {
        session_start();
        // Verificamos que el usuario este autenticado
        isAuth();

        $token = s($_GET['id']);

        if (!$token) {
            header('Location: /dashboard');
        }

        // Revisar que la persona que visita el proyecto sea el propietario
        $proyecto = Proyecto::where('url', $token);

        if ($proyecto->propietarioId !== $_SESSION['id']) {
            header('Location: /dashboard');
        }

        $router->render('dashboard/proyecto', [
            'titulo' => $proyecto->proyecto
        ]);
    }

    public static function perfil(Router $router)
    {
        // Iniciamos la sesion
        session_start();
        // Verificamos que el usuario este autenticado
        isAuth();

        // Inicializamos las alertas
        $alertas = [];

        // Obtenemos el usuario
        $usuario = Usuario::find($_SESSION['id']);


        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sincronizamos los datos y sanitizamos los datos
            $usuario->sincronizar($_POST);

            // Validamos el perfil (obtenemos el perfil)
            $alertas = $usuario->validar_perfil();

            // Si no hay errores
            if (empty($alertas)) {
                // Verificamos que el email no este en uso
                $existeUsuario = Usuario::where('email', $usuario->email);

                if ($existeUsuario && $existeUsuario->id !== $usuario->id) {
                    // Mostrar un mensaje de error
                    Usuario::setAlerta('error', 'Email no válido, cuenta ya registrada');
                    $alertas = Usuario::getAlertas();
                } else {
                    // Guardamos el registro

                    // Guardamos el usuario
                    $usuario->guardar();

                    // Alerta de exito
                    Usuario::setAlerta('exito', 'Guardado Correctamente');

                    // Sacamos la alerta de memoria y la mostramos
                    $alertas = Usuario::getAlertas();

                    // Actualizamos los datos de la sesion
                    $_SESSION['nombre'] = $usuario->nombre;
                }
            }
        }

        // Renderizamos la vista
        $router->render('dashboard/perfil', [
            'titulo' => 'Perfil',
            'usuario' => $usuario,
            'alertas' => $alertas
        ]);
    }

    public static function cambiar_password(Router $router)
    {
        // Iniciamos la sesion
        session_start();

        // Verificamos que el usuario este autenticado
        isAuth();

        // Inicializamos las alertas
        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] === "POST") {

            // Obtenemos el usuario
            $usuario = Usuario::find($_SESSION['id']);

            // Sincronizar con los datos del usuario
            $usuario->sincronizar($_POST);

            // Verificamos que los campos esten llenos
            $alertas = $usuario->nuevo_password();

            if (empty($alertas)) {
                // Verificamos que el password actual sea correcto
                $resultado = $usuario->comprobar_password();

                // Si el password actual es correcto
                if ($resultado) {
                    // Asignamos el nuevo password                    
                    $usuario->password = $usuario->password_nuevo;

                    // Hashear el nuevo password
                    $usuario->hashPassword();

                    // Actualizamos el password
                    $resultado = $usuario->guardar();

                    if ($resultado) {
                        Usuario::setAlerta('exito', "El Password fue Actualizado Correctamente");
                        $alertas = Usuario::getAlertas();
                    }

                    // Eliminamos propieades que no existen en la base de datos
                    unset($usuario->password_actual);
                    unset($usuario->password_nuevo);
                } else {
                    Usuario::setAlerta('error', 'El password actual es incorrecto');
                    $alertas = Usuario::getAlertas();
                }
            }
        }

        $router->render('dashboard/cambiar-password', [
            'titulo' => 'Cambiar Password',
            'alertas' => $alertas
        ]);
    }
}
