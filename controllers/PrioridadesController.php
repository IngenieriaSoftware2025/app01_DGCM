<?php

namespace Controllers;

use MVC\Router;
use Model\Prioridades;

class PrioridadesController extends AppController
{
    public static function renderizarPagina(Router $router)
    {
        $router->render('prioridades/index', []);
    }

    public static function guardarPrioridad()
    {
        try {
            self::validarMetodo('POST');
            self::limpiarSalida();

            // Debug
            error_log("POST recibido: " . json_encode($_POST));

            // Crear prioridad con los datos del POST
            $datos = [
                'nombre' => $_POST['nombre'] ?? '',
                'situacion' => 1
            ];

            $prioridad = new Prioridades($datos);
            $resultado = $prioridad->guardarPrioridad();

            // Debug
            error_log("Resultado guardado: " . json_encode($resultado));

            self::responderJson([
                'tipo' => $resultado['exito'] ? 'success' : 'error',
                'mensaje' => $resultado['mensaje'],
                'prioridad' => $resultado['exito'] ? $prioridad : null
            ], $resultado['exito'] ? 200 : 400);
        } catch (\Exception $e) {
            error_log("Error en guardarPrioridad controller: " . $e->getMessage());
            self::responderJson([
                'tipo' => 'error',
                'mensaje' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public static function buscarPrioridad()
    {
        try {
            self::validarMetodo('GET');
            self::limpiarSalida();

            $id = $_GET['id_prioridad'] ?? null;
            $prioridad = Prioridades::buscarPorId($id);

            self::responderJson([
                'tipo' => 'success',
                'prioridad' => $prioridad
            ]);
        } catch (\Exception $e) {
            self::responderJson([
                'tipo' => 'error',
                'mensaje' => $e->getMessage()
            ], 404);
        }
    }

    public static function obtenerPrioridades()
    {
        try {
            self::validarMetodo('GET');
            self::limpiarSalida();

            $prioridades = Prioridades::obtenerTodos();

            self::responderJson([
                'tipo' => 'success',
                'prioridades' => $prioridades ?: [],
                'mensaje' => $prioridades ? 'Prioridades obtenidas correctamente' : 'No hay prioridades registradas'
            ]);
        } catch (\Exception $e) {
            self::responderJson([
                'tipo' => 'error',
                'mensaje' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public static function modificarPrioridad()
    {
        try {
            self::validarMetodo('POST');
            self::limpiarSalida();

            $id = $_POST['id_prioridad'] ?? null;
            $prioridad = Prioridades::buscarPorId($id);
            $prioridad->sincronizar($_POST);

            $resultado = $prioridad->guardarPrioridad();

            self::responderJson([
                'tipo' => $resultado['exito'] ? 'success' : 'error',
                'mensaje' => $resultado['mensaje'],
                'prioridad' => $resultado['exito'] ? $prioridad : null
            ], $resultado['exito'] ? 200 : 400);
        } catch (\Exception $e) {
            self::responderJson([
                'tipo' => 'error',
                'mensaje' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public static function eliminarPrioridad()
    {
        try {
            self::validarMetodo('POST');
            self::limpiarSalida();

            $id = $_POST['id_prioridad'] ?? null;
            $prioridad = Prioridades::buscarPorId($id);

            $resultado = $prioridad->eliminarPrioridad();

            self::responderJson([
                'tipo' => $resultado['exito'] ? 'success' : 'error',
                'mensaje' => $resultado['mensaje']
            ], $resultado['exito'] ? 200 : 400);
        } catch (\Exception $e) {
            self::responderJson([
                'tipo' => 'error',
                'mensaje' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}