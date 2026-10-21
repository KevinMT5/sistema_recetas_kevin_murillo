CREATE DATABASE IF NOT EXISTS sistema_recetas
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE sistema_recetas;

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    rol VARCHAR(255) NOT NULL DEFAULT 'administrador',
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS establecimientos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    direccion VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS medicos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    numero_jv VARCHAR(255) NOT NULL UNIQUE,
    especialidad VARCHAR(255) NULL,
    establecimiento_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT medicos_establecimiento_fk
        FOREIGN KEY (establecimiento_id) REFERENCES establecimientos(id)
        ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pacientes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    fecha_nacimiento DATE NULL,
    telefono VARCHAR(25) NULL,
    direccion VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS medicamentos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    presentacion VARCHAR(255) NULL,
    concentracion VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS recetas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL,
    numero_receta VARCHAR(255) NOT NULL UNIQUE,
    medico_id BIGINT UNSIGNED NOT NULL,
    paciente_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT recetas_medico_fk
        FOREIGN KEY (medico_id) REFERENCES medicos(id)
        ON DELETE RESTRICT,
    CONSTRAINT recetas_paciente_fk
        FOREIGN KEY (paciente_id) REFERENCES pacientes(id)
        ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS receta_detalles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    receta_id BIGINT UNSIGNED NOT NULL,
    medicamento_id BIGINT UNSIGNED NOT NULL,
    cantidad INT UNSIGNED NOT NULL,
    dosis VARCHAR(255) NOT NULL,
    indicaciones TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT detalles_receta_fk
        FOREIGN KEY (receta_id) REFERENCES recetas(id)
        ON DELETE CASCADE,
    CONSTRAINT detalles_medicamento_fk
        FOREIGN KEY (medicamento_id) REFERENCES medicamentos(id)
        ON DELETE RESTRICT
) ENGINE=InnoDB;

INSERT INTO users (name, email, password, rol, created_at, updated_at)
VALUES (
    'Administrador',
    'admin@recetas.test',
    '$2y$10$z1J2Ndw4JHQ6Wf/80j3onuM1aM2tGp1FDyQzXObcAabiybjKnO/Vm',
    'administrador',
    NOW(),
    NOW()
)
ON DUPLICATE KEY UPDATE name = VALUES(name), updated_at = NOW();

INSERT INTO establecimientos (id, nombre, direccion, created_at, updated_at) VALUES
    (1, 'Hospital Nacional', 'San Salvador', NOW(), NOW()),
    (2, 'Clínica Central', 'Santa Tecla', NOW(), NOW())
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), direccion = VALUES(direccion), updated_at = NOW();

INSERT INTO medicos (id, nombre, numero_jv, especialidad, establecimiento_id, created_at, updated_at) VALUES
    (1, 'Dra. Ana Martínez', 'JV-1001', 'Medicina general', 1, NOW(), NOW()),
    (2, 'Dr. Carlos Hernández', 'JV-1002', 'Pediatría', 2, NOW(), NOW())
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), especialidad = VALUES(especialidad), updated_at = NOW();

INSERT INTO pacientes (id, nombre, fecha_nacimiento, telefono, direccion, created_at, updated_at) VALUES
    (1, 'María López', '1990-05-15', '7000-0001', 'San Salvador', NOW(), NOW()),
    (2, 'José Pérez', '1985-11-20', '7000-0002', 'Santa Tecla', NOW(), NOW())
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), telefono = VALUES(telefono), updated_at = NOW();

INSERT INTO medicamentos (id, nombre, presentacion, concentracion, created_at, updated_at) VALUES
    (1, 'Acetaminofén', 'Tabletas', '500 mg', NOW(), NOW()),
    (2, 'Amoxicilina', 'Cápsulas', '500 mg', NOW(), NOW()),
    (3, 'Ibuprofeno', 'Tabletas', '400 mg', NOW(), NOW())
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), presentacion = VALUES(presentacion), updated_at = NOW();

INSERT INTO recetas (id, fecha, numero_receta, medico_id, paciente_id, created_at, updated_at)
VALUES (1, CURRENT_DATE, 'REC-0001', 1, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE fecha = VALUES(fecha), updated_at = NOW();

INSERT INTO receta_detalles (id, receta_id, medicamento_id, cantidad, dosis, indicaciones, created_at, updated_at)
VALUES (1, 1, 1, 10, '1 tableta cada 8 horas', 'Tomar después de los alimentos', NOW(), NOW())
ON DUPLICATE KEY UPDATE cantidad = VALUES(cantidad), dosis = VALUES(dosis), updated_at = NOW();
