-- Header comment: Esquema de Base de Datos - App Concejo Municipal de Chía
-- Version: 1.0
-- Motor: MySQL / MariaDB

-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS concejo_chia
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE concejo_chia;

-- =============================================
-- FASE 1: Tablas del MVP
-- =============================================

-- Tabla: noticias
CREATE TABLE IF NOT EXISTS noticias (
  id_noticia INT AUTO_INCREMENT PRIMARY KEY,
  titulo VARCHAR(255) NOT NULL,
  contenido TEXT NOT NULL,
  url_imagen VARCHAR(255) DEFAULT NULL,
  fecha_pub DATETIME DEFAULT CURRENT_TIMESTAMP,
  estado ENUM('activo', 'inactivo') DEFAULT 'activo',
  INDEX idx_estado_fecha (estado, fecha_pub)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: documentos
CREATE TABLE IF NOT EXISTS documentos (
  id_doc INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(200) NOT NULL,
  categoria VARCHAR(100) NOT NULL,
  url_pdf VARCHAR(255) NOT NULL,
  fecha_sub DATE NOT NULL,
  INDEX idx_categoria (categoria),
  INDEX idx_fecha (fecha_sub)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: agenda
CREATE TABLE IF NOT EXISTS agenda (
  id_evento INT AUTO_INCREMENT PRIMARY KEY,
  titulo VARCHAR(150) NOT NULL,
  fecha_hora DATETIME NOT NULL,
  link_yt VARCHAR(255) DEFAULT NULL,
  INDEX idx_fecha_hora (fecha_hora)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: admin_usuarios (Panel de administración interno)
CREATE TABLE IF NOT EXISTS admin_usuarios (
  id_admin INT AUTO_INCREMENT PRIMARY KEY,
  usuario VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  nombre VARCHAR(150) NOT NULL,
  rol ENUM('admin', 'editor') DEFAULT 'editor',
  fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_usuario (usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- FASE 2: Tablas proyectadas (NO crear aún)
-- =============================================
-- These are commented out - to be implemented in Phase 2

/*
CREATE TABLE IF NOT EXISTS usuarios (
  id_user INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(150) NOT NULL,
  email VARCHAR(200) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS concejales (
  id_concejal INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(150) NOT NULL,
  partido VARCHAR(100),
  biografia TEXT,
  foto_url VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS encuestas (
  id_encuesta INT AUTO_INCREMENT PRIMARY KEY,
  pregunta TEXT NOT NULL,
  fecha_cierre DATE,
  estado ENUM('activa', 'cerrada') DEFAULT 'activa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS suscripciones (
  id_user INT NOT NULL,
  id_concejal INT NOT NULL,
  PRIMARY KEY (id_user, id_concejal),
  FOREIGN KEY (id_user) REFERENCES usuarios(id_user) ON DELETE CASCADE,
  FOREIGN KEY (id_concejal) REFERENCES concejales(id_concejal) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
*/

-- =============================================
-- DATOS DE PRUEBA (Seed Data)
-- =============================================

-- Admin por defecto (password: admin123 - CAMBIAR EN PRODUCCIÓN)
-- The hash below is for 'admin123' generated with password_hash()
INSERT INTO admin_usuarios (usuario, password_hash, nombre, rol) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador Prensa', 'admin'),
('editor_prensa', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Editor de Prensa', 'editor');

-- Noticias de ejemplo
INSERT INTO noticias (titulo, contenido, url_imagen, fecha_pub, estado) VALUES
('Aprobado nuevo acuerdo para el desarrollo sostenible de Chía', 'El Concejo Municipal de Chía aprobó por unanimidad el Acuerdo No. 15 de 2026, que establece los lineamientos para el desarrollo sostenible del municipio. Este acuerdo busca promover prácticas ambientales responsables y un crecimiento urbano planificado que preserve los recursos naturales de la región.', NULL, '2026-08-15 10:00:00', 'activo'),
('Sesión Plenaria extraordinaria convocada para el 20 de agosto', 'Se convoca a sesión plenaria extraordinaria del Concejo Municipal de Chía para el día 20 de agosto de 2026, a las 9:00 AM. En el orden del día se incluye la discusión del proyecto de acuerdo sobre el presupuesto participativo 2027.', NULL, '2026-08-12 14:30:00', 'activo'),
('Concejo de Chía rinde informe de gestión del primer semestre 2026', 'En cumplimiento de la Ley de Transparencia, el Concejo Municipal de Chía presenta su informe de gestión correspondiente al primer semestre de 2026. Durante este periodo se aprobaron 8 acuerdos municipales y se realizaron 24 sesiones plenarias.', NULL, '2026-08-10 09:00:00', 'activo'),
('Nueva resolución fortalece la participación ciudadana en sesiones', 'El Concejo Municipal aprobó una nueva resolución que amplía los mecanismos de participación ciudadana durante las sesiones plenarias. Los ciudadanos podrán inscribirse para intervenir en las sesiones a través de la aplicación móvil del Concejo.', NULL, '2026-08-08 11:00:00', 'activo'),
('Comisión de Presupuesto inicia estudio del Plan de Desarrollo', 'La Comisión Primera de Presupuesto y Hacienda del Concejo Municipal inició el estudio detallado del Plan de Desarrollo Municipal 2026-2030, con énfasis en los programas de inversión social.', NULL, '2026-08-05 16:00:00', 'activo'),
('Concejo aprueba presupuesto para mejoramiento de vías rurales', 'En sesión del 1 de agosto, el Concejo Municipal de Chía aprobó un presupuesto adicional de $2.500 millones para el mejoramiento de las vías rurales del municipio, beneficiando a más de 15.000 habitantes de las veredas.', NULL, '2026-08-01 10:30:00', 'activo');

-- Eventos de ejemplo
INSERT INTO agenda (titulo, fecha_hora, link_yt) VALUES
('Sesión Plenaria Ordinaria', '2026-08-20 09:00:00', 'https://www.youtube.com/watch?v=ejemplo1'),
('Comisión Primera - Presupuesto y Hacienda', '2026-08-22 14:00:00', NULL),
('Comisión Segunda - Gobierno y Asuntos Sociales', '2026-08-25 10:00:00', NULL),
('Sesión Plenaria Extraordinaria', '2026-08-28 09:00:00', 'https://www.youtube.com/watch?v=ejemplo2'),
('Audiencia Pública de Rendición de Cuentas', '2026-09-05 15:00:00', 'https://www.youtube.com/watch?v=ejemplo3');

-- Documentos de ejemplo
INSERT INTO documentos (nombre, categoria, url_pdf, fecha_sub) VALUES
('Acuerdo No. 15 de 2026 - Plan de Desarrollo Sostenible', 'Acuerdo', '/documentos/acuerdo_015_2026.pdf', '2026-08-15'),
('Acuerdo No. 14 de 2026 - Presupuesto Vías Rurales', 'Acuerdo', '/documentos/acuerdo_014_2026.pdf', '2026-08-01'),
('Resolución No. 08 de 2026 - Participación Ciudadana', 'Resolución', '/documentos/resolucion_008_2026.pdf', '2026-08-08'),
('Acta Sesión Plenaria No. 24 - Julio 2026', 'Acta', '/documentos/acta_024_julio_2026.pdf', '2026-07-28'),
('Acuerdo No. 13 de 2026 - Política Pública de Juventud', 'Acuerdo', '/documentos/acuerdo_013_2026.pdf', '2026-07-15'),
('Resolución No. 07 de 2026 - Calendario de Sesiones', 'Resolución', '/documentos/resolucion_007_2026.pdf', '2026-07-10'),
('Acta Sesión Plenaria No. 23 - Julio 2026', 'Acta', '/documentos/acta_023_julio_2026.pdf', '2026-07-14'),
('Acuerdo No. 12 de 2026 - Cultura y Deporte', 'Acuerdo', '/documentos/acuerdo_012_2026.pdf', '2026-06-30');
