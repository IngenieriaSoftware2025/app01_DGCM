<?php

namespace Controllers;

use MVC\Router;
use Model\Clientes;

class ClientesController extends AppController
{
    public static function renderizarPagina(Router $router)
    {
        $router->render('clientes/index', []);
    }

    public static function guardarCliente()
    {
        try {
            self::validarMetodo('POST');
            self::limpiarSalida();

            // Debug
            error_log("POST recibido: " . json_encode($_POST));

            // Crear cliente con los datos del POST
            $datos = [
                'nombres' => $_POST['nombres'] ?? '',
                'apellidos' => $_POST['apellidos'] ?? '',
                'telefono' => $_POST['telefono'] ?? '',
                'correo' => $_POST['correo'] ?? '',
                'direccion' => $_POST['direccion'] ?? '',
                'situacion' => 1
            ];

            $cliente = new Clientes($datos);
            $resultado = $cliente->guardarCliente();

            // Debug
            error_log("Resultado guardado: " . json_encode($resultado));

            self::responderJson([
                'tipo' => $resultado['exito'] ? 'success' : 'error',
                'mensaje' => $resultado['mensaje'],
                'cliente' => $resultado['exito'] ? $cliente : null
            ], $resultado['exito'] ? 200 : 400);
        } catch (\Exception $e) {
            error_log("Error en guardarCliente controller: " . $e->getMessage());
            self::responderJson([
                'tipo' => 'error',
                'mensaje' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public static function buscarCliente()
    {
        try {
            self::validarMetodo('GET');
            self::limpiarSalida();

            $id = $_GET['id_cliente'] ?? null;
            $cliente = Clientes::buscarPorId($id);

            self::responderJson([
                'tipo' => 'success',
                'cliente' => $cliente
            ]);
        } catch (\Exception $e) {
            self::responderJson([
                'tipo' => 'error',
                'mensaje' => $e->getMessage()
            ], 404);
        }
    }

    public static function obtenerClientes()
    {
        try {
            self::validarMetodo('GET');
            self::limpiarSalida();

            $clientes = Clientes::obtenerTodos();

            self::responderJson([
                'tipo' => 'success',
                'clientes' => $clientes ?: [],
                'mensaje' => $clientes ? 'Clientes obtenidos correctamente' : 'No hay clientes registrados'
            ]);
        } catch (\Exception $e) {
            self::responderJson([
                'tipo' => 'error',
                'mensaje' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public static function modificarCliente()
    {
        try {
            self::validarMetodo('POST');
            self::limpiarSalida();

            $id = $_POST['id_cliente'] ?? null;
            $cliente = Clientes::buscarPorId($id);
            $cliente->sincronizar($_POST);

            $resultado = $cliente->guardarCliente();

            self::responderJson([
                'tipo' => $resultado['exito'] ? 'success' : 'error',
                'mensaje' => $resultado['mensaje'],
                'cliente' => $resultado['exito'] ? $cliente : null
            ], $resultado['exito'] ? 200 : 400);
        } catch (\Exception $e) {
            self::responderJson([
                'tipo' => 'error',
                'mensaje' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public static function eliminarCliente()
    {
        try {
            self::validarMetodo('POST');
            self::limpiarSalida();

            $id = $_POST['id_cliente'] ?? null;
            $cliente = Clientes::buscarPorId($id);

            $resultado = $cliente->eliminarCliente();

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
