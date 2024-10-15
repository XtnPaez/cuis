<?php

//////////////////////////////////////////
// Función para normalizar calle altura //
//////////////////////////////////////////

function normalizar_calle_altura()
{
    include 'conn.php';    
    $q1 = "SELECT idlistado, calle, altura FROM sig.public.direcciones_norm WHERE geo = true";
    $res1 = pg_query($dbconn, $q1) or die('Error: ' . pg_last_error());
    while($row1 = pg_fetch_array($res1,NULL,PGSQL_ASSOC) )
        {
            $elaidi = $row1['idlistado'];
            // API Procesos Geográficos https://ws.usig.buenosaires.gob.ar/rest/normalizar_direcciones?calle=julio%20roca&altura=782&desambiguar=1    
            $peticion1 = str_replace(" ","%20",'https://ws.usig.buenosaires.gob.ar/rest/normalizar_direcciones?'
                    . 'calle=' . $row1['calle']
                    . '&altura=' . $row1['altura']
                    . '&desambiguar=1');
            $json1 = file_get_contents($peticion1, true);
            $json1_output = json_decode($json1);
            // si el json en TipoResultado trae algo distinto a DireccionNormalizada (error) pongo estado en 1 y salgo del if
            if($json1_output->TipoResultado != 'DireccionNormalizada')
                {
                    $q2 = "UPDATE sig.public.direcciones_norm SET estado = 1 WHERE idlistado = " . $elaidi;
                    $res2 = pg_query($dbconn,$q2);                
                }
                else
                {
                    $cn = str_replace("'","`",$json1_output->DireccionesCalleAltura->direcciones[0]->Calle);
                    $q3 = "UPDATE sig.public.direcciones_norm SET
                    cod_calle = " . $json1_output->DireccionesCalleAltura->direcciones[0]->CodigoCalle 
                    . ", calle_norm = '" . $cn
                    . "', altura_norm = " . $json1_output->DireccionesCalleAltura->direcciones[0]->Altura 
                    . ", estado = 2 " 
                    . " WHERE idlistado = " . $elaidi;
                    $res3 = pg_query($dbconn,$q3);
                }; // if
        }; // while
}; // funcion

/////////////////////////////////////
// Función para traer Datos Útiles //
/////////////////////////////////////

function traer_datos_utiles()
{
    include 'conn.php';
    $q4 = "SELECT idlistado, calle_norm, altura_norm FROM sig.public.direcciones_norm where estado = 2";
    $res4 = pg_query($dbconn, $q4);
    while($row4 = pg_fetch_array($res4,NULL,PGSQL_ASSOC) )
        {
            $elaidi = $row4['idlistado'];
            // API Datos Utiles https://ws.usig.buenosaires.gob.ar/datos_utiles?calle=peru&altura=782
            $peticion4 = str_replace(" ","%20",'https://ws.usig.buenosaires.gob.ar/datos_utiles?'
                    . 'calle=' . $row4['calle_norm'] 
                    . '&altura=' . $row4['altura_norm']);
            $json4 = file_get_contents($peticion4, true);
            $json4_output = json_decode($json4);
            if(empty($json4_output))
            {
                $q6 = "UPDATE sig.public.direcciones_norm SET estado = 3 WHERE idlistado = " . $elaidi;
                $res6 = pg_query($dbconn,$q6);
            }
            else
            {
                // paso los atributos a variables para que no pinchen los que vienen vacíos - Si son integer, ponerle 9999 y no null
                if (empty($json4_output->comuna)){$comuna = null;}else{$comuna = $json4_output->comuna;};
                if (empty($json4_output->barrio)){$barrio = null;}else{$barrio = $json4_output->barrio;};
                if (empty($json4_output->comisaria)){$comisaria = null;}else{$comisaria = $json4_output->comisaria;};
                if (empty($json4_output->area_hospitalaria)){$area_hospitalaria = null;}else{$area_hospitalaria = $json4_output->area_hospitalaria;};
                if (empty($json4_output->region_sanitaria)){$region_sanitaria = null;}else{$region_sanitaria = $json4_output->region_sanitaria;};
                if (empty($json4_output->distrito_escolar)){$distrito_escolar = null;}else{$distrito_escolar = $json4_output->distrito_escolar;};
                if (empty($json4_output->codigo_postal)){$codigo_postal = 9999;}else{$codigo_postal = $json4_output->codigo_postal;};
                if (empty($json4_output->codigo_postal_argentino)){$codigo_postal_argentino = null;}else{$codigo_postal_argentino = $json4_output->codigo_postal_argentino;};
                $q5 = "UPDATE sig.public.direcciones_norm SET
                comuna_norm = '" . $comuna
                . "', barrio_norm = '" . $barrio
                . "', comisaria_norm = '" . $comisaria
                . "', areah_norm = '" . $area_hospitalaria
                . "', regions_norm = '" . $region_sanitaria
                . "', de_norm = '" . $distrito_escolar
                . "', codpos = '" . $codigo_postal
                . "', codposar = '" . $codigo_postal_argentino
                . "', estado = 4 "
                . "WHERE idlistado = " . $elaidi;
                $res5 = pg_query($dbconn, $q5);
            }; // if
        }; // while
}; // funcion

