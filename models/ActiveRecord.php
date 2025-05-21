<?php

namespace Model;

use PDO;

class ActiveRecord
{
    // Base DE DATOS
    protected static $db;
    protected static $tabla = '';
    protected static $columnasDB = [];

    protected static $idTabla = '';

    // Alertas y Mensajes
    protected static $alertas = [];

    // Definir la conexión a la BD - includes/database.php
    public static function setDB($database)
    {
        self::$db = $database;
    }

    public static function setAlerta($tipo, $mensaje)
    {
        static::$alertas[$tipo][] = $mensaje;
    }
    // Validación
    public static function getAlertas()
    {
        return static::$alertas;
    }

    public function validar()
    {
        static::$alertas = [];
        return static::$alertas;
    }

    // Registros - CRUD
    public function guardar()
    {
        $resultado = '';
        $id = static::$idTabla ?? 'id';
        if (!is_null($this->$id)) {
            // actualizar
            $resultado = $this->actualizar();
        } else {
            // Creando un nuevo registro
            $resultado = $this->crear();
        }
        return $resultado;
    }

    public static function all()
    {
        $query = "SELECT * FROM " . static::$tabla . " WHERE situacion = 1";
        return static::consultarSQL($query);
    }

    // Busca un registro por su id
    public static function find($id = [])
    {
        $idQuery = static::$idTabla ?? 'id';
        $query = "SELECT * FROM " . static::$tabla;

        if (is_array(static::$idTabla)) {
            foreach (static::$idTabla as $key => $value) {
                if ($value == reset(static::$idTabla)) {
                    $query .= " WHERE $value = " . self::$db->quote($id[$value]);
                } else {
                    $query .= " AND $value = " . self::$db->quote($id[$value]);
                }
            }
        } else {
            $query .= " WHERE $idQuery = $id";
        }

        $resultado = self::consultarSQL($query);
        return array_shift($resultado);
    }

    // Obtener Registro
    public static function get($limite)
    {
        $query = "SELECT * FROM " . static::$tabla . " LIMIT ${limite}";
        $resultado = self::consultarSQL($query);
        return array_shift($resultado);
    }

    // Busqueda Where con Columna 
    public static function where($columna, $valor, $condicion = '=')
    {
        $query = "SELECT * FROM " . static::$tabla . " WHERE ${columna} ${condicion} '${valor}'";
        $resultado = self::consultarSQL($query);
        return  $resultado;
    }

    // SQL para Consultas Avanzadas.
    public static function SQL($consulta)
    {
        $query = $consulta;
        $resultado = self::$db->query($query);
        return $resultado;
    }

