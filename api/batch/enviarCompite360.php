<?php

/*
 * argv[1] : codigoEmpresa
 * argv[2] : matricula inicial
 * argv[3] : PRUEBAS O PRODUCCION
 */
session_start();
set_time_limit(0);
ini_set('display_errors', '1');
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

//
$_SESSION["generales"]["codigoempresa"] = $argv[1];
if (isset($argv[2])) {
    if ($argv[2] != 'TO') {
        $_SESSION["generales"]["matriculas"] = ltrim(trim($argv[2]), "0");
    } else {
        $_SESSION["generales"]["matriculas"] = '';
    }
} else {
    $_SESSION["generales"]["matriculas"] = '';
}
if (isset($argv[3])) {
    $_SESSION["generales"]["tipoambiente"] = $argv[3];
} else {
    $_SESSION["generales"]["tipoambiente"] = 'PRODUCCION';
}

if (isset($argv[4])) {
    $_SESSION["generales"]["cuales"] = $argv[4];
} else {
    $_SESSION["generales"]["cuales"] = 'SI';
}

$_SESSION["generales"]["batch"] = 'si';
$_SESSION["generales"]["zonahoraria"] = 'America/Bogota';
$_SESSION["generales"]["codigousuario"] = 'BATCH';
$_SESSION["generales"]["tipousuario"] = '01';
$_SESSION["generales"]["validado"] = 'SI';
$_SESSION["generales"]["idioma"] = "es";
$_SESSION["generales"]["zonahoraria"] = "America/Bogota";
date_default_timezone_set($_SESSION["generales"]["zonahoraria"]);

//
require_once ('../configuracion/common.php');
$_SESSION["generales"]["pathabsoluto"] = PATH_ABSOLUTO_SITIO;
require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesCompite360.php');
require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

//
$fechalog = date("Ymd") . '_' . date("His");
$nameLog = 'batchEnviarCompite360_' . $fechalog;
$nameLog1 = 'batchEnviarCompite360_Resumen_' . $fechalog;
$procesados = 0;
$horaini = date("H:i:s");

//
\logApi::general2($nameLog, '', 'Entro al proceso envio de matriculas a compite360');

//
$txtMatricula = '';
if ($_SESSION["generales"]["matriculas"] != '') {
    $arrMat = explode(",", $_SESSION["generales"]["matriculas"]);
    foreach ($arrMat as $m) {
        if ($txtMatricula != '') {
            $txtMatricula .= ",";
        }
        $txtMatricula .= "'" . $m . "'";
    }
}
if ($txtMatricula != '' && $txtMatricula != 'TO') {
    $query = "matricula IN (" . $txtMatricula . ")";
} else {
    if ($_SESSION["generales"]["cuales"] != 'TO') {
        $query = "matricula > '' and organizacion not in ('02','12','13','14') and categoria not in ('2','3') and compite360 not in ('SI') and (feccancelacion = '' or feccancelacion > '20171231')";
    } else {
        $query = "matricula > '' and organizacion not in ('02','12','13','14') and categoria not in ('2','3') and (feccancelacion = '' or feccancelacion > '20171231')";
    }
}

//
if ($_SESSION["generales"]["tipoambiente"] == 'PRODUCCION') {
    $mysqli = conexionMysqliApi('P-' . $_SESSION["generales"]["codigoempresa"]);
} else {
    $mysqli = conexionMysqliApi();
}

//
\logApi::general2($nameLog, '', 'Query preparado : ' . $query);
$temx = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', $query, "matricula", 'matricula,ctrestmatricula,fecmatricula,razonsocial,organizacion,categoria,feccancelacion');
if ($temx === false) {
    $mysqli->close();
    \logApi::general2($nameLog, '', 'Error recuperando mreg_est_inscritos : ' . $_SESSION["generales"]["mensajeerror"]);
    \logApi::general2($nameLog, '', 'Finaliza envío a compite360');
    \logApi::general2($nameLog, '', '---------------------------');
    exit();
}

