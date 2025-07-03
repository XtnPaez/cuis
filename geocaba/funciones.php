<?php

//////////////////////////////////////////
// Función para normalizar calle altura //
//////////////////////////////////////////

function normalizar_calle_altura()
{
    include 'config.php';    
    $q1 = "SELECT id, calle_m, altura_m FROM public.direcciones_temp WHERE geo is true";
    $res1 = pg_query($dbconn, $q1) or die('Error: ' . pg_last_error());
    while ($row1 = pg_fetch_array($res1, NULL, PGSQL_ASSOC)) {
        $elaidi = $row1['id'];
        $peticion1 = 'https://ws.usig.buenosaires.gob.ar/rest/normalizar_direcciones?' .
        'calle=' . urlencode($row1['calle_m']) .
        '&altura=' . urlencode($row1['altura_m']) .
        '&desambiguar=1';
        $json1 = @file_get_contents($peticion1);
        if ($json1 === false) {
            // Falla al conectar o error de red
            $qError = "UPDATE public.direcciones_temp SET estado = 98 WHERE id = " . intval($elaidi);
            pg_query($dbconn, $qError);
            continue;
        }
        $json1_output = json_decode($json1);
        if (
            !is_object($json1_output) ||
            !isset($json1_output->TipoResultado) ||
            $json1_output->TipoResultado != 'DireccionNormalizada'
        ) {
            // JSON inválido o resultado inesperado
            $q2 = "UPDATE public.direcciones_temp SET estado = 99 WHERE id = " . intval($elaidi);
            pg_query($dbconn, $q2);
            continue;
        }
        // Si llegó hasta acá, la dirección es válida
        $dir = $json1_output->DireccionesCalleAltura->direcciones[0];
        $cn = str_replace("'", "`", $dir->Calle);
        $q3 = "UPDATE public.direcciones_temp SET
            codigo_calle = " . intval($dir->CodigoCalle) . ",
            calle = '" . $cn . "',
            altura = " . intval($dir->Altura) . ",
            estado = 1
            WHERE id = " . intval($elaidi);
        pg_query($dbconn, $q3);
    }; // while
}; // funcion

/////////////////////////////////////
// Función para traer Datos Útiles //
/////////////////////////////////////

function traer_datos_utiles()
{
    include 'config.php';
    $q4 = "SELECT id, calle, altura FROM public.direcciones_temp WHERE estado = 1";
    $res4 = pg_query($dbconn, $q4);
    while ($row4 = pg_fetch_array($res4, NULL, PGSQL_ASSOC)) {
        $elaidi = intval($row4['id']);
        // URL segura
        $peticion4 = 'https://ws.usig.buenosaires.gob.ar/datos_utiles?' .
            'calle=' . urlencode($row4['calle']) .
            '&altura=' . urlencode($row4['altura']);
        $json4 = @file_get_contents($peticion4);
        // Si no responde
        if ($json4 === false) {
            $qError = "UPDATE public.direcciones_temp SET estado = 98 WHERE id = $elaidi";
            pg_query($dbconn, $qError);
            continue;
        }
        $json4_output = json_decode($json4);
        // Si el JSON no se pudo decodificar
        if (!is_object($json4_output)) {
            $qError = "UPDATE public.direcciones_temp SET estado = 99 WHERE id = $elaidi";
            pg_query($dbconn, $qError);
            continue;
        }
        // Extraer campos, con control y sanitización
        $comuna = isset($json4_output->comuna) ? pg_escape_string($json4_output->comuna) : null;
        $barrio = isset($json4_output->barrio) ? pg_escape_string($json4_output->barrio) : null;
        $comisaria = isset($json4_output->comisaria) ? pg_escape_string($json4_output->comisaria) : null;
        $area_hospitalaria = isset($json4_output->area_hospitalaria) ? pg_escape_string($json4_output->area_hospitalaria) : null;
        $region_sanitaria = isset($json4_output->region_sanitaria) ? pg_escape_string($json4_output->region_sanitaria) : null;
        $distrito_escolar = isset($json4_output->distrito_escolar) ? pg_escape_string($json4_output->distrito_escolar) : null;
        $comisaria_vecinal = isset($json4_output->comisaria_vecinal) ? pg_escape_string($json4_output->comisaria_vecinal) : null;
        $seccion_catastral = isset($json4_output->seccion_catastral) ? pg_escape_string($json4_output->seccion_catastral) : null;
        $codigo_postal = isset($json4_output->codigo_postal) ? intval($json4_output->codigo_postal) : 9999;
        $codigo_postal_argentino = isset($json4_output->codigo_postal_argentino) ? pg_escape_string($json4_output->codigo_postal_argentino) : null;
        $q5 = "
            UPDATE public.direcciones_temp SET
                comuna = " . ($comuna === null ? "NULL" : "'$comuna'") . ",
                barrio = " . ($barrio === null ? "NULL" : "'$barrio'") . ",
                comisaria = " . ($comisaria === null ? "NULL" : "'$comisaria'") . ",
                area_hospitalaria = " . ($area_hospitalaria === null ? "NULL" : "'$area_hospitalaria'") . ",
                region_sanitaria = " . ($region_sanitaria === null ? "NULL" : "'$region_sanitaria'") . ",
                distrito_escolar = " . ($distrito_escolar === null ? "NULL" : "'$distrito_escolar'") . ",
                comisaria_vecinal = " . ($comisaria_vecinal === null ? "NULL" : "'$comisaria_vecinal'") . ",
                seccion_catastral = " . ($seccion_catastral === null ? "NULL" : "'$seccion_catastral'") . ",
                codigo_postal = $codigo_postal,
                codigo_postal_argentino = " . ($codigo_postal_argentino === null ? "NULL" : "'$codigo_postal_argentino'") . ",
                estado = 1
            WHERE id = $elaidi
        ";
        pg_query($dbconn, $q5);
    }
}


