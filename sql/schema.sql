-- --------------------------------------------------------
-- Base de datos: `reposteria_db`
-- --------------------------------------------------------

CREATE DATABASE IF NOT EXISTS `reposteria_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `reposteria_db`;

-- --------------------------------------------------------
-- Tabla: `admin`
-- --------------------------------------------------------
CREATE TABLE `admin` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL
);

-- 🔐 Insertar un administrador por defecto
-- Usuario: admin@reposteria.com
-- Contraseña: admin123
INSERT INTO `admin` (`nombre`, `email`, `password`)
VALUES ('Administrador', 'admin@reposteria.com',
'$2y$10$YzR8T0vV86wAa.YQ2aEIZuR5T97tGxPPNo7O3rL3GvRJmnyKXxkfi');

-- --------------------------------------------------------
-- Tabla: `productos`
-- --------------------------------------------------------
CREATE TABLE `productos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL,
  `descripcion` TEXT NOT NULL,
  `precio` DECIMAL(10,2) NOT NULL,
  `categoria` VARCHAR(50) NOT NULL,
  `imagen` VARCHAR(255) DEFAULT NULL,
  `disponible` TINYINT(1) DEFAULT 1,
  `creado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 🍪 Datos de ejemplo
INSERT INTO `productos` (`nombre`, `descripcion`, `precio`, `categoria`, `imagen`, `disponible`) VALUES
('Torta de Chocolate', 'Deliciosa torta con cobertura cremosa de cacao puro', 45000, 'Tortas', 'torta_chocolate.jpg', 1),
('Postre de Maracuyá', 'Suave mousse tropical con base de galleta', 12000, 'Postres', 'postre_maracuya.jpg', 1),
('Cupcake Vainilla', 'Mini pastel con betún de crema y chispas', 6000, 'Galletería', 'cupcake_vainilla.jpg', 1),
('Pan de Queso', 'Horneado fresco con sabor auténtico', 3000, 'Panadería', 'pan_queso.jpg', 1);

-- --------------------------------------------------------
-- Tabla: `pedidos`
-- --------------------------------------------------------
CREATE TABLE `pedidos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `cliente` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `telefono` VARCHAR(50),
  `direccion` VARCHAR(255),
  `total` DECIMAL(10,2) NOT NULL,
  `estado` ENUM('pendiente','en_preparacion','listo','entregado') DEFAULT 'pendiente',
  `fecha` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Ejemplo vacío (los pedidos se agregarán desde el sistema)
