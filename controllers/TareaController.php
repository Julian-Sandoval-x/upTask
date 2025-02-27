<?php

namespace Controllers;

use Model\Proyecto;
use Model\Tarea;

class TareaController
{

    public static function index()
    {
        $proyectoId = s($_GET['id']);

        // Si no hay un proyectoId redirigimos al dashboard
        if (!$proyectoId) {
            header("Location: /dashboard");
        }

        // Iniciamos la sesion
        session_start();

        // Obtenemos el proyecto actual
        $proyecto = Proyecto::where('url', $proyectoId);

        // Si el proyecto no existe o no es del usuario actual
        if (!$proyecto || $proyecto->propietarioId !== $_SESSION['id']) {
            // Lo redirigimos a la pagina de error
            header("Location: /404");
        }

        $tareas = Tarea::belongsTo('proyectoId', $proyecto->id);

        echo json_encode(['tareas' => $tareas]);
    }

    public static function crear()
    {
        if ($_SERVER['REQUEST_METHOD'] === "POST") {

            // Iniciamos la sesion
            session_start();

            $proyectoId = $_POST['proyectoId'];

            $proyecto = Proyecto::where('url', $proyectoId);

            if (!$proyecto || $proyecto->propietarioId !== $_SESSION['id']) {
                $respuesta = [
                    'tipo' => 'error',
                    'mensaje' => "Hubo un error al agregar la tarea"
                ];

                echo json_encode($respuesta);
                return;
            }

            // Instanciamos y creamos la tarea
            $tarea = new Tarea($_POST);
            // Asignamos el id del proyecto actual
            $tarea->proyectoId = $proyecto->id;
            $resultado = $tarea->guardar();

            // Si la tarea se agrego correctamente
            $respuesta = [
                'tipo' => 'exito',
                'id' => $resultado['id'],
                'mensaje' => 'Tarea Creada Correctamente',
                'proyectoId' => $proyecto->id
            ];
            echo json_encode($respuesta);
        }
    }
    public static function actualizar()
    {
        if ($_SERVER['REQUEST_METHOD'] === "POST") {

            // Validamos que el proyecto exita
            $proyecto = Proyecto::where('url', $_POST['proyectoId']);

            // Iniciamos la sesion
            session_start();
            if (!$proyecto || $proyecto->propietarioId !== $_SESSION['id']) {
                $respuesta = [
                    'tipo' => 'error',
                    'mensaje' => 'Hubo un error al actualizar la tarea'
                ];
                return;
            }

            $tarea = new Tarea($_POST);

            // Cambiamos el valor del proyectoId (Actualmente la URL) por el id del proyecto
            $tarea->proyectoId = $proyecto->id;

            $resultado = $tarea->guardar();

            // Si el estado de la tarea se cambio correctamente
            if ($resultado) {
                $respuesta = [
                    'tipo' => 'exito',
                    'id' => $tarea->id,
                    'proyectoId' => $proyecto->id,
                    'mensaje' => "Actualizado correctamente"
                ];

                echo json_encode(['respuesta' => $respuesta]);
            }
        }
    }
    public static function eliminar()
    {
        if ($_SERVER['REQUEST_METHOD'] === "POST") {

            // Validamos que el proyecto exita
            $proyecto = Proyecto::where('url', $_POST['proyectoId']);

            // Iniciamos la sesion
            session_start();
            if (!$proyecto || $proyecto->propietarioId !== $_SESSION['id']) {
                $respuesta = [
                    'tipo' => 'error',
                    'mensaje' => 'Hubo un error al actualizar la tarea'
                ];
                return;
            }

            $tarea = new Tarea($_POST);
            $resultado = $tarea->eliminar();

            $resultado = [
                'resultado' => $resultado,
                'mensaje' => 'Tarea Eliminada Correctamente',
                'tipo' => 'exito'
            ];

            echo json_encode($resultado);
        }
    }
}
