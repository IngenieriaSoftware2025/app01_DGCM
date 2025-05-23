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
    self::validarMetodo('POST');
    self::limpiarSalida();

    // Armo los datos directamente
    $datos = [
        'nombre'    => $_POST['nombre']    ?? '',
        'situacion' => 1
    ];

    // Creo el objeto y lo guardo
    $prioridad = new Prioridades($datos);
    $resultado  = $prioridad->guardarPrioridad();

    self::responderJson([
        'tipo'=> $resultado['exito']   ? 'success' : 'error',
        'mensaje'=> $resultado['mensaje'],
        'prioridad'=> $resultado['exito']   ? $resultado['prioridad'] : null,
        'debugError'=> $resultado['error']    ?? null
    ], $resultado['exito'] ? 200 : 400);
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

            // Sólo activas
            $prioridades = Prioridades::obtenerActivas();

            self::responderJson([
                'tipo' => 'success',
                'prioridades' => $prioridades ?: [],
                'mensaje' => $prioridades
                    ? 'Prioridades obtenidas correctamente'
                    : 'No hay prioridades registradas'
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

            $id        = $_POST['id_prioridad'] ?? null;
            $prioridad = Prioridades::buscarPorId($id);

            $filas = Prioridades::consultarSQL(
                "SELECT COUNT(*) AS cnt
             FROM productos
             WHERE id_prioridad = " . (int)$id . "
               AND situacion = 1"
            );

            $count = 0;
            if (!empty($filas)) {
                $first = $filas[0];
                $count = is_object($first)
                    ? ($first->cnt  ?? 0)
                    : ($first['cnt'] ?? 0);
            }

            if ($count > 0) {
                throw new \Exception('No se puede eliminar: hay productos asignados a esta prioridad');
            }

            $res = $prioridad->eliminarPrioridad();

            self::responderJson([
                'tipo'    => $res['exito'] ? 'success' : 'error',
                'mensaje' => $res['mensaje']
            ], $res['exito'] ? 200 : 400);
        } catch (\Exception $e) {
            self::responderJson([
                'tipo'    => 'error',
                'mensaje' => $e->getMessage()
            ], 400);
        }
    }
}
