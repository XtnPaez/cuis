<?php

//////////////////////////////////////////
// Función para normalizar calle altura //
//////////////////////////////////////////

function normalizar_calle_altura()
{
    include 'config.php';    
    $q1 = "SELECT id, sh_calle, sh_numero FROM public.dirapi WHERE sh_numero SIMILAR TO '[0-9]+' AND estado IS NULL";
    $res1 = pg_query($dbconn, $q1) or die('Error: ' . pg_last_error());
    while($row1 = pg_fetch_array($res1,NULL,PGSQL_ASSOC) )
        {
            $elaidi = $row1['id'];
            // API Procesos Geográficos https://ws.usig.buenosaires.gob.ar/rest/normalizar_direcciones?calle=julio%20roca&altura=782&desambiguar=1    
            $peticion1 = str_replace(" ","%20",'https://ws.usig.buenosaires.gob.ar/rest/normalizar_direcciones?'
                    . 'calle=' . $row1['sh_calle']
                    . '&altura=' . $row1['sh_numero']
                    . '&desambiguar=1');
            $json1 = file_get_contents($peticion1, true);
            $json1_output = json_decode($json1);
            // si el json en TipoResultado trae algo distinto a DireccionNormalizada (error) pongo estado en 1 y salgo del if
            if($json1_output->TipoResultado != 'DireccionNormalizada')
                {
                    $q2 = "UPDATE public.dirapi SET estado = 'API no encontró la direccion' WHERE id = " . $elaidi;
                    $res2 = pg_query($dbconn,$q2);                
                }
                else
                {
                    $cn = str_replace("'","`",$json1_output->DireccionesCalleAltura->direcciones[0]->Calle);
                    $q3 = "UPDATE public.dirapi SET
                    api_codigo_calle = " . $json1_output->DireccionesCalleAltura->direcciones[0]->CodigoCalle 
                    . ", api_calle = '" . $cn
                    . "', api_altura = " . $json1_output->DireccionesCalleAltura->direcciones[0]->Altura 
                    . ", estado = null " 
                    . " WHERE id = " . $elaidi;
                    $res3 = pg_query($dbconn,$q3);
                }; // if
        }; // while
}; // funcion

/////////////////////////////////////
// Función para traer Datos Útiles //
/////////////////////////////////////

function traer_datos_utiles()
{
    include 'config.php';
    $q4 = "SELECT id, api_calle, api_altura FROM public.dirapi where sh_numero SIMILAR TO '[0-9]+' AND estado IS NULL";
    $res4 = pg_query($dbconn, $q4);
    while($row4 = pg_fetch_array($res4,NULL,PGSQL_ASSOC) )
        {
            $elaidi = $row4['id'];
            // API Datos Utiles https://ws.usig.buenosaires.gob.ar/datos_utiles?calle=peru&altura=782
            $peticion4 = str_replace(" ","%20",'https://ws.usig.buenosaires.gob.ar/datos_utiles?'
                    . 'calle=' . $row4['api_calle'] 
                    . '&altura=' . $row4['api_altura']);
            $json4 = file_get_contents($peticion4, true);
            $json4_output = json_decode($json4);
            if(empty($json4_output))
            {
                $q6 = "UPDATE public.dirapi SET estado = 'API no trajo datos utiles' WHERE id = " . $elaidi;
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
                $q5 = "UPDATE public.dirapi SET
                api_comuna = '" . $comuna
                . "', api_barrio = '" . $barrio
                . "', api_comisaria = '" . $comisaria
                . "', api_area_hospitalaria = '" . $area_hospitalaria
                . "', api_region_sanitaria = '" . $region_sanitaria
                . "', api_distrito_escolar = '" . $distrito_escolar
                . "', api_codigo_postal = '" . $codigo_postal
                . "', api_codigo_postal_argentino = '" . $codigo_postal_argentino
                . "', estado = null "
                . "WHERE id = " . $elaidi;
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
    include 'config.php';
    $q10 = "SELECT id, api_codigo_calle, api_altura FROM public.dirapi WHERE api_codigo_calle IS NOT NULL";
    $res10 = pg_query($dbconn, $q10);
    while($row10 = pg_fetch_array($res10,NULL,PGSQL_ASSOC) )
        {
            $elaidi = $row10['id'];
            // API usig https://ws.usig.buenosaires.gob.ar/geocoder/2.2/geocoding?cod_calle=17071&altura=782&metodo=puertas
            $peticion10 = 'https://ws.usig.buenosaires.gob.ar/geocoder/2.2/geocoding?'
                    . 'cod_calle=' . $row10['api_codigo_calle']
                    . '&altura=' . $row10['api_altura']
                    . '&metodo=puertas';
            // como devuelve un json mal armado, lo masajeo
            $json10 = file_get_contents($peticion10, true);
            $sin1 = str_replace("(","",$json10);
            $sin2 = str_replace(")","",$sin1);
            $json10_output = json_decode($sin2);
            if(empty($json10_output))
                {
                    $q11= "UPDATE public.dirapi SET estado = 'API no trajo coordenadas GKBA' WHERE id = " . $elaidi;
                    $res11 = pg_query($dbconn, $q11);
                }
                else
                {
                    $q12 = "UPDATE public.dirapi SET 
                    api_x_gkba = " . $json10_output->x
                    . ", api_y_gkba = " . $json10_output->y
                    . ", estado = null WHERE id = " . $elaidi;
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
    $q13 = "SELECT id, api_x_gkba, api_y_gkba FROM public.dirapi WHERE api_x_gkba is not null";
    $res13 = pg_query($dbconn, $q13);
    while($row13 = pg_fetch_array($res13,NULL,PGSQL_ASSOC) )
        {
            $elaidi = $row13['id'];
            // API usig https://ws.usig.buenosaires.gob.ar/rest/convertir_coordenadas?x=108150.992445&y=101357.282955&output=lonlat
            $peticion13 = 'https://ws.usig.buenosaires.gob.ar/rest/convertir_coordenadas?'
                    . 'x=' . $row13['api_x_gkba']
                    . '&y=' . $row13['api_y_gkba']
                    . '&output=lonlat';
            $json13 = file_get_contents($peticion13, False);
            $json13_output = json_decode($json13);
            if(empty($json13_output))
                {
                    $q14= "UPDATE public.dirapi SET estado = 'API no trajo wgs84' WHERE id = " . $elaidi;
                    $res14 = pg_query($dbconn,$q14);
                }
                else
                {
                    $q15 = "UPDATE public.dirapi SET 
                    api_x_wgs84 = " . $json13_output->resultado->x
                    . ", api_y_wgs84 = " . $json13_output->resultado->y
                    . ", estado = null WHERE id = " . $elaidi;
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