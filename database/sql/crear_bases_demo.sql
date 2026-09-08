-- Ejecute como administrador MySQL. Cambie la clave antes de usar en un servidor público.
CREATE DATABASE IF NOT EXISTS sistema_bonos_demo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS sistema_bonos_demo_personas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS sistema_bonos_demo_medichile CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'bonos_demo'@'localhost' IDENTIFIED BY 'CAMBIAR_ESTA_CLAVE';
GRANT ALL PRIVILEGES ON sistema_bonos_demo.* TO 'bonos_demo'@'localhost';
GRANT ALL PRIVILEGES ON sistema_bonos_demo_personas.* TO 'bonos_demo'@'localhost';
GRANT ALL PRIVILEGES ON sistema_bonos_demo_medichile.* TO 'bonos_demo'@'localhost';
FLUSH PRIVILEGES;
