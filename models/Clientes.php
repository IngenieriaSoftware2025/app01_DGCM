<?php

namespace Model;

class Clientes extends ActiveRecord
{
    // Configuración de la tabla
    protected static $tabla = 'clientes';
    protected static $idTabla = 'id_cliente';
    protected static $columnasDB = [
        'id_cliente',
        'nombres',
        'apellidos',
        'telefono',
        'correo',
        'direccion',
        'situacion'
    ];
    
    // Propiedades de instancia
    public $id_cliente;
    public $nombres;
    public $apellidos;
    public $telefono;
    public $correo;
    public $direccion;
    public $situacion;

    // Array para errores
    protected static $errores = [];

    public function __construct($args = [])
    {
        foreach (static::$columnasDB as $col) {
            $this->$col = $args[$col] ?? ($col === 'situacion' ? 1 : '');
        }
    }

    // Método principal de validación
    public function validar()
    {
        self::$errores = [];

        // Campos obligatorios
        $camposObligatorios = [
            'nombres' => 'El nombre es obligatorio',
            'apellidos' => 'El apellido es obligatorio',
            'telefono' => 'El teléfono es obligatorio',
            'correo' => 'El correo es obligatorio',
            'direccion' => 'La dirección es obligatoria'
        ];

        foreach ($camposObligatorios as $campo => $mensaje) {
            if (empty($this->$campo)) {
                self::$errores[] = $mensaje;
            }
        }

        // Validaciones específicas
        if (!empty($this->telefono) && !preg_match('/^\d{8}$/', $this->telefono)) {
            self::$errores[] = 'El teléfono debe tener 8 dígitos';
        }

        if (!empty($this->correo) && !filter_var($this->correo, FILTER_VALIDATE_EMAIL)) {
            self::$errores[] = 'El correo no es válido';
        }

        return self::$errores;
    }

    // Métodos CRUD específicos
    public function guardarCliente()
{
    try {
        // Validar primero
        $errores = $this->validar();
        if (!empty($errores)) {
            return [
                'exito' => false,
                'mensaje' => implode(', ', $errores)
            ];
        }

        // Intentar crear/actualizar
        $resultado = $this->id_cliente ? $this->actualizar() : $this->crear();

        if (!$resultado) {
            throw new \Exception('Error en la operación de base de datos');
        }

        return [
            'exito' => true,
            'mensaje' => 'Cliente guardado correctamente',
            'cliente' => $this
        ];

    } catch (\Exception $e) {
        error_log("Error en guardarCliente: " . $e->getMessage());
        return [
            'exito' => false,
            'mensaje' => 'Error al guardar el cliente',
            'error' => $e->getMessage()
        ];
    }
}

    public function eliminarCliente()
    {
        try {
            $this->situacion = 0;
            $resultado = $this->guardar();

            if ($resultado && (!isset($resultado['resultado']) || $resultado['resultado'])) {
                return [
                    'exito' => true,
                    'mensaje' => 'Cliente eliminado correctamente'
                ];
            }

            throw new \Exception($resultado['error'] ?? 'Error al eliminar el cliente');
        } catch (\Exception $e) {
            return [
                'exito' => false,
                'mensaje' => $e->getMessage()
            ];
        }
    }

    // Métodos estáticos de búsqueda
    public static function buscarPorId($id)
    {
        if (!$id) {
            throw new \Exception('ID no proporcionado');
        }

        $resultado = static::find($id);
        if (!$resultado) {
            throw new \Exception('Cliente no encontrado');
        }

        $cliente = new self((array)$resultado);
        return $cliente;
    }

    public static function obtenerTodos()
    {
        return static::all();
    }
}