    // crea un nuevo registro - MODIFICADO PARA INFORMIX
    public function crear()
    {
        // Sanitizar los datos
        $atributos = $this->sanitizarAtributos();

        // Preparar las columnas y valores para Informix
        $columnas = array_keys($atributos);
        $valores = array_values($atributos);

        // Depurar los datos que estamos intentando insertar
        error_log("Tabla: " . static::$tabla);
        error_log("Columnas: " . json_encode($columnas));
        error_log("Valores: " . json_encode($valores));

        // Construimos la consulta con prepared statements para mayor seguridad y compatibilidad
        $queryPrep = "INSERT INTO " . static::$tabla . " (";
        $queryPrep .= implode(", ", $columnas);
        $queryPrep .= ") VALUES (";

        $placeholders = [];
        foreach ($columnas as $col) {
            $placeholders[] = "?";
        }

        $queryPrep .= implode(", ", $placeholders);
        $queryPrep .= ")";

        error_log("Consulta preparada: " . $queryPrep);

        try {
            // Preparar la consulta
            $stmt = self::$db->prepare($queryPrep);

            if (!$stmt) {
                error_log("Error al preparar la consulta: " . json_encode(self::$db->errorInfo()));
                return [
                    'resultado' => false,
                    'error' => "Error al preparar la consulta: " . json_encode(self::$db->errorInfo())
                ];
            }

            // Extraer solo los valores sin comillas para los parámetros
            $valoresClean = [];
            foreach ($atributos as $key => $value) {
                // Quitamos las comillas que añadió quote()
                $valoresClean[] = trim($value, "'");
            }

            // Ejecutar con los valores como parámetros
            $resultado = $stmt->execute($valoresClean);

            if (!$resultado) {
                error_log("Error al ejecutar: " . json_encode($stmt->errorInfo()));
                return [
                    'resultado' => false,
                    'error' => json_encode($stmt->errorInfo())
                ];
            }

            // Obtener el ID insertado
            $id = null;
            // Para Informix, usamos una consulta separada para obtener el último ID
            try {
                $idQuery = static::$idTabla ?? 'id';
                $seqQuery = "SELECT FIRST 1 " . $idQuery . " FROM " . static::$tabla . " ORDER BY " . $idQuery . " DESC";
                error_log("Consulta para obtener ID: " . $seqQuery);
                $stmtId = self::$db->query($seqQuery);
                if ($stmtId) {
                    $lastRow = $stmtId->fetch(PDO::FETCH_ASSOC);
                    $id = $lastRow[$idQuery] ?? null;
                    error_log("ID obtenido: " . ($id ?? 'null'));
                } else {
                    error_log("Error al consultar ID: " . json_encode(self::$db->errorInfo()));
                }
            } catch (\Exception $idEx) {
                error_log("Excepción al obtener ID: " . $idEx->getMessage());
                // Continuamos aunque no podamos obtener el ID
            }

            return [
                'resultado' => $resultado,
                'id' => $id
            ];
        } catch (\PDOException $e) {
            // Registrar el error para depuración
            error_log("Error SQL en crear(): " . $e->getMessage());
            error_log("Error código: " . $e->getCode());
            error_log("Error info: " . json_encode(self::$db->errorInfo()));

            return [
                'resultado' => false,
                'error' => $e->getMessage()
            ];
        } catch (\Exception $e) {
            error_log("Excepción general en crear(): " . $e->getMessage());
            return [
                'resultado' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function actualizar()
    {
        // Sanitizar los datos
        $atributos = $this->sanitizarAtributos();

        // Iterar para ir agregando cada campo de la BD
        $valores = [];
        foreach ($atributos as $key => $value) {
            $valores[] = "{$key}={$value}";
        }
        $id = static::$idTabla ?? 'id';

        $query = "UPDATE " . static::$tabla . " SET ";
        $query .=  join(', ', $valores);

        if (is_array(static::$idTabla)) {
            foreach (static::$idTabla as $key => $value) {
                if ($value == reset(static::$idTabla)) {
                    $query .= " WHERE $value = " . self::$db->quote($this->$value);
                } else {
                    $query .= " AND $value = " . self::$db->quote($this->$value);
                }
            }
        } else {
            $query .= " WHERE " . $id . " = " . self::$db->quote($this->$id) . " ";
        }

        try {
            $resultado = self::$db->exec($query);
            return [
                'resultado' =>  $resultado,
            ];
        } catch (\PDOException $e) {
            // Registrar el error para depuración
            error_log("Error SQL en actualizar(): " . $e->getMessage());
            error_log("Consulta: " . $query);

            return [
                'resultado' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    // Eliminar un registro - Toma el ID de Active Record
    public function eliminar()
    {
        $idQuery = static::$idTabla ?? 'id';
        $query = "DELETE FROM "  . static::$tabla . " WHERE $idQuery = " . self::$db->quote($this->id);
        $resultado = self::$db->exec($query);
        return $resultado;
    }

    public static function consultarSQL($query)
    {
        try {
            // Consultar la base de datos
            $resultado = self::$db->query($query);

            // Iterar los resultados
            $array = [];
            while ($registro = $resultado->fetch(PDO::FETCH_ASSOC)) {
                $array[] = static::crearObjeto($registro);
            }

            // liberar la memoria
            $resultado->closeCursor();

            // retornar los resultados
            return $array;
        } catch (\PDOException $e) {
            error_log("Error en consultarSQL: " . $e->getMessage());
            error_log("Consulta: " . $query);
            return [];
        }
    }

    public static function fetchArray($query)
    {
        try {
            $resultado = self::$db->query($query);
            $respuesta = $resultado->fetchAll(PDO::FETCH_ASSOC);
            $data = [];
            foreach ($respuesta as $value) {
                $data[] = array_change_key_case(array_map('utf8_encode', $value));
            }
            $resultado->closeCursor();
            return $data;
        } catch (\PDOException $e) {
            error_log("Error en fetchArray: " . $e->getMessage());
            return [];
        }
    }

    public static function fetchFirst($query)
    {
        try {
            $resultado = self::$db->query($query);
            $respuesta = $resultado->fetchAll(PDO::FETCH_ASSOC);
            $data = [];
            foreach ($respuesta as $value) {
                $data[] = array_change_key_case(array_map('utf8_encode', $value));
            }
            $resultado->closeCursor();
            return array_shift($data);
        } catch (\PDOException $e) {
            error_log("Error en fetchFirst: " . $e->getMessage());
            return null;
        }
    }

    protected static function crearObjeto($registro)
    {
        $objeto = new static;

        foreach ($registro as $key => $value) {
            $key = strtolower($key);
            if (property_exists($objeto, $key)) {
                $objeto->$key = utf8_encode($value);
            }
        }

        return $objeto;
    }

    // Identificar y unir los atributos de la BD
    public function atributos()
    {
        $atributos = [];
        foreach (static::$columnasDB as $columna) {
            $columna = strtolower($columna);
            if ($columna === 'id' || $columna === static::$idTabla) continue;
            $atributos[$columna] = $this->$columna;
        }
        return $atributos;
    }

    public function sanitizarAtributos()
    {
        $atributos = $this->atributos();
        $sanitizado = [];
        foreach ($atributos as $key => $value) {
            $sanitizado[$key] = self::$db->quote($value);
        }
        return $sanitizado;
    }

    public function sincronizar($args = [])
    {
        foreach ($args as $key => $value) {
            if (property_exists($this, $key) && !is_null($value)) {
                $this->$key = $value;
            }
        }
    }
}
