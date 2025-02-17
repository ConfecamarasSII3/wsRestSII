<?php

function armarIm7Certificado($mysqli,$numliq, $numrec, $expediente, $tipocer, $cantidad, $valor, $fecharecibo = '', $horarecibo = '', $numerorecibo = '', $numerooperacion = '', $textocertificado = '') {
    //
    if ($numliq != 0) {
        $liq = retornarRegistroMysqliApi($mysqli,"mreg_liquidacion","idliquidacion=" . $numliq);
    } else {
        $liq = array();
    }

    //
    if (($liq === 0) || ($liq === false) || (empty($liq)) || (ltrim($liq["idliquidacion"], "0") == '')) {
        $liq = array();
        $liq["fecharecibo"] = $fecharecibo;
        $liq["horarecibo"] = $horarecibo;
        $liq["numerorecibo"] = $numerorecibo;
        $liq["numerooperacion"] = $numerooperacion;
        $liq["nombrecliente"] = '';
        $liq["idusuario"] = '';
        $liq["cargoafiliacion"] = 'NO';
        $liq["cargogastoadministrativo"] = 'NO';
        $liq["cargoentidadoficial"] = 'NO';
    } else {
        $liq["numeroliquidacion"] = $liq["idliquidacion"];
    }

    $tipox = '';
    switch ($tipocer) {
        case "CerMat": $tipox = '01';
            break;
        case "CerExi": $tipox = '02';
            break;
        case "CerEsadl": $tipox = '06';
            break;
        case "CerLibRegMer": $tipox = '04';
            break;
        case "CerLibEsadl": $tipox = '04';
            break;
        case "CerPro": $tipox = '07';
            break;
    }

    //
    if (!defined('PUERTO_HTTP')) {
        define('PUERTO_HTTP', '80');
    }
    if (ltrim(PUERTO_HTTP, "0") == '') {
        $puerto = '80';
    } else {
        $puerto = PUERTO_HTTP;
    }

    //
    $tipohttp = TIPO_HTTP;
    $idcertificado = $liq["numerooperacion"] . '-' . \funcionesGenerales::generarAleatorioAlfanumerico8($mysqli);

    //
    list($tipohttp1, $tipohttp2) = explode(':', $tipohttp);

    //
    $tipoimpresion = 'hojamembrete';

    //
    $txtCertificado = '';
    $txtCertificado .= encriptarCadenaIm7('@@@@TIPOARCHIVO=Certificados') . "|";
    $txtCertificado .= encriptarCadenaIm7('##PROGRAMA##genIm7Certificados') . "|";
    $txtCertificado .= encriptarCadenaIm7('##FECHA##' . $liq["fecharecibo"]) . "|";
    $txtCertificado .= encriptarCadenaIm7('##HORA##' . $liq["horarecibo"]) . "|";
    $txtCertificado .= encriptarCadenaIm7('##NUMERORECIBO##' . $liq["numerorecibo"]) . "|";
    $txtCertificado .= encriptarCadenaIm7('##NUMEROOPERACION##' . $liq["numerooperacion"]) . "|";
    $txtCertificado .= encriptarCadenaIm7('##NOMBRECLIENTE##' . $liq["nombrecliente"]) . "|";
    $txtCertificado .= encriptarCadenaIm7('##USUARIO##' . $liq["idusuario"]) . "|";
    $txtCertificado .= encriptarCadenaIm7('##NUMEROINTERNO##') . "|";
    $txtCertificado .= encriptarCadenaIm7('##NUMEROUNICO##') . "|";
    $txtCertificado .= encriptarCadenaIm7('##RAZONSOCIAL##' . $liq["nombrecliente"]) . "|";
    $txtCertificado .= encriptarCadenaIm7('##CANTIDAD##' . sprintf("%03s", $cantidad)) . "|";
    $txtCertificado .= encriptarCadenaIm7('##TIPOCERTIFICADO##' . sprintf("%02s", $tipox)) . "|";

    //
    if ($liq["cargoafiliacion"] == 'SI') {
        $txtCertificado .= encriptarCadenaIm7('##TIPOGASTO##' . '2') . "|";
    } else {
        if ($liq["cargogastoadministrativo"] == 'SI') {
            $txtCertificado .= encriptarCadenaIm7('##TIPOGASTO##' . '1') . "|";
        } else {
            if ($liq["cargoentidadoficial"] == 'SI') {
                $txtCertificado .= encriptarCadenaIm7('##TIPOGASTO##' . '3') . "|";
            } else {
                $txtCertificado .= encriptarCadenaIm7('##TIPOGASTO##' . '0') . "|";
            }
        }
    }

    //
    $txtCertificado .= encriptarCadenaIm7('##VALORCERTIFICADO##' . $valor) . "|";
    $txtCertificado .= encriptarCadenaIm7('##CODIGOEMPRESA##' . $_SESSION["generales"]["codigoempresa"]) . "|";
    $txtCertificado .= encriptarCadenaIm7('##TIPOCONEXION##' . $tipohttp) . "|";
    $txtCertificado .= encriptarCadenaIm7('##PUERTO##' . $puerto) . "|";
    $txtCertificado .= encriptarCadenaIm7('##IDCERTIFICADO##' . $idcertificado) . "|";

    //
    $txtCerti = explode('[FINLINEA]', base64_decode($textocertificado));

    //
    foreach ($txtCerti as $txt) {
        if (substr($txt, 0, 21) == '##ULTIMOANORENOVADO##') {
            $txt1 = substr($txt, 21, 4);
            $txtCertificado .= encriptarCadenaIm7('##ULTIMOANORENOVADO##' . $txt1) . "|";
        } else {
            $txtCertificado .= encriptarCadenaIm7($txt) . "|";
        }
    }

    //
    $arrCampos = array(
        'id',
        'recibo',
        'operacion',
        'fecha',
        'hora',
        'estado',
        'contenido'
    );
    $arrValores = array(
        "'" . $idcertificado . "'",
        "'" . $liq["numerorecibo"] . "'",
        "'" . $liq["numerooperacion"] . "'",
        "'" . $liq["fecharecibo"] . "'",
        "'" . $liq["horarecibo"] . "'",
        "'0'",
        "'" . addslashes($txtCertificado) . "'"
    );
    insertarRegistrosMysqliApi($mysqli,'mreg_certificados_expedidos', $arrCampos, $arrValores);

    // Disparador de impresor
    $name = '../tmp/' . $idcertificado . '.im7';

    //
    $tipohttp = TIPO_HTTP;
    $puerto = PUERTO_HTTP;
    $urldominio = HTTP_HOST;
    
    $tipohttp = 'http://';
    $puerto = 80;


    if ($_SESSION["generales"]["codigoempresa"] == '55') {
        $tipohttp = 'http://';
        $puerto = '80';
        $urldominio = 'enlinea.ccas.org.co';
    }


    //
    $gestor = fopen($name, "wb");
    fwrite($gestor, encriptarCadenaIm7('@@@@TIPOARCHIVO=DisparadorCertificados') . chr(13) . chr(10));
    fwrite($gestor, encriptarCadenaIm7('##URLDOMINIO##' . $urldominio) . chr(13) . chr(10));
    fwrite($gestor, encriptarCadenaIm7('##ARCHIVO##' . $idcertificado . '.im7') . chr(13) . chr(10));
    fwrite($gestor, encriptarCadenaIm7('##CODIGOEMPRESA##' . $_SESSION["generales"]["codigoempresa"]) . chr(13) . chr(10));
    fwrite($gestor, encriptarCadenaIm7('##TIPOCONEXION##' . $tipohttp) . chr(13) . chr(10));
    fwrite($gestor, encriptarCadenaIm7('##PUERTO##' . $puerto) . chr(13) . chr(10));
    fwrite($gestor, encriptarCadenaIm7('##IDCERTIFICADO##' . $idcertificado) . chr(13) . chr(10));
    fwrite($gestor, encriptarCadenaIm7('##TIPOIMPRESION##' . $tipoimpresion) . chr(13) . chr(10));
    fclose($gestor);

    //
    unset($gestor);
    return $name;
}

