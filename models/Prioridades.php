<?php

namespace Model;

class Prioridades extends ActiveRecord
{
    //Heredadas
    protected static $tabla = 'prioridades';
    protected static $idTabla = 'id_prioridad';
    protected static $columnasDB = [
        'id_prioridad',
        'nombre',
        'situacion'
    ];

    //Propias
    public $id_prioridad;
    public $nombre;
    public $situacion;

    //Errores
    protected static $errores = [];

    public function __construct($args = [])
    {
        foreach (static::$columnasDB as $col) {
            $this->$col = $args[$col] ?? ($col === 'situacion' ? 1 : '');
        }
    }

    public function validar()
    {
        self::$errores = [];

        $camposObligatorios = [
            'nombre' => 'El nombre de la prioridad es obligatorio'
        ];

        foreach ($camposObligatorios as $campo => $mensaje) {
            if (empty($this->$campo)) {
                self::$errores[] = $mensaje;
            }
        }
        return self::$errores;
    }

    protected function arrayAtributos()
    {
        return [
            'id_prioridad' => $this->id_prioridad,
            'nombre' => $this->nombre,
            'situacion' => $this->situacion
        ];
    }

    public function guardarPrioridad()
    {
        try {
            $errores = $this->validar();

            if (!empty($errores)) {
                return [
                    'exito' => false,
                    'mensaje' => implode(', ', $errores)
                ];
            }

            $resultado = $this->id_prioridad ? $this->actualizar() : $this->crear();

            if (!$resultado) {
                throw new \Exception('Error en la operación de base de datos');
            }

            return [
                'exito' => true,
                'mensaje' => 'Prioridad guardada correctamente',
                'prioridad' => $this->arrayAtributos()
            ];
        } catch (\Exception $e) {
            error_log("Error en guardarPrioridad: " . $e->getMessage());
            return [
                'exito' => false,
                'mensaje' => 'Error al guardar la prioridad',
                'error' => $e->getMessage()
            ];
        }
    }

    public function eliminarPrioridad()
    {
        try {
            $this->situacion = 0;
            $resultado = $this->guardar();

            if (($resultado['resultado']) ?? false) {
                return [
                    'exito' => true,
                    'mensaje' => 'Prioridad eliminada correctamente'
                ];
            }

            throw new \Exception($resultado['error'] ?? 'Error al eliminar la prioridad');
        } catch (\Exception $e) {
            return [
                'exito' => false,
                'mensaje' => $e->getMessage()
            ];
        }
    }

    public static function buscarPorId($id)
    {
        if (!$id) {
            throw new \Exception('Id no proporcionado');
        }
        $resultado = static::find($id);
        if (!$resultado) {
            throw new \Exception('Prioridad no encontrada');
        }
        $prioridad = new self((array)$resultado);
        return $prioridad;
    }

    public static function obtenerTodos()
    {
        return static::all();
    }
}