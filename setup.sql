DROP DATABASE IF EXISTS ironshield;
CREATE DATABASE ironshield CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE ironshield;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- Tabla: empresas
CREATE TABLE empresas (
  cif VARCHAR(20) NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  PRIMARY KEY (cif),
  UNIQUE (nombre)
);

INSERT INTO empresas (cif, nombre) VALUES
('B34234234', 'IGT');

-- Tabla: usuarios
CREATE TABLE usuarios (
  dni VARCHAR(20) NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL,
  contrasena VARCHAR(255) NOT NULL,
  fecha_registro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  empresa_id VARCHAR(20),
  rol ENUM('admin','usuario') NOT NULL,
  PRIMARY KEY (dni),
  UNIQUE (email),
  KEY (empresa_id),
  FOREIGN KEY (empresa_id) REFERENCES empresas(cif) ON DELETE CASCADE
);

INSERT INTO usuarios (dni, nombre, email, contrasena, fecha_registro, empresa_id, rol) VALUES
('23867227N', 'm', 'm@gmail.com', '$2y$10$4N2oaxg3SRAuqDFx9PDYoOK0t7KC0JPuXM.ofAh598ormFlwDKVz6', '2025-05-05 17:17:41', 'B34234234', 'usuario'),
('23867434n', 'jose espinosa', 'marc@gmail.com', '$2y$10$58kp3sVizlU8Jpw6eWH3FeNvu41IwtT/qVDAQ2mBBu9PPaOdw5qXW', '2025-05-05 18:49:11', 'B34234234', 'admin');

-- Tabla: auditoria
CREATE TABLE auditoria (
  id INT(11) NOT NULL AUTO_INCREMENT,
  usuario_id VARCHAR(20) NOT NULL,
  empresa_id VARCHAR(20),
  accion VARCHAR(255) NOT NULL,
  detalles TEXT,
  ip_origen VARCHAR(45),
  fecha TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY (usuario_id),
  KEY (empresa_id),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(dni) ON DELETE CASCADE,
  FOREIGN KEY (empresa_id) REFERENCES empresas(cif) ON DELETE CASCADE
);

-- Tabla: blacklist
CREATE TABLE blacklist (
  id INT(11) NOT NULL AUTO_INCREMENT,
  tipo ENUM('IP','dominio') NOT NULL,
  valor VARCHAR(255) NOT NULL,
  rango VARCHAR(50),
  fecha TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  comentario TEXT,
  usuario_id VARCHAR(20) NOT NULL,
  empresa_id VARCHAR(20),
  PRIMARY KEY (id),
  UNIQUE KEY (empresa_id, tipo, valor),
  KEY (usuario_id),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(dni) ON DELETE CASCADE,
  FOREIGN KEY (empresa_id) REFERENCES empresas(cif) ON DELETE CASCADE
);

-- Tabla: escaneos
CREATE TABLE escaneos (
  id INT(11) NOT NULL AUTO_INCREMENT,
  ip VARCHAR(45) NOT NULL,
  mac VARCHAR(17),
  puertos_abiertos TEXT,
  sistema_operativo VARCHAR(100),
  fecha TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  usuario_id VARCHAR(20),
  empresa_id VARCHAR(20),
  PRIMARY KEY (id),
  KEY (usuario_id),
  KEY (empresa_id),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(dni) ON DELETE CASCADE,
  FOREIGN KEY (empresa_id) REFERENCES empresas(cif) ON DELETE CASCADE
);

-- Tabla: logs_sniffer
CREATE TABLE logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_origen VARCHAR(45),
    ip_destino VARCHAR(45),
    puerto_origen INT,
    puerto_destino INT,
    protocolo VARCHAR(10),
    tamano INT,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP
);


-- Tabla: pagos
CREATE TABLE pagos (
  id_pago INT(11) NOT NULL AUTO_INCREMENT,
  usuario_id VARCHAR(20),
  empresa_id VARCHAR(20),
  plan_id INT(11) NOT NULL,
  monto DECIMAL(10,2) NOT NULL,
  fecha_pago DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  metodo_pago ENUM('tarjeta','paypal','transferencia','otros') NOT NULL,
  estado ENUM('pendiente','completado','fallido') NOT NULL DEFAULT 'pendiente',
  referencia VARCHAR(100),
  periodicidad ENUM('mensual','anual') NOT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id_pago),
  KEY (usuario_id),
  KEY (empresa_id),
  KEY (plan_id),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(dni) ON DELETE CASCADE,
  FOREIGN KEY (empresa_id) REFERENCES empresas(cif) ON DELETE CASCADE
);

INSERT INTO pagos (id_pago, usuario_id, empresa_id, plan_id, monto, fecha_pago, metodo_pago, estado, referencia, periodicidad, activo) VALUES
(13, '23867227N', 'B34234234', 3, 306.00, '2025-05-05 19:17:41', 'tarjeta', 'pendiente', 'pago_6818f2b5c3cf4', 'anual', 1);

-- Tabla: whitelist
CREATE TABLE whitelist (
  id INT(11) NOT NULL AUTO_INCREMENT,
  tipo ENUM('IP','dominio') NOT NULL,
  valor VARCHAR(255) NOT NULL,
  rango VARCHAR(50),
  fecha TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  comentario TEXT,
  usuario_id VARCHAR(20) NOT NULL,
  empresa_id VARCHAR(20),
  PRIMARY KEY (id),
  UNIQUE KEY (empresa_id, tipo, valor),
  KEY (usuario_id),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(dni) ON DELETE CASCADE,
  FOREIGN KEY (empresa_id) REFERENCES empresas(cif) ON DELETE CASCADE
);
