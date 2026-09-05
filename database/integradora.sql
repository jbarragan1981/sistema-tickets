-- =============================================================
-- Gestión de incidencias
-- Base de datos: integradora
-- Motor: MySQL / MariaDB 
--
-- Ejecutar desde phpMyAdmin (Importar) o desde consola:
--   mysql -u root < database/integradora.sql
-- =============================================================

CREATE DATABASE IF NOT EXISTS integradora
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE integradora;

-- -------------------------------------------------------------
-- Catálogo de categorías de incidencia
-- -------------------------------------------------------------
DROP TABLE IF EXISTS incidencias;
DROP TABLE IF EXISTS categorias;

CREATE TABLE categorias (
    id     INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(60) NOT NULL UNIQUE,
    color  VARCHAR(7)  NOT NULL DEFAULT '#2C8C8C'
) ENGINE = InnoDB;

INSERT INTO categorias (nombre, color) VALUES
    ('Hardware',        '#EC7A69'),
    ('Software',        '#2C8C8C'),
    ('Red',             '#14274E'),
    ('Accesos',         '#F2B035'),
    ('Infraestructura', '#5FC2A4');

-- -------------------------------------------------------------
-- Tabla principal: incidencias reportadas
-- El campo estado alimenta las columnas del tablero.
-- -------------------------------------------------------------
CREATE TABLE incidencias (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    titulo       VARCHAR(120) NOT NULL,
    descripcion  TEXT         NOT NULL,
    categoria_id INT          NOT NULL,
    prioridad    ENUM('Baja', 'Media', 'Alta', 'Crítica')            NOT NULL DEFAULT 'Media',
    estado       ENUM('Nuevo', 'En proceso', 'Resuelto', 'Cerrado')  NOT NULL DEFAULT 'Nuevo',
    reportante   VARCHAR(100) NOT NULL,
    correo       VARCHAR(150) NOT NULL,
    area_codigo  SMALLINT     NOT NULL,
    fecha_reporte       DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_incidencia_categoria
        FOREIGN KEY (categoria_id) REFERENCES categorias (id),

    INDEX idx_estado (estado),
    INDEX idx_prioridad (prioridad)
) ENGINE = InnoDB;

-- -------------------------------------------------------------
-- Datos de prueba: ocho incidencias repartidas entre los cuatro
-- estados del tablero, con categorías y prioridades variadas.
-- -------------------------------------------------------------
INSERT INTO incidencias
    (titulo, descripcion, categoria_id, prioridad, estado, reportante, correo, area_codigo, fecha_reporte)
VALUES
    ('La impresora del laboratorio B no responde',
     'La impresora HP del laboratorio de sistemas no enciende desde esta mañana. Ya se revisó el cable de poder.',
     1, 'Media', 'Nuevo', 'Carlos Mendoza', 'carlos.mendoza@ecotec.edu.ec', 102, '2026-09-01 08:15:00'),

    ('Solicitud de acceso a carpeta compartida de Secretaría',
     'Se requiere acceso de lectura a la carpeta compartida de matrículas para el nuevo asistente administrativo.',
     4, 'Baja', 'Nuevo', 'Estefanía Rojas', 'estefania.rojas@ecotec.edu.ec', 201, '2026-09-02 09:40:00'),

    ('Proyector del aula 305 no muestra imagen',
     'El proyector enciende pero la pantalla permanece en negro al conectar cualquier laptop por HDMI.',
     1, 'Alta', 'Nuevo', 'Mariana Torres', 'mariana.torres@ecotec.edu.ec', 305, '2026-09-03 11:05:00'),

    ('Caída intermitente de la red en el bloque de laboratorios',
     'Los estudiantes reportan desconexiones de Wi-Fi cada 15 minutos aproximadamente durante las prácticas.',
     3, 'Alta', 'En proceso', 'Luis Andrade', 'luis.andrade@ecotec.edu.ec', 110, '2026-08-30 14:20:00'),

    ('Error al generar reportes en el sistema de calificaciones',
     'Al exportar el reporte de notas del segundo parcial el sistema muestra un error 500 y no descarga el archivo.',
     2, 'Crítica', 'En proceso', 'Marcela Vera', 'marcela.vera@ecotec.edu.ec', 204, '2026-08-29 16:50:00'),

    ('Actualización de antivirus pendiente en sala de cómputo',
     'Los equipos de la sala de cómputo 2 muestran la licencia del antivirus como vencida desde la semana pasada.',
     2, 'Media', 'En proceso', 'Andrés Palacios', 'andres.palacios@ecotec.edu.ec', 108, '2026-08-28 10:10:00'),

    ('Reinicio del servidor de correo institucional',
     'El correo institucional dejó de enviar mensajes por unas horas; se reinició el servicio y quedó operativo.',
     5, 'Alta', 'Resuelto', 'Diego Salas', 'diego.salas@ecotec.edu.ec', 501, '2026-08-27 07:30:00'),

    ('Cambio de contraseña del router principal del edificio A',
     'Se actualizó la contraseña del router principal tras la auditoría de seguridad y se notificó a los usuarios.',
     3, 'Media', 'Cerrado', 'Jorge Ponce', 'jorge.ponce@ecotec.edu.ec', 100, '2026-08-18 13:00:00');
