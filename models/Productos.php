<?php

namespace Model;

class Productos extends ActiveRecord
{
    //Heredadas
    protected static $tabla = 'productos';
    protected static $idTabla = 'id_producto';
    protected static $columnasDB = [
        'id_producto',
        'nombre',
        'cantidad',
        'id_categoria',
        'id_prioridad',
        'comprado',
        'situacion'
    ];

    //Propias
    public $id_producto;
    public $nombre;
    public $cantidad;
    public $id_categoria;
    public $id_prioridad;
    public $comprado;
    public $situacion;

    // Propiedades para relaciones
    public $categoria_nombre;
    public $prioridad_nombre;

    //Errores
    protected static $errores = [];

    public function __construct($args = [])
    {
        $this->id_producto = $args['id_producto'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->cantidad = isset($args['cantidad']) ? (int)$args['cantidad'] : 0;
        $this->id_categoria = isset($args['id_categoria']) ? (int)$args['id_categoria'] : 0;
        $this->id_prioridad = isset($args['id_prioridad']) ? (int)$args['id_prioridad'] : 0;
        $this->comprado = isset($args['comprado']) ? (int)$args['comprado'] : 0;
        $this->situacion = isset($args['situacion']) ? (int)$args['situacion'] : 1;
    }

    public function validar()
    {
        self::$errores = [];

        $camposObligatorios = [
            'nombre' => 'El nombre del producto es obligatorio',
            'cantidad' => 'La cantidad es obligatoria',
            'id_categoria' => 'La categoría es obligatoria',
            'id_prioridad' => 'La prioridad es obligatoria'
        ];

        foreach ($camposObligatorios as $campo => $mensaje) {
            if (empty($this->$campo)) {
                self::$errores[] = $mensaje;
            }
        }

        // Validar que la cantidad sea un número positivo
        if ($this->cantidad && (!is_numeric($this->cantidad) || $this->cantidad <= 0)) {
            self::$errores[] = 'La cantidad debe ser un número mayor a 0';
        }

        return self::$errores;
    }

    protected function arrayAtributos()
    {
        $atributos = [
            'id_producto' => $this->id_producto,
            'nombre' => $this->nombre,
            'cantidad' => $this->cantidad,
            'id_categoria' => $this->id_categoria,
            'id_prioridad' => $this->id_prioridad,
            'comprado' => $this->comprado,
            'situacion' => $this->situacion
        ];

        // Agregar nombres de relaciones si están disponibles
        if (isset($this->categoria_nombre)) {
            $atributos['categoria_nombre'] = $this->categoria_nombre;
        }
        if (isset($this->prioridad_nombre)) {
            $atributos['prioridad_nombre'] = $this->prioridad_nombre;
        }

        return $atributos;
    }

    public function guardarProducto()
    {
        try {

            $this->cantidad = (int)$this->cantidad;
            $this->id_categoria = (int)$this->id_categoria;
            $this->id_prioridad = (int)$this->id_prioridad;
            $this->comprado = (int)$this->comprado;
            $this->situacion = (int)$this->situacion;

            $errores = $this->validar();

            if (!empty($errores)) {
                return [
                    'exito' => false,
                    'mensaje' => implode(', ', $errores)
                ];
            }

            if (!$this->verificarRelaciones()) {
                return [
                    'exito' => false,
                    'mensaje' => 'La categoría o prioridad seleccionada no existe'
                ];
            }

            error_log("Intentando guardar producto: " . json_encode($this->arrayAtributos()));

            // Verificar duplicado en la misma categoría
            $duplicado = $this->verificarDuplicado();
            if ($duplicado) {
                return [
                    'exito' => false,
                    'mensaje' => 'Ya existe un producto con este nombre en la misma categoría'
                ];
            }

            $resultado = $this->id_producto ? $this->actualizar() : $this->crear();

            if (!$resultado) {
                throw new \Exception('Error en la operación de base de datos');
            }

            return [
                'exito' => true,
                'mensaje' => 'Producto guardado correctamente',
                'producto' => $this->arrayAtributos()
            ];
        } catch (\Exception $e) {
            error_log("Error en guardarProducto: " . $e->getMessage());
            return [
                'exito' => false,
                'mensaje' => 'Error al guardar el producto',
                'error' => $e->getMessage()
            ];
        }
    }

    private function verificarRelaciones()
    {
        $query = "SELECT 
                (SELECT COUNT(*) FROM categorias WHERE id_categoria = ? AND situacion = 1) as cat_exists,
                (SELECT COUNT(*) FROM prioridades WHERE id_prioridad = ? AND situacion = 1) as pri_exists";

        $resultado = self::$db->query($query, [
            $this->id_categoria,
            $this->id_prioridad
        ]);

        return $resultado[0]['cat_exists'] > 0 && $resultado[0]['pri_exists'] > 0;
    }

    private function verificarDuplicado()
    {
        $query = "SELECT COUNT(*) as total FROM " . static::$tabla .
            " WHERE nombre = ? AND id_categoria = ? AND id_producto != ? AND situacion = 1";
        $resultado = self::$db->query($query, [
            $this->nombre,
            $this->id_categoria,
            $this->id_producto ?? 0
        ]);
        return $resultado[0]['total'] > 0;
    }

    public static function obtenerTodosConRelaciones()
    {
        $query = "SELECT p.*, c.nombre as categoria_nombre, pr.nombre as prioridad_nombre 
                 FROM productos p 
                 LEFT JOIN categorias c ON p.id_categoria = c.id_categoria 
                 LEFT JOIN prioridades pr ON p.id_prioridad = pr.id_prioridad 
                 WHERE p.situacion = 1 
                 ORDER BY c.nombre, CASE 
                    WHEN pr.nombre = 'Alta' THEN 1 
                    WHEN pr.nombre = 'Media' THEN 2 
                    WHEN pr.nombre = 'Baja' THEN 3 
                    ELSE 4 
                 END, p.nombre";

        return static::consultarSQL($query);
    }

    public static function obtenerPorCategoria()
    {
        $query = "SELECT p.*, c.nombre as categoria_nombre, pr.nombre as prioridad_nombre 
                 FROM productos p 
                 LEFT JOIN categorias c ON p.id_categoria = c.id_categoria 
                 LEFT JOIN prioridades pr ON p.id_prioridad = pr.id_prioridad 
                 WHERE p.situacion = 1 
                 ORDER BY c.nombre, 
                 CASE 
                    WHEN pr.nombre = 'Alta' THEN 1 
                    WHEN pr.nombre = 'Media' THEN 2 
                    WHEN pr.nombre = 'Baja' THEN 3 
                    ELSE 4 
                 END";

        return static::consultarSQL($query);
    }

    public function marcarComprado($estado = 1)
    {
        try {
            $this->comprado = $estado;
            $resultado = $this->actualizar();

            if (!$resultado) {
                throw new \Exception('Error al actualizar el estado del producto');
            }

            return [
                'exito' => true,
                'mensaje' => $estado ? 'Producto marcado como comprado' : 'Producto desmarcado como comprado'
            ];
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
            throw new \Exception('ID no proporcionado');
        }

        $query = "SELECT p.*, c.nombre as categoria_nombre, pr.nombre as prioridad_nombre 
                 FROM " . static::$tabla . " p
                 LEFT JOIN categorias c ON p.id_categoria = c.id_categoria 
                 LEFT JOIN prioridades pr ON p.id_prioridad = pr.id_prioridad 
                 WHERE p.id_producto = ?";

        $resultado = self::$db->query($query, [$id]);

        if (!$resultado) {
            throw new \Exception('Producto no encontrado');
        }

        return new self((array)$resultado[0]);
    }

    public function eliminarProducto()
    {
        try {
            $this->situacion = 0;
            $resultado = $this->actualizar();

            if (!$resultado) {
                throw new \Exception('Error al eliminar el producto');
            }

            return [
                'exito' => true,
                'mensaje' => 'Producto eliminado correctamente'
            ];
        } catch (\Exception $e) {
            return [
                'exito' => false,
                'mensaje' => $e->getMessage()
            ];
        }
    }
}
