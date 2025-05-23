<?php

namespace Model;

class Categorias extends ActiveRecord
{
    //Heredadas
    protected static $tabla = 'categorias';
    protected static $idTabla = 'id_categoria';
    protected static $columnasDB = [
        'id_categoria',
        'nombre',
        'situacion'
    ];

    //Propias
    public $id_categoria;
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
            'nombre' => 'El nombre de la categoría es obligatorio'
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
            'id_categoria' => $this->id_categoria,
            'nombre' => $this->nombre,
            'situacion' => $this->situacion
        ];
    }

    public function guardarCategoria()
    {
        try {
            error_log("=== DEBUG GUARDAR CATEGORIA ===");
            error_log("Estado inicial: " . json_encode($this->arrayAtributos()));

            $errores = $this->validar();
            if (!empty($errores)) {
                return [
                    'exito' => false,
                    'mensaje' => implode(', ', $errores)
                ];
            }

            // Guardar y obtener resultado
            $resultado = $this->id_categoria ? $this->actualizar() : $this->crear();

            if (!$resultado) {
                throw new \Exception('Error en la operación de base de datos');
            }

            // Retornar con los datos actualizados, incluyendo el ID
            return [
                'exito' => true,
                'mensaje' => $this->id_categoria ?
                    'Categoría actualizada correctamente' :
                    'Categoría guardada correctamente',
                'categoria' => $this->arrayAtributos()
            ];
        } catch (\Exception $e) {
            error_log("Error en guardarCategoria: " . $e->getMessage());
            return [
                'exito' => false,
                'mensaje' => 'Error al guardar la categoría',
                'error' => $e->getMessage()
            ];
        }
    }
    public function eliminarCategoria()
    {
        try {
            // borrado lógico
            $this->situacion = 0;

            $resultado = $this->guardarCategoria();

            if ($resultado['exito']) {
                return ['exito' => true, 'mensaje' => 'Categoría eliminada correctamente'];
            }
            return ['exito' => false, 'mensaje' => $resultado['mensaje']];
        } catch (\Exception $e) {
            return ['exito' => false, 'mensaje' => $e->getMessage()];
        }
    }

    public static function obtenerActivas()
    {
        $sql = "SELECT * FROM categorias WHERE situacion = 1";
        $filas = static::consultarSQL($sql);
        $lista = [];
        foreach ($filas as $fila) {
            $lista[] = new self((array)$fila);
        }
        return $lista;
    }

    public static function buscarPorId($id)
    {
        if (!$id) {
            throw new \Exception('Id no proporcionado');
        }
        $resultado = static::find($id);
        if (!$resultado) {
            throw new \Exception('Categoría no encontrada');
        }
        $categoria = new self((array)$resultado);
        return $categoria;
    }

    public static function obtenerTodos()
    {
        return static::all();
    }
}
