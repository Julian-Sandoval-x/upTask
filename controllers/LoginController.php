<?php

namespace Controllers;

use Classes\Email;
use MVC\Router;
use Model\Usuario;

class LoginController
{
    public static function login(Router $router)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = new Usuario($_POST);

            $alertas = $usuario->validarLogin();

            if (empty($alertas)) {
                // Verificamos que el usuario exista
                $usuario = Usuario::where('email', $usuario->email);

                // Verificamos si el usuario existe o si esta confirmado
                if (!$usuario || !$usuario->confirmado) {
                    Usuario::setAlerta("error", "El Usuario No Existe o no esta confirmado");
                } else {
                    // El usuario existe
                    // Comprobamos el password del usuario
                    if (password_verify($_POST['password'], $usuario->password)) {
                        // Iniciamos la sesion
                        session_start();

                        // Llenamos los datos de la sesion con los datos del usuario
                        $_SESSION['id'] = $usuario->id;
                        $_SESSION['nombre'] = $usuario->nombre;
                        $_SESSION['email'] = $usuario->email;
                        $_SESSION['login'] = true;

                        // Redireccionamos al dashboard
                        header('Location: /dashboard');
                    } else {
                        Usuario::setAlerta("error", "El password es incorrecto");
                    }
                }


                // Verificamos que la contraseña sea correcta
            }
        }

        $alertas = Usuario::getAlertas();

        // Render a la vista
        $router->render('auth/login', [
            'titulo' => 'Bienvenido',
            'alertas' => $alertas ?? []
        ]);
    }

    public static function logout()
    {
        // Obtenemos los datos de la sesion
        session_start();
        $_SESSION = [];
        header("Location: /");
    }

    public static function crear(Router $router)
    {

        // Inicializamos las alertas
        $alertas = [];

        // Instanciamos el usuario
        $usuario = new Usuario;



        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sincronizamos nuestor objeto con los parametros en $_POST
            $usuario->sincronizar($_POST);

            // Obtenemos las alertas del formulario
            $alertas = $usuario->validarNuevaCuenta();

            // Verificamos que haya llenado todos los campos del formulario
            if (empty($alertas)) {
                // Verificamos si el usuario ya esta registrado por medio de su email
                $existeUsuario = Usuario::where('email', $usuario->email);

                // Validamos si el usuario existe
                if ($existeUsuario) {
                    // Agregamos la alerta a nuestro arreglo
                    Usuario::setAlerta('error', 'El usuario ya esta registrado');
                    $alertas = Usuario::getAlertas();
                } else {
                    // Creamos un nuevo usuario

                    // Hasheamos el password
                    $usuario->hashPassword();

                    // Eliminamos el password2
                    unset($usuario->password2);

                    // Generar el token
                    $usuario->crearToken();

                    // Damos el usuario de alta en la base de datos
                    $resultado = $usuario->guardar();

                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);

                    $email->enviarConfirmacion();

                    if ($resultado) {
                        header('Location: /mensaje');
                    }
                }
            }
        }

        // Render a la vista
        $router->render('auth/crear', [
            'titulo' => 'Crea tu cuenta en UpTask',
            'usuario' => $usuario,
            'alertas' => $alertas
        ]);
    }

    public static function olvide(Router $router)
    {
        // Inicializamos las alertas
        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = new Usuario($_POST);
            $alertas = $usuario->validarEmail();

            if (empty($alertas)) {
                // Buscamos el usuario en la base de datos
                $usuario = Usuario::where('email', $usuario->email);

                if ($usuario && $usuario->confirmado) {
                    // Se encontro al usuario

                    // Eliminamos las columnas extras
                    unset($usuario->password2);

                    // Generamos un nuevo token
                    $usuario->crearToken();

                    // Actualizar el usuario
                    $usuario->guardar();

                    // Enviar el email
                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                    $email->enviarRecuperacion();

                    // Imprimir la alerta
                    Usuario::setAlerta('exito', "Hemos enviado las instrucciones a tu email");
                } else {
                    Usuario::setAlerta('error', 'El usuario no existe o no esta confirmado');
                }
            }
        }

        $alertas = Usuario::getAlertas();
        // Render a la vista
        $router->render('auth/olvide', [
            'titulo' => 'Recuperar Password',
            'alertas' => $alertas
        ]);
    }

    public static function restablecer(Router $router)
    {
        // Obtenemos el token
        $token = s($_GET['token']);
        $mostrar = true;

        if (!$token) {
            header("Location: /");
        }

        // Identificamos el usuario con ese token
        $usuario = Usuario::where("token", $token);
        if (!$usuario) {
            Usuario::setAlerta("error", "Token no válido");
            $mostrar = false;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Añadir el nuevo password
            // Sincronizamos los datos de la variable POST
            $usuario->sincronizar($_POST);

            // Validamos el password
            $alertas = $usuario->validarPassword();

            // Lleno el formulario correctamente
            if (empty($alertas)) {
                // Hasheamos el nuevo password
                $usuario->hashPassword();

                // Eliminamos el token
                $usuario->token = null;

                // Guardamos el usuario en la BD
                $resultado = $usuario->guardar();

                // Reedireccionamos al login
                if ($resultado) {
                    header("Location: /");
                }
            }
        }

        // Obtenemos las alertas
        $alertas = Usuario::getAlertas();
        // Render a la vista
        $router->render('auth/restablecer', [
            'titulo' => 'Restablecer Password',
            'alertas' => $alertas,
            'mostrar' => $mostrar
        ]);
    }

    public static function mensaje(Router $router)
    {

        // Render a la vista
        $router->render('auth/mensaje', [
            'titulo' => 'Cuenta Creada Exitosamente',
        ]);
    }
    public static function confirmar(Router $router)
    {

        $alertas = [];
        $token = s($_GET['token']);
        $usuario = Usuario::where('token', $token);

        // Si no cuenta con token lo enviamos a la pagina de login
        if (!$token) {
            header('Location: /');
        }

        // Encontrar al usuario correspondiente al token
        // Si no existe el usuario
        if (empty($usuario)) {
            // No se encontró el usuario con ese token
            // Mostramos el mensaje de error
            Usuario::setAlerta('error', 'Token No Válido');
        } else {
            // Eliminamos el campo de password2
            unset($usuario->password2);
            // Modificamos el usuario a confirmado y borramos el token
            $usuario->confirmado = "1";
            $usuario->token = null;
            // Guardamos las modificaciones 
            $usuario->guardar();
            // Mostramos el mensaje de éxito
            Usuario::setAlerta('exito', "Cuenta Comprobada Correctamente");
        }

        $alertas = Usuario::getAlertas();
        // Render a la vista
        $router->render('auth/confirmar', [
            'titulo' => 'Confirma tu cuenta UpTask',
            'alertas' => $alertas
        ]);
    }
}
