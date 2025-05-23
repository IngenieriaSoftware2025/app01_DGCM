
-- Tabla de clientes
select * from clientes
CREATE TABLE clientes (
    id_cliente SERIAL PRIMARY KEY,
    nombres VARCHAR(80) NOT NULL,
    apellidos VARCHAR(80) NOT NULL,
    telefono VARCHAR(20),
    correo VARCHAR(100),
    direccion VARCHAR(200),
    situacion SMALLINT DEFAULT 1
);
DELETE FROM clientes;

select *from categorias
-- Tabla de categorías\
drop table categorias
CREATE TABLE categorias (
    id_categoria SERIAL PRIMARY KEY,
    nombre VARCHAR(30) NOT NULL,
    situacion SMALLINT DEFAULT 1
);


select *from prioridades

drop table prioridades

CREATE TABLE prioridades (
    id_prioridad SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    situacion SMALLINT DEFAULT 1
);


-- Tabla de productos
drop table productos
CREATE TABLE productos (
    id_producto SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    cantidad INTEGER NOT NULL,
    id_categoria INTEGER NOT NULL,
    id_prioridad INTEGER NOT NULL,
    id_cliente INTEGER NOT NULL,
    comprado SMALLINT DEFAULT 0,
    situacion SMALLINT DEFAULT 1
);

select * from productos


-- productos
ALTER TABLE productos ADD CONSTRAINT FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria);
ALTER TABLE productos ADD CONSTRAINT FOREIGN KEY (id_prioridad) REFERENCES prioridades(id_prioridad);
ALTER TABLE productos ADD CONSTRAINT FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente);
