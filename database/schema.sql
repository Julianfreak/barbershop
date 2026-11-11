-- Esquema inicial de BuzzCut BarberShop.
-- Compatible con MySQL 5.7.
-- Este archivo se ejecuta automáticamente solo cuando el volumen MySQL está vacío.

CREATE DATABASE IF NOT EXISTS buzzcut
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE buzzcut;

CREATE TABLE IF NOT EXISTS ROL (
  id TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(30) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_rol_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS USUARIO (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(120) NOT NULL,
  nombre_usuario VARCHAR(60) NOT NULL,
  contrasena VARCHAR(255) NOT NULL,
  rol_id TINYINT UNSIGNED NOT NULL,
  telefono VARCHAR(25) DEFAULT NULL,
  correo VARCHAR(254) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_usuario_nombre_usuario (nombre_usuario),
  UNIQUE KEY uq_usuario_correo (correo),
  KEY idx_usuario_rol_id (rol_id),
  CONSTRAINT fk_usuario_rol FOREIGN KEY (rol_id) REFERENCES ROL (id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS PRODUCTO (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo VARCHAR(50) NOT NULL,
  nombre VARCHAR(120) NOT NULL,
  precio DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  stock INT UNSIGNED NOT NULL DEFAULT 0,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_producto_codigo (codigo),
  KEY idx_producto_nombre (nombre),
  CONSTRAINT chk_producto_precio CHECK (precio >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS VENTA (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id INT UNSIGNED NOT NULL,
  servicio VARCHAR(60) DEFAULT NULL,
  productos TEXT DEFAULT NULL,
  total DECIMAL(12,2) NOT NULL,
  fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_venta_usuario_id (usuario_id),
  KEY idx_venta_fecha (fecha),
  CONSTRAINT fk_venta_usuario FOREIGN KEY (usuario_id) REFERENCES USUARIO (id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT chk_venta_total CHECK (total >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS VENTA_PRODUCTO (
  venta_id INT UNSIGNED NOT NULL,
  producto_id INT UNSIGNED NOT NULL,
  cantidad INT UNSIGNED NOT NULL DEFAULT 1,
  precio_unitario DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (venta_id, producto_id),
  KEY idx_venta_producto_producto_id (producto_id),
  CONSTRAINT fk_venta_producto_venta FOREIGN KEY (venta_id) REFERENCES VENTA (id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_venta_producto_producto FOREIGN KEY (producto_id) REFERENCES PRODUCTO (id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO ROL (id, nombre) VALUES
  (1, 'admin'),
  (2, 'empleado');

-- Precios iniciales de referencia. Ajustarlos a los precios reales del negocio.
INSERT IGNORE INTO PRODUCTO (codigo, nombre, precio, stock) VALUES
  ('shampoo', 'Shampoo', 55000.00, 0),
  ('cera', 'Cera', 25000.00, 0),
  ('gel', 'Gel', 15000.00, 0),
  ('maquina', 'Máquina de Corte', 180000.00, 0),
  ('tratamiento', 'Tratamiento Capilar', 75000.00, 0);