//////////////////////////////////////////
// Función para traer datos de catastro //
//////////////////////////////////////////

function traer_datos_de_catastro()
{
    include 'conn.php';
    $q7 = "SELECT id, codigo_calle, altura FROM public.direcciones_temp WHERE estado = 1";
    $res7 = pg_query($dbconn, $q7);
    while($row7 = pg_fetch_array($res7,NULL,PGSQL_ASSOC) )
        {
            $elaidi = $row7['id'];
            // API catastro https://datosabiertos-catastro-apis.buenosaires.gob.ar/catastro/parcela/?codigo_calle=17071&altura=782
            $peticion7 = 'https://datosabiertos-catastro-apis.buenosaires.gob.ar/catastro/parcela/?'
                    . 'codigo_calle=' . $row7['codigo_calle']
                    . '&altura=' . $row7['altura']
                    . '&aprox';
            $json7 = file_get_contents($peticion7, False);
            $json7_output = json_decode($json7);
            if(empty($json7_output))
                {
                    $q9 = "UPDATE public.direcciones_temp SET estado = 99 WHERE id = " . $elaidi;
                    $res9 = pg_query($dbconn,$q9);
                }
                else
                {
                // paso los atributos a variables para que no pinchen los que vienen vacíos
                if (empty($json7_output->smp)){$smp = null;}else{$smp = $json7_output->smp;};
                if (empty($json7_output->seccion)){$seccion = null;}else{$seccion = $json7_output->seccion;};
                if (empty($json7_output->manzana)){$manzana = null;}else{$manzana = $json7_output->manzana;};
                if (empty($json7_output->parcela)){$parcela = null;}else{$parcela = $json7_output->parcela;};
                if (empty($json7_output->superficie_total)){$superficie_total = 'null';}else{$superficie_total = $json7_output->superficie_total;};
                if (empty($json7_output->superficie_cubierta)){$superficie_cubierta = 'null';}else{$superficie_cubierta = $json7_output->superficie_cubierta;};
                if (empty($json7_output->frente)){$frente = 'null';}else{$frente = $json7_output->frente;};
                if (empty($json7_output->fondo)){$fondo = 'null';}else{$fondo = $json7_output->fondo;};
                if (empty($json7_output->propiedad_horizontal)){$propiedad_horizontal = 'null';}else{$propiedad_horizontal = $json7_output->propiedad_horizontal;};
                if (empty($json7_output->pisos_bajo_rasante)){$pisos_bajo_rasante = 'null';}else{$pisos_bajo_rasante = $json7_output->pisos_bajo_rasante;};
                if (empty($json7_output->pisos_sobre_rasante)){$pisos_sobre_rasante = 'null';}else{$pisos_sobre_rasante = $json7_output->pisos_sobre_rasante;};
                    $q8 = "UPDATE sig.mapa.diruni SET 
                    smp = '" . $smp
                    . "', seccion_catastral = '" . $seccion
                    . "', manzana = '" . $manzana
                    . "', parcela = '" . $parcela
                    . "', superficie_total = " . $superficie_total
                    . ", superficie_cubierta = " . $superficie_cubierta
                    . ", frente = " . $frente
                    . ", fondo = " . $fondo
                    . ", propiedad_horizontal = '" . $propiedad_horizontal
                    . "', pisos_bajo_rasante = " . $pisos_bajo_rasante
                    . ", pisos_sobre_rasante = " . $pisos_sobre_rasante
                    . ", estado = 1 WHERE id = " . $elaidi;
                    $res8 = pg_query($dbconn, $q8);        
                }; // if
        }; // while
}; //funcion

