
-- Tabla de clientes
CREATE TABLE clientes (
    id_cliente SERIAL PRIMARY KEY,
    nombres VARCHAR(80) NOT NULL,
    apellidos VARCHAR(80) NOT NULL,
    telefono VARCHAR(20),
    correo VARCHAR(100),
    direccion VARCHAR(200),
    situacion SMALLINT DEFAULT 1
);

-- Tabla de categorías
Drop table categorias
CREATE TABLE categorias (
    id_categoria SERIAL PRIMARY KEY,
    nombre VARCHAR(30) UNIQUE NOT NULL,
    situacion SMALLINT DEFAULT 1
);

CREATE TABLE prioridades (
    id_prioridad SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    situacion SMALLINT DEFAULT 1
);


-- Tabla de productos
CREATE TABLE productos (
    id_producto SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    cantidad INTEGER NOT NULL,
    id_categoria INTEGER NOT NULL,
    id_prioridad INTEGER NOT NULL,
    comprado SMALLINT DEFAULT 0,
    situacion SMALLINT DEFAULT 1
);


-- Tabla compras (cabecera)
CREATE TABLE compras (
    id_compra SERIAL PRIMARY KEY,
    fecha DATETIME YEAR TO SECOND DEFAULT CURRENT YEAR TO SECOND,
    id_cliente INTEGER NOT NULL,
    situacion SMALLINT DEFAULT 1
);

-- Tabla detalle de compra
CREATE TABLE detalle_compra (
    id_detalle_compra SERIAL PRIMARY KEY,
    id_compra INTEGER NOT NULL,
    id_producto INTEGER NOT NULL,
    cantidad INTEGER NOT NULL,
    id_prioridad INTEGER NOT NULL,
    comprado SMALLINT DEFAULT 0,
    situacion SMALLINT DEFAULT 1
);

-- Tabla ventas (cabecera)
CREATE TABLE ventas (
    id_venta SERIAL PRIMARY KEY,
    fecha DATETIME YEAR TO SECOND DEFAULT CURRENT YEAR TO SECOND,
    id_cliente INTEGER NOT NULL,
    situacion SMALLINT DEFAULT 1
);

-- Tabla detalle de venta
CREATE TABLE detalle_venta (
    id_detalle_venta SERIAL PRIMARY KEY,
    id_venta INTEGER NOT NULL,
    id_producto INTEGER NOT NULL,
    cantidad INTEGER NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2)
);

--EJEMPLO DE FK
ALTER TABLE producto_insumo ADD CONSTRAINT FOREIGN KEY (id_producto) REFERENCES productos_menu;

-- productos
ALTER TABLE productos ADD CONSTRAINT FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria);
ALTER TABLE productos ADD CONSTRAINT FOREIGN KEY (id_prioridad) REFERENCES prioridades(id_prioridad);

-- compras
ALTER TABLE compras ADD CONSTRAINT FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente);

-- detalle_compra
ALTER TABLE detalle_compra ADD CONSTRAINT FOREIGN KEY (id_compra) REFERENCES compras(id_compra);
ALTER TABLE detalle_compra ADD CONSTRAINT FOREIGN KEY (id_producto) REFERENCES productos(id_producto);
ALTER TABLE detalle_compra ADD CONSTRAINT FOREIGN KEY (id_prioridad) REFERENCES prioridades(id_prioridad);

-- ventas
ALTER TABLE ventas ADD CONSTRAINT FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente);

-- detalle_venta
ALTER TABLE detalle_venta ADD CONSTRAINT FOREIGN KEY (id_venta) REFERENCES ventas(id_venta);
ALTER TABLE detalle_venta ADD CONSTRAINT FOREIGN KEY (id_producto) REFERENCES productos(id_producto);

