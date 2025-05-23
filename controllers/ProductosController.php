<?php

namespace Controllers;

use MVC\Router;
use Model\Productos;

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
            error_log("POST recibido (producto): " . json_encode($_POST));

            $datos = [
                'nombre' => $_POST['nombre']       ?? '',
                'cantidad' => $_POST['cantidad']     ?? '',
                'id_categoria' => $_POST['id_categoria'] ?? '',
                'id_prioridad' => $_POST['id_prioridad'] ?? '',
                'id_cliente'   => $_POST['id_cliente']   ?? '',
            ];

            $producto = new Productos($datos);
            $resultado = $producto->guardarProducto();

            // Debug
            error_log("Resultado guardarProducto: " . json_encode($resultado));

            self::responderJson([
                'tipo'     => $resultado['exito'] ? 'success' : 'error',
                'mensaje'  => $resultado['mensaje'],
                'producto' => $resultado['exito'] ? $producto : null
            ], $resultado['exito'] ? 200 : 400);
        } catch (\Exception $e) {
            error_log("Error en guardarProducto controller: " . $e->getMessage());
            self::responderJson([
                'tipo'    => 'error',
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
                'tipo'     => 'success',
                'producto' => $producto
            ]);
        } catch (\Exception $e) {
            self::responderJson([
                'tipo'    => 'error',
                'mensaje' => $e->getMessage()
            ], 404);
        }
    }

    public static function obtenerProductos()
    {
        try {
            self::validarMetodo('GET');
            self::limpiarSalida();

            // Trae productos + nombre de categoría + nombre de prioridad
            $lista = Productos::obtenerConRelaciones();

            // Convierte cada objeto a un array plano
            $data = Productos::toArrayList($lista);

            self::responderJson([
                'tipo'      => 'success',
                'productos' => $data,
                'mensaje'   => $data
                    ? 'Productos obtenidos correctamente'
                    : 'No hay productos registrados'
            ]);
        } catch (\Exception $e) {
            self::responderJson([
                'tipo'    => 'error',
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
                'tipo'     => $resultado['exito'] ? 'success' : 'error',
                'mensaje'  => $resultado['mensaje'],
                'producto' => $resultado['exito'] ? $producto : null
            ], $resultado['exito'] ? 200 : 400);
        } catch (\Exception $e) {
            self::responderJson([
                'tipo'    => 'error',
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
                'tipo'    => $resultado['exito'] ? 'success' : 'error',
                'mensaje' => $resultado['mensaje']
            ], $resultado['exito'] ? 200 : 400);
        } catch (\Exception $e) {
            self::responderJson([
                'tipo'    => 'error',
                'mensaje' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
