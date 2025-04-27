-- Inserciones para tabla Usuario
INSERT INTO usuario (email, roles, password, nombre, dni, telefono, calle, num_calle, cod_postal, ciudad) 
VALUES
('laura.gomez@gmail.com', '["ROLE_USER"]', '1234', 'Laura Gómez', '12345678A', '612345678', 'Calle Luna', 12, '28001', 'Madrid'),
('carlos.martinez@gmail.com', '["ROLE_USER"]', '1234', 'Carlos Martínez', '87654321B', '678901234', 'Calle Sol', 7, '41001', 'Sevilla'),
('ana.lopez@gmail.com', '["ROLE_USER"]', '1234', 'Ana López', '45678912C', '699887766', 'Calle Flor', 21, '46002', 'Valencia'),
('pedro.sanchez@gmail.com', '["ROLE_USER"]', '1234', 'Pedro Sánchez', '11223344D', '611223344', 'Calle Río', 5, '08001', 'Barcelona'),
('marta.perez@gmail.com', '["ROLE_USER"]', '1234', 'Marta Pérez', '99887766E', '688776655', 'Calle Mar', 33, '50001', 'Zaragoza');
-- ('admin@gmail.com', '["ROLE_ADMIN"]', '1234', 'Admin', ' ', ' ', ' ', ' ', ' ', ' ');

UPDATE `usuario` SET `password` = '$2y$13$6lQH/3bURs1eTGRnOjxIsOxAV/nXxqGGHTnC.hG4xsltBcBqDgZ8O' 
		WHERE `roles` = '["ROLE_USER"]' or `roles` = '["ROLE_ADMIN"]';


-- Inserciones para tabla Mascota
INSERT INTO mascota (id_usuario_id, nombre, num_chip, observaciones) VALUES
(1, 'Luna', '9812345678', 'Vacunado'),
(1, 'Max', '9823456789', 'Tranquilo'),
(2, 'Mia', '9834567890', 'Alergia al pollo'),
(3, 'Rocky', '9845678901', 'Muy activo'),
(3, 'Nala', '9856789012', 'Vacunado'),
(4, 'Toby', '9867890123', "Le gustan las pelotas"),
(4, 'Simba', '9878901234', 'Tranquilo'),
(5, 'Coco', '9889012345', "Asustadizo"),
(5, 'Luna', '9890123456', 'Vacunado'),
(2, 'Max', '9901234567', 'Tranquilo');




