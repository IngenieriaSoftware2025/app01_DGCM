<?php

namespace Model;

class Productos extends ActiveRecord
{
    // Heredadas
    protected static $tabla = 'productos';
    protected static $idTabla = 'id_producto';
    protected static $columnasDB = [
        'id_producto',
        'nombre',
        'cantidad',
        'id_categoria',
        'id_prioridad',
        'id_cliente',
        'comprado',
        'situacion'
    ];

    // Propias
    public $id_producto;
    public $nombre;
    public $cantidad;
    public $id_categoria;
    public $id_prioridad;
    public $id_cliente;
    public $comprado;
    public $situacion;

    // Para relaciones
    public $categoria_nombre;
    public $prioridad_nombre;
    public $cliente_nombre;

    // Errores
    protected static $errores = [];

    public function __construct($args = [])
    {
        // Inicializar columnas básicas
        foreach (static::$columnasDB as $col) {
            $this->$col = $args[$col]
                ?? (
                    $col === 'situacion' ? 1
                  : ($col === 'comprado'  ? 0
                  : '')
                );
        }

        // Inicializar campos de relación (si vienen en $args)
        $this->categoria_nombre  = $args['categoria_nombre']  ?? '';
        $this->prioridad_nombre  = $args['prioridad_nombre']  ?? '';
        $this->cliente_nombre  = $args['cliente_nombre']  ?? '';
    }

    public function validar()
    {
        self::$errores = [];

        if (empty($this->nombre)) {
            self::$errores[] = 'El nombre del producto es obligatorio';
        }
        if (!is_numeric($this->cantidad) || $this->cantidad < 1) {
            self::$errores[] = 'La cantidad debe ser un número mayor o igual a 1';
        }
        if (empty($this->id_categoria)) {
            self::$errores[] = 'Debe seleccionar una categoría';
        }
        if (empty($this->id_prioridad)) {
            self::$errores[] = 'Debe seleccionar una prioridad';
        }
        if (empty($this->id_cliente)) {
            self::$errores[] = 'Debe seleccionar una cliente';
        }

        return self::$errores;
    }

    protected function arrayAtributos()
    {
        return [
            'id_producto'=> $this->id_producto,
            'nombre'=> $this->nombre,
            'cantidad'=> $this->cantidad,
            'id_categoria'=> $this->id_categoria,
            'id_prioridad'=> $this->id_prioridad,
            'id_cliente'=> $this->id_cliente,
            'comprado'=> $this->comprado,
            'situacion'=> $this->situacion,
            'categoria_nombre'=> $this->categoria_nombre,
            'prioridad_nombre'=> $this->prioridad_nombre,
            'cliente_nombre'=> $this->cliente_nombre
        ];
    }

    public static function toArrayList(array $instancias): array
    {
        return array_map(function(self $p) {
            return $p->arrayAtributos();
        }, $instancias);
    }

    public function guardarProducto()
    {
        try {
            $errores = $this->validar();
            if (!empty($errores)) {
                return ['exito' => false, 'mensaje' => implode(', ', $errores)];
            }

            $resultado = $this->id_producto
                ? $this->actualizar()
                : $this->crear();

            if (!$resultado) {
                throw new \Exception('Error en la operación de base de datos');
            }

            return [
                'exito'    => true,
                'mensaje'  => 'Producto guardado correctamente',
                'producto' => $this->arrayAtributos()
            ];
        } catch (\Exception $e) {
            error_log("Error en guardarProducto: " . $e->getMessage());
            return ['exito' => false, 'mensaje' => 'Error al guardar el producto', 'error' => $e->getMessage()];
        }
    }

    public function eliminarProducto()
    {
        try {
            $this->situacion = 0;
            $resultado = $this->guardarProducto();
            if ($resultado['exito']) {
                return ['exito' => true, 'mensaje' => 'Producto eliminado correctamente'];
            }
            throw new \Exception($resultado['error'] ?? 'Error al eliminar el producto');
        } catch (\Exception $e) {
            return ['exito' => false, 'mensaje' => $e->getMessage()];
        }
    }

    public static function buscarPorId($id)
    {
        if (!$id) {
            throw new \Exception('Id no proporcionado');
        }
        $row = static::find($id);
        if (!$row) {
            throw new \Exception('Producto no encontrado');
        }
        return new self((array)$row);
    }

    public static function obtenerTodos()
    {
        return static::all();
    }

    // Métodos para traer relaciones JOIN

  public static function obtenerConRelaciones()
{
    $sql = "
        SELECT
            p.*,
            c.nombre AS categoria_nombre,
            pr.nombre AS prioridad_nombre,
            cl.nombres || ' ' || cl.apellidos AS cliente_nombre
        FROM productos p
        JOIN categorias c ON p.id_categoria = c.id_categoria
        JOIN prioridades pr ON p.id_prioridad = pr.id_prioridad
        JOIN clientes cl ON p.id_cliente = cl.id_cliente 
        WHERE p.situacion = 1
    ";
    $filas = static::consultarSQL($sql);
    $lista = [];
    foreach ($filas as $fila) {
        $lista[] = new self((array)$fila);
    }
    return $lista;
}


}
