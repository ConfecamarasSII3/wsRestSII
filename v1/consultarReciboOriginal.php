<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait consultarRecibo {

    public function consultarRecibo(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/s3_v4_api.php');
        $resError = set_error_handler('myErrorHandler');

        if (TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'EFS_S3') {
            require_once ('../../../librerias/funciones/s3_v4.php');
        }

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION['jsonsalida']['recibo'] = '';
        $_SESSION['jsonsalida']['fecha'] = '';
        $_SESSION['jsonsalida']['hora'] = '';
        $_SESSION['jsonsalida']['operacion'] = '';
        $_SESSION['jsonsalida']['factura'] = '';
        $_SESSION['jsonsalida']['radicado'] = '';
        $_SESSION['jsonsalida']['rutasii'] = '';
        $_SESSION['jsonsalida']['usuario'] = '';
        $_SESSION['jsonsalida']['tipogasto'] = '';
        $_SESSION['jsonsalida']['idclase'] = '';
        $_SESSION['jsonsalida']['identificacion'] = '';
        $_SESSION['jsonsalida']['nombre'] = '';
        $_SESSION['jsonsalida']['direccion'] = '';
        $_SESSION['jsonsalida']['municipio'] = '';
        $_SESSION['jsonsalida']['telefono'] = '';
        $_SESSION['jsonsalida']['email'] = '';
        $_SESSION['jsonsalida']['tipotramite'] = '';
        $_SESSION['jsonsalida']['valorneto'] = '';
        $_SESSION['jsonsalida']['tipodoc'] = '';
        $_SESSION['jsonsalida']['numerodoc'] = '';
        $_SESSION['jsonsalida']['origendoc'] = '';
        $_SESSION['jsonsalida']['fechadoc'] = '';
        $_SESSION['jsonsalida']['municipiodoc'] = '';
        $_SESSION['jsonsalida']['numerointernorue'] = '';
        $_SESSION['jsonsalida']['numerounicorue'] = '';

        $servicios = array();
        $_SESSION['jsonsalida']['servicios'] = $servicios;

        $imagenes = array();
        $_SESSION['jsonsalida']['imagenes'] = $imagenes;
        
        $certificados = array();
        $_SESSION["jsonsalida"]["certificados"] = $certificados;


        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("recibo", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('consultarRecibo', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

       $mysqli = conexionMysqliApi();
        
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexión a BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        // ********************************************************************** //
        // Construye arreglo de tipos documentales
        // ********************************************************************** // 
        $trd = array();
        $query = "SELECT * from bas_tipodoc where 1=1 order by idtipodoc";
        $mysqli->set_charset("utf8");
        $resQueryTD = $mysqli->query($query);
        if (!empty($resQueryTD)) {
            while ($trdtemp = $resQueryTD->fetch_array(MYSQL_ASSOC)) {
                $trd[$trdtemp['idtipodoc']] = array(
                    'tiposirep' => $trdtemp['homologasirep'],
                    'tipodigitalizacion' => $trdtemp['homologadigitalizacion']
                );
            }
        }
        $resQueryTD->free();

        // ********************************************************************** //
        // Consulta el recibo
        // ********************************************************************** // 
        $queryRecibosSII = "SELECT r.recibo as recibo,r.fecha as fecha,r.hora as hora,r.operacion as operacion,"
                . "r.factura as factura,r.codigobarras as radicado,cb.actoreparto as rutasii,r.usuario as usuario,"
                . "r.tipogasto as tipogasto,r.tipoidentificacion as idclase,r.identificacion as identificacion,"
                . "r.razonsocial as nombre,r.direccion as direccion,r.municipio as municipio,r.telefono1 as telefono,"
                . "r.email as email,r.tipotramite as tipotramite,r.valorneto as valorneto,cb.tipdoc as tipodoc,cb.numdoc as numerodoc,"
                . "cb.oridoc as origendoc,cb.fecdoc as fechadoc,cb.mundoc as municipiodoc,rue.numerointernorue as numerointernorue,"
                . "rue.numuidrue as numerounicorue,r.estado as estado "
                . "FROM mreg_recibosgenerados r "
                . "LEFT JOIN mreg_est_codigosbarras cb on r.codigobarras=cb.codigobarras "
                . "LEFT JOIN mreg_rue_radicacion rue on r.codigobarras=rue.codigobarras "
                . "WHERE r.recibo='" . $_SESSION["entrada"]["recibo"] . "' LIMIT 1";

        $mysqli->set_charset("utf8");
        $resQueryRecibosSII = $mysqli->query($queryRecibosSII);

        if (!empty($resQueryRecibosSII)) {
            while ($reciboTemp = $resQueryRecibosSII->fetch_array(MYSQL_ASSOC)) {

                // ********************************************************************** //
                // Retornar el recibo recuperado
                // ********************************************************************** // 

                $_SESSION['jsonsalida']['recibo'] = trim((string)$reciboTemp['recibo']);
                $_SESSION['jsonsalida']['fecha'] = trim((string)$reciboTemp['fecha']);
                $_SESSION['jsonsalida']['hora'] = trim((string)$reciboTemp['hora']);
                $_SESSION['jsonsalida']['operacion'] = trim((string)$reciboTemp['operacion']);
                $_SESSION['jsonsalida']['factura'] = trim((string)$reciboTemp['factura']);
                $_SESSION['jsonsalida']['radicado'] = trim((string)$reciboTemp['radicado']);
                $_SESSION['jsonsalida']['usuario'] = trim((string)$reciboTemp['usuario']);
                $_SESSION['jsonsalida']['tipogasto'] = trim((string)$reciboTemp['tipogasto']);
                $_SESSION['jsonsalida']['idclase'] = trim((string)$reciboTemp['idclase']);
                $_SESSION['jsonsalida']['identificacion'] = trim((string)$reciboTemp['identificacion']);
                $_SESSION['jsonsalida']['nombre'] = trim((string)$reciboTemp['nombre']);
                $_SESSION['jsonsalida']['direccion'] = trim((string)$reciboTemp['direccion']);
                $_SESSION['jsonsalida']['municipio'] = trim((string)$reciboTemp['municipio']);
                $_SESSION['jsonsalida']['telefono'] = trim((string)$reciboTemp['telefono']);
                $_SESSION['jsonsalida']['email'] = trim((string)$reciboTemp['email']);
                $_SESSION['jsonsalida']['tipotramite'] = trim((string)$reciboTemp['tipotramite']);
                $_SESSION['jsonsalida']['valorneto'] = doubleval($reciboTemp['valorneto']);
                if (trim($reciboTemp['radicado'] != "")) {
                    $_SESSION['jsonsalida']['rutasii'] = trim((string)$reciboTemp['rutasii']);
                    $_SESSION['jsonsalida']['tipodoc'] = trim((string)$reciboTemp['tipodoc']);
                    $_SESSION['jsonsalida']['numerodoc'] = trim((string)$reciboTemp['numerodoc']);
                    $_SESSION['jsonsalida']['origendoc'] = trim((string)$reciboTemp['origendoc']);
                    $_SESSION['jsonsalida']['fechadoc'] = trim((string)$reciboTemp['fechadoc']);
                    $_SESSION['jsonsalida']['municipiodoc'] = trim((string)$reciboTemp['municipiodoc']);
                    $_SESSION['jsonsalida']['numerointernorue'] = trim((string)$reciboTemp['numerointernorue']);
                    $_SESSION['jsonsalida']['numerounicorue'] = trim((string)$reciboTemp['numerounicorue']);
                } else {
                    $_SESSION['jsonsalida']['tipodoc'] = "";
                    $_SESSION['jsonsalida']['numerodoc'] = "";
                    $_SESSION['jsonsalida']['origendoc'] = "";
                    $_SESSION['jsonsalida']['fechadoc'] = "";
                    $_SESSION['jsonsalida']['municipiodoc'] = "";
                    $_SESSION['jsonsalida']['numerointernorue'] = "";
                    $_SESSION['jsonsalida']['numerounicorue'] = "";
                }

                //
                $resy = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion', "numerorecibo='" . $_SESSION["entrada"]["recibo"] . "'");

                //
                $_SESSION['jsonsalida']['cumplorequisitosbenley1780'] = '';
                $_SESSION['jsonsalida']['mantengorequisitosbenley1780'] = '';
                $_SESSION['jsonsalida']['renunciobeneficiosley1780'] = '';
                $_SESSION['jsonsalida']['multadoponal'] = '';
                $_SESSION['jsonsalida']['controlactividadaltoimpacto'] = '';

                //
                if ($resy && !empty($resy)) {
                    $_SESSION['jsonsalida']['cumplorequisitosbenley1780'] = $resy["cumplorequisitosbenley1780"];
                    $_SESSION['jsonsalida']['mantengorequisitosbenley1780'] = $resy["mantengorequisitosbenley1780"];
                    $_SESSION['jsonsalida']['renunciobeneficiosley1780'] = $resy["renunciobeneficiosley1780"];
                    $_SESSION['jsonsalida']['multadoponal'] = $resy["multadoponal"];
                    $_SESSION['jsonsalida']['controlactividadaltoimpacto'] = $resy["controlactividadaltoimpacto"];
                }
            }
            $resQueryRecibosSII->free();
        } else {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Recibo no localizado en la BD.';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        unset($resQueryRecibosSII);

        // ********************************************************************** //
        // Retornar Servicios asociados al recibo
        // ********************************************************************** // 
        $queryRecibosSincronizados = "SELECT r.idservicio as servicio,s.nombre as nservicio,r.matricula as matricula,"
                . "r.proponente as proponente,r.identificacion identificacion,r.razonsocial as nombre,"
                . "r.cantidad as cantidad,r.valorbase as valorbase,r.valorservicio as valorservicio,r.ano as ano "
                . "FROM mreg_recibosgenerados_detalle r "
                . "LEFT JOIN mreg_servicios s on r.idservicio=s.idservicio "
                . "WHERE r.recibo='" . $_SESSION["entrada"]["recibo"] . "'";

        $resQueryRecibosSincronizados = $mysqli->query($queryRecibosSincronizados);

        if (!empty($resQueryRecibosSincronizados)) {
            while ($servicioT = $resQueryRecibosSincronizados->fetch_array(MYSQL_ASSOC)) {
                $servicio = array();
                $servicio['servicio'] = trim((string)$servicioT['servicio']);
                $servicio['nservicio'] = trim((string)$servicioT['nservicio']);
                $servicio['matricula'] = trim((string)$servicioT['matricula']);
                $servicio['proponente'] = trim((string)$servicioT['proponente']);
                $servicio['identificacion'] = trim((string)$servicioT['identificacion']);
                $servicio['nombre'] = trim((string)$servicioT['nombre']);
                $servicio['cantidad'] = doubleval($servicioT['cantidad']);
                $servicio['valorbase'] = doubleval($servicioT['valorbase']);
                $servicio['valorservicio'] = doubleval($servicioT['valorservicio']);
                $servicio['ano'] = trim((string)$servicioT['ano']);
                $servicios[] = $servicio;
            }
            $_SESSION['jsonsalida']['servicios'] = $servicios;
        }

        // ********************************************************************** //
        // Retornar imágenes formularios (503), recibo (509) y notificación sipref (518-519) del recibo
        // ********************************************************************** // 
        $queryImagenes = "SELECT * FROM mreg_radicacionesanexos WHERE eliminado<>'SI' and numerorecibo='" . $_SESSION['jsonsalida']['recibo'] . "' and tipoanexo IN('503','509','518','519')";

        $resQueryImagenes = $mysqli->query($queryImagenes);

        if (!empty($resQueryImagenes)) {
            while ($imagent = $resQueryImagenes->fetch_array(MYSQL_ASSOC)) {
                $tiposirep = '';
                $tipodigitalizacion = '';
                if (isset($trd[$imagent["idtipodoc"]])) {
                    $tiposirep = $trd[$imagent["idtipodoc"]]["tiposirep"];
                    $tipodigitalizacion = $trd[$imagent["idtipodoc"]]["tipodigitalizacion"];
                }
                $imagen = array();
                //$imagen['url'] = TIPO_HTTP . HTTP_HOST . "/" . PATH_RELATIVO_IMAGES . $_SESSION["entrada"]["codigoempresa"] . "/" . $imagent['path'];
                if (TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'EFS_S3') {
                    $imagen['url'] = obtenerUrlRepositorioS3Api($imagent['path']);
                } else {
                    $imagen['url'] = TIPO_HTTP . HTTP_HOST . "/" . PATH_RELATIVO_IMAGES . "/" . $_SESSION["entrada"]["codigoempresa"] . "/" . $imagent['path'];
                }

                $imagen['idanexo'] = ($imagent['idanexo']);
                $imagen['tipo'] = trim((string)$imagent['idtipodoc']);

                //WSIERRA : 2018-11-22  - Incluye campo tipoanexo
                $imagen['tipoanexo'] = trim((string)$imagent['tipoanexo']);

                $imagen['tiposirep'] = $tiposirep;
                $imagen['tipodigitalizacion'] = $tipodigitalizacion;
                $imagen['identificador'] = trim((string)$imagent['identificador']);
                $strings = explode(".", $imagent['path']);
                $imagen['formato'] = $strings[count($strings) - 1];
                $imagen['identificacion'] = trim((string)$imagent['identificacion']);
                $imagen['nombre'] = trim((string)$imagent['nombre']);
                $imagen['matricula'] = trim((string)$imagent['matricula']);
                $imagen['proponente'] = trim((string)$imagent['proponente']);
                $imagen['fechadocumento'] = trim((string)$imagent['fechadoc']);
                $imagen['origen'] = trim((string)$imagent['txtorigendoc']);
                $imagen['observaciones'] = trim((string)$imagent['observaciones']);
                $imagenes[] = $imagen;
            }
            $resQueryImagenes->free();

            $_SESSION['jsonsalida']['imagenes'] = $imagenes;
        }

        // **************************************************************************** //
        // 2018-08-27: JINT: Se adiciona búsqueda de certificados relacionados.
        // **************************************************************************** //
        $certs = retornarRegistrosMysqliApi($mysqli,'mreg_certificados_virtuales', "recibo='" . $_SESSION["jsonsalida"]["recibo"] . "'", "id");
        if ($certs && !empty($certs)) {
            foreach ($certs as $cx) {
                $cert1 = array();
                $cert1["codigoverificacion"] = $cx["id"];
                $cert1["tipocertificado"] = $cx["tipocertificado"];
                $cert1["path"] = TIPO_HTTP . HTTP_HOST . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $cx["path"];
                $_SESSION["jsonsalida"]["certificados"][] = $cert1;
            }
        }
        unset($certs);

        //
        $mysqli->close();
        $resQueryRecibosSincronizados->free();
        unset($resQueryRecibosSincronizados);

        //
        $mysqli->close();
       
        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}
