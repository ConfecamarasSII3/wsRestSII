<?php

/**
 * Genera archicvos en formato im7 que son entendibles para impresorVb6
 * -
 * @param 	string		$numliq		Número de liquidación
 */
function encriptarCadenaIm7($cadena) {
    $arrayEncripcion = array(
        ' ' => '[$3h]',
        '-' => '[ght]',
        '=' => '[#k=]',
        '+' => '[)($]',
        '_' => '[320]',
        '?' => '[21$]',
        'ø' => '[82$]',
        '@' => '[>%4]',
        '"' => '[81?]',
        '&' => '[550]',
        '$' => '[=)9]',
        '%' => '[pt7]',
        '/' => '[050]',
        '\\' => '[?&$]',
        '\'' => '[rt2]',
        '`' => '[rt2]',
        "(" => '[17&]',
        ")" => '[1&7]',
        "!" => '[%2%]',
        "°" => '[2%2]',
        "," => '[pt1]',
        ";" => '[pt2]',
        ":" => '[pt3]',
        "." => '[tp6]',
        "[" => '[pt4]',
        "]" => '[pt5]',
        "*" => '[pt6]',
        "#" => '[a3.]',
        '0' => '[asz]',
        '1' => '[i9q]',
        '2' => '[q9i]',
        '3' => '[%#2]',
        '4' => '[?;j]',
        '5' => '[r0*]',
        '6' => '[**4]',
        '7' => '[m$2]',
        '8' => '[$2M]',
        '9' => '[!*5]',
        'a' => '[07x]',
        '·' => '[(7#]',
        'b' => '[x05]',
        'c' => '[31a]',
        'd' => '[3a1]',
        'e' => '[bb9]',
        'È' => '[#7)]',
        'f' => '[9bb]',
        'g' => '[b9b]',
        'h' => '[9b9]',
        'i' => '[7xO]',
        'Ì' => '[$=6]',
        'j' => '[Zw3]',
        'k' => '[AA1]',
        'l' => '[Q6&]',
        'm' => '[w0A]',
        'n' => '[A0W]',
        'Ò' => '[n&f]',
        'o' => '[qwe]',
        'Û' => '[?0=]',
        'p' => '[i0d]',
        'q' => '[9zB]',
        'r' => '[23W]',
        's' => '[09g]',
        't' => '[DFa]',
        'u' => '[fdA]',
        '˙' => '[!&%]',
        'v' => '[pR2]',
        'w' => '[zq6]',
        'x' => '[(4$]',
        'y' => '[005]',
        'z' => '[x$2]',
        'A' => '[007x]',
        '¡' => '[(7##]',
        'B' => '[x005]',
        'C' => '[331a]',
        'D' => '[3aa1]',
        'E' => '[bb9b]',
        '…' => '[#77)]',
        'F' => '[9bbc]',
        'G' => '[b9b1]',
        'H' => '[9b93]',
        'I' => '[7xO0]',
        'Õ' => '[0?66]',
        'J' => '[Zw3w]',
        'K' => '[AA1q]',
        'L' => '[Q6&%]',
        'M' => '[w0A3]',
        'N' => '[A0Wi]',
        '—' => '[n&fl]',
        'O' => '[qwe3]',
        '”' => '[?0=&]',
        'P' => '[i0dd]',
        'Q' => '[9zBg]',
        'R' => '[23W8]',
        'S' => '[09gqm]',
        'T' => '[DFae]',
        'U' => '[fdA$]',
        '⁄' => '[!&%1]',
        'V' => '[pR2e]',
        'W' => '[zq6o]',
        'X' => '[(4$0]',
        'Y' => '[005r]',
        'Z' => '[x$22]'
    );

    $Encriptada = "";
    $cant = 0;
    for ($i = 0; $i < strlen($cadena); $i++) {
        $cant++;
        try {
            if (isset($arrayEncripcion[substr($cadena, $i, 1)])) {
                $char = $arrayEncripcion[substr($cadena, $i, 1)];
            } else {
                $char = '[' . substr($cadena, $i, 1) . ']';
            }
        } catch (Exception $e) {
            $char = "[" . substr($cadena, $i, 1) . "]";
        }
        if ($cant == 1) {
            $Encriptada = $char;
        } else {
            $Encriptada .= "/" . $char;
        }
    }
    return $Encriptada;
}

/**
 * 
 * Genera im7 con el recibo de caja
 * @param $numliq		Número de la liquidacion
 * 						Se utiliza cuando la liquidacin ha quedado guardada en mreg_liquidacion
 * @param $tiposalida	Tipo de salida
 * 						* D: Dipslay - Pantalla - No almacena en el repositorio
 * 						* I: Impresión - Almacena en el repositorio
 * 						* A: Archivo: Almacena en el repositorio 
 * @param $orig			Indica si es original (SI) o copia (NO)
 * @param $arreglo		Arreglo de datos delr ecibo cuando este no se almacena en mreg_liquidacion
 * 						* numerorecibo
 * 						* numerooperacion
 * 						* numerorecuperacion
 * 						* fecharecibo
 * 						* horarecibo
 * 						* tipoid
 * 						* identificacion
 * 						* nombre
 * 						* direccion
 * 						* telefono
 * 						* municipio
 * 						* valoriva
 * 						* valortotal
 * 						* formapago
 * 							- 01 efectivo
 * 							- 02 cheque		
 * 							- 03 t.debito
 * 							- 04 PSE /ACH
 * 							- 05 Visa
 * 							- 06 Mastercard
 * 							- 07 American
 * 							- 08 Diners
 * 							- 09 Credencial
 * 							- 10 Consignación
 * 						* numcheque
 * 						* codbanco
 * 						* alertavalor
 * 						* facturacancelada
 * 						* renglones (los servicios incluidos en el pago)
 * 							* servicio
 * 							* nombre
 * 							* expediente
 * 							* ano
 * 							* cantidad
 * 							* valorbase
 * 							* valor
 * 					
 */
