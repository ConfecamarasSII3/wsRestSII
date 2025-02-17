<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait consultarRelacionRecibos {

    public function consultarRelacionRecibos(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesS3V4.php');
        $resError = set_error_handler('myErrorHandler');

        //cantidad de registros
        $limit = 100;

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $arrRecibos = array();
        $_SESSION["jsonsalida"]["recibos"] = $arrRecibos;
        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("fechainicial", true);
        $api->validarParametro("fechafinal", true);
        $api->validarParametro("horainicial", false);
        $api->validarParametro("horafinal", false);
        $api->validarParametro("operador", false);

        if (!(\funcionesGenerales::validarFecha($_SESSION["entrada"]["fechainicial"]))) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'El parámetro fechainicial no es una fecha válida';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        if (!(\funcionesGenerales::validarFecha($_SESSION["entrada"]["fechafinal"]))) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'El parámetro fechafinal no es una fecha válida';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if ($_SESSION["entrada"]["fechainicial"] > $_SESSION["entrada"]["fechafinal"]) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'fechafinal debe ser mayor a la  fechainicial';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        if (\funcionesGenerales::diferenciaEntreFechasCalendario($_SESSION["entrada"]["fechainicial"], $_SESSION["entrada"]["fechafinal"]) > 31) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La diferencia en días entre la fechainicial y la fechafinal no debe ser mayor a 11';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida si son definidos rangos de horas de consulta
        // ********************************************************************** // 
        $_SESSION["entrada"]["horainicial"] = sprintf("%06s", $_SESSION["entrada"]["horainicial"]);
        $_SESSION["entrada"]["horafinal"] = sprintf("%06s", $_SESSION["entrada"]["horafinal"]);
        if ($_SESSION["entrada"]["horafinal"] == '000000') {
            $_SESSION["entrada"]["horafinal"] = '235959';
        }
        $horas = substr(sprintf("%06s", $_SESSION["entrada"]["horainicial"]), 0, 2);
        if ($horas <= '24' && $horas >= '00') {
            $horaInicial = $_SESSION["entrada"]["horainicial"];
        } else {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'horainicial debe ser entre 00 y 24 horas';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        $horas = substr(sprintf("%06s", $_SESSION["entrada"]["horafinal"]), 0, 2);
        if ($horas <= '24' && $horas >= '00') {
            $horaFinal = $_SESSION["entrada"]["horafinal"];
        } else {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'horafinal debe ser entre 00 y 24 horas';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('consultarRelacionRecibos', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        // ********************************************************************** //
        // Buscar recibos
        // ********************************************************************** // 
        $mysqli = conexionMysqliApi();
        
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexión a BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        
        $trd = array();
        $trdtemps = retornarRegistrosMysqliApi($mysqli, 'bas_tipodoc', "1=1", "idtipodoc");
        foreach ($trdtemps as $trdtemp) {
            $trd[$trdtemp['idtipodoc']] = array(
                'tiposirep' => $trdtemp['homologasirep'],
                'tipodigitalizacion' => $trdtemp['homologadigitalizacion']
            );
        }

        if ($_SESSION["entrada"]["fechainicial"] == $_SESSION["entrada"]["fechafinal"]) {
            $query = "SELECT r.recibo as recibo, r.operacion as operacion, r.codigobarras as radicado, r.idliquidacion as idliquidacion, cb.actoreparto as rutasii, "
                    . "r.fecha as fecha, r.hora as hora, r.usuario as usuario, r.tipotramite as tipotramite,"
                    . "r.tipoidentificacion as idclase, r.identificacion as identificacion,r.razonsocial as nombre,"
                    . "r.valorneto as valor, r.tipogasto as tipogasto, r.estado as estado, r.idformapago as formapago "
                    . "FROM mreg_recibosgenerados r LEFT JOIN mreg_est_codigosbarras cb on r.codigobarras=cb.codigobarras "
                    . "WHERE r.fecha ='" . $_SESSION["entrada"]["fechafinal"] . "' and ("
                    . "r.hora between '" . $_SESSION["entrada"]["horainicial"] . "' and '" . $_SESSION["entrada"]["horafinal"] . "')";
        }

        if ($_SESSION["entrada"]["fechainicial"] != $_SESSION["entrada"]["fechafinal"]) {
            $query = "SELECT r.recibo as recibo, r.operacion as operacion, r.codigobarras as radicado, r.idliquidacion as idliquidacion, cb.actoreparto as rutasii, "
                    . "r.fecha as fecha, r.hora as hora, r.usuario as usuario, r.tipotramite as tipotramite,"
                    . "r.tipoidentificacion as idclase, r.identificacion as identificacion,r.razonsocial as nombre,"
                    . "r.valorneto as valor, r.tipogasto as tipogasto, r.estado as estado, r.idformapago as formapago "
                    . "FROM mreg_recibosgenerados r LEFT JOIN mreg_est_codigosbarras cb on r.codigobarras=cb.codigobarras "
                    . "WHERE r.fecha between '" . $_SESSION["entrada"]["fechainicial"] . "' and '" . $_SESSION["entrada"]["fechafinal"] . "'";
        }

        if (trim($_SESSION["entrada"]["operador"]) != '') {
            $query .= " and r.usuario = '" . $_SESSION["entrada"]["operador"] . "'";
        }

        $mysqli->set_charset("utf8");
        $resQueryReciboss = ejecutarQueryMysqliApi($mysqli,$query);

        if (!empty($resQueryReciboss)) {
            foreach ($resQueryReciboss as $reciboTemp) {
                //
                $liqs = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion_campos', "idliquidacion=" . $reciboTemp['idliquidacion'] . " and campo='subtipotramite'", "contenido");
                
                //
                $matBase = '';
                $proBase = '';
                $recibo = array();
                $recibo['recibo'] = $reciboTemp['recibo'];
                $recibo['operacion'] = $reciboTemp['operacion'];
                $recibo['radicado'] = $reciboTemp['radicado'];
                if (isset($reciboTemp['rutasii']) && $reciboTemp['rutasii'] != null) {
                    $recibo['rutasii'] = $reciboTemp['rutasii'];
                } else {
                    $recibo['rutasii'] = "";
                }
                $recibo['fecha'] = $reciboTemp['fecha'];
                $recibo['hora'] = $reciboTemp['hora'];
                $recibo['usuario'] = $reciboTemp['usuario'];
                $recibo['idliquidacion'] = $reciboTemp['idliquidacion'];
                $recibo['tipotramite'] = $reciboTemp['tipotramite'];
                $recibo['subtipotramite'] = $liqs;
                $recibo['idclase'] = $reciboTemp['idclase'];
                $recibo['identificacion'] = $reciboTemp['identificacion'];
                $recibo['nombre'] = $reciboTemp['nombre'];
                $recibo['valor'] = doubleval($reciboTemp['valor']);
                $recibo['tipogasto'] = $reciboTemp['tipogasto'];
                $recibo['estado'] = $reciboTemp['estado'];
                $recibo['formapago'] = $reciboTemp['formapago'];
                $recibo['tipocertificadoespecial'] = '';
                $recibo['tipocertificadoespecialdescripcion'] = '';
                $recibo['estadocertificadoespecial'] = '';
                $recibo['estadocertificadoespecialdescripcion'] = '';
                if ($reciboTemp["tipotramite"] == 'certificadosespeciales') {
                    $temx1 = retornarRegistrosMysqliApi($mysqli, 'mreg_certificados_especiales', "recibo='" . $reciboTemp['recibo'] . "'", "id");
                    if ($temx1 && !empty($temx1)) {
                        foreach ($temx1 as $t1) {
                            $recibo['tipocertificadoespecial'] = $t1["tipocertificado"];
                            $recibo['tipocertificadoespecialdescripcion'] = retornarRegistroMysqliApi($mysqli, 'mreg_tipos_certificados_especiales', "id='" . $t1["tipocertificado"] . "'", "descripcion");
                            $recibo['estadocertificadoespecial'] = $t1["idestado"];
                            switch ($t1["idestado"]) {
                                case "1" : $recibo['estadocertificadoespecialdescripcion'] = 'PENDIENTE';
                                    break;
                                case "2" : $recibo['estadocertificadoespecialdescripcion'] = 'EN PROCESO';
                                    break;
                                case "3" : $recibo['estadocertificadoespecialdescripcion'] = 'TERMINADO';
                                    break;
                                case "4" : $recibo['estadocertificadoespecialdescripcion'] = 'INFORMADO POR EMAIL';
                                    break;
                                case "5" : $recibo['estadocertificadoespecialdescripcion'] = 'ENTREGADO PRESENCIAL';
                                    break;
                                case "9" : $recibo['estadocertificadoespecialdescripcion'] = 'ANULADO';
                                    break;
                            }
                        }
                    }
                    unset($temx1);
                }

                $servicios = array();
                // ********************************************************************** //
                // Retornar servicios del recibo
                // ********************************************************************** //                 

                $queryServicios = "SELECT r.idservicio as servicio,s.nombre as nservicio,r.matricula as matricula,"
                        . "r.proponente as proponente,r.identificacion identificacion,r.razonsocial as nombre,"
                        . "r.cantidad as cantidad,r.valorbase as valorbase,r.valorservicio as valorservicio,r.ano as ano "
                        . "FROM mreg_recibosgenerados_detalle r "
                        . "LEFT JOIN mreg_servicios s on r.idservicio=s.idservicio "
                        . "WHERE r.recibo='" . $reciboTemp['recibo'] . "'";
                $resQueryServicioss = ejecutarQueryMysqliApi($mysqli,$queryServicios);
                if (!empty($resQueryServicioss)) {
                    foreach ($resQueryServicioss as $servent) {
                        $servicio = array();
                        $servicio['servicio'] = $servent['servicio'];
                        $servicio['nservicio'] = $servent['nservicio'];
                        $servicio['matricula'] = $servent['matricula'];
                        $servicio['proponente'] = $servent['proponente'];
                        $servicio['identificacion'] = $servent['identificacion'];
                        $servicio['nombre'] = $servent['nombre'];
                        $servicio['cantidad'] = $servent['cantidad'];
                        $servicio['valorbase'] = $servent['valorbase'];
                        $servicio['valorservicio'] = $servent['valorservicio'];
                        $servicio['ano'] = $servent['ano'];
                        $servicios[] = $servicio;
                        if ($matBase == '') {
                            $matBase = $servicio['matricula'];
                        }
                        if ($proBase == '') {
                            $proBase = $servicio['proponente'];
                        }
                    }
                }
                $recibo['servicios'] = $servicios;

                //
                $imagenes = array();
                // ********************************************************************** //
                // Retornar imágenes del radicado
                // ********************************************************************** //                 
                $queryImagenes = "SELECT an.idanexo as idanexo,an.idtipodoc as tipo, an.identificador as identificador,an.identificacion as identificacion,an.nombre as nombre,"
                        . "an.matricula as matricula,an.proponente as proponente,an.fechadoc as fechadocumento,an.txtorigendoc as origen,an.observaciones as observaciones,an.path as url "
                        . "FROM mreg_radicacionesanexos an "
                        . "WHERE an.eliminado!='SI' and an.numerorecibo='" . $reciboTemp['recibo'] . "'";

                $resQueryImagenes = ejecutarQueryMysqliApi($mysqli,$queryImagenes);
                if (!empty($resQueryImagenes)) {
                    foreach ($resQueryImagenes as $imagent) {
                        $tiposirep = '';
                        $tipodigitalizacion = '';
                        if (isset($trd[$imagent["tipo"]])) {
                            $tiposirep = $trd[$imagent["tipo"]]["tiposirep"];
                            $tipodigitalizacion = $trd[$imagent["tipo"]]["tipodigitalizacion"];
                        }
                        $imagen = array();
                        if (TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'EFS_S3') {
                            $imagen['url'] = \funcionesS3V4::obtenerUrlRepositorioS3($imagent['url']);
                        } else {
                            $imagen['url'] = TIPO_HTTP . HTTP_HOST . "/" . PATH_RELATIVO_IMAGES . "/" . $_SESSION["entrada"]["codigoempresa"] . "/" . $imagent['url'];
                        }

                        $imagen['idanexo'] = doubleval($imagent['idanexo']);
                        $imagen['tipo'] = trim((string)$imagent['tipo']);
                        $imagen['tiposirep'] = $tiposirep;
                        $imagen['tipodigitalizacion'] = $tipodigitalizacion;
                        $imagen['identificador'] = trim((string)$imagent['identificador']);
                        $strings = explode(".", $imagent['url']);
                        $imagen['formato'] = $strings[count($strings) - 1];
                        $imagen['identificacion'] = trim((string)$imagent['identificacion']);
                        $imagen['nombre'] = trim((string)$imagent['nombre']);
                        $imagen['matricula'] = trim((string)$imagent['matricula']);
                        $imagen['proponente'] = trim((string)$imagent['proponente']);
                        $imagen['fechadocumento'] = trim($imagent['fechadocumento']);
                        $imagen['origen'] = trim($imagent['origen']);
                        $imagen['observaciones'] = trim((string)$imagent['observaciones']);
                        $imagenes[] = $imagen;
                    }
                }

                //
                $imagenes1 = array();
                // ********************************************************************** //
                // Retornar imágenes de certificados
                // ********************************************************************** //                 
                $queryImagenes = "SELECT * from  mreg_certificados_virtuales where recibo='" . $reciboTemp['recibo'] . "'";
                $resQueryImagenes = ejecutarQueryMysqliApi($mysqli,$queryImagenes);
                if (!empty($resQueryImagenes)) {
                    foreach ($resQueryImagenes as $imagent) {
                        $imagen1 = array();
                        $imagen1['url'] = TIPO_HTTP . HTTP_HOST . "/" . PATH_RELATIVO_IMAGES . $_SESSION["entrada"]["codigoempresa"] . "/" . $imagent['path'];
                        $imagenes1[] = $imagen1;
                    }
                }

                //
                $recibo['imagenescertificados'] = $imagenes1;

                //
                $recibo['imagenes'] = $imagenes;
                $arrRecibos[] = $recibo;
            }
        } else {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se encontraron resultados para los datos solicitados';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $_SESSION["jsonsalida"]["recibos"] = $arrRecibos;

        $mysqli->close();
        unset($resQueryRecibos);

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}
