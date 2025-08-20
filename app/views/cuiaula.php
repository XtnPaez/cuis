<?php
  // chequeo inicio de sesión
  session_start();
  // traigo la conexion
  require_once('../config/config.php'); 
?>
<!doctype html>
<html lang="es">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CUIS : CUIaula</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/sticky.css" rel="stylesheet">
    <link rel="apple-touch-icon" href="../images/apple-icon-180x180.png" sizes="180x180">
    <link rel="icon" href="../images/favicon-32x32.png" sizes="32x32" type="image/png">
    <link rel="icon" href="../images/favicon-16x16.png" sizes="16x16" type="image/png">
    <link rel="icon" href="../images/favicon.ico">
  </head>
  <body class="d-flex flex-column min-vh-100">
    <!-- Traigo navbar -->
    <?php include('../includes/navbar.php'); ?>
    <main class="container mt-5 pt-5 flex-grow-1">
        <h2 class="text-center mb-4">Gestión de aulas</h2>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <!-- Tarjeta 1: Generar QRs -->
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Generar QR de aulas</h5>
                        <p class="card-text">Se generan los QR para todas las aulas y se almacena el archivo en una carpeta del sistema.</p>
                        <a href="#" class="btn btn-primary mt-auto w-100">Generar QRs</a>
                    </div>
                </div>
            </div>
            <!-- Tarjeta 2: Listar QRs -->
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Listar QR de aulas</h5>
                        <p class="card-text">Se genera la lista de todos los QR con la referencia CUI -> aula.</p>
                        <a href="#" class="btn btn-primary mt-auto w-100">Listar QRs</a>
                    </div>
                </div>
            </div>
            <!-- Tarjeta 3: Buscar QR por CUI -->
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Buscar QR por CUI</h5>
                        <p class="card-text">Muestra el formulario y trae los resultados a esta página.</p>
                        <a href="#" class="btn btn-primary mt-auto w-100">Buscar QR por CUI</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Pendientes -->
        <div class="mt-3 p-3 border border-warning rounded bg-light">
            <h6 class="text-warning">Pendientes:</h6>
            <ul class="mb-0">
                <li>Crear la vista de la relación CUI -> aula</li>
                <li>Funcionalidad de las cards</li>
                <li>Generar QR debe generar solo para aquellos que no existan aun. Un flag en la tabla. Muestra un modal con el OK.</li>
                <li>Listar debe ser aqui mismo, paginado.</li>
                <li>Buscar QR por CUI</li>
<p>
agregar en postgres con esta wuery <br><br>

-- FOREIGN TABLE: mapa_fdw.locales_madre

-- DROP FOREIGN TABLE IF EXISTS mapa_fdw.locales_madre;

CREATE FOREIGN TABLE IF NOT EXISTS mapa_fdw.locales_tipo(
    idlocal integer OPTIONS (column_name 'idlocal') NOT NULL,
    tipo_local integer OPTIONS (column_name 'tipo_local') NOT NULL,
    categoria_local character varying OPTIONS (column_name 'categoria_local'),
	id_categoria integer OPTIONS (column_name 'id_categoria') NOT NULL,
	id_tipo integer OPTIONS (column_name 'id_tipo') NOT NULL
)
    SERVER mapa_server
    OPTIONS (schema_name 'data_locales', table_name 'locales_tipo');

ALTER FOREIGN TABLE mapa_fdw.locales_tipo
    OWNER TO postgres;
<br><br>
las tablas que faltan para esta query

<br><br>
select lom.cui, cat.categoria_local, tip.tipo_local, lom.local, ldg.construccion, ldg.planta
from mapa_fwd.data_locales.locales_tipo lot 
left join mapa_fwd.entidades_madre.locales_madre lom on lom.idlocal = lot.idlocal
left join mapa_fwd.data_locales.locales_tipo_codigo_tipo tip on tip.id_tipo = lot.id_tipo
left join mapa_fwd.data_locales.locales_tipo_codigo_categoria cat on cat.id_categoria = lot.id_categoria
left join mapa_fwd.data_locales.locales_datos_generales ldg on lot.idlocal = ldg.idlocal
where lot.id_tipo in (29, 30, 31, 32)
order by lom.cui asc, lom.local asc

</p>
            </ul>
        </div>      
        <!-- termina pendientes -->
    </main>
    <!-- Traigo footer -->
    <?php include('../includes/footer.php'); ?>
    <script src="../js/bootstrap.bundle.min.js"></script>
  </body>
</html>