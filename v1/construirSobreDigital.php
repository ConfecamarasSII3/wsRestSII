<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;
use PDFA;

trait construirSobreDigital
{

    public function construirSobreDigital(API $api)
    {

        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/PDFA.class.php');
        // require_once($_SESSION["generales"]["pathabsoluto"] . '/api/EncodingNew.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        \logApi::general2('api_' . __FUNCTION__, $_SESSION["entrada"]["usuariows"], 'Inicia construccion.');
        
        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION['jsonsalida']['url'] = '';

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('construirSobreDigital', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Crea la conexión con la BD
        // ********************************************************************** // 
        $mysqli = conexionMysqliApi();
        if ($mysqli->connect_error) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexion a la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        // ********************************************************************** //
        // Generar construirSobreDigital
        // ********************************************************************** // 


        foreach ($_SESSION["entrada"]["detalletramite"] as $detalleSobre) {

            \logApi::general2('api_' . __FUNCTION__, $_SESSION["entrada"]["usuariows"], 'Lee detalle del tramite Liquidación No.: ' . $detalleSobre["idliquidacion"]);

            $detalleTramite['idliquidacion'] = $detalleSobre["idliquidacion"];
            $detalleTramite['numerorecuperacion'] = $detalleSobre["numerorecuperacion"];
            $detalleTramite['fechahora'] = $detalleSobre["fechahora"];
            $detalleTramite['tipotramite'] = $detalleSobre["tipotramite"];

            $detalleTramite['idecliente'] = $detalleSobre["idecliente"];
            $detalleTramite['nomcliente'] = $detalleSobre["nomcliente"];

            $detalleTramite['idefirmante'] = $detalleSobre["idefirmante"];


            $detalleFirmante['nombre1firmante'] = '';
            $detalleFirmante['nombre2firmante'] = '';
            $detalleFirmante['apellido1firmante'] = '';
            $detalleFirmante['apellido2firmante'] = '';

            if (isset($detalleSobre["nomfirmante"])) {
                $detalleTramite['nomfirmante'] = trim($detalleSobre["nomfirmante"]);
            } else {
                $detalleFirmante['nombre1firmante'] = $detalleSobre["nom1firmante"];
                $detalleFirmante['nombre2firmante'] = $detalleSobre["nom2firmante"];
                $detalleFirmante['apellido1firmante'] = $detalleSobre["ape1firmante"];
                $detalleFirmante['apellido2firmante'] = $detalleSobre["ape2firmante"];

                $nombreCompletoFirmante = $detalleFirmante['nombre1firmante'] . ' '
                    . $detalleFirmante['nombre2firmante'] . ' '
                    . $detalleFirmante['apellido1firmante'] . ' '
                    . $detalleFirmante['apellido2firmante'];

                $detalleTramite['nomfirmante'] = trim($nombreCompletoFirmante);
            }

            $detalleTramite['dependencia'] = $detalleSobre["dependencia"];
            $detalleTramite['seriesubserie'] = $detalleSobre["seriesubserie"];
            $detalleTramite['numfolios'] = $detalleSobre["numfolios"];

            //Weymer : 2019-07-30 : Se incluye para identificar el estado final y tipo de sobre digital.
            $detalleTramite['tipofirmado'] = '1';
            if (isset($detalleSobre["tipofirmado"]) && (trim($detalleSobre["tipofirmado"]) == '2')) {
                $detalleTramite['tipofirmado'] = $detalleSobre["tipofirmado"];
            }
        }


        $encabezadoCamara['logo'] = PATH_ABSOLUTO_SITIO . "/images/logocamara" . $_SESSION["generales"]["codigoempresa"] . ".jpg";

        $encabezadoCamara['nombre'] = RAZONSOCIAL;
        $encabezadoCamara['direccion'] = DIRECCION1;
        $encabezadoCamara['telefono'] = PBX;
        $encabezadoCamara['ciudad'] = retornarNombreMunicipioMysqliApi($mysqli, MUNICIPIO);

        $textofirmante1 = str_replace("'","´",base64_decode($_SESSION["entrada"]['firmantes']));

        $firmantes = array($textofirmante1);
        $tamaño_archivo = 0;
        $s = 0;
        $archivosAdjuntos = array();

        if (
            $detalleTramite['idliquidacion'] == "" &&
            $detalleTramite['numerorecuperacion'] == "" &&
            $detalleTramite['fechahora'] == "" &&
            $detalleTramite['tipotramite'] == "" &&
            $detalleTramite['idecliente'] == "" &&
            $detalleTramite['nomcliente'] == "" &&
            $detalleTramite['idefirmante'] == "" &&
            $detalleTramite['nomfirmante'] == "" &&
            $detalleTramite['dependencia'] == "" &&
            $detalleTramite['seriesubserie'] == "" &&
            $detalleTramite['numfolios'] == ""
        ) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Faltan campos en detalletramite.';
        } elseif ($_SESSION["entrada"]["firmantes"] == "") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'El campo firmantes no puede ir vacío.';
        } else {
            if (count($_SESSION["entrada"]["soportes"]) > 0) {
                borrarRegistrosMysqliApi($mysqli, 'mreg_liquidacion_sobre_detalle', "idliquidacion=" . $detalleTramite['idliquidacion']);
                $errorSoporte = 'no';
                if (!is_dir($_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/liquidacionmreg/' . date("Ymd")) || !is_readable($_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/liquidacionmreg/' . date("Ymd"))) {
                    mkdir($_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/liquidacionmreg/' . date("Ymd"), 0777);
                    \funcionesGenerales::crearIndex($_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/liquidacionmreg/' . date("Ymd"));
                }
                foreach ($_SESSION["entrada"]["soportes"] as $soporte) {
                    if ($soporte["urlsoporte"] != "") {
                        $ftem = file_get_contents($soporte["urlsoporte"]);
                        $espdf = \funcionesGenerales::esPdf($ftem);
                        if ($espdf === false) {
                            sleep(3);
                            $ftem = file_get_contents($soporte["urlsoporte"]);
                            $espdf = \funcionesGenerales::esPdf($ftem);
                        }
                        if ($espdf === false) {
                            \logApi::general2('api_' . __FUNCTION__, $_SESSION["entrada"]["usuariows"], 'Leyo archivo : ' . $soporte["urlsoporte"] . ' y no es un pdf valido');
                            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                            $_SESSION["jsonsalida"]["mensajeerror"] = "Leyo archivo : " . $soporte["urlsoporte"] . " y no es un pdf valido";
                            $errorSoporte = "si";
                        } else {                        
                            $alea = \funcionesGenerales::generarAleatorioAlfanumerico10($mysqli);
                            file_put_contents($_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/liquidacionmreg/' . date("Ymd") . '/' . $alea . '.pdf', $ftem);
                            $tam0 = \funcionesGenerales::tamanoArchivo($_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/liquidacionmreg/' . date("Ymd") . '/' . $alea . '.pdf');
                            $tam1 = $tam0 / 1024  / 1024;
                            \logApi::general2("api_" . __FUNCTION__, $_SESSION["entrada"]["usuariows"], 'Leyo archivo : ' . $soporte["urlsoporte"] . ', creo archivo : ' . $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/liquidacionmreg/' . date("Ymd") . '/' . $alea . '.pdf, Tamano : ' . $tam1);
                            if (!isset($soporte["tiposoporte"])) {
                                $soporte["tiposoporte"] = '';
                            }
                            $archivosAdjuntos[$s] = $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/liquidacionmreg/' . date("Ymd") . '/' . $alea . '.pdf' . '|' . $soporte["descripcionsoporte"] . '|';
                            $tamaño_archivo += $tam1;
                            $s++;
                            if (!isset($soporte["tipoanexo"]) || $soporte["tipoanexo"] == '') {
                                $soporte["tipoanexo"] = "501";
                                $posXor = strpos($soporte["descripcionsoporte"],'FORMULARIO');
                                if ($posXor) {
                                    $soporte["tipoanexo"] = "503";
                                }
                                $posres = strpos($soporte["descripcionsoporte"],'RESPONSABILIDAD');
                                if ($posres) {
                                    $soporte["tipoanexo"] = "504";
                                }
                            }
                            if (!isset($soporte["idtipodoc"]) || $soporte["idtipodoc"] == '') {
                                $soporte["idtipodoc"] = "90.01.017";
                                $posXor = strpos(strtoupper($soporte["descripcionsoporte"]),'FORMULARIO');
                                if ($posXor !== false) {
                                    $soporte["idtipodoc"] = "90.01.011";
                                }
                                $posXor = strpos(strtoupper($soporte["descripcionsoporte"]),'SOLICITUD');
                                if ($posXor !== false) {
                                    $soporte["idtipodoc"] = "90.01.033";
                                }
                                $posXor = strpos(strtoupper($soporte["descripcionsoporte"]),'ACTA');
                                if ($posXor !== false) {
                                    $soporte["idtipodoc"] = "90.01.015";
                                }
                                $posXor = strpos(strtoupper($soporte["descripcionsoporte"]),'ESCRITURA');
                                if ($posXor !== false) {
                                    $soporte["idtipodoc"] = "90.01.013";
                                }
                                
                            }
                            if (!isset($soporte["numdoc"]) || $soporte["numdoc"] == '') {
                                $soporte["numdoc"] = 'N/A';
                            }
                            if (!isset($soporte["fechadoc"]) || $soporte["fechadoc"] == '') {
                                $soporte["fechadoc"] = date ("Ymd");
                            }
                            if (!isset($soporte["txtorigendoc"]) || $soporte["txtorigendoc"] == '') {
                                $soporte["txtorigendoc"] = 'EL COMERCIANTE';
                            }
                            if (!isset($soporte["identificacioncomerciante"]) || $soporte["identificacioncomerciante"] == '') {
                                $soporte["identificacioncomerciante"] = $detalleTramite['idecliente'];
                            }
                            
                            if (!isset($soporte["nombrecomerciante"]) || $soporte["nombrecomerciante"] == '') {
                                $soporte["nombrecomerciante"] = $detalleTramite['nomcliente'];
                            }
                            
                            $arrCampos = array (
                                'idsobre',
                                'idliquidacion',
                                'matricula',
                                'proponente',
                                'identificacion',
                                'nombre',
                                'tipoarchivo',
                                'tipoanexo',
                                'idtipodoc',
                                'numdoc',
                                'fechadoc',
                                'txtorigendoc',
                                'identificador',
                                'observaciones',
                                'pesoarchivo',
                                'hashfirmado',
                                'path'
                            );
                            $arrValores = array (
                                "''",
                                $detalleTramite['idliquidacion'],
                                "''",
                                "''",
                                "'" . $soporte["identificacioncomerciante"] . "'",
                                "'" . addslashes($soporte["nombrecomerciante"]) . "'",
                                "'pdf'",
                                "'" . $soporte["tipoanexo"] . "'",
                                "'" . $soporte["idtipodoc"] . "'",
                                "'" . $soporte["numdoc"] . "'",
                                "'" . $soporte["fechadoc"] . "'",
                                "'" . $soporte["txtorigendoc"] . "'",
                                "''",
                                "'" . addslashes($soporte["descripcionsoporte"]) . "'",
                                $tam0,
                                "''",
                                "'liquidacionmreg/" . date("Ymd") . "/" . $alea . ".pdf'"                                
                            );
                            insertarRegistrosMysqliApi($mysqli, 'mreg_liquidacion_sobre_detalle', $arrCampos, $arrValores);
                        }
                    } else {
                        $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                        $_SESSION["jsonsalida"]["mensajeerror"] = 'La url en soporte no puede estar vacía.';
                        $errorSoporte = "si";
                    }
                }
                
                //
                if ($errorSoporte == 'si') {
                    $mysqli->close();
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                    exit ();
                }
                
                //
                \logApi::general2('api_' . __FUNCTION__, $_SESSION["entrada"]["usuariows"], 'Tamano total  : ' . $tamaño_archivo);

                //Verifica si costruye sobre de link o de adjuntos.
                $sobreLink = ($tamaño_archivo <= 300) ? "no" : "si";
                // $sobreLink = 'no';
                $fdate = date("Ymd");
                $fhora = date("His");
                $fip = \funcionesGenerales::localizarIP();

                $url_tmp_sobre = PATH_ABSOLUTO_SITIO . "/tmp/" . $_SESSION["generales"]["codigoempresa"] . "-Sobre-" . $detalleTramite["idliquidacion"] . '-' . $detalleTramite["tipotramite"] . "-" . $fdate . $fhora . ".pdf";

                //Instancia clase para creación de sobre digitales (En segundo plano ejecuta Jars)
                $pdfa = new PDFA();
                $xsobre = $pdfa->generarSobreFirmado("NA", $firmantes, $encabezadoCamara, $detalleTramite, $archivosAdjuntos, $url_tmp_sobre, $sobreLink);
                unset($pdfa);

                //
                $url_salida_jar = str_replace(".pdf", "-SF.pdf", $url_tmp_sobre);

                //Verifica si el directorio final existe en el repositorio, sino lo construye
                $dirx = date("Ymd");
                $path = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/sobredigitalmreg/' . $dirx;
                if (!is_dir($path) || !is_readable($path)) {
                    mkdir($path, 0777);
                    \funcionesGenerales::crearIndex($path);
                }

                if (file_exists($url_salida_jar)) {
                    $url_absoluta_repositorio = '';
                    $aleatorio = \funcionesGenerales::generarAleatorioAlfanumerico10('');
                    $pathSobresDigitales = 'sobredigitalmreg/' . $dirx . '/API_' . $aleatorio . '.pdf';

                    $url_relativa_repositorio = PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["entrada"]["codigoempresa"] . '/' . $pathSobresDigitales;

                    if (!copy($url_salida_jar, $url_relativa_repositorio)) {
                        $mysqli->close();
                        $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                        $_SESSION["jsonsalida"]["mensajeerror"] = 'No fue posible trasladar el sobre al repositorio, error en copy.';
                        $api->response($api->json($_SESSION["jsonsalida"]), 200);
                    } else {
                        $url_absoluta_repositorio = TIPO_HTTP . HTTP_HOST . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["entrada"]["codigoempresa"] . '/' . $pathSobresDigitales;

                        //
                        // Almacena el log indicando que el sobre se almaceno en el repositorio
                        $arrCampos = array(
                            'fecha',
                            'hora',
                            'ip',
                            'idliquidacion',
                            'numerorecuperacion',
                            'momento',
                            'tipotramite',
                            'tipoidefirmante',
                            'identificacionfirmante',
                            'fechaexpfirmante',
                            'nombrefirmante',
                            'apellido1firmante',
                            'apellido2firmante',
                            'nombre1firmante',
                            'nombre2firmante',
                            'emailfirmante',
                            'celularfirmante',
                            'texto',
                            'respuesta'
                        );


                        $arrValores = array(
                            "'" . $fdate . "'",
                            "'" . $fhora . "'",
                            "'" . $fip . "'",
                            "'" . $detalleTramite["idliquidacion"] . "'",
                            "'" . $detalleTramite["numerorecuperacion"] . "'",
                            "'copiosobre'",
                            "'" . $detalleTramite['tipotramite'] . "'",
                            "''",
                            "'" . $detalleTramite['idefirmante'] . "'",
                            "''",
                            "'" . addslashes($detalleTramite['nomfirmante']) . "'",
                            "''",
                            "''",
                            "''",
                            "''",
                            "''",
                            "''",
                            "''",
                            "'Creo el sobre digital en el repositorio : .../" . $pathSobresDigitales . "'"
                        );
                        $res = insertarRegistrosMysqliApi($mysqli, 'mreg_firmadoelectronico_log', $arrCampos, $arrValores);
                        //
                    }

                    $criterioBusqueda = "idliquidacion='" . $detalleTramite["idliquidacion"] . "' and numerorecuperacion='" . $detalleTramite["numerorecuperacion"] . "'";

                    $arrTemLiq = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion', $criterioBusqueda);

                    if ($arrTemLiq && !empty($arrTemLiq)) {
                        $arrCampos1 = array(
                            'idsobre',
                            'idliquidacion',
                            'fecha',
                            'hora',
                            'ip',
                            'tipotramite',
                            'identificacionfirmante',
                            'nombre1firmante',
                            'nombre2firmante',
                            'apellido1firmante',
                            'apellido2firmante',
                            'numeroarchivos',
                            'path'
                        );


                        $arrValores1 = array(
                            "'" . $aleatorio . "'",
                            $detalleTramite["idliquidacion"],
                            "'" . $fdate . "'",
                            "'" . $fhora . "'",
                            "'" . $fip . "'",
                            "'" . $detalleTramite["tipotramite"] . "'",
                            "'" . $detalleTramite["idefirmante"] . "'",
                            "'" . $detalleFirmante['nombre1firmante'] . "'",
                            "'" . $detalleFirmante['nombre2firmante'] . "'",
                            "'" . $detalleFirmante['apellido1firmante'] . "'",
                            "'" . $detalleFirmante['apellido2firmante'] . "'",
                            $s,
                            "'" . $pathSobresDigitales . "'"
                        );
                        $resInsert = insertarRegistrosMysqliApi($mysqli, 'mreg_liquidacion_sobre', $arrCampos1, $arrValores1);

                        if (!$resInsert) {
                            $mysqli->close();
                            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                            $_SESSION["jsonsalida"]["mensajeerror"] = 'No fue posible grabar registro de sobre digital en el SII.';
                            $api->response($api->json($_SESSION["jsonsalida"]), 200);
                        } else {

                            if ($detalleTramite['tipofirmado'] == '1') {
                                $arrCampos2 = array('idestado', 'firmadoelectronicamente', 'firmadomanuscrita');
                                $arrValores2 = array("'19'",  "'si'", "''");
                            }
                            if ($detalleTramite['tipofirmado'] == '2') {
                                $arrCampos2 = array('idestado', 'firmadoelectronicamente', 'firmadomanuscrita');
                                $arrValores2 = array("'44'",  "''", "'si'");
                            }

                            $resUpdate = regrabarRegistrosMysqliApi($mysqli, 'mreg_liquidacion', $arrCampos2, $arrValores2, $criterioBusqueda);
                            if (!$resUpdate) {
                                $mysqli->close();
                                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                                $_SESSION["jsonsalida"]["mensajeerror"] = 'No fue posible cambiar asignar estado a la liquidación.';
                                $api->response($api->json($_SESSION["jsonsalida"]), 200);
                            }
                        }
                    }
                    $_SESSION['jsonsalida']['url'] = $url_absoluta_repositorio;
                } else {
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'Se presentaron problemas en la construcción del sobre digital.';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                }
            } else {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No hay soportes para adjuntar al sobre digital.';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }



        $mysqli->close();
        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }
}