//////////////////////////////////////////
// Función para traer datos de catastro //
//////////////////////////////////////////

function traer_datos_de_catastro()
{
    include 'conn.php';
    $q7 = "SELECT id, codigo_calle, altura_norm FROM sig.mapa.diruni WHERE codigo_calle is not null and parcela is null";
    $res7 = pg_query($dbconn, $q7);
    while($row7 = pg_fetch_array($res7,NULL,PGSQL_ASSOC) )
        {
            $elaidi = $row7['id'];
            // API catastro https://datosabiertos-catastro-apis.buenosaires.gob.ar/catastro/parcela/?codigo_calle=17071&altura=782
            $peticion7 = 'https://datosabiertos-catastro-apis.buenosaires.gob.ar/catastro/parcela/?'
                    . 'codigo_calle=' . $row7['codigo_calle']
                    . '&altura=' . $row7['altura_norm']
                    . '&aprox';
            $json7 = file_get_contents($peticion7, False);
            $json7_output = json_decode($json7);
            if(empty($json7_output))
                {
                    $q9 = "UPDATE sig.mapa.diruni SET estado = 5 WHERE id = " . $elaidi;
                    $res9 = pg_query($dbconn,$q9);
                }
                else
                {
                    // paso los atributos a variables para que no pinchen los que vienen vacíos
                if (empty($json7_output->smp)){$smp = null;}else{$smp = $json7_output->smp;};
                if (empty($json7_output->seccion)){$seccion = null;}else{$seccion = $json7_output->seccion;};
                if (empty($json7_output->manzana)){$manzana = null;}else{$manzana = $json7_output->manzana;};
                if (empty($json7_output->parcela)){$parcela = null;}else{$parcela = $json7_output->parcela;};
                // if (empty($json7_output->superficie_total)){$superficie_total = 'null';}else{$superficie_total = $json7_output->superficie_total;};
                // if (empty($json7_output->superficie_cubierta)){$superficie_cubierta = 'null';}else{$superficie_cubierta = $json7_output->superficie_cubierta;};
                // if (empty($json7_output->frente)){$frente = 'null';}else{$frente = $json7_output->frente;};
                // if (empty($json7_output->fondo)){$fondo = 'null';}else{$fondo = $json7_output->fondo;};
                // if (empty($json7_output->pisos_bajo_rasante)){$pisos_bajo_rasante = 'null';}else{$pisos_bajo_rasante = $json7_output->pisos_bajo_rasante;};
                // if (empty($json7_output->pisos_sobre_rasante)){$pisos_sobre_rasante = 'null';}else{$pisos_sobre_rasante = $json7_output->pisos_sobre_rasante;};
                // if (empty($json7_output->fuente)){$fuente = null;}else{$fuente = $json7_output->fuente;};
                //if (empty($json7_output->cantidad_puertas)){$cantidad_puertas = 'null';}else{$cantidad_puertas = $json7_output->cantidad_puertas;};
                    $q8 = "UPDATE sig.mapa.diruni SET 
                    smp = '" . $smp
                    . "', seccion_catastral = '" . $seccion
                    . "', manzana = '" . $manzana
                    . "', parcela = '" . $parcela
                    // . "', superficie_total = " . $superficie_total
                    // . ", superficie_cubierta = " . $superficie_cubierta
                    // . ", frente = " . $frente
                    // . ", fondo = " . $fondo
                    // . ", pisos_bajo_rasante = " . $pisos_bajo_rasante
                    // . ", pisos_sobre_rasante = " . $pisos_sobre_rasante
                    // . ", fuente_catastro = '" . $fuente
                    // . "', cantidad_puertas = " . $cantidad_puertas
                    . "', estado = 6 WHERE id = " . $elaidi;
                    $res8 = pg_query($dbconn, $q8);        
                }; // if
        }; // while
}; //funcion

