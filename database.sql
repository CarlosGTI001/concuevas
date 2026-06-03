CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  must_change_credentials TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS site_settings (
  `key` VARCHAR(120) PRIMARY KEY,
  `value` TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS services (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(180) NOT NULL,
  short_description VARCHAR(255) NOT NULL,
  long_description TEXT NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS projects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(180) NOT NULL,
  slug VARCHAR(220) NOT NULL UNIQUE,
  excerpt VARCHAR(255) NOT NULL,
  description TEXT NOT NULL,
  cover_image_url TEXT NOT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS project_images (
  id INT AUTO_INCREMENT PRIMARY KEY,
  project_id INT NOT NULL,
  image_url TEXT NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  CONSTRAINT fk_project_images_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS quote_requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(180) NOT NULL,
  email VARCHAR(180) NULL,
  phone VARCHAR(60) NOT NULL,
  project_type VARCHAR(100) NOT NULL,
  message TEXT NOT NULL,
  created_at DATETIME NOT NULL
);

INSERT INTO users (name, email, password_hash, must_change_credentials, created_at)
SELECT 'Administrador', 'admin@cuevas.local', '$2b$10$GVHY2Zx7try8eH8qxjN6HO9QUhoERBfqiWDzzsHNPYOJyACDD462S', 1, NOW()
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'admin@cuevas.local');

INSERT INTO services (name, short_description, long_description, sort_order, created_at, updated_at)
SELECT 'Construcción Residencial', 'Casas y ampliaciones con control de calidad.', 'Ejecución residencial con planificación, supervisión técnica y acabados.', 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM services);

INSERT INTO services (name, short_description, long_description, sort_order, created_at, updated_at)
SELECT 'Obra Comercial', 'Locales, oficinas y espacios de alto flujo.', 'Proyectos comerciales con enfoque en operación y tiempos de apertura.', 2, NOW(), NOW()
WHERE (SELECT COUNT(*) FROM services) = 1;

INSERT INTO projects (title, slug, excerpt, description, cover_image_url, created_at, updated_at)
SELECT 'Centro Corporativo Delta', 'centro-corporativo-delta', 'Edificio comercial de 4 niveles con estacionamiento subterráneo.', 'Proyecto integral de diseño y construcción con instalaciones especiales.', 'https://placehold.co/900x600', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM projects);

INSERT INTO project_images (project_id, image_url, sort_order, created_at)
SELECT p.id, 'https://placehold.co/900x600', 1, NOW()
FROM projects p
WHERE p.slug = 'centro-corporativo-delta'
AND NOT EXISTS (SELECT 1 FROM project_images);

INSERT INTO site_settings (`key`, `value`) VALUES
('site_name', 'Construcciones Cuevas'),
('site_tagline', 'Contruccion & Arquitectura'),
('meta_description', 'Construcciones Cuevas: proyectos de construcción y arquitectura con enfoque técnico.'),
('hero_title', 'Construcción inteligente para proyectos que sí se terminan a tiempo'),
('hero_text', 'Planificación, diseño, ejecución y supervisión en un solo equipo.'),
('about_intro', 'Equipo multidisciplinario enfocado en construir con calidad y cumplimiento.'),
('about_text', 'Combinamos arquitectura, ingeniería y administración de obra para entregas confiables.'),
('about_image', 'https://placehold.co/780x520'),
('contact_phone', '+52 000 000 0000'),
('contact_email', 'contacto@construccionescuevas.com'),
('contact_address', 'Av. Principal 123, Zona Centro'),
('contact_image', 'https://placehold.co/760x520')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);
