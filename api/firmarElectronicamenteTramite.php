<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;
use PDFA;

trait firmarElectronicamenteTramite {

    public function firmarElectronicamenteTramite(API $api) {

        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/PDFA.class.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesFirmadoElectronico.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

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
        $api->validarParametro("idusuario", true);
        $api->validarParametro("idliquidacion", true);
        $api->validarParametro("identificacioncontrol", true);
        $api->validarParametro("emailcontrol", true);
        $api->validarParametro("celularcontrol", true);
        // $api->validarParametro("nombrecontrol", true);
        $api->validarParametro("clavefirmado", false);
        $nombrecontrol = '';
        
        if (!$api->validarToken('firmarElectronicamenteTramite', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Abre conexión con la BD
        // ********************************************************************** //
        $mysqli = conexionMysqliApi();
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error conectandose a la BD.';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Recupera la liquidación 
        // ********************************************************************** //
        $_SESSION["tramite"] = \funcionesRegistrales::retornarMregLiquidacion($mysqli, $_SESSION["entrada"]["idliquidacion"], 'L');
        if ($_SESSION["tramite"] === false) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Liquidación no localizada en la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Verifica el estado de la liquidación
        // ********************************************************************** //
        if ($_SESSION["tramite"]["idestado"] > '05') {
            if ($_SESSION["tramite"]["idestado"] != '10') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Liquidación se encuentra en un estado que no permite ser firmada.';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        // ********************************************************************** //
        // Verifica el tipo de trámite
        // ********************************************************************** //
        $tiptra = $_SESSION["tramite"]["tipotramite"];
        if (trim($_SESSION["tramite"]["subtipotramite"]) != '') {
            $tiptra = $_SESSION["tramite"]["subtipotramite"];
        }
        $tipoTramite = retornarRegistroMysqliApi($mysqli, 'bas_tipotramites', "id='" . $tiptra . "'");
        if ($tipoTramite === false || empty($tipoTramite)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Tipo de trámite de la liquidación no identificado';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // *************************************************************************************** //
        // Verifica que el usuario enviado sea un usuario verificado activo o registrado activo
        // *************************************************************************************** //
        $tUsuario = '';

        //
        $oknacional = 'no';
        $_SESSION["generales"]["emailusuariocontrol"] = $_SESSION["entrada"]["emailcontrol"];
        $_SESSION["generales"]["identificacionusuariocontrol"] = $_SESSION["entrada"]["identificacioncontrol"];
        $nal = \funcionesGenerales::validarUsuarioNacional($_SESSION["entrada"]["emailcontrol"], $_SESSION["entrada"]["clavefirmado"], $_SESSION["entrada"]["identificacioncontrol"]);
        if ($nal["codigoerror"] == '9993') {
            $clavelimpia = \funcionesGenerales::encrypt_decrypt('decrypt', 'c0nf3c4m4r4s', 'c0nf3c4m4r4s', trim($_SESSION["entrada"]["clavefirmado"]));
            $nal = \funcionesGenerales::validarUsuarioNacional($_SESSION["entrada"]["emailcontrol"], $clavelimpia, $_SESSION["entrada"]["identificacioncontrol"]);
            if ($nal["codigoerror"] == '0000') {
                $oknacional = 'si';
            } else {
                if ($nal["codigoerror"] == '9993') {
                    $_SESSION["jsonsalida"]["codigoerror"] = '9999';
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'La clave del usuario verificado es incorrecta (' . $_SESSION["entrada"]["clavefirmado"] . ')';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                }
            }
        } else {
            if ($nal["codigoerror"] == '0000') {
                $oknacional = 'si';
                $nombrecontrol = $nal["nombre"];
            }
        }
        if ($oknacional == 'si') {
            $tUsuario = 'nacional';
        } else {
            $usu = retornarRegistroMysqliApi($mysqli, 'usuarios_verificados', "email='" . $_SESSION["entrada"]["emailcontrol"] . "' and identificacion='" . $_SESSION["entrada"]["identificacioncontrol"] . "' and celular='" . $_SESSION["entrada"]["celularcontrol"] . "'");
            if ($usu && !empty($usu)) {
                if ($usu["estado"] == 'VE' && $usu["claveconfirmacion"] != '') {
                    $clavelimpia = \funcionesGenerales::encrypt_decrypt('decrypt', 'c0nf3c4m4r4s', 'c0nf3c4m4r4s', trim($_SESSION["entrada"]["clavefirmado"]));
                    if ($usu["claveacceso"] != md5($clavelimpia) &&
                            $usu["claveacceso"] != md5($_SESSION["entrada"]["clavefirmado"]) &&
                            $usu["claveacceso"] != sha1($clavelimpia) &&
                            $usu["claveacceso"] != sha1($_SESSION["entrada"]["clavefirmado"]) &&
                            $usu["claveacceso"] != $_SESSION["entrada"]["clavefirmado"] &&
                            !password_verify($clavelimpia, $usu["claveacceso"]) &&
                            !password_verify(trim($_SESSION["entrada"]["clavefirmado"]), $usu["claveacceso"])) {
                        $mysqli->close();
                        $_SESSION["jsonsalida"]["codigoerror"] = '9999';
                        // $_SESSION["jsonsalida"]["mensajeerror"] = 'La clave del usuario verificado es incorrecta (' . $clavelimpia . ')';
                        $_SESSION["jsonsalida"]["mensajeerror"] = 'La clave del usuario verificado es incorrecta (' . $_SESSION["entrada"]["clavefirmado"] . ')';
                        $api->response($api->json($_SESSION["jsonsalida"]), 200);
                    } else {
                        $tUsuario = 'verificado';
                        $nombrecontrol = $usu["nombre"];
                    }
                }
            }

            if ($tUsuario == '') {
                $usu = retornarRegistroMysqliApi($mysqli, 'usuarios_registrados', "email='" . $_SESSION["entrada"]["emailcontrol"] . "' and identificacion='" . $_SESSION["entrada"]["identificacioncontrol"] . "' and celular='" . $_SESSION["entrada"]["celularcontrol"] . "'");
                if ($usu && !empty($usu)) {
                    if ($usu["estado"] == 'AP') {
                        $clavelimpia = \funcionesGenerales::encrypt_decrypt('decrypt', 'c0nf3c4m4r4s', 'c0nf3c4m4r4s', trim($_SESSION["entrada"]["clavefirmado"]));
                        if ($usu["clave"] != md5($clavelimpia) &&
                                $usu["clave"] != md5($_SESSION["entrada"]["clavefirmado"]) &&
                                $usu["clave"] != sha1($clavelimpia) &&
                                $usu["clave"] != sha1($_SESSION["entrada"]["clavefirmado"]) &&
                                $usu["clave"] != $_SESSION["entrada"]["clavefirmado"] &&
                                !password_verify($clavelimpia, $usu["clave"]) &&
                                !password_verify($_SESSION["entrada"]["clavefirmado"], $usu["clave"])
                        ) {
                            $mysqli->close();
                            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
                            // $_SESSION["jsonsalida"]["mensajeerror"] = 'La clave del usuario registrado es incorrecta (' . $clavelimpia . ')';
                            $_SESSION["jsonsalida"]["mensajeerror"] = 'La clave del usuario registrado es incorrecta (' . $_SESSION["entrada"]["clavefirmado"] . ')';
                            $api->response($api->json($_SESSION["jsonsalida"]), 200);
                        } else {
                            $tUsuario = 'registrado';
                            $nombrecontrol = $usu["nombre"];
                        }
                    }
                }
            }
        }
        if ($tUsuario == '') {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se encontró en la BD un usuario (registrado o verificado) que concuerde con la información reportada';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ******************************************************************************************************** //
        // Verifica si el usuario reportado puede firmar en relación con la exigencia de usuario verificado
        // ******************************************************************************************************** //
        if ($tipoTramite["exigeverificado"] == 'si' && $tUsuario == 'registrado') {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'El trámite seleccionado exige que el firmante sea un usuario verificado';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ******************************************************************************************************** //
        // Verifica si el usuario reportado puede firmar en relación el tipo de firmante
        // ******************************************************************************************************** //
        $puedeFirmar = 'no';
        $tfs = explode(",", $tipoTramite["firmante"]);
        foreach ($tfs as $tf) {
            if ($tf == '99') {
                $puedeFirmar = 'si';
            }
        }
        if ($puedeFirmar == 'no') {
            foreach ($tfs as $tf) {
                if ($tf != '99') {
                    if ($tf == '01') { // Firma el propietario del trámite
                        if ($_SESSION["entrada"]["identificacioncontrol"] == $_SESSION["tramite"]["idepnat"]) {
                            $puedeFirmar = 'si';
                        } else {
                            $matx = '';
                            if (ltrim($_SESSION["tramite"]["matriculabase"], "0") != '') {
                                $matx = $_SESSION["tramite"]["matriculabase"];
                            } else {
                                if (ltrim($_SESSION["tramite"]["idexpedientebase"], "0") != '') {
                                    $matx = $_SESSION["tramite"]["idexpedientebase"];
                                }
                            }
                            if ($matx != '') {
                                $exp = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, ltrim(trim($matx), "0"), '', '', '', 'si', 'N');
                                if ($_SESSION["entrada"]["identificacioncontrol"] == ltrim($exp["identificacion"], "0")) {
                                    $puedeFirmar = 'si';
                                } else {
                                    foreach ($exp["propietarios"] as $px) {
                                        if ($px["identificacionpropietario"] == $_SESSION["entrada"]["identificacioncontrol"]) {
                                            $puedeFirmar = 'si';
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if ($puedeFirmar == 'no') {
                        if ($tf == '11') { // Representantes legales
                            if ($_SESSION["entrada"]["identificacioncontrol"] == $_SESSION["tramite"]["iderepleg"]) {
                                $puedeFirmar = 'si';
                            } else {
                                $matx = '';
                                if (ltrim($_SESSION["tramite"]["matriculabase"], "0") != '') {
                                    $matx = $_SESSION["tramite"]["matriculabase"];
                                } else {
                                    if (ltrim($_SESSION["tramite"]["idexpedientebase"], "0") != '') {
                                        $matx = $_SESSION["tramite"]["idexpedientebase"];
                                    }
                                }
                                if ($matx != '') {
                                    $exp = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, ltrim(trim($matx), "0"), '', '', '', 'si', 'N');
                                    foreach ($exp["vinculos"] as $vx) {
                                        if ($vx["tipovinculo"] == 'ADM' ||
                                                $vx["tipovinculo"] == 'RLP' ||
                                                $vx["tipovinculo"] == 'RLS' ||
                                                $vx["tipovinculo"] == 'RLS1' ||
                                                $vx["tipovinculo"] == 'RLS2') {
                                            if ($vx["identificacionotros"] == $_SESSION["entrada"]["identificacioncontrol"]) {
                                                $puedeFirmar = 'si';
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if ($puedeFirmar == 'no') {
                        if ($tf == '21') { // Socios
                            $matx = '';
                            if (ltrim($_SESSION["tramite"]["matriculabase"], "0") != '') {
                                $matx = $_SESSION["tramite"]["matriculabase"];
                            } else {
                                if (ltrim($_SESSION["tramite"]["idexpedientebase"], "0") != '') {
                                    $matx = $_SESSION["tramite"]["idexpedientebase"];
                                }
                            }
                            if ($matx != '') {
                                $exp = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, ltrim(trim($matx), "0"), '', '', '', 'si', 'N');
                                foreach ($exp["vinculos"] as $vx) {
                                    if ($vx["tipovinculo"] == 'SOC') {
                                        if ($vx["identificacionotros"] == $_SESSION["entrada"]["identificacioncontrol"]) {
                                            $puedeFirmar = 'si';
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if ($puedeFirmar == 'no') {
                        if ($tf == '31') { // Junta directiva
                            $matx = '';
                            if (ltrim($_SESSION["tramite"]["matriculabase"], "0") != '') {
                                $matx = $_SESSION["tramite"]["matriculabase"];
                            } else {
                                if (ltrim($_SESSION["tramite"]["idexpedientebase"], "0") != '') {
                                    $matx = $_SESSION["tramite"]["idexpedientebase"];
                                }
                            }
                            if ($matx != '') {
                                $exp = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, ltrim(trim($matx), "0"), '', '', '', 'si', 'N');
                                foreach ($exp["vinculos"] as $vx) {
                                    if ($vx["tipovinculo"] == 'JDP' ||
                                            $vx["tipovinculo"] == 'JDS') {
                                        if ($vx["identificacionotros"] == $_SESSION["entrada"]["identificacioncontrol"]) {
                                            $puedeFirmar = 'si';
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if ($puedeFirmar == 'no') {
                        if ($tf == '41') { // Revisores fiscales
                            $matx = '';
                            if (ltrim($_SESSION["tramite"]["matriculabase"], "0") != '') {
                                $matx = $_SESSION["tramite"]["matriculabase"];
                            } else {
                                if (ltrim($_SESSION["tramite"]["idexpedientebase"], "0") != '') {
                                    $matx = $_SESSION["tramite"]["idexpedientebase"];
                                }
                            }
                            if ($matx != '') {
                                $exp = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, ltrim(trim($matx), "0"), '', '', '', 'si', 'N');
                                foreach ($exp["vinculos"] as $vx) {
                                    if ($vx["tipovinculo"] == 'RFP' ||
                                            $vx["tipovinculo"] == 'RFS' ||
                                            $vx["tipovinculo"] == 'RFS1') {
                                        if ($vx["identificacionotros"] == $_SESSION["entrada"]["identificacioncontrol"]) {
                                            $puedeFirmar = 'si';
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if ($puedeFirmar == 'no') {
                        if ($tf == '91') { // El proponente                            
                            $matx = '';
                            if (ltrim($_SESSION["tramite"]["proponentebase"], "0") != '') {
                                $prpx = $_SESSION["tramite"]["proponentebase"];
                            } else {
                                if (ltrim($_SESSION["tramite"]["idexpedientebase"], "0") != '') {
                                    $prpx = $_SESSION["tramite"]["idexpedientebase"];
                                }
                            }
                            $exp = array();
                            if ($prpx != '') {
                                $exp = \funcionesRegistrales::retornarExpedienteProponente($mysqli, $prpx);
                            } else {
                                $exp1 = retornarRegistroMysqliApi($mysqli, 'mreg_liquidaciondatos', "idliquidacion=" . $_SESSION["tramite"]["idliquidacion"]);
                                if ($exp1 && !empty($exp1)) {
                                    $exp = \funcionesGenerales::desserializarExpedienteProponente($mysqli, $exp1["xml"]);
                                }
                            }
                            if ($exp && is_array($exp)) {
                                if ($exp["organizacion"] == '01') {
                                    if ($exp["identificacion"] == $_SESSION["entrada"]["identificacioncontrol"]) {
                                        $puedeFirmar = 'si';
                                    }
                                } else {
                                    foreach ($exp["representanteslegales"] as $vx) {
                                        if ($vx["identificacionrepleg"] == $_SESSION["entrada"]["identificacioncontrol"]) {
                                            $puedeFirmar = 'si';
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        //
        if ($puedeFirmar == 'no') {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'El usuario reportado no está habilitado para fimar el trámite';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // Almacena la data del firmante

        $_SESSION["tramite"]["identificacionfirmante"] = $_SESSION["entrada"]["identificacioncontrol"];
        $_SESSION["tramite"]["emailfirmante"] = $_SESSION["entrada"]["emailcontrol"];
        $_SESSION["tramite"]["celularfirmante"] = $_SESSION["entrada"]["celularcontrol"];
        $_SESSION["tramite"]["nombrefirmante"] = $nombrecontrol;
        $_SESSION["tramite"]["nombre1firmante"] = '';
        $_SESSION["tramite"]["nombre2firmante"] = '';
        $_SESSION["tramite"]["apellido1firmante"] = '';
        $_SESSION["tramite"]["apellido2firmante"] = '';

        //
        \logApi::general2('api_' . __FUNCTION__, $_SESSION["entrada"]["usuariows"], 'Inicia proceso de firma digital de trámites: ' . json_encode($_SESSION["entrada"]));

        $fdate = date("Ymd");
        $fhora = date("His");
        $fip = \funcionesGenerales::localizarIP();
        $_SESSION["tramite"]["listado"] = \funcionesFirmadoElectronico::armarListaSoportes($mysqli, $fdate, $fhora, $fip);

        // ******************************************************************************************** //
        // Encuentra el númeto total de folios contenidos en los archivos
        // ******************************************************************************************** //
        $totalFolios = 0;
        foreach ($_SESSION["tramite"]["listado"] as $a) {
            $num = \funcionesFirmadoElectronico::getNumPagesInPDF($_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $a["path"]);
            $totalFolios = $totalFolios + $num;
        }

        // ******************************************************************************************** //
        // Arma la tabla mreg_liquidacion_sobre 
        // ******************************************************************************************** //
        $numsobre = \funcionesGenerales::generarAleatorioAlfanumerico10($mysqli, 'mreg_liquidacion_sobre');

        //
        $arrCampos = array(
            'idsobre',
            'idliquidacion',
            'fecha',
            'hora',
            'ip',
            'pin',
            'tipotramite',
            'identificacionfirmante',
            'nombrefirmante',
            'nombre1firmante',
            'nombre2firmante',
            'apellido1firmante',
            'apellido2firmante',
            'numeroarchivos'
        );
        $arrValores = array(
            "'" . $numsobre . "'",
            $_SESSION["tramite"]["idliquidacion"],
            "'" . $fdate . "'",
            "'" . $fhora . "'",
            "'" . $fip . "'",
            "''",
            "'" . $_SESSION["tramite"]["tipotramite"] . "'",
            "'" . $_SESSION["tramite"]["identificacionfirmante"] . "'",
            "'" . addslashes($_SESSION["tramite"]["nombrefirmante"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["nombre1firmante"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["nombre2firmante"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["apellido1firmante"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["apellido2firmante"]) . "'",
            count($_SESSION["tramite"]["listado"])
        );
        $res = insertarRegistrosMysqliApi($mysqli, 'mreg_liquidacion_sobre', $arrCampos, $arrValores);
        if ($res === false) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error almacenando mreg_liquidacion_sobre : ' . $_SESSION["generales"]["mensajeerror"];
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ******************************************************************************************** //
        // Arma la tabla mreg_liquidacion_sobre_detalle
        // Con la referencia a los archivos contenidos en el sobre
        // ******************************************************************************************** //
        $arrCampos = array(
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
        $arrValores = array();
        $iVal = 0;
        foreach ($_SESSION["tramite"]["listado"] as $l) {
            $iVal++;
            $arrValores[$iVal] = array(
                "'" . $numsobre . "'",
                $_SESSION["tramite"]["idliquidacion"],
                "'" . $l["matricula"] . "'",
                "'" . $l["proponente"] . "'",
                "'" . $l["identificacion"] . "'",
                "'" . addslashes($l["nombre"]) . "'",
                "'" . $l["tipoarchivo"] . "'",
                "'" . $l["tipoanexo"] . "'",
                "'" . $l["idtipodoc"] . "'",
                "'" . $l["numdoc"] . "'",
                "'" . $l["fechadoc"] . "'",
                "'" . addslashes($l["origendoc"]) . "'",
                "'" . $l["identificador"] . "'",
                "'" . addslashes($l["observaciones"]) . "'",
                intval($l["pesoarchivo"]),
                "'" . addslashes($l["hashfirmado"]) . "'",
                "'" . $l["path"] . "'"
            );
        }
        $res = insertarRegistrosBloqueMysqliApi($mysqli, 'mreg_liquidacion_sobre_detalle', $arrCampos, $arrValores);
        if ($res === false) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error almacenando mreg_liquidacion_sobre_detalle : ' . $_SESSION["generales"]["mensajeerror"];
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }



        // ************************************************************************ //
        // Almacena el log de firmado del trámite
        // Indicando que el usuario acepto el firmado del tramite
        // ************************************************************************ //
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
            $_SESSION["tramite"]["idliquidacion"],
            "'" . $_SESSION["tramite"]["numerorecuperacion"] . "'",
            "'firmadotramites'",
            "'" . $_SESSION["tramite"]["tipotramite"] . "'",
            "'" . $_SESSION["tramite"]["tipoidefirmante"] . "'",
            "'" . $_SESSION["tramite"]["identificacionfirmante"] . "'",
            "'" . $_SESSION["tramite"]["fechaexpfirmante"] . "'",
            "'" . addslashes($_SESSION["tramite"]["nombrefirmante"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["apellido1firmante"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["apellido2firmante"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["nombre1firmante"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["nombre2firmante"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["emailfirmante"]) . "'",
            "'" . $_SESSION["tramite"]["celularfirmante"] . "'",
            "''",
            "'Firmo el tramite'"
        );
        $res = insertarRegistrosMysqliApi($mysqli, 'mreg_firmadoelectronico_log', $arrCampos, $arrValores);
        if ($res === false) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error almacenando log de firmado : ' . $_SESSION["generales"]["mensajeerror"];
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ************************************************************************** //
        // Arma el sobre
        // Este se debe almacenar en el path mregsobres/idsobre.pdf
        // Debe validarse que exista el directorio antes de continuar
        // ************************************************************************** //
        //WEYMER
        $arrTra = retornarRegistroMysqliApi($mysqli, 'bas_tipotramites', "id='" . $_SESSION["tramite"]["tipotramite"] . "'");

        $pdfa = new PDFA();

        //ARMADO DE NOMBRE DEL CLIENTE
        $nombreCompletoFirmante = $_SESSION["tramite"]["nombre1firmante"] . ' '
                . $_SESSION["tramite"]["nombre2firmante"] . ' '
                . $_SESSION["tramite"]["apellido1firmante"] . ' '
                . $_SESSION["tramite"]["apellido2firmante"];

        if (trim($nombreCompletoFirmante) == '') {
            $nombreCompletoFirmante = $_SESSION["tramite"]["nombrefirmante"];
        }

        $textofirmante1 = 'EL SEÑOR(A) ' . trim($nombreCompletoFirmante) . ' IDENTIFICADO(A) CON EL NÚMERO ' .
                $_SESSION["tramite"]["identificacionfirmante"] . ' FIRMÓ ELECTRÓNICAMENTE ' .
                'LOS FORMULARIOS Y ANEXOS DOCUMENTALES DEL TRÁMITE EL ' . \funcionesGenerales::mostrarFecha($fdate) . ' A LAS ' . \funcionesgenerales::mostrarHora($fhora) . ' ' .
                'DANDO FE DEL CONTENIDO DE LOS MISMOS.';

        //ADICIÓN DE TEXTO DE FIRMA ELECTRÓNICA A ARREGLO DE FIRMANTES
        $firmantes = array($textofirmante1);

        //ARREGLO DATOS ENCABEZADO SOBRE ELECTRÓNICO
        $encabezadoCamara['logo'] = $_SESSION["generales"]["pathabsoluto"] . "/images/logocamara" . $_SESSION["generales"]["codigoempresa"] . ".jpg";

        $encabezadoCamara['nombre'] = RAZONSOCIAL;
        $encabezadoCamara['direccion'] = DIRECCION1;
        $encabezadoCamara['telefono'] = PBX;
        $encabezadoCamara['ciudad'] = retornarRegistroMysqliApi($mysqli, 'bas_municipios', "codigomunicipio='" . MUNICIPIO . "'", "ciudad");

        //ARREGLO DATOS DETALLE SOBRE ELECTRÓNICO
        $detalleTramite['idliquidacion'] = $_SESSION["tramite"]["idliquidacion"];
        $detalleTramite['numerorecuperacion'] = $_SESSION["tramite"]["numerorecuperacion"];
        $detalleTramite['fechahora'] = \funcionesGenerales::mostrarFecha($fdate) . ' ' . \funcionesGenerales::mostrarHora($fhora);
        $detalleTramite['tipotramite'] = $_SESSION["tramite"]["tipotramite"];

        $detalleTramite['idecliente'] = $_SESSION["tramite"]["identificacionbase"];
        $detalleTramite['nomcliente'] = $_SESSION["tramite"]["nombrebase"];

        $detalleTramite['idefirmante'] = $_SESSION["tramite"]["identificacionfirmante"];
        $detalleTramite['nomfirmante'] = $nombreCompletoFirmante;

        $detalleTramite['numfolios'] = "";
        $detalleTramite['dependencia'] = "Registros Públicos";
        $detalleTramite['seriesubserie'] = "";

        switch ($arrTra["tiporegistro"]) {
            case "RegMer" : $detalleTramite['seriesubserie'] = "Registro Mercantil";
                break;
            case "RegEsadl" : $detalleTramite['seriesubserie'] = "Registro de Entidades Sin Animo de Lucro";
                break;
            case "RegPro" : $detalleTramite['seriesubserie'] = "Registro de Proponentes";
                break;
        }

        $detalleTramite['numfolios'] = $totalFolios;

        //OBTENER PATH DE ADJUNTOS A LA RESPECTIVA LIQUIDACIÓN
        //WSI 2016-04-04 la busqueda debe realizarse por idliquidacion y idsobre
        //$arrTem = retornarRegistros('mreg_liquidacion_sobre_detalle', "idliquidacion=" . $_SESSION["tramite"]["idliquidacion"], "id");
        $arrTem = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidacion_sobre_detalle', "idliquidacion=" . $_SESSION["tramite"]["idliquidacion"] . " and idsobre like '" . $numsobre . "'", "id");

        //ARMADO DE ARREGLO DE PATH ADJUNTOS
        $archivosAdjuntos = array();

        //CALCULAR EL PESO TOTAL DE SOPORTES A ADJUNTAS EN MB
        $pesoTotal = 0;
        $peso = 0;
        $sobreLink = 'no';

        foreach ($arrTem as $d) {
            $peso = trim($d["pesoarchivo"]);
            $pesoMB = round(($peso / 1048576), 2);
            $pesoTotal += $pesoMB;
        }

        //$pesoTotal=500;
        //Si peso total sobrepasa 300MB envia adicionalmente 
        if ($pesoTotal <= 300) {

            $i = 0;
            foreach ($arrTem as $t) {
                $i++;
                $rutaAbsolutaAdjunto = PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/" . $t["path"];
                $descripcionAdjunto = $i . ' - ' . $t["observaciones"] . ' (' . $t["identificador"] . ')';
                $archivosAdjuntos[$i] = $rutaAbsolutaAdjunto . '|' . $descripcionAdjunto . '|';
            }
            $sobreLink = 'no';
        } else {
            $i = 0;
            foreach ($arrTem as $t) {
                $i++;
                $rutaRelativaAdjunto = TIPO_HTTP . HTTP_HOST . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $t["path"];
                $descripcionAdjunto = $i . ' - ' . $t["observaciones"] . ' (' . $t["identificador"] . ')';
                $archivosAdjuntos[$i] = $rutaRelativaAdjunto . '|' . $descripcionAdjunto . '|';
            }
            $sobreLink = 'si';
        }



        //RUTA DE SALIDA SOBRE PDF/A
        $rutaOutPDFA = PATH_ABSOLUTO_SITIO . "/tmp/" . $_SESSION["generales"]["codigoempresa"] . "-Sobre-" . $_SESSION["tramite"]["idliquidacion"] . '-' . $_SESSION["tramite"]["tipotramite"] . "-" . $fdate . $fhora . ".pdf";

        $nsobre = str_replace(".pdf", "-SF.pdf", $rutaOutPDFA);

        //GENERACIÓN DE SOBRE ELECTRÓNICO (QUEDA CON EL NOMBRE DADO EN RUTA ADICIONADO -SF)
        $x = $pdfa->generarSobreFirmado('NA', $firmantes, $encabezadoCamara, $detalleTramite, $archivosAdjuntos, $rutaOutPDFA, $sobreLink);

        unset($pdfa);

        if (!$x) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error generando el Sobre Digital';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        // ******************* FIN GENERACION DE SOBRE **************************** //
        // ************************************************************************** //
        // Almacena el log informando el armado del sobre
        // ************************************************************************** //
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
            $_SESSION["tramite"]["idliquidacion"],
            "'" . $_SESSION["tramite"]["numerorecuperacion"] . "'",
            "'armadosobre'",
            "'" . $_SESSION["tramite"]["tipotramite"] . "'",
            "'" . $_SESSION["tramite"]["tipoidefirmante"] . "'",
            "'" . $_SESSION["tramite"]["identificacionfirmante"] . "'",
            "'" . $_SESSION["tramite"]["fechaexpfirmante"] . "'",
            "'" . addslashes($_SESSION["tramite"]["nombrefirmante"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["apellido1firmante"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["apellido2firmante"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["nombre1firmante"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["nombre2firmante"]) . "'",
            "'" . addslashes($_SESSION["tramite"]["emailfirmante"]) . "'",
            "'" . $_SESSION["tramite"]["celularfirmante"] . "'",
            "''",
            "'Armo el sobre con los documentos del tramite. Sobre No. " . $numsobre . "'"
        );
        $res = insertarRegistrosMysqliApi($mysqli, 'mreg_firmadoelectronico_log', $arrCampos, $arrValores);
        if ($res === false) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error almacenando log de firmado : ' . $_SESSION["generales"]["mensajeerror"];
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        // ************************************************************************** //
        // Ubicar el sobre en el repositorio
        // ************************************************************************** //
        //
        $dirx = date("Ymd");
        $path = $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/sobredigitalmreg/' . $dirx;
        if (!is_dir($path) || !is_readable($path)) {
            mkdir($path, 0777);
            \funcionesGenerales::crearIndex($path);
        }

        //
        if (file_exists($nsobre)) {

            if (copy($nsobre, $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/sobredigitalmreg/' . $dirx . '/' . $numsobre . '.pdf')) {
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
                    $_SESSION["tramite"]["idliquidacion"],
                    "'" . $_SESSION["tramite"]["numerorecuperacion"] . "'",
                    "'copiosobre'",
                    "'" . $_SESSION["tramite"]["tipotramite"] . "'",
                    "'" . $_SESSION["tramite"]["tipoidefirmante"] . "'",
                    "'" . $_SESSION["tramite"]["identificacionfirmante"] . "'",
                    "'" . $_SESSION["tramite"]["fechaexpfirmante"] . "'",
                    "'" . addslashes($_SESSION["tramite"]["nombrefirmante"]) . "'",
                    "'" . addslashes($_SESSION["tramite"]["apellido1firmante"]) . "'",
                    "'" . addslashes($_SESSION["tramite"]["apellido2firmante"]) . "'",
                    "'" . addslashes($_SESSION["tramite"]["nombre1firmante"]) . "'",
                    "'" . addslashes($_SESSION["tramite"]["nombre2firmante"]) . "'",
                    "'" . addslashes($_SESSION["tramite"]["emailfirmante"]) . "'",
                    "'" . $_SESSION["tramite"]["celularfirmante"] . "'",
                    "''",
                    "'Creo el sobre digital en el repositorio : .../sobredigitalmreg/" . $dirx . "/" . $numsobre . ".pdf'"
                );
                $res = insertarRegistrosMysqliApi($mysqli, 'mreg_firmadoelectronico_log', $arrCampos, $arrValores);
                if ($res === false) {
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["codigoerror"] = '9999';
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'Error almacenando log de firmado : ' . $_SESSION["generales"]["mensajeerror"];
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                }
            } else {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = '9999';
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No fue posible trasladar el sobre al repositorio, error en copy';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        } else {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No fue posible trasladar el sobre al repositorio, problema en generación de sobre digital (' . $nsobre . ')';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ************************************************************************** //
        // Actualiza mreg_liquidacion_sobre con el path del sobre digital
        // ************************************************************************** //
        $arrCampos = array('path');
        $arrValores = array("'sobredigitalmreg/" . $dirx . "/" . $numsobre . ".pdf'");
        regrabarRegistrosMysqliApi($mysqli, 'mreg_liquidacion_sobre', $arrCampos, $arrValores, "idsobre='" . $numsobre . "'");

        // ******************************************************************************************** //
        // Actualiza la tabla mreg_liquidacion
        // - idestado = 19
        // - firmadoelectronicamente = 'si'
        // ******************************************************************************************** //
        $_SESSION["tramite"]["idestado"] = '19';
        $_SESSION["tramite"]["firmadoelectronicamente"] = 'si';
        $_SESSION["tramite"]["numerosobredigital"] = $numsobre;
        \funcionesRegistrales::grabarLiquidacionMreg($mysqli);

        //
        $mysqli->close();

        //
        $_SESSION["jsonsalida"]["url"] = TIPO_HTTP . HTTP_HOST . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . "sobredigitalmreg/" . $dirx . "/" . $numsobre . ".pdf";
        $atx = $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . "sobredigitalmreg/" . $dirx . "/" . $numsobre . ".pdf";

        // **************************************************************************** //
        // Envío de email al firmante
        // **************************************************************************** //
        $mensaje = 'Apreciado usuario<br><br>';
        $mensaje .= 'Anexamos a este correo sobre digital del trámite de ' . $_SESSION["tramite"]["tipotramite"] . ', No. ';
        $mensaje .= $_SESSION["entrada"]["idliquidacion"] . ', firmado electrónicamente por ';
        $mensaje .= $_SESSION["tramite"]["nombrefirmante"] . ', el ' . \funcionesGenerales::mostrarFecha($fdate) . ' ';
        $mensaje .= 'a las ' . \funcionesGenerales::mostrarHora($fhora) . '.<br><br>';
        $mensaje .= 'Cordialmente<br>';
        $mensaje .= 'Area de Registros Públicos<br>';
        $mensaje .= RAZONSOCIAL . '<br><br>';
        $mensaje .= 'Este correo se envía con carácter informativo, no constituye soporte de presentación o radicación ante la Cámara ';
        $mensaje .= 'de Comercio. Para formalizar la presentación ante la Cámara de Comercio, debe surtirse el pago del servicio.';
        $attach = array();
        $attach[] = $atx;
        \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $_SESSION["entrada"]["emailcontrol"], 'Sobre digital del trámite ' . $_SESSION["entrada"]["idliquidacion"] . ' en la ' . RAZONSOCIAL, $mensaje, $attach);

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}