//////////////////////////////////////////////////
// Función para traer pares de coordenadas GKBA //
//////////////////////////////////////////////////

function traer_coordenadas_gkba()
{
    include 'conn.php';
    $q10 = "SELECT idlistado, cod_calle, altura_norm FROM sig.public.direcciones_norm WHERE cod_calle is not null";
    $res10 = pg_query($dbconn, $q10);
    while($row10 = pg_fetch_array($res10,NULL,PGSQL_ASSOC) )
        {
            $elaidi = $row10['idlistado'];
            // API usig https://ws.usig.buenosaires.gob.ar/geocoder/2.2/geocoding?cod_calle=17071&altura=782&metodo=puertas
            $peticion10 = 'https://ws.usig.buenosaires.gob.ar/geocoder/2.2/geocoding?'
                    . 'cod_calle=' . $row10['cod_calle']
                    . '&altura=' . $row10['altura_norm']
                    . '&metodo=puertas';
            // como devuelve un json mal armado, lo masajeo
            $json10 = file_get_contents($peticion10, true);
            $sin1 = str_replace("(","",$json10);
            $sin2 = str_replace(")","",$sin1);
            $json10_output = json_decode($sin2);
            if(empty($json10_output))
                {
                    $q11= "UPDATE sig.public.direcciones_norm SET estado = 7 WHERE idlistado = " . $elaidi;
                    $res11 = pg_query($dbconn, $q11);
                }
                else
                {
                    $q12 = "UPDATE sig.public.direcciones_norm SET 
                    x = " . $json10_output->x
                    . ", y = " . $json10_output->y
                    . ", estado = 8 WHERE idlistado = " . $elaidi;
                    $res12 = pg_query($dbconn, $q12);        
                }; // if
        }; // while
}; //funcion

///////////////////////////////////////////////////
// Función para traer pares de coordenadas WGS84 //
///////////////////////////////////////////////////

function traer_coordenadas_wgs84()
{
    include 'conn.php';
    $q13 = "SELECT id, x_gkba, y_gkba FROM sig.mapa.diruni WHERE x_gkba is not null";
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
                    $q14= "UPDATE sig.mapa.diruni SET estado = 9 WHERE id = " . $elaidi;
                    $res14 = pg_query($dbconn,$q14);
                }
                else
                {
                    $q15 = "UPDATE sig.mapa.diruni SET 
                    x_wgs84 = " . $json13_output->resultado->x
                    . ", y_wgs84 = " . $json13_output->resultado->y
                    . ", estado = 10 WHERE id = " . $elaidi;
                    $res15 = pg_query($dbconn, $q15);        
                }; // if
        }; // while
}; //funcion

////////////////////////////////////////
// Función para crear el geom en 4326 //
////////////////////////////////////////

function crear_geom4326()
{
    include 'conn.php';
    // hago el geom con postgis
    $q20 = 'UPDATE sig.mapa.diruni set geom4326 = ST_SetSRID(ST_MakePoint(x_wgs84, y_wgs84),4326) where x_wgs84 is not null and y_wgs84 is not null';
    $res20 = pg_query($dbconn, $q20);
}

?>