<?php
// =====================================================
// BACKEND PHP - SISTEMA DE CATÁLOGO DE DESCARGAS
// =====================================================

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json; charset=utf-8');

// Configuración de base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'tu_base_datos');
define('DB_USER', 'tu_usuario');
define('DB_PASS', 'tu_password');

// Configuración de directorios
define('ARCHIVOS_DIR', __DIR__ . '/descargas/');

// Configuración de límites
define('QUERY_TIMEOUT', 30);
define('MAX_ROWS', 5000);

class CatalogoDescargas {
    private $pdo;
    
    public function __construct() {
        try {
            $this->pdo = new PDO(
                "pgsql:host=" . DB_HOST . ";dbname=" . DB_NAME,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_TIMEOUT => QUERY_TIMEOUT
                ]
            );
        } catch (PDOException $e) {
            $this->logError(null, "Error de conexión: " . $e->getMessage());
            $this->sendError(500, "Error interno del servidor");
        }
    }
    
    // =====================================================
    // ENDPOINT: /catalogo/listar
    // =====================================================
    public function listar() {
        if (!$this->usuarioAutorizado()) {
            $this->sendError(403, "No tenés permiso para acceder a Descargas");
        }
        
        try {
            $stmt = $this->pdo->prepare("SELECT get_catalogo_completo() as catalogo");
            $stmt->execute();
            $result = $stmt->fetch();
            
            $catalogo = json_decode($result['catalogo'], true);
            
            header('Content-Type: application/json');
            echo json_encode($catalogo, JSON_UNESCAPED_UNICODE);
            
        } catch (PDOException $e) {
            $this->logError(null, "Error obteniendo catálogo: " . $e->getMessage());
            $this->sendError(500, "No se pudo cargar el catálogo");
        }
    }
    
    // =====================================================
    // ENDPOINT: /catalogo/accion
    // =====================================================
    public function accion() {
        if (!$this->usuarioAutorizado()) {
            $this->sendError(403, "No tenés permiso para acceder a Descargas");
        }
        
        $id = $_GET['id'] ?? null;
        $accion = $_GET['accion'] ?? null;
        $formato = $_GET['formato'] ?? null;
        
        if (!$id || !$accion || !$formato) {
            $this->sendError(400, "Parámetros inválidos");
        }
        
        try {
            // Validar que la acción existe y está disponible
            $stmt = $this->pdo->prepare("SELECT * FROM validar_accion(?, ?, ?)");
            $stmt->execute([$id, $accion, $formato]);
            $validacion = $stmt->fetch();
            
            if (!$validacion['es_valida']) {
                $this->sendError(404, "Acción no disponible");
            }
            
            // Si hay archivo pregenerado, servirlo directamente
            if ($validacion['uri_archivo']) {
                $this->servirArchivo($validacion['uri_archivo'], $validacion['content_type']);
                return;
            }
            
            // Verificar que la VM existe antes de ejecutar la query
            $vm_name = $this->extraerNombreVM($validacion['sql_query']);
            if ($vm_name && !$this->vmExists($vm_name)) {
                $this->logError(null, "Vista materializada no encontrada: " . $vm_name, $this->getUsuario());
                $this->sendError(500, "Recurso no disponible, contactá al administrador");
            }
            
            // Generar archivo según el formato
            switch ($formato) {
                case 'csv':
                    $this->generarCSV($validacion['sql_query'], $id);
                    break;
                case 'xlsx':
                    $this->generarXLSX($validacion['sql_query'], $id);
                    break;
                case 'geojson':
                    $this->generarGeoJSON($validacion['sql_query'], $id);
                    break;
                case 'preview':
                    $this->generarPreview($validacion['sql_query']);
                    break;
                default:
                    $this->sendError(400, "Formato no soportado");
            }
            
        } catch (PDOException $e) {
            $this->logError($id, "Error ejecutando acción: " . $e->getMessage(), $this->getUsuario());
            $this->sendError(500, "No se pudo generar el archivo, intentá de nuevo");
        }
    }
    
    // =====================================================
    // GENERADORES DE ARCHIVOS
    // =====================================================
    
    private function generarCSV($sql_query, $catalogo_id) {
        $datos = $this->ejecutarQuery($sql_query);
        
        if (empty($datos)) {
            $this->sendError(500, "No hay datos disponibles");
        }
        
        $filename = "padron_cui_" . date('Y-m-d') . ".csv";
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, must-revalidate');
        
        $output = fopen('php://output', 'w');
        
        // BOM para UTF-8 (para Excel)
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Headers
        fputcsv($output, array_keys($datos[0]), ';');
        
        // Datos
        foreach ($datos as $row) {
            fputcsv($output, $row, ';');
        }
        
        fclose($output);
    }
    
    private function generarXLSX($sql_query, $catalogo_id) {
        require_once 'vendor/autoload.php'; // PhpSpreadsheet
        
        $datos = $this->ejecutarQuery($sql_query);
        
        if (empty($datos)) {
            $this->sendError(500, "No hay datos disponibles");
        }
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Padrón CUI');
        
        // Headers
        $headers = array_keys($datos[0]);
        $sheet->fromArray([$headers], NULL, 'A1');
        
        // Datos
        $sheet->fromArray($datos, NULL, 'A2');
        
        // Formatear headers
        $headerRange = 'A1:' . $sheet->getHighestColumn() . '1';
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E0E0E0');
        
        // Auto-size columnas
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $filename = "padron_cui_" . date('Y-m-d') . ".xlsx";
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, must-revalidate');
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
    }
    
    private function generarGeoJSON($sql_query, $catalogo_id) {
        // Query modificada para generar GeoJSON con PostGIS
        $geojson_query = str_replace(
            'SELECT *',
            'SELECT cui, estado, sector, gestionado, predio, direccion_principal, comuna, barrio, codigo_postal, ' .
            'ST_AsGeoJSON(ST_SetSRID(ST_MakePoint(x_wgs84, y_wgs84), 4326))::json as geometry',
            $sql_query
        );
        
        $datos = $this->ejecutarQuery($geojson_query);
        
        if (empty($datos)) {
            $this->sendError(500, "No hay datos disponibles");
        }
        
        $features = [];
        
        foreach ($datos as $row) {
            $geometry = $row['geometry'];
            unset($row['geometry']); // Remover geometry de properties
            
            $features[] = [
                'type' => 'Feature',
                'properties' => $row,
                'geometry' => $geometry
            ];
        }
        
        $geojson = [
            'type' => 'FeatureCollection',
            'features' => $features
        ];
        
        $filename = "padron_cui_" . date('Y-m-d') . ".geojson";
        
        header('Content-Type: application/geo+json; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, must-revalidate');
        
        echo json_encode($geojson, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
    
    private function generarPreview($sql_query) {
        $preview_query = $sql_query . " LIMIT 50";
        $datos = $this->ejecutarQuery($preview_query);
        
        if (empty($datos)) {
            echo json_encode(['columnas' => [], 'filas' => []], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        $preview = [
            'columnas' => array_keys($datos[0]),
            'filas' => $datos
        ];
        
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($preview, JSON_UNESCAPED_UNICODE);
    }
    
    // =====================================================
    // FUNCIONES DE UTILIDAD
    // =====================================================
    
    private function ejecutarQuery($sql) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Error en consulta: " . $e->getMessage());
        }
    }
    
    private function servirArchivo($uri_archivo, $content_type) {
        $filepath = ARCHIVOS_DIR . ltrim($uri_archivo, '/');
        
        if (!file_exists($filepath)) {
            $this->sendError(404, "Archivo no encontrado");
        }
        
        $filename = basename($filepath);
        
        header('Content-Type: ' . $content_type);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        header('Cache-Control: no-cache, must-revalidate');
        
        readfile($filepath);
    }
    
    private function vmExists($vm_name) {
        try {
            $stmt = $this->pdo->prepare("SELECT vm_exists(?) as exists");
            $stmt->execute([$vm_name]);
            $result = $stmt->fetch();
            return $result['exists'];
        } catch (PDOException $e) {
            return false;
        }
    }
    
    private function extraerNombreVM($sql_query) {
        if (preg_match('/FROM\s+(vm_padron_cui_\d{8})/i', $sql_query, $matches)) {
            return $matches[1];
        }
        return null;
    }
    
    private function usuarioAutorizado() {
        // Implementar lógica de autorización según tu sistema de login
        // Por ejemplo, verificar sesión, JWT token, etc.
        
        session_start();
        return isset($_SESSION['usuario_id']); // Ejemplo básico
    }
    
    private function getUsuario() {
        session_start();
        return $_SESSION['usuario_nombre'] ?? 'anonimo';
    }
    
    private function logError($id_query, $error_message, $usuario = null) {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO error_log (id_query, error_message, usuario, contexto) VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([
                $id_query,
                $error_message,
                $usuario,
                $_SERVER['REQUEST_URI'] ?? 'Backend PHP'
            ]);
        } catch (PDOException $e) {
            // Si no se puede loguear, al menos escribir en error_log de PHP
            error_log("CatalogoDescargas Error: " . $error_message);
        }
    }
    
    private function sendError($code, $message) {
        http_response_code($code);
        echo json_encode(['error' => $message], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// =====================================================
// ROUTER SIMPLE
// =====================================================

$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);
$path = rtrim($path, '/');

$catalogo = new CatalogoDescargas();

switch ($path) {
    case '/catalogo/listar':
        $catalogo->listar();
        break;
        
    case '/catalogo/accion':
        $catalogo->accion();
        break;
        
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint no encontrado'], JSON_UNESCAPED_UNICODE);
        break;
}
?>