<?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    global $config;
    $db = $config['db'];

    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $db['host'],
        (int) $db['port'],
        $db['name'],
        $db['charset']
    );

    $pdo = new PDO($dsn, $db['user'], $db['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false, // Force real prepared statements
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ]);

    ensure_database_schema($pdo);

    return $pdo;
}

function ensure_database_schema(PDO $pdo): void
{
    static $initialized = false;

    if ($initialized) {
        return;
    }

    $requiredTables = [
        'users',
        'site_settings',
        'services',
        'projects',
        'project_images',
        'quote_requests',
    ];

    $placeholders = implode(',', array_fill(0, count($requiredTables), '?'));
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name IN ($placeholders)"
    );
    $stmt->execute($requiredTables);
    $existingCount = (int) $stmt->fetchColumn();

    if ($existingCount !== count($requiredTables)) {
        run_schema_sql($pdo, __DIR__ . '/../database.sql');
    }

    run_schema_migrations($pdo);
    $initialized = true;
}

function run_schema_sql(PDO $pdo, string $sqlFile): void
{
    if (!is_file($sqlFile)) {
        throw new RuntimeException("No se encontró el archivo de esquema: $sqlFile");
    }

    $sql = file_get_contents($sqlFile);
    if ($sql === false || trim($sql) === '') {
        throw new RuntimeException("El archivo de esquema está vacío o no se pudo leer: $sqlFile");
    }

    $statements = split_sql_statements($sql);
    if ($statements === []) {
        throw new RuntimeException("No se encontraron sentencias SQL válidas en: $sqlFile");
    }

    $pdo->beginTransaction();
    try {
        foreach ($statements as $statement) {
            $pdo->exec($statement);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw new RuntimeException('No se pudo inicializar la base de datos automáticamente: ' . $e->getMessage(), 0, $e);
    }
}

function split_sql_statements(string $sql): array
{
    $lines = preg_split('/\R/', $sql) ?: [];
    $filteredLines = [];

    foreach ($lines as $line) {
        $trimmed = ltrim($line);
        if (strpos($trimmed, '--') === 0) {
            continue;
        }
        $filteredLines[] = $line;
    }

    $cleanSql = trim(implode("\n", $filteredLines));
    if ($cleanSql === '') {
        return [];
    }

    $parts = preg_split('/;\s*(?:\R|$)/', $cleanSql) ?: [];
    $statements = [];

    foreach ($parts as $part) {
        $statement = trim($part);
        if ($statement !== '') {
            $statements[] = $statement;
        }
    }

    return $statements;
}

function run_schema_migrations(PDO $pdo): void
{
    ensure_users_migration($pdo);
    ensure_services_image_migration($pdo);
    ensure_sliders_table_migration($pdo);
    ensure_values_table_migration($pdo);
    ensure_goals_table_migration($pdo);
    ensure_service_images_table_migration($pdo);
    ensure_project_images_migration($pdo);
    ensure_page_sections_migration($pdo);
    
    // Ensure email in quote_requests is nullable
    $pdo->exec("ALTER TABLE quote_requests MODIFY email VARCHAR(180) NULL");
}

function ensure_page_sections_migration(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS page_sections (
        id INT AUTO_INCREMENT PRIMARY KEY,
        section_key VARCHAR(100) NOT NULL UNIQUE,
        title VARCHAR(100) NOT NULL,
        sort_order INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1
    )");

    // Initial seed if empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM page_sections");
    if ((int)$stmt->fetchColumn() === 0) {
        $sections = [
            ['hero', 'Carrusel Principal', 1],
            ['services', 'Servicios Destacados', 2],
            ['cta_simple', 'Llamado a la Acción (Cotizar)', 3],
            ['about', 'Quiénes Somos', 4],
            ['objective', 'Nuestro Objetivo', 5],
            ['values', 'Valores / Por qué elegirnos', 6],
            ['projects', 'Proyectos Recientes', 7],
        ];
        $insert = $pdo->prepare("INSERT INTO page_sections (section_key, title, sort_order) VALUES (?, ?, ?)");
        foreach ($sections as $s) {
            $insert->execute($s);
        }
    }

    // Ensure cta_simple exists in existing databases
    $check = $pdo->prepare("SELECT COUNT(*) FROM page_sections WHERE section_key = 'cta_simple'");
    $check->execute();
    if ((int)$check->fetchColumn() === 0) {
        $pdo->exec("INSERT INTO page_sections (section_key, title, sort_order) VALUES ('cta_simple', 'Llamado a la Acción (Cotizar)', 3)");
        $pdo->exec("UPDATE page_sections SET sort_order = sort_order + 1 WHERE section_key != 'cta_simple' AND sort_order >= 3");
    }
}

function ensure_service_images_table_migration(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS service_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        service_id INT NOT NULL DEFAULT 0,
        session_id VARCHAR(100) NULL,
        image_url TEXT NOT NULL,
        sort_order INT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Add session_id if it doesn't exist
    $columnStmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'service_images' AND column_name = 'session_id'");
    $columnStmt->execute();
    if ((int)$columnStmt->fetchColumn() === 0) {
        $pdo->exec("ALTER TABLE service_images ADD COLUMN session_id VARCHAR(100) NULL AFTER service_id");
    }
}

function ensure_project_images_migration(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS project_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        project_id INT NOT NULL DEFAULT 0,
        session_id VARCHAR(100) NULL,
        image_url TEXT NOT NULL,
        sort_order INT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $columnStmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'project_images' AND column_name = 'session_id'");
    $columnStmt->execute();
    if ((int)$columnStmt->fetchColumn() === 0) {
        $pdo->exec("ALTER TABLE project_images ADD COLUMN session_id VARCHAR(100) NULL AFTER project_id");
    }
}

function ensure_goals_table_migration(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS site_goals (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT NULL,
        sort_order INT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
}

function ensure_values_table_migration(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS site_values (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT NULL,
        icon VARCHAR(100) DEFAULT 'fas fa-gem',
        sort_order INT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
}

function ensure_users_migration(PDO $pdo): void
{
    $columnStmt = $pdo->prepare(
        "SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'users' AND column_name = 'must_change_credentials'"
    );
    $columnStmt->execute();
    $hasColumn = (int) $columnStmt->fetchColumn() > 0;

    if (!$hasColumn) {
        $pdo->exec("ALTER TABLE users ADD COLUMN must_change_credentials TINYINT(1) NOT NULL DEFAULT 0 AFTER password_hash");
    }

    $defaultHash = '$2b$10$GVHY2Zx7try8eH8qxjN6HO9QUhoERBfqiWDzzsHNPYOJyACDD462S';
    $updateStmt = $pdo->prepare(
        "UPDATE users
         SET must_change_credentials = 1
         WHERE email = 'admin@cuevas.local'
         AND password_hash = :default_hash"
    );
    $updateStmt->execute(['default_hash' => $defaultHash]);
}

function ensure_services_image_migration(PDO $pdo): void
{
    $columnStmt = $pdo->prepare(
        "SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'services' AND column_name = 'image_url'"
    );
    $columnStmt->execute();
    $hasColumn = (int) $columnStmt->fetchColumn() > 0;

    if (!$hasColumn) {
        $pdo->exec("ALTER TABLE services ADD COLUMN image_url TEXT NULL AFTER long_description");
    }
}

function ensure_sliders_table_migration(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS sliders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NULL,
        text TEXT NULL,
        image_url TEXT NOT NULL,
        position VARCHAR(50) DEFAULT 'center-center',
        image_position VARCHAR(50) DEFAULT 'center-center',
        sort_order INT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    // Ensure columns exist if table was already created
    $columns = [
        'position' => "ALTER TABLE sliders ADD COLUMN position VARCHAR(50) DEFAULT 'center-center' AFTER image_url",
        'image_position' => "ALTER TABLE sliders ADD COLUMN image_position VARCHAR(50) DEFAULT 'center-center' AFTER position"
    ];

    foreach ($columns as $name => $sql) {
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'sliders' AND column_name = :col"
        );
        $stmt->execute(['col' => $name]);
        if ((int) $stmt->fetchColumn() === 0) {
            $pdo->exec($sql);
        }
    }
}