if (empty($temx) || count($temx) == 0) {
    $mysqli->close();
    \logApi::general2($nameLog, '', 'No encontro matriculas para enviar a compite360');
    \logApi::general2($nameLog, '', 'Finaliza envío de compite360');
    \logApi::general2($nameLog, '', '----------------------------');
    exit();
}

$arrMats = array();
foreach ($temx as $tx) {
    if ($tx["ctrestmatricula"] == 'NA' || $tx["razonsocial"] == 'NO ASIGNADO' || $tx["razonsocial"] == 'NO ASIGNADA') {
        if ($tx["fecmatricula"] > '20171231') {
            $arrMats[] = $tx;
        }
    } else {
        $arrMats[] = $tx;
    }
}
unset($temx);
\logApi::general2($nameLog, '', 'Encontro ' . count($arrMats) . ' pendientes de envio a compite360');

//
// Carga arreglo con descripciones de los codigos UNSPSC
$_SESSION["compite360"]["unspsc"] = array();
$arrTem = retornarRegistrosMysqliApi($mysqli, 'mreg_unspsc', "1=1", "idcodigo");
foreach ($arrTem as $txc) {
    $_SESSION["compite360"]["unspsc"][$txc["idcodigo"]] = $txc;
}
unset($arrTem);

//
// Carga arreglo de salarios mínimos
$_SESSION["compite360"]["smlv"] = array();
$ix = 0;
$arrTem = retornarRegistrosMysqliApi($mysqli, 'bas_smlv', "1=1", "fecha");
foreach ($arrTem as $t) {
    $ix++;
    $_SESSION["compite360"]["smlv"][$ix] = $t;
}
unset($arrTem);

//
foreach ($arrMats as $m) {
    if (ltrim(trim($m["matricula"]), "0") != '') {
        $_SESSION["expediente"] = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, ltrim(trim($m["matricula"]), "0"), '', '', '', 'si', 'N');
        \logApi::general2($nameLog, '', 'Enviando ... ' . ltrim(trim($m["matricula"]), "0") . ' : Inicio');
        echo $m["matricula"] . "\r\n";
        $result = actualizarMatriculaCompite360($mysqli, $nameLog);
        $txtresult = 'D.M : ' . $result["dm"] . "|";
        $txtresult .= 'D.R. : ' . $result["dr"] . "|";
        $txtresult .= 'I.F. : ' . $result["if"] . "|";
        $txtresult .= 'R.L. : ' . $result["rl"] . "|";
        $txtresult .= 'J.D. : ' . $result["jd"] . "|";
        $txtresult .= 'EMB. : ' . $result["emb"] . "|";
        $txtresult .= 'EST. : ' . $result["est"];
        \logApi::general2($nameLog1, $m["matricula"], $txtresult);
    }
}

//
$mysqli->close();

//
\logApi::general2($nameLog, '', 'Finaliza envío a compite360');
\logApi::general2($nameLog, '', '---------------------------');
$horafin = date("H:i:s");
exit();