//////////////////////////////////////////////////
// Función para traer pares de coordenadas GKBA //
//////////////////////////////////////////////////

function traer_coordenadas_gkba()
{
    include 'config.php';
    $q10 = "SELECT id, codigo_calle, altura FROM public.direcciones_temp WHERE estado = 1";
    $res10 = pg_query($dbconn, $q10);
    while($row10 = pg_fetch_array($res10,NULL,PGSQL_ASSOC) )
        {
            $elaidi = $row10['id'];
            // API usig https://ws.usig.buenosaires.gob.ar/geocoder/2.2/geocoding?cod_calle=17071&altura=782&metodo=puertas
            $peticion10 = 'https://ws.usig.buenosaires.gob.ar/geocoder/2.2/geocoding?'
                    . 'cod_calle=' . $row10['codigo_calle']
                    . '&altura=' . $row10['altura']
                    . '&metodo=puertas';
            // como devuelve un json mal armado, lo masajeo
            $json10 = file_get_contents($peticion10, true);
            $sin1 = str_replace("(","",$json10);
            $sin2 = str_replace(")","",$sin1);
            $json10_output = json_decode($sin2);
            if(empty($json10_output))
                {
                    $q11= "UPDATE public.direcciones_temp SET estado = 99 WHERE id = " . $elaidi;
                    $res11 = pg_query($dbconn, $q11);
                }
                else
                {
                    $q12 = "UPDATE public.direcciones_temp SET 
                    x_gkba = " . $json10_output->x
                    . ", y_gkba = " . $json10_output->y
                    . ", estado = 1 WHERE id = " . $elaidi;
                    $res12 = pg_query($dbconn, $q12);        
                }; // if
        }; // while
}; //funcion

///////////////////////////////////////////////////
// Función para traer pares de coordenadas WGS84 //
///////////////////////////////////////////////////

function traer_coordenadas_wgs84()
{
    include 'config.php';
    $q13 = "SELECT id, x_gkba, y_gkba FROM public.direcciones_temp WHERE estado = 1";
    $res13 = pg_query($dbconn, $q13);
    while($row13 = pg_fetch_array($res13,NULL,PGSQL_ASSOC) )
        {
            $elaidi = $row13['id'];
            // API usig https://ws.usig.buenosaires.gob.ar/rest/convertir_coordenadas?x=108150.992445&y=101357.282955&output=lonlat
            $peticion13 = 'https://ws.usig.buenosaires.gob.ar/rest/convertir_coordenadas?'
                    . 'x=' . $row13['x_gkba']
                    . '&y=' . $row13['y_gkba']
                    . '&output=lonlat';
            $json13 = file_get_contents($peticion13, False);
            $json13_output = json_decode($json13);
            if(empty($json13_output))
                {
                    $q14= "UPDATE public.direcciones_temp SET estado = 99 WHERE id = " . $elaidi;
                    $res14 = pg_query($dbconn,$q14);
                }
                else
                {
                    $q15 = "UPDATE public.direcciones_temp SET 
                    x_wgs84 = " . $json13_output->resultado->x
                    . ", y_wgs84 = " . $json13_output->resultado->y
                    . ", estado = 1 WHERE id = " . $elaidi;
                    $res15 = pg_query($dbconn, $q15);        
                }; // if
        }; // while
}; //funcion

/////////////////////////////////
// Función para crear los geom //
/////////////////////////////////

function crear_geom()
{
    include 'config.php';
    // hago el geom con postgis
    $q20 = 'UPDATE public.dirapi set geomwgs84 = ST_SetSRID(ST_MakePoint(api_x_wgs84, api_y_wgs84),4326) where api_x_wgs84 is not null and api_y_wgs84 is not null';
    $res20 = pg_query($dbconn, $q20);
    $q21 = 'UPDATE public.dirapi set geomgkba = ST_SetSRID(ST_MakePoint(api_x_gkba, api_y_gkba),100006) where api_x_gkba is not null and api_y_gkba is not null';
    $res21 = pg_query($dbconn, $q21);
}


