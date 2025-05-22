<?php

namespace Controllers;

use MVC\Router;
use Model\Productos;
use Model\Categorias;
use Model\Prioridades;

class ProductosController extends AppController
{
    public static function renderizarPagina(Router $router)
    {
        $router->render('productos/index', []);
    }

    public static function guardarProducto()
    {
        try {
            self::validarMetodo('POST');
            self::limpiarSalida();

            // Debug
            error_log("POST recibido: " . json_encode($_POST));

            if (
                empty($_POST['nombre']) || empty($_POST['cantidad']) ||
                empty($_POST['id_categoria']) || empty($_POST['id_prioridad'])
            ) {
                throw new \Exception('Todos los campos son obligatorios');
            }

            // Crear producto con los datos del POST
            $datos = [
                'nombre' => $_POST['nombre'] ?? '',
                'cantidad' => $_POST['cantidad'] ?? '',
                'id_categoria' => $_POST['id_categoria'] ?? '',
                'id_prioridad' => $_POST['id_prioridad'] ?? '',
                'comprado' => 0,
                'situacion' => 1
            ];

            $producto = new Productos($datos);
            $resultado = $producto->guardarProducto();

            // Debug
            error_log("Resultado guardado: " . json_encode($resultado));

            self::responderJson([
                'tipo' => $resultado['exito'] ? 'success' : 'error',
                'mensaje' => $resultado['mensaje'],
                'error'    => $resultado['error']   ?? null,
                'producto' => $resultado['exito'] ? $producto : null
            ], $resultado['exito'] ? 200 : 400);
        } catch (\Exception $e) {
            error_log("Error en guardarProducto controller: " . $e->getMessage());
            self::responderJson([
                'tipo' => 'error',
                'mensaje' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public static function buscarProducto()
    {
        try {
            self::validarMetodo('GET');
            self::limpiarSalida();

            $id = $_GET['id_producto'] ?? null;
            $producto = Productos::buscarPorId($id);

            self::responderJson([
                'tipo' => 'success',
                'producto' => $producto
            ]);
        } catch (\Exception $e) {
            self::responderJson([
                'tipo' => 'error',
                'mensaje' => $e->getMessage()
            ], 404);
        }
    }

    public static function obtenerProductos()
    {
        try {
            self::validarMetodo('GET');
            self::limpiarSalida();

            $productos = Productos::obtenerTodosConRelaciones();

            self::responderJson([
                'tipo' => 'success',
                'productos' => $productos ?: [],
                'mensaje' => $productos ? 'Productos obtenidos correctamente' : 'No hay productos registrados'
            ]);
        } catch (\Exception $e) {
            self::responderJson([
                'tipo' => 'error',
                'mensaje' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public static function modificarProducto()
    {
        try {
            self::validarMetodo('POST');
            self::limpiarSalida();

            $id = $_POST['id_producto'] ?? null;
            $producto = Productos::buscarPorId($id);
            $producto->sincronizar($_POST);

            $resultado = $producto->guardarProducto();

            self::responderJson([
                'tipo' => $resultado['exito'] ? 'success' : 'error',
                'mensaje' => $resultado['mensaje'],
                'producto' => $resultado['exito'] ? $producto : null
            ], $resultado['exito'] ? 200 : 400);
        } catch (\Exception $e) {
            self::responderJson([
                'tipo' => 'error',
                'mensaje' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public static function eliminarProducto()
    {
        try {
            self::validarMetodo('POST');
            self::limpiarSalida();

            $id = $_POST['id_producto'] ?? null;
            $producto = Productos::buscarPorId($id);
            $resultado = $producto->eliminarProducto();

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

    public static function marcarComprado()
    {
        try {
            self::validarMetodo('POST');
            self::limpiarSalida();

            $id = $_POST['id_producto'] ?? null;
            $estado = $_POST['comprado'] ?? 1;

            $producto = Productos::buscarPorId($id);
            $resultado = $producto->marcarComprado($estado);

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

    public static function obtenerPorCategoria()
    {
        try {
            self::validarMetodo('GET');
            self::limpiarSalida();

            $productos = Productos::obtenerPorCategoria();

            self::responderJson([
                'tipo' => 'success',
                'productos' => $productos ?: [],
                'mensaje' => $productos ? 'Productos obtenidos correctamente' : 'No hay productos registrados'
            ]);
        } catch (\Exception $e) {
            self::responderJson([
                'tipo' => 'error',
                'mensaje' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