function armarIm7Recibo($mysqli, $numliq, $tiposalida = 'D', $orig = 'SI', $arreglo = array(), $claveprepago = '', $saldoprepago = '', $cajero = '', $reimpresion = 'NO', $tiporecibo = 'S') {
    if ($numliq != 0) {
        $liq = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion', "idliquidacion=" . $numliq);
        $vueltas = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion_campos', "idliquidacion=" . $numliq . " and campo='vueltas'", "contenido");
        if ($vueltas == '') {
            $vueltas = 0;
        }
        $det = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidaciondetalle', "idliquidacion=" . $numliq, "idliquidacion,secuencia");
        $exp = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidacionexpedientes', "idliquidacion=" . $numliq, "idliquidacion, secuencia");
        $rec = retornarRegistroMysqliApi($mysqli, 'mreg_recibosgenerados', "recibo='" . $liq["numerorecibo"] . "'");
        $numunicorue = '';
        $rrue = retornarRegistroMysqliApi($mysqli, 'mreg_rue_radicacion', "recibolocal='" . $liq["numerorecibo"] . "'");
        if ($rrue && !empty($rrue)) {
            $numunicorue = trim($rrue["numerounicoconsulta"]);
        }

        if ($tiporecibo == 'S') {
            $name = '../tmp/' . $numliq . '-Recibo-' . $liq["numerorecibo"] . '.im7';
        } else {
            $name = '../tmp/' . $numliq . '-Recibo-' . $liq["numerorecibogob"] . '.im7';
        }
        $gestor = fopen($name, "wb");
        fwrite($gestor, encriptarCadenaIm7('@@@@TIPOARCHIVO=Recibo') . chr(13) . chr(10));
        fwrite($gestor, encriptarCadenaIm7('##PROGRAMA##genIm7Recibo') . chr(13) . chr(10));
        fwrite($gestor, encriptarCadenaIm7('##FECHA##' . $liq["fecharecibo"]) . chr(13) . chr(10));
        fwrite($gestor, encriptarCadenaIm7('##HORA##' . $liq["horarecibo"]) . chr(13) . chr(10));
        if ($tiporecibo == 'S') {
            fwrite($gestor, encriptarCadenaIm7('##NUMERORECIBO##' . $liq["numerorecibo"]) . chr(13) . chr(10));
            fwrite($gestor, encriptarCadenaIm7('##NUMEROOPERACION##' . $liq["numerooperacion"]) . chr(13) . chr(10));
        } else {

            fwrite($gestor, encriptarCadenaIm7('##NUMERORECIBO##' . $liq["numerorecibogob"]) . chr(13) . chr(10));
            fwrite($gestor, encriptarCadenaIm7('##NUMEROOPERACION##' . $liq["numerooperaciongob"]) . chr(13) . chr(10));
        }
        fwrite($gestor, encriptarCadenaIm7('##NOMBRECLIENTE##' . $liq["nombrecliente"]) . chr(13) . chr(10));
        fwrite($gestor, encriptarCadenaIm7('##USUARIO##' . $liq["idusuario"]) . chr(13) . chr(10));
        fwrite($gestor, encriptarCadenaIm7('##NUMEROINTERNO##') . chr(13) . chr(10));
        fwrite($gestor, encriptarCadenaIm7('##NUMEROUNICO##') . chr(13) . chr(10));
        fwrite($gestor, encriptarCadenaIm7('##REIMPRESION##' . $reimpresion) . chr(13) . chr(10));
        //
        // Cuerpo del recibo
        //
        $fpago = '';
        if ($liq["pagoefectivo"] != 0) {
            $fpago = 'Efectivo';
        }
        if ($liq["pagocheque"] != 0) {
            $fpago = 'En cheque';
        }
        if ($liq["pagoconsignacion"] != 0) {
            $fpago = 'En consignación';
        }
        if ($liq["pagoqr"] != 0) {
            $fpago = 'En QR';
        }
        if ($liq["pagotdebito"] != 0) {
            $fpago = 'Tarj. Débito';
        }
        if ($liq["pagoach"] != 0) {
            $fpago = 'Sistema ACH';
        }
        if ($liq["pagovisa"] != 0) {
            $fpago = 'Tarj. Crédito';
        }
        if ($liq["pagomastercard"] != 0) {
            $fpago = 'Tarj. Crédito';
        }
        if ($liq["pagocredencial"] != 0) {
            $fpago = 'Tarj. Crédito';
        }
        if ($liq["pagodiners"] != 0) {
            $fpago = 'Tarj. Crédito';
        }
        if ($liq["pagoamerican"] != 0) {
            $fpago = 'Tarj. Crédito';
        }
        if ($liq["pagoprepago"] != 0) {
            $fpago = 'Cargo a prepago';
        }

        if ($orig != 'SI') {
            fwrite($gestor, encriptarCadenaIm7('*** ESTA ES UNA COPIA DEL ORIGINAL ***') . chr(13) . chr(10));
        }
        fwrite($gestor, encriptarCadenaIm7('FECHA: ' . \funcionesGenerales::mostrarFecha($liq["fecharecibo"])) . chr(13) . chr(10));
        fwrite($gestor, encriptarCadenaIm7('OPERAC.: ' . $liq["numerooperacion"]) . chr(13) . chr(10));
        fwrite($gestor, encriptarCadenaIm7('NUM.REC: ' . sprintf("%-6s", $liq["numerorecuperacion"]) . '  RECIBO NO. ' . $liq["numerorecibo"]) . chr(13) . chr(10));
        fwrite($gestor, encriptarCadenaIm7('NUM.RAD: ' . sprintf("%-6s", $liq["numeroradicacion"])) . chr(13) . chr(10));
        fwrite($gestor, encriptarCadenaIm7('HORA: ' . \funcionesGenerales::mostrarHora($liq["horarecibo"]) . '      PAGINA 1 DE 1') . chr(13) . chr(10));
        if ($numunicorue != '') {
            fwrite($gestor, encriptarCadenaIm7('NUM. UNICO RUES: ' . $numunicorue) . chr(13) . chr(10));
        }

        //
        fwrite($gestor, encriptarCadenaIm7('USUARIO: ' . sprintf("%-8s", $liq["idusuario"])) . chr(13) . chr(10));
        fwrite($gestor, encriptarCadenaIm7('-------------------------------------') . chr(13) . chr(10));
        fwrite($gestor, encriptarCadenaIm7('MAT/INSC: (' . $det[1]["expediente"] . ')') . chr(13) . chr(10));
        if ($liq && !empty($liq)) {
            if ($liq["nombrebase"] != '') {
                fwrite($gestor, encriptarCadenaIm7(sprintf("%-33s", substr($liq["nombrebase"], 0, 33))) . chr(13) . chr(10));
            }
        }

        //
        fwrite($gestor, encriptarCadenaIm7('-------------------------------------') . chr(13) . chr(10));
        fwrite($gestor, encriptarCadenaIm7(sprintf("%-33s", substr(\funcionesGenerales::utf8_decode($liq["nombrecliente"] . ' ' . $liq["apellidocliente"]), 0, 33))) . chr(13) . chr(10));
        fwrite($gestor, encriptarCadenaIm7('NIT/CC: ' . $liq["identificacioncliente"] . '  RUE: ') . chr(13) . chr(10));
        fwrite($gestor, encriptarCadenaIm7('Email: ' . $liq["email"]) . chr(13) . chr(10));

        //
        fwrite($gestor, encriptarCadenaIm7('FORMA DE PAGO: ' . $fpago) . chr(13) . chr(10));
        fwrite($gestor, encriptarCadenaIm7('DESCRIPCION       DET.          VALOR') . chr(13) . chr(10));
        fwrite($gestor, encriptarCadenaIm7('----------------- ---- --------------') . chr(13) . chr(10));
        $totrecibo = 0;
        foreach ($det as $dt) {
            $serv = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $dt["idservicio"] . "'");
            $incluir = '';
            if (!defined('SEPARAR_RECIBOS') || SEPARAR_RECIBOS == 'NO') {
                $incluir = 'si';
            } else {
                if (defined('SEPARAR_RECIBOS') && SEPARAR_RECIBOS == 'SI' && $tiporecibo == 'S' && ltrim((string) $serv["conceptodepartamental"], "0") == '') {
                    $incluir = 'si';
                }
                if (defined('SEPARAR_RECIBOS') && SEPARAR_RECIBOS == 'SI' && $tiporecibo == 'G' && ltrim((string) $serv["conceptodepartamental"], "0") != '') {
                    $incluir = 'si';
                }
            }
            if (trim($serv["descripcioncorta"]) != '') {
                $servtxt = sprintf("%-17s", substr($serv["descripcioncorta"], 0, 17));
            } else {
                $servtxt = sprintf("%-17s", substr($serv["nombre"], 0, 17));
            }
            if (trim($dt["ano"]) != '') {
                $canttxt = $dt["ano"];
            } else {
                $canttxt = sprintf("%4s", $dt["cantidad"]);
            }
            $valtxt = sprintf("%14s", number_format($dt["valorservicio"], 0));
            if ($incluir == 'si') {
                $totrecibo = $totrecibo + $dt["valorservicio"];
                fwrite($gestor, encriptarCadenaIm7($servtxt . ' ' . $canttxt . ' ' . $valtxt) . chr(13) . chr(10));
                if (doubleval($dt["valorbase"]) != 0) {
                    fwrite($gestor, encriptarCadenaIm7('Valor base:' . number_format($dt["valorbase"])) . chr(13) . chr(10));
                }
            }
        }
        fwrite($gestor, encriptarCadenaIm7('----------------- ---- --------------') . chr(13) . chr(10));

        if (($liq["cargogastoadministrativo"] == 'SI') || ($liq["cargoentidadoficial"] == 'SI')) {
            $servtxt = sprintf("%-17s", '*** TOTAL RECIBO');
            $canttxt = sprintf("%4s", ' ');
            $valtxt = sprintf("%14s", '0');
            fwrite($gestor, encriptarCadenaIm7($servtxt . ' ' . $canttxt . ' ' . $valtxt) . chr(13) . chr(10));
            if ($liq["cargogastoadministrativo"] == 'SI') {
                fwrite($gestor, encriptarCadenaIm7('*** SIN COSTO PARA EL CLIENTE ***') . chr(13) . chr(10));
            }
            if ($liq["cargoentidadoficial"] == 'SI') {
                fwrite($gestor, encriptarCadenaIm7('*** SIN COSTO PARA LA ENTIDAD ***') . chr(13) . chr(10));
            }
        } else {
            $servtxt = sprintf("%-17s", '*** TOTAL RECIBO');
            $canttxt = sprintf("%4s", ' ');
            if ($liq["cargoafiliacion"] == 'SI') {
                $valtxt = sprintf("%14s", '0');
            } else {
                $valtxt = sprintf("%14s", number_format($totrecibo, 0));
            }
            fwrite($gestor, encriptarCadenaIm7($servtxt . ' ' . $canttxt . ' ' . $valtxt) . chr(13) . chr(10));
            if ($liq["cargoafiliacion"] == 'SI') {
                fwrite($gestor, encriptarCadenaIm7('*** CON CARGO A CUPO AFILIADOS ***') . chr(13) . chr(10));
            }
            if ($liq["pagoprepago"] != 0) {
                fwrite($gestor, encriptarCadenaIm7('*** CON CARGO AL CUPO DE PREPAGO ***') . chr(13) . chr(10));
            }
            if ($liq["cargoafiliacion"] != 'SI' && $liq["pagoprepago"] == 0) {
                if ($vueltas > 0) {
                    if (!defined('SEPARAR_RECIBOS') && SEPARAR_RECIBOS == 'NO') {
                        $servtxt = sprintf("%-17s", '*** TOTAL PAGADO');
                        $canttxt = sprintf("%4s", ' ');
                        $val = $totrecibo + $vueltas;
                        $valtxt = sprintf("%14s", number_format($val, 0));
                        fwrite($gestor, encriptarCadenaIm7($servtxt . ' ' . $canttxt . ' ' . $valtxt) . chr(13) . chr(10));
                        $servtxt = sprintf("%-17s", '*** VUELTAS');
                        $canttxt = sprintf("%4s", ' ');
                        $valtxt = sprintf("%14s", number_format($vueltas, 0));
                        fwrite($gestor, encriptarCadenaIm7($servtxt . ' ' . $canttxt . ' ' . $valtxt) . chr(13) . chr(10));
                    }
                }
            }
        }

        //
        if (!defined('SEPARAR_RECIBOS') || SEPARAR_RECIBOS == 'NO') {
            $servcam = 0;
            $servgob = 0;
            $servtot = 0;
            foreach ($_SESSION["tramite"]["liquidacion"] as $dt) {
                if (isset($dt["idservicio"]) && $dt["idservicio"] != '') {
                    $serv = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $dt["idservicio"] . "'");
                    if (ltrim(trim((string) $serv["conceptodepartamental"]), "0") != '') {
                        $servgob = $servgob + $dt["valorservicio"];
                    } else {
                        $servcam = $servcam + $dt["valorservicio"];
                    }
                    $servtot = $servtot + $dt["valorservicio"];
                }
            }

            if ($servgob != 0) {
                if ($_SESSION["generales"]["codigoempresa"] == '55') {
                    if ($rec["tipogasto"] == '7') {
                        fwrite($gestor, encriptarCadenaIm7('-------------------------------------') . chr(13) . chr(10));
                        fwrite($gestor, encriptarCadenaIm7('Le informamos  que  con  este  recibo') . chr(13) . chr(10));
                        fwrite($gestor, encriptarCadenaIm7('usted esta pagando:') . chr(13) . chr(10));
                        fwrite($gestor, encriptarCadenaIm7('') . chr(13) . chr(10));
                        fwrite($gestor, encriptarCadenaIm7('- IMPUESTO DE REGISTRO  a favor de la') . chr(13) . chr(10));
                        fwrite($gestor, encriptarCadenaIm7('Gobernacion correspondiente: $' . number_format($servgob, 0)) . chr(13) . chr(10));
                        fwrite($gestor, encriptarCadenaIm7('') . chr(13) . chr(10));
                        fwrite($gestor, encriptarCadenaIm7('- TRÁMITE REGISTRAL  ante  Cámara  de') . chr(13) . chr(10));
                        fwrite($gestor, encriptarCadenaIm7('Comercio Aburra Sur: $' . number_format($servcam, 0)) . chr(13) . chr(10));
                    } else {
                        fwrite($gestor, encriptarCadenaIm7('-------------------------------------') . chr(13) . chr(10));
                        fwrite($gestor, encriptarCadenaIm7('Le informamos  que  con  este  recibo') . chr(13) . chr(10));
                        fwrite($gestor, encriptarCadenaIm7('usted esta pagando:') . chr(13) . chr(10));
                        fwrite($gestor, encriptarCadenaIm7('') . chr(13) . chr(10));
                        fwrite($gestor, encriptarCadenaIm7('- IMPUESTO DE REGISTRO  a favor de la') . chr(13) . chr(10));
                        fwrite($gestor, encriptarCadenaIm7('Gobernacion de Antioquia: $' . number_format($servgob, 0)) . chr(13) . chr(10));
                        fwrite($gestor, encriptarCadenaIm7('') . chr(13) . chr(10));
                        fwrite($gestor, encriptarCadenaIm7('- TRÁMITE REGISTRAL  ante  Cámara  de') . chr(13) . chr(10));
                        fwrite($gestor, encriptarCadenaIm7('Comercio Aburra Sur: $' . number_format($servcam, 0)) . chr(13) . chr(10));
                    }
                } else {
                    fwrite($gestor, encriptarCadenaIm7('-------------------------------------') . chr(13) . chr(10));
                    fwrite($gestor, encriptarCadenaIm7('Queremos informarle que con este Reci') . chr(13) . chr(10));
                    fwrite($gestor, encriptarCadenaIm7('bo de Pago, por un total $' . number_format($servtot, 0) . ',') . chr(13) . chr(10));
                    fwrite($gestor, encriptarCadenaIm7('Usted   no   solo   esta  pagando  lo') . chr(13) . chr(10));
                    fwrite($gestor, encriptarCadenaIm7('correspondiente al  tramite registral') . chr(13) . chr(10));
                    fwrite($gestor, encriptarCadenaIm7('ante    la   Camara   de    Comercio,') . chr(13) . chr(10));
                    fwrite($gestor, encriptarCadenaIm7('equivalente a $' . number_format($servcam, 0) . ', ') . chr(13) . chr(10));
                    fwrite($gestor, encriptarCadenaIm7('sino tambien el Impuesto de  Registro') . chr(13) . chr(10));
                    fwrite($gestor, encriptarCadenaIm7('para la  Gobernacion,  por  valor  de') . chr(13) . chr(10));
                    fwrite($gestor, encriptarCadenaIm7('$' . number_format($servgob, 0) . ',') . chr(13) . chr(10));
                    fwrite($gestor, encriptarCadenaIm7('en calidad  de Sujeto Activo.') . chr(13) . chr(10));
                }
            }
        }

        //
        if (trim($claveprepago) != '') {
            fwrite($gestor, encriptarCadenaIm7('----------------- ---- --------------') . chr(13) . chr(10));
            fwrite($gestor, encriptarCadenaIm7('Clave prepago: ' . $claveprepago) . chr(13) . chr(10));
        }

        //
        if (doubleval($saldoprepago) != 0) {
            fwrite($gestor, encriptarCadenaIm7('----------------- ---- --------------') . chr(13) . chr(10));
            fwrite($gestor, encriptarCadenaIm7('Saldoprepago: ' . $saldoprepago) . chr(13) . chr(10));
        }

        //
        if (trim($liq["numeroradicacion"]) != '') {
            switch ($_SESSION["generales"]["codigoempresa"]) {
                case "20" :
                    fwrite($gestor, encriptarCadenaIm7('----------------- ---- --------------') . chr(13) . chr(10));
                    fwrite($gestor, encriptarCadenaIm7('Para informacion sobre este(os) docu-') . chr(13) . chr(10));
                    fwrite($gestor, encriptarCadenaIm7('mento(s)  comuniquese  al  8962121  o') . chr(13) . chr(10));
                    fwrite($gestor, encriptarCadenaIm7('consulte  en  www.ccmpc.org.co,  link') . chr(13) . chr(10));
                    fwrite($gestor, encriptarCadenaIm7('servicios, servicios en linea, consul') . chr(13) . chr(10));
                    fwrite($gestor, encriptarCadenaIm7('ta estado de tramites, alli digite el') . chr(13) . chr(10));
                    fwrite($gestor, encriptarCadenaIm7('siguiente numero:') . chr(13) . chr(10));
                    fwrite($gestor, encriptarCadenaIm7($liq["numeroradicacion"]) . chr(13) . chr(10));
                    break;

                case "51" :
                    fwrite($gestor, encriptarCadenaIm7('----------------- ---- --------------') . chr(13) . chr(10));
                    fwrite($gestor, encriptarCadenaIm7('Codigo de barras: ' . $liq["numeroradicacion"]) . chr(13) . chr(10));
                    fwrite($gestor, encriptarCadenaIm7('----------------- ---- --------------') . chr(13) . chr(10));
                    fwrite($gestor, encriptarCadenaIm7('Para conocer el estado de su  tramite') . chr(13) . chr(10));
                    fwrite($gestor, encriptarCadenaIm7('ir a: https://ccoa.org.co/consultar-e') . chr(13) . chr(10));
                    fwrite($gestor, encriptarCadenaIm7('stado-tramite/') . chr(13) . chr(10));
                    break;

                default :
                    fwrite($gestor, encriptarCadenaIm7('----------------- ---- --------------') . chr(13) . chr(10));
                    fwrite($gestor, encriptarCadenaIm7('Codigo de barras: ' . $liq["numeroradicacion"]) . chr(13) . chr(10));
                    fwrite($gestor, encriptarCadenaIm7('----------------- ---- --------------') . chr(13) . chr(10));
                    fwrite($gestor, encriptarCadenaIm7('Para conocer el estado de su  tramite') . chr(13) . chr(10));
                    fwrite($gestor, encriptarCadenaIm7('ir a: https://sii.confecamaras.co/vis') . chr(13) . chr(10));
                    fwrite($gestor, encriptarCadenaIm7('ta/plantilla/consultarSolicitudes.php') . chr(13) . chr(10));
                    fwrite($gestor, encriptarCadenaIm7('?empresa=' . $_SESSION["generales"]["codigoempresa"]) . chr(13) . chr(10));
                    break;
            }
        }

        //
        if (trim($claveprepago) != '') {
            fwrite($gestor, encriptarCadenaIm7('Clave prepago: ' . $claveprepago) . chr(13) . chr(10));
        }

        //
        if ($liq["valortotal"] != 0) {
            if (defined('CFE_FECHA_INICIAL') && CFE_FECHA_INICIAL != '' && date("Ymd") >= CFE_FECHA_INICIAL) {
                fwrite($gestor, encriptarCadenaIm7('----------------- ---- --------------') . chr(13) . chr(10));
                fwrite($gestor, encriptarCadenaIm7('La factura electronica  sera  enviada') . chr(13) . chr(10));
                fwrite($gestor, encriptarCadenaIm7('al correo electronico  reportado  por') . chr(13) . chr(10));
                fwrite($gestor, encriptarCadenaIm7('el cliente. En caso  de no recibirla,') . chr(13) . chr(10));
                fwrite($gestor, encriptarCadenaIm7('por favor comunicarse al ' . TELEFONO_ATENCION_USUARIOS) . chr(13) . chr(10));
                if (defined('CFE_EMAIL_ATENCION_USUARIOS') && CFE_EMAIL_ATENCION_USUARIOS != '') {
                    fwrite($gestor, encriptarCadenaIm7('o al correo:') . chr(13) . chr(10));
                    fwrite($gestor, encriptarCadenaIm7(CFE_EMAIL_ATENCION_USUARIOS) . chr(13) . chr(10));
                }
            }
        }

        //		
        fclose($gestor);
        unset($gestor);

        //
        if ($tiporecibo == 'S') {
            $name1 = '../tmp/' . $numliq . '-Recibo-' . $liq["numerorecibo"] . '.tx1';
        } else {
            $name1 = '../tmp/' . $numliq . '-Recibo-' . $liq["numerorecibogob"] . '.tx1';
        }
        // $name1 = '../tmp/' . $numliq . '-Recibo-' . $liq["numerorecibo"] . '.tx1';
        $gestor1 = fopen($name1, "wb");
        fwrite($gestor1, ('@@@@TIPOARCHIVO=Recibo') . chr(13) . chr(10));
        fwrite($gestor1, ('##PROGRAMA##genIm7Recibo') . chr(13) . chr(10));
        fwrite($gestor1, ('##FECHA##' . $liq["fecharecibo"]) . chr(13) . chr(10));
        fwrite($gestor1, ('##HORA##' . $liq["horarecibo"]) . chr(13) . chr(10));
        if ($tiporecibo == 'S') {
            fwrite($gestor1, ('##NUMERORECIBO##' . $liq["numerorecibo"]) . chr(13) . chr(10));
            fwrite($gestor1, ('##NUMEROOPERACION##' . $liq["numerooperacion"]) . chr(13) . chr(10));
        } else {
            fwrite($gestor1, ('##NUMERORECIBO##' . $liq["numerorecibogob"]) . chr(13) . chr(10));
            fwrite($gestor1, ('##NUMEROOPERACION##' . $liq["numerooperaciongob"]) . chr(13) . chr(10));
        }
        fwrite($gestor1, ('##NOMBRECLIENTE##' . $liq["nombrecliente"]) . chr(13) . chr(10));
        fwrite($gestor1, ('##USUARIO##' . $liq["idusuario"]) . chr(13) . chr(10));
        fwrite($gestor1, ('##NUMEROINTERNO##') . chr(13) . chr(10));
        fwrite($gestor1, ('##NUMEROUNICO##') . chr(13) . chr(10));
        fwrite($gestor1, ('##REIMPRESION##' . $reimpresion) . chr(13) . chr(10));
        //
        // Cuerpo del recibo
        //
        $fpago = '';
        if ($liq["pagoefectivo"] != 0) {
            $fpago = 'Efectivo';
        }
        if ($liq["pagocheque"] != 0) {
            $fpago = 'En cheque';
        }
        if ($liq["pagoconsignacion"] != 0) {
            $fpago = 'En consignación';
        }
        if ($liq["pagoqr"] != 0) {
            $fpago = 'En QR';
        }
        if ($liq["pagotdebito"] != 0) {
            $fpago = 'Tarj. Débito';
        }
        if ($liq["pagoach"] != 0) {
            $fpago = 'Sistema ACH';
        }
        if ($liq["pagovisa"] != 0) {
            $fpago = 'Tarj. Crédito';
        }
        if ($liq["pagomastercard"] != 0) {
            $fpago = 'Tarj. Crédito';
        }
        if ($liq["pagocredencial"] != 0) {
            $fpago = 'Tarj. Crédito';
        }
        if ($liq["pagodiners"] != 0) {
            $fpago = 'Tarj. Crédito';
        }
        if ($liq["pagoamerican"] != 0) {
            $fpago = 'Tarj. Crédito';
        }
        if ($orig != 'SI') {
            fwrite($gestor1, ('*** ESTA ES UNA COPIA DEL ORIGINAL ***') . chr(13) . chr(10));
        }
        fwrite($gestor1, encriptarCadenaIm7('FECHA: ' . \funcionesGenerales::mostrarFecha($liq["fecharecibo"])) . chr(13) . chr(10));
        if ($tiporecibo == 'S') {
            fwrite($gestor1, encriptarCadenaIm7('OPERAC.: ' . $liq["numerooperacion"]) . chr(13) . chr(10));
            fwrite($gestor1, ('NUM.REC: ' . sprintf("%-6s", $liq["numerorecuperacion"]) . '  RECIBO NO. ' . $liq["numerorecibo"]) . chr(13) . chr(10));
        } else {
            fwrite($gestor1, encriptarCadenaIm7('OPERAC.: ' . $liq["numerooperaciongob"]) . chr(13) . chr(10));
            fwrite($gestor1, ('NUM.REC: ' . sprintf("%-6s", $liq["numerorecuperacion"]) . '  RECIBO NO. ' . $liq["numerorecibogob"]) . chr(13) . chr(10));
        }
        fwrite($gestor1, ('NUM.RAD: ' . sprintf("%-6s", $liq["numeroradicacion"])) . chr(13) . chr(10));
        fwrite($gestor1, ('HORA: ' . \funcionesGenerales::mostrarHora($liq["horarecibo"]) . '      PAGINA 1 DE 1') . chr(13) . chr(10));
        if ($numunicorue != '') {
            fwrite($gestor1, ('NUM. UNICO RUES: ' . $numunicorue) . chr(13) . chr(10));
        }

        //
        fwrite($gestor1, ('USUARIO: ' . sprintf("%-8s", $liq["idusuario"])) . chr(13) . chr(10));
        fwrite($gestor1, ('-------------------------------------') . chr(13) . chr(10));
        fwrite($gestor1, ('MAT/INSC: (' . $det[1]["expediente"] . ')') . chr(13) . chr(10));
        fwrite($gestor1, ('-------------------------------------') . chr(13) . chr(10));

        //
        fwrite($gestor1, (sprintf("%-33s", substr(\funcionesGenerales::utf8_decode($liq["nombrecliente"] . ' ' . $liq["apellidocliente"]), 0, 33))) . chr(13) . chr(10));
        fwrite($gestor1, ('NIT/CC: ' . $liq["identificacioncliente"] . '  RUE: ') . chr(13) . chr(10));
        fwrite($gestor1, ('Email: ' . $liq["email"]) . chr(13) . chr(10));

        //
        fwrite($gestor1, ('FORMA DE PAGO: ' . $fpago) . chr(13) . chr(10));
        fwrite($gestor1, ('DESCRIPCION       DET.          VALOR') . chr(13) . chr(10));
        fwrite($gestor1, ('----------------- ---- --------------') . chr(13) . chr(10));
        $totrecibo = 0;
        foreach ($det as $dt) {
            $serv = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $dt["idservicio"] . "'");
            if (!defined('SEPARAR_RECIBOS') || SEPARAR_RECIBOS == 'NO') {
                $incluir = 'si';
            } else {
                if (defined('SEPARAR_RECIBOS') && SEPARAR_RECIBOS == 'SI' && $tiporecibo == 'S' && ltrim((string) $serv["conceptodepartamental"], "0") == '') {
                    $incluir = 'si';
                }
                if (defined('SEPARAR_RECIBOS') && SEPARAR_RECIBOS == 'SI' && $tiporecibo == 'G' && ltrim((string) $serv["conceptodepartamental"], "0") != '') {
                    $incluir = 'si';
                }
            }
            if (trim($serv["descripcioncorta"]) != '') {
                $servtxt = sprintf("%-17s", substr($serv["descripcioncorta"], 0, 17));
            } else {
                $servtxt = sprintf("%-17s", substr($serv["nombre"], 0, 17));
            }
            if (trim($dt["ano"]) != '') {
                $canttxt = $dt["ano"];
            } else {
                $canttxt = sprintf("%4s", $dt["cantidad"]);
            }
            $valtxt = sprintf("%14s", number_format($dt["valorservicio"], 0));
            if ($incluir == 'si') {
                $totrecibo = $totrecibo + $dt["valorservicio"];
                fwrite($gestor1, ($servtxt . ' ' . $canttxt . ' ' . $valtxt) . chr(13) . chr(10));
                if (doubleval($dt["valorbase"]) != 0) {
                    fwrite($gestor1, encriptarCadenaIm7('Valor base:' . number_format($dt["valorbase"])) . chr(13) . chr(10));
                }
            }
        }

        if (($liq["cargogastoadministrativo"] == 'SI') || ($liq["cargoentidadoficial"] == 'SI')) {
            $servtxt = sprintf("%-17s", '*** TOTAL PAGADO');
            $canttxt = sprintf("%4s", ' ');
            $valtxt = sprintf("%14s", '0');
            fwrite($gestor1, ($servtxt . ' ' . $canttxt . ' ' . $valtxt) . chr(13) . chr(10));
            if ($liq["cargogastoadministrativo"] == 'SI') {
                fwrite($gestor1, ('*** SIN COSTO PARA EL CLIENTE ***') . chr(13) . chr(10));
            }
            if ($liq["cargoentidadoficial"] == 'SI') {
                fwrite($gestor1, ('*** SIN COSTO PARA LA ENTIDAD ***') . chr(13) . chr(10));
            }
        } else {
            $servtxt = sprintf("%-17s", '*** TOTAL PAGADO');
            $canttxt = sprintf("%4s", ' ');
            if ($liq["cargoafiliacion"] == 'SI') {
                $valtxt = sprintf("%14s", '0');
            } else {
                $valtxt = sprintf("%14s", number_format($totrecibo, 0));
            }
            fwrite($gestor1, ($servtxt . ' ' . $canttxt . ' ' . $valtxt) . chr(13) . chr(10));
            if ($liq["cargoafiliacion"] == 'SI') {
                fwrite($gestor1, ('*** CON CARGO A CUPO AFILIADOS ***') . chr(13) . chr(10));
            }
        }

        if (trim($claveprepago) != '') {
            fwrite($gestor1, ('----------------- ---- --------------') . chr(13) . chr(10));
            fwrite($gestor1, ('Clave prepago: ' . $claveprepago) . chr(13) . chr(10));
        }
        if (doubleval($saldoprepago) != 0) {
            fwrite($gestor1, ('----------------- ---- --------------') . chr(13) . chr(10));
            fwrite($gestor1, ('Saldoprepago: ' . $claveprepago) . chr(13) . chr(10));
        }

        if (trim($liq["numeroradicacion"]) != '') {

            //
            switch ($_SESSION["generales"]["codigoempresa"]) {
                case "20" :
                    fwrite($gestor1, ('----------------- ---- --------------') . chr(13) . chr(10));
                    fwrite($gestor1, ('Para informacion sobre este(os) docu-') . chr(13) . chr(10));
                    fwrite($gestor1, ('mento(s)  comuniquese  al  8962121  o') . chr(13) . chr(10));
                    fwrite($gestor1, ('consulte  en  www.ccmpc.org.co,  link') . chr(13) . chr(10));
                    fwrite($gestor1, ('servicios, servicios en linea, consul') . chr(13) . chr(10));
                    fwrite($gestor1, ('ta estado de tramites, alli digite el') . chr(13) . chr(10));
                    fwrite($gestor1, ('siguiente numero:') . chr(13) . chr(10));
                    fwrite($gestor1, ($liq["numeroradicacion"]) . chr(13) . chr(10));
                    break;

                case "55" :
                    fwrite($gestor1, ('----------------- ---- --------------') . chr(13) . chr(10));
                    fwrite($gestor1, ('Codigo de barras: ' . $liq["numeroradicacion"]) . chr(13) . chr(10));
                    fwrite($gestor1, ('----------------- ---- --------------') . chr(13) . chr(10));
                    fwrite($gestor1, ('Para conocer el estado de su  tramite') . chr(13) . chr(10));
                    fwrite($gestor1, ('dirijase a: www.ccas.org.co') . chr(13) . chr(10));
                    break;

                default :
                    fwrite($gestor1, ('----------------- ---- --------------') . chr(13) . chr(10));
                    fwrite($gestor1, ('Codigo de barras: ' . $liq["numeroradicacion"]) . chr(13) . chr(10));
                    fwrite($gestor1, ('----------------- ---- --------------') . chr(13) . chr(10));
                    fwrite($gestor1, ('Para conocer el estado de su  tramite') . chr(13) . chr(10));
                    fwrite($gestor1, ('dirijase a: ') . chr(13) . chr(10));
                    fwrite($gestor1, (TIPO_HTTP . HTTP_HOST . "/cnr.php?em=" . $_SESSION["generales"]["codigoempresa"] . "&cb=" . $liq["numeroradicacion"]) . chr(13) . chr(10));
                    break;
            }
        }

        if (defined('CFE_FECHA_INICIAL') && CFE_FECHA_INICIAL != '' && date("Ymd") >= CFE_FECHA_INICIAL) {
            fwrite($gestor1, ('----------------- ---- --------------') . chr(13) . chr(10));
            fwrite($gestor1, ('La factura electronica  sera  enviada') . chr(13) . chr(10));
            fwrite($gestor1, ('al correo electronico  reportado  por') . chr(13) . chr(10));
            fwrite($gestor1, ('el cliente. En caso  de no recibirla,') . chr(13) . chr(10));
            fwrite($gestor1, ('por favor comunicarse al ' . TELEFONO_ATENCION_USUARIOS) . chr(13) . chr(10));
        }

        //		
        fclose($gestor1);
    } else {
        $rrue = retornarRegistroMysqliApi($mysqli, 'mreg_rue_radicacion', "recibolocal='" . $arreglo["numerorecibo"] . "'");
        if ($rrue && !empty($rrue)) {
            $numunicorue = trim($rrue["numerounicoconsulta"]);
        }

        $name = '../tmp/Recibo-' . $arreglo["numerorecibo"] . '.im7';
        $gestor = fopen($name, "wb");
        //
        // Titulos del recibo
        //
        fwrite($gestor, encriptarCadenaIm7('@@@@TIPOARCHIVO=Recibo') . chr(13) . chr(10));
        fwrite($gestor, encriptarCadenaIm7('##PROGRAMA##genIm7Recibo') . chr(13) . chr(10));
        fwrite($gestor, encriptarCadenaIm7('##FECHA##' . $arreglo["fecharecibo"]) . chr(13) . chr(10));
        fwrite($gestor, encriptarCadenaIm7('##HORA##' . $arreglo["horarecibo"]) . chr(13) . chr(10));
        fwrite($gestor, encriptarCadenaIm7('##NUMERORECIBO##' . $arreglo["numerorecibo"]) . chr(13) . chr(10));
        fwrite($gestor, encriptarCadenaIm7('##NUMEROOPERACION##' . $arreglo["numerooperacion"]) . chr(13) . chr(10));
        fwrite($gestor, encriptarCadenaIm7('##NOMBRECLIENTE##' . $arreglo["nombre"]) . chr(13) . chr(10));
        if (trim($cajero != '')) {
            fwrite($gestor, encriptarCadenaIm7('##USUARIO##' . $cajero) . chr(13) . chr(10));
        } else {
            fwrite($gestor, encriptarCadenaIm7('##USUARIO##' . $_SESSION["generales"]["codigousuario"]) . chr(13) . chr(10));
        }
        fwrite($gestor, encriptarCadenaIm7('##NUMEROINTERNO##') . chr(13) . chr(10));
        fwrite($gestor, encriptarCadenaIm7('##NUMEROUNICO##') . chr(13) . chr(10));
        fwrite($gestor, encriptarCadenaIm7('##REIMPRESION##' . $reimpresion) . chr(13) . chr(10));
        //
        // Cuerpo del recibo
        //
        $fpago = '';
        if ($arreglo["formapago"] == '01') {
            $fpago = 'Efectivo';
        }
        if ($arreglo["formapago"] == '02') {
            $fpago = 'En cheque';
        }
        if ($arreglo["formapago"] == '03') {
            $fpago = 'Tarj. Débito';
        }
        if ($arreglo["formapago"] == '04') {
            $fpago = 'Sistema ACH';
        }
        if ($arreglo["formapago"] == '05') {
            $fpago = 'Sistema VISA';
        }
        if ($arreglo["formapago"] == '06') {
            $fpago = 'Sistema MASTERCARD';
        }
        if ($arreglo["formapago"] == '07') {
            $fpago = 'Sistema AMERICAN';
        }
        if ($arreglo["formapago"] == '08') {
            $fpago = 'Sistema DINERS';
        }
        if ($arreglo["formapago"] == '09') {
            $fpago = 'Sistema CREDENCIAL';
        }
        if ($arreglo["formapago"] == '10') {
            $fpago = 'Consignación';
        }
        if ($orig != 'SI') {
            fwrite($gestor, encriptarCadenaIm7('*** ESTA ES UNA COPIA DEL ORIGINAL ***') . chr(13) . chr(10));
        }
        fwrite($gestor, encriptarCadenaIm7('FECHA: ' . \funcionesGenerales::mostrarFecha($arreglo["fecharecibo"]) . '  OPERAC.: ' . $arreglo["numerooperacion"]) . chr(13) . chr(10));
        fwrite($gestor, encriptarCadenaIm7('NUM.REC: ' . sprintf("%-6s", $arreglo["numerorecuperacion"]) . '  RECIBO NO. ' . $arreglo["numerorecibo"]) . chr(13) . chr(10));
        fwrite($gestor, encriptarCadenaIm7('HORA: ' . \funcionesGenerales::mostrarHora($arreglo["horarecibo"]) . '      PAGINA 1 DE 1') . chr(13) . chr(10));
        if ($numunicorue != '') {
            fwrite($gestor, encriptarCadenaIm7('NUM. UNICO RUES: ' . $numunicorue) . chr(13) . chr(10));
        }

        fwrite($gestor, encriptarCadenaIm7('USUARIO: ' . sprintf("%-8s", $_SESSION["generales"]["codigousuario"])) . chr(13) . chr(10));
        fwrite($gestor, encriptarCadenaIm7('-------------------------------------') . chr(13) . chr(10));
        fwrite($gestor, encriptarCadenaIm7('MAT/INSC: (' . $arreglo["renglones"][1]["expediente"] . ')') . chr(13) . chr(10));
        fwrite($gestor, encriptarCadenaIm7('-------------------------------------') . chr(13) . chr(10));
        fwrite($gestor, encriptarCadenaIm7(sprintf("%-33s", substr($arreglo["nombre"], 0, 33))) . chr(13) . chr(10));
        fwrite($gestor, encriptarCadenaIm7('NIT/CC: ' . $arreglo["identificacion"] . '  RUE: ') . chr(13) . chr(10));
        fwrite($gestor, encriptarCadenaIm7('FORMA DE PAGO: ' . $fpago) . chr(13) . chr(10));
        fwrite($gestor, encriptarCadenaIm7('DESCRIPCION       DET.          VALOR') . chr(13) . chr(10));
        fwrite($gestor, encriptarCadenaIm7('----------------- ---- --------------') . chr(13) . chr(10));
        foreach ($arreglo["renglones"] as $dt) {
            $serv = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $dt["servicio"] . "'");
            if (trim($serv["descripcioncorta"]) != '') {
                $servtxt = sprintf("%-17s", substr($serv["descripcioncorta"], 0, 17));
            } else {
                $servtxt = sprintf("%-17s", substr($serv["nombre"], 0, 17));
            }
            if (trim($dt["ano"]) != '') {
                $canttxt = $dt["ano"];
            } else {
                $canttxt = sprintf("%4s", $dt["cantidad"]);
            }
            $valtxt = sprintf("%14s", number_format($dt["valor"], 0));
            fwrite($gestor, encriptarCadenaIm7($servtxt . ' ' . $canttxt . ' ' . $valtxt) . chr(13) . chr(10));
            if (doubleval($dt["valorbase"]) != 0) {
                fwrite($gestor, encriptarCadenaIm7('Valor base:' . number_format($dt["valorbase"])) . chr(13) . chr(10));
            }
        }
        $servtxt = sprintf("%-17s", '*** TOTAL PAGADO');
        $canttxt = sprintf("%4s", ' ');
        $valtxt = sprintf("%14s", number_format($arreglo["valortotal"], 0));
        fwrite($gestor, encriptarCadenaIm7($servtxt . ' ' . $canttxt . ' ' . $valtxt) . chr(13) . chr(10));
        if (trim($arreglo["facturacancelada"]) != '') {
            fwrite($gestor, encriptarCadenaIm7('----------------- ---- --------------') . chr(13) . chr(10));
            fwrite($gestor, encriptarCadenaIm7('Fact. cancelada/abonada: ' . $arreglo["facturacancelada"]) . chr(13) . chr(10));
        }
        if (trim($claveprepago) != '') {
            fwrite($gestor, encriptarCadenaIm7('----------------- ---- --------------') . chr(13) . chr(10));
            fwrite($gestor, encriptarCadenaIm7('Clave prepago: ' . $claveprepago) . chr(13) . chr(10));
        }
        if (ltrim($saldoprepago, "0") != '') {
            fwrite($gestor, encriptarCadenaIm7('----------------- ---- --------------') . chr(13) . chr(10));
            fwrite($gestor, encriptarCadenaIm7('Saldoprepago: ' . $claveprepago) . chr(13) . chr(10));
        }

        if (trim($arreglo["codbarras"]) != '') {
            fwrite($gestor, encriptarCadenaIm7('----------------- ---- --------------') . chr(13) . chr(10));
            fwrite($gestor, encriptarCadenaIm7('COD.BARRAS:' . $arreglo["codbarras"]) . chr(13) . chr(10));
        }
        fclose($gestor);
        unset($gestor);

        $name1 = '../tmp/Recibo-' . $arreglo["numerorecibo"] . '.tx1';
        $gestor1 = fopen($name1, "wb");
        //
        // Titulos del recibo
        //
        fwrite($gestor1, ('@@@@TIPOARCHIVO=Recibo') . chr(13) . chr(10));
        fwrite($gestor1, ('##PROGRAMA##genIm7Recibo') . chr(13) . chr(10));
        fwrite($gestor1, ('##FECHA##' . $arreglo["fecharecibo"]) . chr(13) . chr(10));
        fwrite($gestor1, ('##HORA##' . $arreglo["horarecibo"]) . chr(13) . chr(10));
        fwrite($gestor1, ('##NUMERORECIBO##' . $arreglo["numerorecibo"]) . chr(13) . chr(10));
        fwrite($gestor1, ('##NUMEROOPERACION##' . $arreglo["numerooperacion"]) . chr(13) . chr(10));
        fwrite($gestor1, ('##NOMBRECLIENTE##' . $arreglo["nombre"]) . chr(13) . chr(10));
        if (trim($cajero != '')) {
            fwrite($gestor1, ('##USUARIO##' . $cajero) . chr(13) . chr(10));
        } else {
            fwrite($gestor1, ('##USUARIO##' . $_SESSION["generales"]["codigousuario"]) . chr(13) . chr(10));
        }
        fwrite($gestor1, ('##NUMEROINTERNO##') . chr(13) . chr(10));
        fwrite($gestor1, ('##NUMEROUNICO##') . chr(13) . chr(10));
        fwrite($gestor1, ('##REIMPRESION##' . $reimpresion) . chr(13) . chr(10));
        //
        // Cuerpo del recibo
        //
        $fpago = '';
        if ($arreglo["formapago"] == '01')
            $fpago = 'Efectivo';
        if ($arreglo["formapago"] == '02')
            $fpago = 'En cheque';
        if ($arreglo["formapago"] == '03')
            $fpago = 'Tarj. Débito';
        if ($arreglo["formapago"] == '04')
            $fpago = 'Sistema ACH';
        if ($arreglo["formapago"] == '05')
            $fpago = 'Sistema VISA';
        if ($arreglo["formapago"] == '06')
            $fpago = 'Sistema MASTERCARD';
        if ($arreglo["formapago"] == '07')
            $fpago = 'Sistema AMERICAN';
        if ($arreglo["formapago"] == '08')
            $fpago = 'Sistema DINERS';
        if ($arreglo["formapago"] == '09')
            $fpago = 'Sistema CREDENCIAL';
        if ($arreglo["formapago"] == '10')
            $fpago = 'Consignación';
        if ($orig != 'SI') {
            fwrite($gestor1, ('*** ESTA ES UNA COPIA DEL ORIGINAL ***') . chr(13) . chr(10));
        }
        fwrite($gestor1, ('FECHA: ' . \funcionesGenerales::mostrarFecha($arreglo["fecharecibo"]) . '  OPERAC.: ' . $arreglo["numerooperacion"]) . chr(13) . chr(10));
        fwrite($gestor1, ('NUM.REC: ' . sprintf("%-6s", $arreglo["numerorecuperacion"]) . '  RECIBO NO. ' . $arreglo["numerorecibo"]) . chr(13) . chr(10));
        fwrite($gestor1, ('HORA: ' . \funcionesGenerales::mostrarHora($arreglo["horarecibo"]) . '      PAGINA 1 DE 1' ) . chr(13) . chr(10));
        if ($numunicorue != '') {
            fwrite($gestor1, ('NUM. UNICO RUES: ' . $numunicorue) . chr(13) . chr(10));
        }
        fwrite($gestor1, ('USUARIO: ' . sprintf("%-8s", $_SESSION["generales"]["codigousuario"])) . chr(13) . chr(10));
        fwrite($gestor1, ('-------------------------------------') . chr(13) . chr(10));
        fwrite($gestor1, ('MAT/INSC: (' . $arreglo["renglones"][1]["expediente"] . ')') . chr(13) . chr(10));
        fwrite($gestor1, ('-------------------------------------') . chr(13) . chr(10));
        fwrite($gestor1, (sprintf("%-33s", substr($arreglo["nombre"], 0, 33))) . chr(13) . chr(10));
        fwrite($gestor1, ('NIT/CC: ' . $arreglo["identificacion"] . '  RUE: ') . chr(13) . chr(10));
        fwrite($gestor1, ('FORMA DE PAGO: ' . $fpago) . chr(13) . chr(10));
        fwrite($gestor1, ('DESCRIPCION       DET.          VALOR') . chr(13) . chr(10));
        fwrite($gestor1, ('----------------- ---- --------------') . chr(13) . chr(10));
        foreach ($arreglo["renglones"] as $dt) {
            $serv = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $dt["servicio"] . "'");
            if (trim($serv["descripcioncorta"]) != '') {
                $servtxt = sprintf("%-17s", substr($serv["descripcioncorta"], 0, 17));
            } else {
                $servtxt = sprintf("%-17s", substr($serv["nombre"], 0, 17));
            }
            if (trim($dt["ano"]) != '') {
                $canttxt = $dt["ano"];
            } else {
                $canttxt = sprintf("%4s", $dt["cantidad"]);
            }
            $valtxt = sprintf("%14s", number_format($dt["valor"], 0));
            fwrite($gestor1, ($servtxt . ' ' . $canttxt . ' ' . $valtxt) . chr(13) . chr(10));
            if (doubleval($dt["valorbase"]) != 0) {
                fwrite($gestor1, encriptarCadenaIm7('Valor base:' . number_format($dt["valorbase"])) . chr(13) . chr(10));
            }
        }
        $servtxt = sprintf("%-17s", '*** TOTAL PAGADO');
        $canttxt = sprintf("%4s", ' ');
        $valtxt = sprintf("%14s", number_format($arreglo["valortotal"], 0));
        fwrite($gestor1, ($servtxt . ' ' . $canttxt . ' ' . $valtxt) . chr(13) . chr(10));
        if (trim($arreglo["facturacancelada"]) != '') {
            fwrite($gestor1, ('----------------- ---- --------------') . chr(13) . chr(10));
            fwrite($gestor1, ('Fact. cancelada/abonada: ' . $arreglo["facturacancelada"]) . chr(13) . chr(10));
        }
        if (trim($claveprepago) != '') {
            fwrite($gestor1, ('----------------- ---- --------------') . chr(13) . chr(10));
            fwrite($gestor1, ('Clave prepago: ' . $claveprepago) . chr(13) . chr(10));
        }
        if (ltrim($saldoprepago, "0") != '') {
            fwrite($gestor1, ('----------------- ---- --------------') . chr(13) . chr(10));
            fwrite($gestor1, ('Saldoprepago: ' . $claveprepago) . chr(13) . chr(10));
        }

        if (trim($arreglo["codbarras"]) != '') {
            fwrite($gestor1, ('----------------- ---- --------------') . chr(13) . chr(10));
            fwrite($gestor1, ('COD.BARRAS:' . $arreglo["codbarras"]) . chr(13) . chr(10));
        }
        fclose($gestor1);
        unset($gestor1);
    }
    // Genera la salida
    return $name;
}

function armarIm7SelloLibros($mysqli, $libro, $numreg, $arrTexto) {
    $name = '../tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . $libro . '-' . $numreg . '-Sello.im7';
    $gestor = fopen($name, "wb");
    fwrite($gestor, encriptarCadenaIm7('@@@@TIPOARCHIVO=RotuloLibro') . chr(13) . chr(10));
    fwrite($gestor, encriptarCadenaIm7('##PROGRAMA##genIm7SelloLibros') . chr(13) . chr(10));
    fwrite($gestor, encriptarCadenaIm7('##FECHA##' . date("Ymd")) . chr(13) . chr(10));
    fwrite($gestor, encriptarCadenaIm7('##HORA##' . date("His")) . chr(13) . chr(10));
    fwrite($gestor, encriptarCadenaIm7('##USUARIO##' . $_SESSION["generales"]["codigousuario"]) . chr(13) . chr(10));
    fwrite($gestor, encriptarCadenaIm7('##INSCRIPCION##' . $libro . '-' . $numreg) . chr(13) . chr(10));
    foreach ($arrTexto as $t) {
        fwrite($gestor, encriptarCadenaIm7($t) . chr(13) . chr(10));
    }
    fclose($gestor);
    unset($gestor);
    return $name;
}

?>