///////////////////////////////////////
// Función para traer Datos Útiles 2 //
///////////////////////////////////////

function traer_datos_utiles_2()
{
    include 'config.php';
    $q44 = "SELECT id, api_calle, api_altura FROM public.dirapi where sh_numero SIMILAR TO '[0-9]+' AND estado IS NULL";
    $res44 = pg_query($dbconn, $q44);
    while($row44 = pg_fetch_array($res44,NULL,PGSQL_ASSOC) )
        {
            $elaidi = $row44['id'];
            // API Datos Utiles https://ws.usig.buenosaires.gob.ar/datos_utiles?calle=peru&altura=782
            $peticion44 = str_replace(" ","%20",'https://ws.usig.buenosaires.gob.ar/datos_utiles?'
                    . 'calle=' . $row44['api_calle'] 
                    . '&altura=' . $row44['api_altura']);
            $json44 = file_get_contents($peticion44, true);
            $json44_output = json_decode($json44);
            if(empty($json44_output))
            {
                $q46 = "UPDATE public.dirapi SET estado = 'API no trajo datos utiles' WHERE id = " . $elaidi;
                $res46 = pg_query($dbconn,$q46);
            }
            else
            {
                // paso los atributos a variables para que no pinchen los que vienen vacíos - Si son integer, ponerle 9999 y no null
                if (empty($json44_output->comisaria_vecinal)){$comisaria_vecinal = null;}else{$comisaria_vecinal = $json44_output->comisaria_vecinal;};
                if (empty($json44_output->seccion_catastral)){$seccion_catastral = null;}else{$seccion_catastral = $json44_output->seccion_catastral;};
                if (empty($json44_output->distrito_economico)){$distrito_economico = null;}else{$distrito_economico = $json44_output->distrito_economico;};
                if (empty($json44_output->codigo_de_planeamiento_urbano)){$codigo_de_planeamiento_urbano = null;}else{$codigo_de_planeamiento_urbano = $json44_output->codigo_de_planeamiento_urbano;};
                $q45 = "UPDATE public.dirapi SET
                api_comisaria_vecinal = '" . $comisaria_vecinal
                . "', api_seccion_catastral = '" . $seccion_catastral
                . "', api_distrito_economico = '" . $distrito_economico
                . "', api_codigo_de_planeamiento_urbano = '" . $codigo_de_planeamiento_urbano
                . "', estado = null "
                . "WHERE id = " . $elaidi;
                $res45 = pg_query($dbconn, $q45);
            }; // if
        }; // while
}; // funcion

/////////////////////////////////////////////////////////////////
// Función para traer pares de coordenadas WGS84 para los cuis //
/////////////////////////////////////////////////////////////////

function wgs84_para_cuis()
{
    include 'config.php';
    $q13 = "SELECT id, x_gkba, y_gkba FROM cuis.edificios";
    $res13 = pg_query($dbconn, $q13);
    while($row13 = pg_fetch_array($res13,NULL,PGSQL_ASSOC) )
        {
            $elaidi = $row13['id'];
            // API usig https://ws.usig.buenosaires.gob.ar/rest/convertir_coordenadas?x=108150.992445&y=101357.282955&output=lonlat
            $peticion13 = 'https://ws.usig.buenosaires.gob.ar/rest/convertir_coordenadas?'
                    . 'x=' . $row13['x_gkba']
                    . '&y=' . $row13['y_gkba']
                    . '&output=lonlat';
            $json13 = file_get_contents($peticion13, False);
            $json13_output = json_decode($json13);
            if(empty($json13_output))
                {
                    echo "error en " . $elaidi . "<br><br>";
                }
                else
                {
                    $q15 = "UPDATE cuis.edificios SET 
                    x_wgs84 = " . $json13_output->resultado->x
                    . ", y_wgs84 = " . $json13_output->resultado->y
                    . " WHERE id = " . $elaidi;
                    $res15 = pg_query($dbconn, $q15);        
                }; // if
        }; // while
}; //funcion

function crear_geom_para_cuis()
{
    include 'config.php';
    // hago el geom con postgis
    $q20 = 'UPDATE cuis.edificios set geom_wgs84 = ST_SetSRID(ST_MakePoint(x_wgs84, y_wgs84),4326) where x_wgs84 is not null and y_wgs84 is not null';
    $res20 = pg_query($dbconn, $q20);
}


?>