function armarIm7CertificadoHojaBlanca($mysqli,$numliq, $numrec, $numerorecibo, $razonsocial) {

    // Disparador de impresor
    $name = '../tmp/controlImpresion' . $numerorecibo . '.im7';

    //
    $urldominio = HTTP_HOST;    
    $tipohttp = 'http://';
    $puerto = 80;

    //
    $gestor = fopen($name, "wb");
    fwrite($gestor, encriptarCadenaIm7('@@@@TIPOARCHIVO=DisparadorCertificadosHojaBlanca') . chr(13) . chr(10));
    fwrite($gestor, encriptarCadenaIm7('##URLDOMINIO##' . $urldominio) . chr(13) . chr(10));
    fwrite($gestor, encriptarCadenaIm7('##ARCHIVO##' . $numerorecibo . '.im7') . chr(13) . chr(10));
    fwrite($gestor, encriptarCadenaIm7('##CODIGOEMPRESA##' . $_SESSION["generales"]["codigoempresa"]) . chr(13) . chr(10));
    fwrite($gestor, encriptarCadenaIm7('##TIPOCONEXION##' . $tipohttp) . chr(13) . chr(10));
    fwrite($gestor, encriptarCadenaIm7('##PUERTO##' . $puerto) . chr(13) . chr(10));
    fwrite($gestor, encriptarCadenaIm7('##RECIBO##' . $numerorecibo) . chr(13) . chr(10));
    fwrite($gestor, encriptarCadenaIm7('##RAZONSOCIAL##' . $razonsocial) . chr(13) . chr(10));
    fclose($gestor);

    //
    unset($gestor);
    return $name;
}

?>