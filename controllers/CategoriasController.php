<?php

namespace Controllers;

use MVC\Router;
use Model\Categorias;

class CategoriasController extends AppController
{
    public static function renderizarPagina(Router $router)
    {
        $router->render('categorias/index', []);
    }

    public static function guardarCategoria()
    {
        try {
            self::validarMetodo('POST');
            self::limpiarSalida();

            // Debug
            error_log("POST recibido: " . json_encode($_POST));

            // Crear categoria con los datos del POST
            $datos = [
                'nombre' => $_POST['nombre'] ?? '',
                'situacion' => 1
            ];

            $categoria = new Categorias($datos);
            $resultado = $categoria->guardarCategoria();

            // Debug
            error_log("Resultado guardado: " . json_encode($resultado));

            self::responderJson([
                'tipo' => $resultado['exito'] ? 'success' : 'error',
                'mensaje' => $resultado['mensaje'],
                'categoria' => $resultado['exito'] ? $categoria : null
            ], $resultado['exito'] ? 200 : 400);
        } catch (\Exception $e) {
            error_log("Error en guardarCategoria controller: " . $e->getMessage());
            self::responderJson([
                'tipo' => 'error',
                'mensaje' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public static function buscarCategoria()
    {
        try {
            self::validarMetodo('GET');
            self::limpiarSalida();

            $id = $_GET['id_categoria'] ?? null;
            $categoria = Categorias::buscarPorId($id);

            self::responderJson([
                'tipo' => 'success',
                'categoria' => $categoria
            ]);
        } catch (\Exception $e) {
            self::responderJson([
                'tipo' => 'error',
                'mensaje' => $e->getMessage()
            ], 404);
        }
    }

    public static function obtenerCategorias()
    {
        try {
            self::validarMetodo('GET');
            self::limpiarSalida();

            $categorias = Categorias::obtenerTodos();

            self::responderJson([
                'tipo' => 'success',
                'categoria' => $categorias ?: [],
                'mensaje' => $categorias ? 'categorias obtenidos correctamente' : 'No hay categorias registrados'
            ]);
        } catch (\Exception $e) {
            self::responderJson([
                'tipo' => 'error',
                'mensaje' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public static function modificarCategoria()
    {
        try {
            self::validarMetodo('POST');
            self::limpiarSalida();

            $id = $_POST['id_categoria'] ?? null;
            $categoria = Categorias::buscarPorId($id);
            $categoria->sincronizar($_POST);

            $resultado = $categoria->guardarCategoria();

            self::responderJson([
                'tipo' => $resultado['exito'] ? 'success' : 'error',
                'mensaje' => $resultado['mensaje'],
                'categoria' => $resultado['exito'] ? $categoria : null
            ], $resultado['exito'] ? 200 : 400);
        } catch (\Exception $e) {
            self::responderJson([
                'tipo' => 'error',
                'mensaje' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public static function eliminarCategoria()
    {
        try {
            self::validarMetodo('POST');
            self::limpiarSalida();

            $id = $_POST['id_categoria'] ?? null;
            $categoria = Categorias::buscarPorId($id);

            $resultado = $categoria->eliminarCategoria();

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