function actualizarMatriculaCompite360($mysqli, $nameLog1 = '') {
    include($_SESSION["generales"]["pathabsoluto"] . '/configuracion/arregloCompite360.php');

    //
    $result = array (
        'dm' => '',
        'dr' => '',
        'if' => '',
        'rl' => '',
        'jd' => '',
        'emb' => '',
        'est' => '',
        'pif' => '',
        'pcla' => '',
        'pcon' => '',
        'pexp' => ''
    );
    
    //
    if (trim($nameLog1) == '') {
        $nameLog = 'compite360Matricula_' . date("Ymd");
    } else {
        $nameLog = $nameLog1;
    }

    // Filtra si la CC tiene o no activado compite360
    if (!isset($arreglo360[$_SESSION["generales"]["codigoempresa"]])) {
        \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], ('Camara no configurada en el ambiente de compite360'));
        $_SESSION["generales"]["mensajeerror"] = 'Camara no configurada en el ambiente de compite360';
        return false;
    }

    // Filtra si la matrícula debe o no enviarse a compite360
    if (
            $_SESSION["expediente"]["organizacion"] == '02' ||
            $_SESSION["expediente"]["organizacion"] == '12' ||
            $_SESSION["expediente"]["organizacion"] == '14' ||
            $_SESSION["expediente"]["categoria"] == '2' ||
            $_SESSION["expediente"]["categoria"] == '3' ||
            substr($_SESSION["expediente"]["matricula"], 0, 1) == 'S' ||
            substr($_SESSION["expediente"]["matricula"], 0, 1) == 'N'
    ) {
        \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], ('Matricula no reportable a Compite360'));
        $_SESSION["generales"]["mensajeerror"] = 'Matricula no reportable a Compite360';
        return false;
    }

    //
    \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], ('**************************************************'));
    \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], ('************ INICIA SINCRONIZACION MATRICULA NO ' . $_SESSION["expediente"]["matricula"] . ' ***************'));
    \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], ('**************************************************'));
    \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], (''));

    //
    // $_SESSION["compite360"] = array();
    $_SESSION["compite360"]["wsdl"] = '';
    if ($_SESSION["generales"]["tipoambiente"] == 'PRODUCCION') {
        $_SESSION["compite360"]["wsdl"] = 'http://www.compite360.com/compitehtml5/WCFCompite360V2.Servicios.WsCompite360Registro.svc?singleWsdl';
    }
    if ($_SESSION["generales"]["tipoambiente"] == 'PRUEBAS') {
        $_SESSION["compite360"]["wsdl"] = 'http://test.compite360.com/compitehtml5/WCFCompite360V2.Servicios.WsCompite360Registro.svc?singleWsdl';
        // $_SESSION["compite360"]["wsdl"] = 'http://www.compite360.com/compitehtml5/WCFCompite360V2.Servicios.WsCompite360Registro.svc?singleWsdl';
    }

    //
    $_SESSION["compite360"]["metodo"] = '';
    $_SESSION["compite360"]["usuario"] = $arreglo360[$_SESSION["generales"]["codigoempresa"]]["usuario"];
    $_SESSION["compite360"]["contrasena"] = $arreglo360[$_SESSION["generales"]["codigoempresa"]]["clave"];
    $_SESSION["compite360"]["camara"] = $_SESSION["generales"]["codigoempresa"];

    //
    $cantSi = 0;
    $cant = 0;

    $cant++;
    $res = \funcionesCompite360::ReportarDatosMatricula($mysqli, $nameLog);
    if ($res) {
        $result["dm"] = 'OK';
        $cantSi++;

        $cant++;
        $res = \funcionesCompite360::ReportarRenovaciones($mysqli, $nameLog);
        if ($res) {
            $result["dr"] = 'OK';
            $cantSi++;
        } else {
            $result["dr"] = 'ERROR';
        }

        $cant++;
        $res = \funcionesCompite360::ReportarInformacionFinancieraVector($mysqli, $nameLog);
        if ($res) {
            $result["if"] = 'OK';
            $cantSi++;
        } else {
            $result["if"] = 'ERROR';
        }

        $cant++;
        $res = \funcionesCompite360::ReportarRepresentantes($mysqli, $nameLog);
        if ($res) {
            $result["rl"] = 'OK';
            $cantSi++;
        } else {
            $result["rl"] = 'ERROR';
        }

        $cant++;
        $res = \funcionesCompite360::ReportarJuntaDirectiva($mysqli, $nameLog);
        if ($res) {
            $result["jd"] = 'OK';
            $cantSi++;
        } else {
            $result["jd"] = 'ERROR';
        }

        $cant++;
        $res = \funcionesCompite360::ReportarEmbargos($mysqli, $nameLog);
        if ($res) {
            $result["emb"] = 'OK';
            $cantSi++;
        } else {
            $result["emb"] = 'ERROR';
        }

        $cant++;
        $res = \funcionesCompite360::ReportarEstablecimientos($mysqli, $nameLog);
        if ($res) {
            $result["est"] = 'OK';
            $cantSi++;
        } else {
            $result["est"] = 'ERROR';
        }

        if ($_SESSION["expediente"]["proponente"] != '') {
            $res = \funcionesCompite360::ReportarCPInsertarFinacieraVector($mysqli, $_SESSION["expediente"]["matricula"], $_SESSION["expediente"]["proponente"], $nameLog);
            if ($res) {
                $result["pif"] = 'OK';
            } else {
                $result["pif"] = 'ERROR';
            }
            $res = \funcionesCompite360::ReportarCPCLASIFICACIONES($mysqli, $_SESSION["expediente"]["matricula"], $_SESSION["expediente"]["proponente"], $nameLog);
            if ($res) {
                $result["pcla"] = 'OK';
            } else {
                $result["pcla"] = 'ERROR';
            }
            $res = \funcionesCompite360::ReportarSpCP_CONTRATOS($mysqli, $_SESSION["expediente"]["matricula"], $_SESSION["expediente"]["proponente"], $nameLog);
            if ($res) {
                $result["pcon"] = 'OK';
            } else {
                $result["pcon"] = 'ERROR';
            }
            $res = \funcionesCompite360::ReportarCP_EXPERIENCIA($mysqli, $_SESSION["expediente"]["matricula"], $_SESSION["expediente"]["proponente"], $nameLog);
            if ($res) {
                $result["pexp"] = 'OK';
            } else {
                $result["pexp"] = 'ERROR';
            }
        }
    } else {
       $result["dm"] = 'ERROR'; 
    }
    //

    if ($cantSi == $cant) {
        if ($cantSi != 0) {
            if ($_SESSION["generales"]["tipoambiente"] == 'PRODUCCION') {
                unset($_SESSION["expedienteactual"]);
                unset($_SESSION["expedienteproponente"]);
                $arrCampos = array('compite360');
                $arrValores = array("'SI'");
                $condicion = "matricula='" . $_SESSION["expediente"]["matricula"] . "'";
                $res = regrabarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', $arrCampos, $arrValores, $condicion);
                if ($res === false) {
                    \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], ('Error regrabando mreg_est_inscritos con compite360 en SI'));
                    $return = $result;
                    $_SESSION["generales"]["mensajeerror"] = 'Error regrabando mreg_est_inscritos con compite360 en SI';
                } else {
                    $return = $result;
                    $_SESSION["generales"]["mensajeerror"] = 'Actualizacion satisfactoria en compite360';
                }
            } else {
                $return = $result;
            }
        } else {
            $_SESSION["generales"]["mensajeerror"] = 'Actualizacion con errores en compite360<br><br>';
            if (isset($_SESSION["generales"]["mensajeerror1"]) && $_SESSION["generales"]["mensajeerror1"] != '') {
                $_SESSION["generales"]["mensajeerror"] .= $_SESSION["generales"]["mensajeerror1"];
            }
            $return = $result;
        }
    } else {
        $_SESSION["generales"]["mensajeerror"] = 'Actualizacion con errores en compite360';
        if (isset($_SESSION["generales"]["mensajeerror1"]) && $_SESSION["generales"]["mensajeerror1"] != '') {
            $_SESSION["generales"]["mensajeerror"] .= ' ' . $_SESSION["generales"]["mensajeerror1"];
        }
        $return = $result;
    }

    //
    \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], ('************************************************************'));
    \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], ('*********** FINALIZA SINCRONIZACION MATRICULA **************'));
    \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], ('************************************************************'));
    \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], (''));

    return $return;
}

?>