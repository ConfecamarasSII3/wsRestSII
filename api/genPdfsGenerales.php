<?php

/**
 * Función que imprime el recibo de caja y/o factura de venta a crédito
 * -
 * @param 	string		$numliq		Número de liquidación
 */
function encabezadosRecibo($pdf = null, $liq = '', $det = '', $orig = '', $arreglo = array(), $genrec = '', $nir = '', $nuc = '', $tiporecibo = 'S') {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');

    $pdf->AddPage();
    if (!defined('IMPRIMIR_LOGO_EN_RECIBO')) {
        define('IMPRIMIR_LOGO_EN_RECIBO', 'S');
    }
    if (IMPRIMIR_LOGO_EN_RECIBO == 'S') {
        if (file_exists($_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . CODIGO_EMPRESA . '.jpg')) {
            $pdf->Image($_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . CODIGO_EMPRESA . '.jpg', 10, 7, 25, 25);
        }
    }
    if ($orig != 'SI') {
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetXY(30, 9);
        $pdf->Cell(10);
        $pdf->Cell(140, 4, '*** ESTA ES UNA COPIA DEL ORIGINAL***', 0, 0, 'R');
    }
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetXY(30, 12);
    $pdf->Cell(10);
    $pdf->Cell(140, 4, RAZONSOCIAL_RESUMIDA, 0, 0, 'R');

    // $longnit=strlen(NIT);
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetXY(30, 16);
    $pdf->Cell(10);
    $pdf->Cell(140, 4, "Nit. " . NIT, 0, 0, 'R');

    if (!empty($liq)) {
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetXY(30, 20);
        $pdf->Cell(10);
        if (!defined('SEPARAR_RECIBOS') || SEPARAR_RECIBOS == 'NO' || $tiporecibo == 'S') {
            $pdf->Cell(140, 4, "RECIBO No. " . $liq["numerorecibo"], 0, 0, 'R');
        } else {
            $pdf->Cell(140, 4, "RECIBO No. " . $liq["numerorecibogob"], 0, 0, 'R');
        }
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetXY(30, 24);
        $pdf->Cell(10);
        if (!defined('CFE_FECHA_INICIAL') || CFE_FECHA_INICIAL == '' || date("Ymd") < CFE_FECHA_INICIAL) {
            if (!defined('SEPARAR_RECIBOS') || SEPARAR_RECIBOS == 'NO' || $tiporecibo == 'S') {
                $pdf->Cell(140, 4, "Documento equivalente a factura No " . $liq["numerorecibo"], 0, 0, 'R');
            } else {
                $pdf->Cell(140, 4, "Documento equivalente a factura No " . $liq["numerorecibogob"], 0, 0, 'R');
            }
        }
    } else {
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetXY(30, 20);
        $pdf->Cell(10);
        if (!defined('SEPARAR_RECIBOS') || SEPARAR_RECIBOS == 'NO' || $tiporecibo == 'S') {
            $pdf->Cell(140, 4, "RECIBO No. " . $arreglo["numerorecibo"], 0, 0, 'R');
        } else {
            $pdf->Cell(140, 4, "RECIBO No. " . $arreglo["numerorecibogob"], 0, 0, 'R');
        }

        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetXY(30, 24);
        $pdf->Cell(10);
        if (!defined('CFE_FECHA_INICIAL') || CFE_FECHA_INICIAL == '' || date("Ymd") < CFE_FECHA_INICIAL) {
            if (!defined('SEPARAR_RECIBOS') || SEPARAR_RECIBOS == 'NO' || $tiporecibo == 'S') {
                $pdf->Cell(140, 4, "Documento equivalente a factura No " . $arreglo["numerorecibo"], 0, 0, 'R');
            } else {
                $pdf->Cell(140, 4, "Documento equivalente a factura No " . $arreglo["numerorecibogob"], 0, 0, 'R');
            }
        }
    }
    if (!empty($liq)) {
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetXY(30, 28);
        $pdf->Cell(10);
        if (!defined('SEPARAR_RECIBOS') || SEPARAR_RECIBOS == 'NO' || $tiporecibo == 'S') {
            $pdf->Cell(140, 4, \funcionesGenerales::utf8_decode("Nro. operación. " . $liq["numerooperacion"]), 0, 0, 'R');
        } else {
            $pdf->Cell(140, 4, \funcionesGenerales::utf8_decode("Nro. operación. " . $liq["numerooperaciongob"]), 0, 0, 'R');
        }
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetXY(30, 32);
        $pdf->Cell(10);
        $pdf->Cell(140, 4, \funcionesGenerales::utf8_decode("Nro. liquidación. " . $liq["idliquidacion"]), 0, 0, 'R');
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetXY(30, 36);
        $pdf->Cell(10);
        $pdf->Cell(140, 4, "Fecha y hora. " . \funcionesGenerales::mostrarFecha($liq["fecharecibo"]) . " - " . \funcionesGenerales::mostrarHora($liq["horarecibo"]), 0, 0, 'R');
    } else {
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetXY(30, 28);
        $pdf->Cell(10);
        if (!defined('SEPARAR_RECIBOS') || SEPARAR_RECIBOS == 'NO' || $tiporecibo == 'S') {
            $pdf->Cell(140, 4, "Nro. operación. " . $arreglo["numerooperacion"], 0, 0, 'R');
        } else {
            $pdf->Cell(140, 4, "Nro. operación. " . $arreglo["numerooperaciongob"], 0, 0, 'R');
        }
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetXY(30, 32);
        $pdf->Cell(10);
        $pdf->Cell(140, 4, "Fecha y hora. " . \funcionesGenerales::mostrarFecha($arreglo["fecharecibo"]) . " - " . \funcionesGenerales::mostrarHora($arreglo["horarecibo"]), 0, 0, 'R');
    }

    //
    if ($nir != '' && $nuc != '') {
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetXY(30, 40);
        $pdf->Cell(10);
        $pdf->Cell(140, 4, "NIR/NUC: " . $nir . '/' . $nuc, 0, 0, 'R');
    }

    //
    if ($nir != '' && $nuc == '') {
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetXY(30, 44);
        $pdf->Cell(10);
        $pdf->Cell(140, 4, "NIR: " . $nir, 0, 0, 'R');
    }

    //
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetXY(30, 48);
    $pdf->Cell(10);
    $pdf->Cell(140, 4, "Recibo expedido en forma virtual", 0, 0, 'R');

    //
    if (!empty($liq)) {
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetXY(30, 52);
        $pdf->Cell(10);
        $pdf->Cell(140, 4, \funcionesGenerales::utf8_decode("Nro. recuperación. " . $liq["numerorecuperacion"]), 0, 0, 'R');
    } else {
        if (trim($arreglo["numerorecuperacion"]) != '') {
            $pdf->SetFont('Helvetica', '', 10);
            $pdf->SetXY(30, 52);
            $pdf->Cell(10);
            $pdf->Cell(140, 4, \funcionesGenerales::utf8_decode("Nro. recuperación. " . $arreglo["numerorecuperacion"]), 0, 0, 'R');
        }
    }
    if (isset($_SESSION["generales"]["escajero"])) {
        if ($_SESSION["generales"]["escajero"] == 'SI') {
            $pdf->SetFont('Helvetica', '', 10);
            $pdf->SetXY(30, 56);
            $pdf->Cell(10);
            $pdf->Cell(140, 4, "Cajero: " . $_SESSION["generales"]["codigousuario"], 0, 0, 'R');
        }
    }

    //
    if (defined('SEPARAR_RECIBOS') && SEPARAR_RECIBOS == 'SI' && $tiporecibo == 'G') {
        $pdf->SetFont('Helvetica', 'B', 12);
        $pdf->SetXY(5, 63);
        $txt = '*** LOS DINEROS AQUI RECAUDADOS SON TRASLADADOS EN SU TOTALIDAD A LA GOBERNACION, PUES SON IMPUESTOS Y ESTAMPILLAS ESTABLECIDOS EN LA ';
        $txt .= 'LEY 223 de 1995 Y ORDENANZAS EXPEDIDAS POR EL DEPARTAMENTO. LA CÁMARA DE COMERCIO NO OBTIENE REDITO ALGUNO DE ESTOS RECURSOS. ***';
        $pdf->MultiCell(180, 5, \funcionesGenerales::utf8_decode($txt), 0, 'C', 0);
        $pdf->SetFont('Helvetica', '', 10);
        $ilinea1 = 84;
    } else {
        $ilinea1 = 74;
    }

    //
    if (!empty($liq)) {
        if (trim($liq["nombrepagador"]) == '') {
            $pdf->SetFont('Helvetica', '', 10);
            $pdf->SetXY(5, $ilinea1);
            $pdf->Write(5, "Nombre: " . \funcionesGenerales::utf8_decode($liq["nombrecliente"]) . ' ' . \funcionesGenerales::utf8_decode($liq["apellidocliente"]));
        } else {
            $pdf->SetFont('Helvetica', '', 10);
            $pdf->SetXY(5, $ilinea1);
            $pdf->Write(5, "Nombre: " . \funcionesGenerales::utf8_decode($liq["apellidopagador"]) . ' ' . \funcionesGenerales::utf8_decode($liq["nombrepagador"]));
        }
    } else {
        if (isset($arreglo["nombrepagador"])) {
            if (trim($arreglo["nombrepagador"]) == '') {
                $pdf->SetFont('Helvetica', '', 10);
                $pdf->SetXY(5, $ilinea1);
                $pdf->Write(5, "Nombre: " . $arreglo["nombre"]);
            } else {
                $pdf->SetFont('Helvetica', '', 10);
                $pdf->SetXY(5, $ilinea1);
                $pdf->Write(5, "Nombre: " . $arreglo["apellidopagador"] . ' ' . $arreglo["nombrepagador"]);
            }
        } else {
            $pdf->SetFont('Helvetica', '', 10);
            $pdf->SetXY(5, $ilinea1);
            $pdf->Write(5, "Nombre: " . $arreglo["nombre"]);
        }
    }

    //
    $ilinea1 = $ilinea1 + 4;
    if (!empty($liq)) {
        if (trim($liq["nombrepagador"]) == '') {
            if ($liq["idtipoidentificacioncliente"] != '2') {
                $pdf->SetFont('Helvetica', '', 10);
                $pdf->SetXY(5, $ilinea1);
                $pdf->Write(5, "Identificacion: " . $liq["identificacioncliente"]);
            } else {
                $pdf->SetFont('Helvetica', '', 10);
                $pdf->SetXY(5, $ilinea1);
                $pdf->Write(5, "Identificacion: " . \funcionesGenerales::mostrarNit((string) $liq["identificacioncliente"]));
            }
        } else {
            if ($liq["tipoidentificacionpagador"] != '2') {
                $pdf->SetFont('Helvetica', '', 10);
                $pdf->SetXY(5, $ilinea1);
                $pdf->Write(5, "Identificacion: " . $liq["identificacionpagador"]);
            } else {
                $pdf->SetFont('Helvetica', '', 10);
                $pdf->SetXY(5, $ilinea1);
                $pdf->Write(5, "Identificacion: " . \funcionesGenerales::mostrarNit((string) $liq["identificacionpagador"]));
            }
        }
    } else {
        if (isset($arreglo["nombrepagador"])) {
            if (trim($arreglo["nombrepagador"]) == '') {
                if ($arreglo["tipoid"] != '2') {
                    $pdf->SetFont('Helvetica', '', 10);
                    $pdf->SetXY(5, $ilinea1);
                    $pdf->Write(5, "Identificacion: " . $arreglo["identificacion"]);
                } else {
                    $pdf->SetFont('Helvetica', '', 10);
                    $pdf->SetXY(5, $ilinea1);
                    $pdf->Write(5, "Identificacion: " . \funcionesGenerales::mostrarNit((string) $arreglo["identificacion"]));
                }
            } else {
                if ($arreglo["tipoidentificacionpagador"] != '2') {
                    $pdf->SetFont('Helvetica', '', 10);
                    $pdf->SetXY(5, $ilinea1);
                    $pdf->Write(5, "Identificacion: " . $arreglo["identificacionpagador"]);
                } else {
                    $pdf->SetFont('Helvetica', '', 10);
                    $pdf->SetXY(5, $ilinea1);
                    $pdf->Write(5, "Identificacion: " . \funcionesGenerales::mostrarNit((string) $arreglo["identificacionpagador"]));
                }
            }
        } else {
            if ($arreglo["tipoid"] != '2') {
                $pdf->SetFont('Helvetica', '', 10);
                $pdf->SetXY(5, $ilinea1);
                $pdf->Write(5, "Identificacion: " . $arreglo["identificacion"]);
            } else {
                $pdf->SetFont('Helvetica', '', 10);
                $pdf->SetXY(5, $ilinea1);
                $pdf->Write(5, "Identificacion: " . \funcionesGenerales::mostrarNit((string) $arreglo["identificacion"]));
            }
        }
    }

    //
    $ilinea1 = $ilinea1 + 4;
    if (!empty($liq)) {
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetXY(5, $ilinea1);
        $pdf->Write(5, \funcionesGenerales::utf8_decode("Dirección: " . $liq["direccion"]));
        $ilinea1 = $ilinea1 + 4;
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetXY(5, $ilinea1);
        $pdf->Write(5, \funcionesGenerales::utf8_decode("Teléfono: " . trim($liq["telefono"] . ' ' . $liq["movil"])));
    } else {
        if (isset($arreglo["direccionpagador"])) {
            if (trim($arreglo["direccionpagador"]) == '') {
                $pdf->SetFont('Helvetica', '', 10);
                $pdf->SetXY(5, $ilinea1);
                $pdf->Write(5, \funcionesGenerales::utf8_decode("Dirección: " . $liq["direccion"]));
                $ilinea1 = $ilinea1 + 4;
                $pdf->SetFont('Helvetica', '', 10);
                $pdf->SetXY(5, $ilinea1);
                $pdf->Write(5, \funcionesGenerales::utf8_decode("Teléfono: " . $liq["telefono"]));
            } else {
                $pdf->SetFont('Helvetica', '', 10);
                $pdf->SetXY(5, $ilinea1);
                $pdf->Write(5, \funcionesGenerales::utf8_decode("Dirección: " . $liq["direccionpagador"]));
                $ilinea1 = $ilinea1 + 4;
                $pdf->SetFont('Helvetica', '', 10);
                $pdf->SetXY(5, $ilinea1);
                $pdf->Write(5, \funcionesGenerales::utf8_decode("Teléfono: " . $liq["telefonopagador"]));
            }
        } else {
            if (!isset($arreglo["direccion"])) {
                $arreglo["direccion"] = '';
            }
            if (!isset($arreglo["telefono"])) {
                $arreglo["telefono"] = '';
            }
            $pdf->SetFont('Helvetica', '', 10);
            $pdf->SetXY(5, $ilinea1);
            $pdf->Write(5, \funcionesGenerales::utf8_decode("Dirección: " . $arreglo["direccion"]));
            $ilinea1 = $ilinea1 + 4;
            $pdf->SetFont('Helvetica', '', 10);
            $pdf->SetXY(5, $ilinea1);
            $pdf->Write(5, \funcionesGenerales::utf8_decode("Teléfono: " . $arreglo["telefono"]));
        }
    }


    $linea = $ilinea1 + 8;
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(5, $linea);
    $pdf->Write(5, "Cant");
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(20, $linea);
    $pdf->Write(5, "Servicio");
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(40, $linea);
    $pdf->Write(5, \funcionesGenerales::utf8_decode("Descripción"));
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(100, $linea);
    $pdf->Write(5, "Base/Activo");
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(125, $linea);
    $pdf->Write(5, \funcionesGenerales::utf8_decode("Año"));
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(140, $linea);
    $pdf->Write(5, "Mat/Ins");
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(170, $linea);
    $pdf->Write(4, "    Valor");
    $linea = $linea + 5;
    $pdf->Line(5, $linea, 190, $linea);
    return $linea;
}

function validarSaltoPagina($linea = null, $pdf = null, $liq = '', $det = '', $orig = '', $arreglo = array(), $genrec = '', $nir = '', $nuc = '', $tiporecibo = 'S') {
    $linea = $linea + 4;
    if ($linea > 220) {
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetXY(5, $linea);
        $pdf->Write(5, '****** CONTINUA ******');
        $linea = encabezadosRecibo($pdf, $liq, $det, $orig, $arreglo, $genrec, $nir, $nuc, $tiporecibo);
    }
    return $linea;
}

/**
 * 
 * Genera pdf con el recibo de caja
 * @param $numliq		Número de la liquidacion
 * 						Se utiliza cuando la liquidación ha quedado guardada en mreg_liquidacion
 * @param $tiposalida	Tipo de salida
 * 						* D: Display - Pantalla - No almacena en el repositorio
 * 						* I: Impresión - Almacena en el repositorio
 * 						* A: Archivo: Almacena en el repositorio 
 *                      * T: Temporal
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
function armarPdfRecibo($dbx, $numliq, $tiposalida = 'D', $orig = 'SI', $arreglo = array(), $claveprepago = '', $saldoprepago = '', $genrec = '', $tiporecibo = 'S') {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/fpdf186/fpdf_protection.php');

    //
    if (!defined('FPDF_FONTPATH')) {
        define('FPDF_FONTPATH', $_SESSION["generales"]["pathabsoluto"] . '/components/fpdf186/font/');
    }
    if (!class_exists('PDFRecibo')) {

        class PDFRecibo extends FPDF {
            
        }

    }

    $nir = '';
    $nuc = '';
    if ($numliq != 0) {
        $liq = retornarRegistroMysqliApi($dbx, 'mreg_liquidacion', "idliquidacion=" . $numliq);
        $det = retornarRegistrosMysqliApi($dbx, 'mreg_liquidaciondetalle', "idliquidacion=" . $numliq, "idsec");
        $rrue = retornarRegistroMysqliApi($dbx, 'mreg_rue_radicacion', "recibolocal='" . trim($liq["numerorecibo"]) . "'");
        $avueltas = retornarRegistroMysqliApi($dbx, 'mreg_liquidacion_campos', "idliquidacion=" . $numliq . " and campo='vueltas'");
        $rec = retornarRegistroMysqliApi($dbx, 'mreg_recibosgenerados', "recibo='" . $liq["numerorecibo"] . "'");
        if ($avueltas === false || empty($avueltas)) {
            $vueltas = 0;
        } else {
            $vueltas = $avueltas["contenido"];
        }
        $fpag = array();
        if (!defined('SEPARAR_RECIBOS') || (SEPARAR_RECIBOS == 'NO' && $tiporecibo == 'S')) {
            $fpag = retornarRegistrosMysqliApi($dbx, 'mreg_recibosgenerados_fpago', "recibo='" . $liq["numerorecibo"] . "'");
        } else {
            if (!defined('SEPARAR_RECIBOS') || (SEPARAR_RECIBOS == 'SI' && $tiporecibo == 'S')) {
                $fpag = retornarRegistrosMysqliApi($dbx, 'mreg_recibosgenerados_fpago', "recibo='" . $liq["numerorecibo"] . "'");
            } else {
                if (defined('SEPARAR_RECIBOS') && SEPARAR_RECIBOS == 'SI' && $tiporecibo == 'G') {
                    $fpag = retornarRegistrosMysqliApi($dbx, 'mreg_recibosgenerados_fpago', "recibo='" . $liq["numerorecibogob"] . "'");
                }
            }
        }

        //
        if ($rrue && !empty($rrue)) {
            $nir = trim($rrue["numerointernorue"]);
            $nuc = trim($rrue["numerounicoconsulta"]);
        }

        //
        $servcam = 0;
        $servgob = 0;
        $servtot = 0;

        //
        $brutorec = 0;
        $ivarec = 0;
        $totrec = 0;

        //
        $factele = '';

        //
        $pdf = new PDFRecibo("Portrait", "mm", "Letter");

        $pdf->AliasNbPages();
        $linea = encabezadosRecibo($pdf, $liq, $det, $orig, array(), $genrec, $nir, $nuc, $tiporecibo);
        foreach ($det as $det1) {
            $serv = retornarRegistroMysqliApi($dbx, 'mreg_servicios', "idservicio='" . $det1["idservicio"] . "'");
            if ($serv["facturable_electronicamente"] == 'SI') {
                $factele = 'si';
            }
            if (ltrim(trim((string) $serv["conceptodepartamental"]), "0") != '') {
                $servgob = $servgob + $det1["valorservicio"];
            } else {
                $servcam = $servcam + $det1["valorservicio"];
            }
            $servtot = $servtot + $det1["valorservicio"];

            $incluir = '';
            if (!defined('SEPARAR_RECIBOS') || SEPARAR_RECIBOS == 'NO') {
                $incluir = 'si';
            } else {
                if ($tiporecibo == 'S' && ltrim((string) $serv["conceptodepartamental"], "0") == '') {
                    $incluir = 'si';
                }
                if ($tiporecibo == 'G' && ltrim((string) $serv["conceptodepartamental"], "0") != '') {
                    $incluir = 'si';
                }
            }

            //
            if ($incluir == 'si') {
                if ($serv["idesiva"] == 'S') {
                    $ivarec = $ivarec + $det1["valorservicio"];
                } else {
                    $brutorec = $brutorec + $det1["valorservicio"];
                }
                $totrec = $totrec + $det1["valorservicio"];

                //
                $linea = validarSaltoPagina($linea, $pdf, $liq, $det, $orig, array(), $genrec, $nir, $nuc, $tiporecibo);
                $linea = $linea + 2;

                $pdf->SetFont('Helvetica', '', 8);
                $pdf->SetXY(5, $linea);
                $pdf->Write(5, number_format($det1["cantidad"]));

                $pdf->SetFont('Helvetica', '', 8);
                $pdf->SetXY(20, $linea);
                $pdf->Write(5, $det1["idservicio"]);

                $pdf->SetFont('Helvetica', '', 8);
                $pdf->SetXY(40, $linea);
                $pdf->Write(5, substr($serv["nombre"], 0, 30));

                if ($det1["valorbase"] != 0) {
                    $pdf->SetFont('Helvetica', '', 8);
                    $pdf->SetXY(100, $linea);
                    $pdf->Cell(19, 5, "$" . number_format($det1["valorbase"]), 0, 0, 'R');
                }
                if ((trim($det1["ano"]) != '') || (trim($det1["ano"]) != '0000')) {
                    $pdf->SetFont('Helvetica', '', 8);
                    $pdf->SetXY(125, $linea);
                    $pdf->Write(5, $det1["ano"]);
                }

                if ((trim($det1["expediente"]) != '') || (trim($det1["expediente"]) != '00000000')) {
                    $pdf->SetFont('Helvetica', '', 8);
                    $pdf->SetXY(140, $linea);

                    //2017-11-30 WSIERRA:  Blanquear $expediente si cumple las siguientes opciones 
                    switch ($det1["expediente"]) {
                        case 'NUEVANAT':
                        case 'NUEVAJUR':
                        case 'NUEVAEST':
                        case 'NUEVASUC':
                        case 'NUEVAAGE':
                        case 'NUEVAESA':
                            $det1["expediente"] = '';
                            break;
                        default:
                            break;
                    }
                    //

                    $pdf->Write(5, $det1["expediente"]);
                }
                $pdf->SetFont('Helvetica', '', 8);
                $pdf->SetXY(170, $linea);
                $pdf->Cell(15, 4, "$" . number_format($det1["valorservicio"], 2), 0, 0, 'R');
            }
        }

        $linea = validarSaltoPagina($linea, $pdf, $liq, $det, $orig, array(), $genrec, $nir, $nuc, $tiporecibo);
        $linea = $linea + 2;
        $pdf->Line(5, $linea, 190, $linea);

        $linea = $linea + 2;
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetXY(130, $linea);
        $pdf->Write(5, "Valor Total.....");

        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetXY(170, $linea);
        $pdf->Cell(15, 5, "$" . number_format($brutorec), 0, 0, 'R');

        //
        if ($liq["alertavalor"] != 0 && (!defined('SEPARAR_RECIBOS') || SEPARAR_RECIBOS == 'NO' || $tiporecibo == 'S')) {
            $linea = validarSaltoPagina($linea, $pdf, $liq, $det, $orig, array(), $genrec, $nir, $nuc, $tiporecibo);
            $linea = $linea + 2;
            $pdf->SetFont('Helvetica', 'B', 10);
            $pdf->SetXY(130, $linea);
            $pdf->Write(5, "Valor Descuento..");
            $pdf->SetFont('Helvetica', 'B', 10);
            $pdf->SetXY(170, $linea);
            $pdf->Cell(15, 5, "$" . number_format($liq["alertavalor"]), 0, 0, 'R');
        }

        //
        $linea = validarSaltoPagina($linea, $pdf, $liq, $det, $orig, $genrec, $nir, $nuc, $tiporecibo);
        $linea = $linea + 2;
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetXY(130, $linea);
        $pdf->Write(5, "Valor IVA.....");

        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetXY(170, $linea);
        $pdf->Cell(15, 5, "$" . number_format($ivarec), 0, 0, 'R');

        $linea = validarSaltoPagina($linea, $pdf, $liq, $det, $orig, array(), $genrec, $nir, $nuc, $tiporecibo);
        $neto = $brutorec + $ivarec - $liq["alertavalor"];
        $linea = $linea + 2;
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetXY(130, $linea);
        $pdf->Write(5, "Valor NETO....");

        //
        if ($liq["cargogastoadministrativo"] == 'SI' || $liq["cargoafiliacion"] == 'SI' || $liq["cargoentidadoficial"] == 'SI') {
            $pdf->SetFont('Helvetica', 'B', 10);
            $pdf->SetXY(170, $linea);
            $pdf->Cell(15, 5, "$" . '0', 0, 0, 'R');
        } else {
            $pdf->SetFont('Helvetica', 'B', 10);
            $pdf->SetXY(170, $linea);
            $pdf->Cell(15, 5, "$" . number_format($neto), 0, 0, 'R');
        }

        //
        $linea = validarSaltoPagina($linea, $pdf, $liq, $det, $orig, array(), $genrec, $nir, $nuc, $tiporecibo);
        $linea = $linea + 2;
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetXY(5, $linea);
        $pdf->Write(5, "Forma de Pago");
        if ($liq["cargogastoadministrativo"] == 'SI') {
            $linea = validarSaltoPagina($linea, $pdf, $liq, $det, $orig, array(), $genrec, $nir, $nuc, $tiporecibo);
            $linea = $linea + 2;
            $pdf->SetFont('Helvetica', 'B', 10);
            $pdf->SetXY(5, $linea);
            $pdf->Write(5, "*** SIN COSTO PARA EL CLIENTE ***");
        }
        if ($liq["cargoentidadoficial"] == 'SI') {
            $linea = validarSaltoPagina($linea, $pdf, $liq, $det, $orig, array(), $genrec, $nir, $nuc, $tiporecibo);
            $linea = $linea + 2;
            $pdf->SetFont('Helvetica', 'B', 10);
            $pdf->SetXY(5, $linea);
            $pdf->Write(5, "*** SIN COSTO PARA LA ENTIDAD OFICIAL ***");
        }
        if ($liq["cargoafiliacion"] == 'SI') {
            $linea = validarSaltoPagina($linea, $pdf, $liq, $det, $orig, array(), $genrec, $nir, $nuc, $tiporecibo);
            $linea = $linea + 2;
            $pdf->SetFont('Helvetica', 'B', 10);
            $pdf->SetXY(5, $linea);
            $pdf->Write(5, "*** CON CARGO AL CUPO DEL AFILIADO ***");
        }

        //
        foreach ($fpag as $fx) {
            $linea = validarSaltoPagina($linea, $pdf, $liq, $det, $orig, array(), $genrec, $nir, $nuc, $tiporecibo);
            $linea = $linea + 2;
            $pdf->SetFont('Helvetica', 'B', 10);
            $pdf->SetXY(5, $linea);
            switch ($fx["tipo"]) {
                case "1" :
                    $val = $fx["valor"] + $vueltas;
                    $pdf->SetFont('Helvetica', 'B', 10);
                    $pdf->SetXY(5, $linea);
                    $pdf->Write(5, "Pago en Efectivo.....");
                    $pdf->SetFont('Helvetica', 'B', 10);
                    $pdf->SetXY(60, $linea);
                    $pdf->Cell(15, 5, "$" . number_format($val), 0, 0, 'R');
                    if ($vueltas > 0) {
                        $linea = validarSaltoPagina($linea, $pdf, $liq, $det, $orig, array(), $genrec, $nir, $nuc, $tiporecibo);
                        $linea = $linea + 2;
                        $pdf->SetFont('Helvetica', 'B', 10);
                        $pdf->SetXY(5, $linea);
                        $pdf->Write(5, "Vueltas.....");
                        $pdf->SetFont('Helvetica', 'B', 10);
                        $pdf->SetXY(60, $linea);
                        $pdf->Cell(15, 5, "$" . number_format($vueltas), 0, 0, 'R');
                    }
                    break;
                case "2" :
                    $pdf->SetFont('Helvetica', 'B', 10);
                    $pdf->SetXY(5, $linea);
                    $pdf->Write(5, "Pago en Cheque.....");
                    $pdf->SetFont('Helvetica', 'B', 10);
                    $pdf->SetXY(60, $linea);
                    $pdf->Cell(15, 5, "$" . number_format($fx["valor"]), 0, 0, 'R');
                    $linea = validarSaltoPagina($linea, $pdf, $liq, $det, $orig, array(), $genrec, $nir, $nuc, $tiporecibo);
                    $linea = $linea + 2;
                    $pdf->SetFont('Helvetica', 'B', 10);
                    $pdf->SetXY(5, $linea);
                    $pdf->Write(5, \funcionesGenerales::utf8_decode("Número del Cheque....."));
                    $pdf->SetFont('Helvetica', 'B', 10);
                    $pdf->SetXY(80, $linea);
                    $pdf->Cell(15, 5, $fx["cheque"], 0, 0, 'R');
                    $linea = validarSaltoPagina($linea, $pdf, $liq, $det, $orig, array(), $genrec, $nir, $nuc, $tiporecibo);
                    $linea = $linea + 2;
                    $pdf->SetFont('Helvetica', 'B', 10);
                    $pdf->SetXY(5, $linea);
                    $pdf->Cell(60, 5, substr((string) retornarRegistroMysqliApi($dbx, 'bas_codban', "id='" . $fx["banco"] . "'", "nombre"), 0, 30), 0, 0, 'R');
                    break;
                case "3" :
                    $pdf->SetFont('Helvetica', 'B', 10);
                    $pdf->SetXY(5, $linea);
                    $pdf->Write(5, \funcionesGenerales::utf8_decode("Pago en T. Crédito....."));
                    $pdf->SetFont('Helvetica', 'B', 10);
                    $pdf->SetXY(60, $linea);
                    $pdf->Cell(15, 5, "$" . number_format($fx["valor"]), 0, 0, 'R');
                    break;
                case "5" :
                    $pdf->SetFont('Helvetica', 'B', 10);
                    $pdf->SetXY(5, $linea);
                    $pdf->Write(5, \funcionesGenerales::utf8_decode("Pago en Consignación....."));
                    $pdf->SetFont('Helvetica', 'B', 10);
                    $pdf->SetXY(60, $linea);
                    $pdf->Cell(15, 5, "$" . number_format($fx["valor"]), 0, 0, 'R');
                    break;
                case "7" :
                    $pdf->SetFont('Helvetica', 'B', 10);
                    $pdf->SetXY(5, $linea);
                    $pdf->Write(5, \funcionesGenerales::utf8_decode("Pago en T. Débito....."));
                    $pdf->SetFont('Helvetica', 'B', 10);
                    $pdf->SetXY(60, $linea);
                    $pdf->Cell(15, 5, "$" . number_format($fx["valor"]), 0, 0, 'R');
                    break;
                case "8" :
                    $pdf->SetFont('Helvetica', 'B', 10);
                    $pdf->SetXY(5, $linea);
                    $pdf->Write(5, \funcionesGenerales::utf8_decode("Pago con QR....."));
                    $pdf->SetFont('Helvetica', 'B', 10);
                    $pdf->SetXY(60, $linea);
                    $pdf->Cell(15, 5, "$" . number_format($fx["valor"]), 0, 0, 'R');
                    break;
            }
        }

        if ($liq["pagoprepago"] != 0) {
            $linea = validarSaltoPagina($linea, $pdf, $liq, $det, $orig, array(), $genrec, $nir, $nuc, $tiporecibo);
            $linea = $linea + 2;
            $pdf->SetFont('Helvetica', 'B', 10);
            $pdf->SetXY(5, $linea);
            $pdf->Write(5, \funcionesGenerales::utf8_decode("CON CARGO AL CUPO DE PREPAGO ..."));
            $pdf->SetFont('Helvetica', 'B', 10);
            $pdf->SetXY(80, $linea);
            $pdf->Cell(15, 5, "$" . number_format($liq["pagoprepago"]), 0, 0, 'R');
        }

        $linea = validarSaltoPagina($linea, $pdf, $liq, $det, $orig, array(), $genrec, $nir, $nuc, $tiporecibo);
        $linea = $linea + 2;
        $pdf->Line(5, $linea, 190, $linea);
        if (trim($claveprepago) != '') {
            $linea = validarSaltoPagina($linea, $pdf, $liq, $det, $orig, array(), $genrec, $nir, $nuc, $tiporecibo);
            $linea = $linea + 2;
            $pdf->SetFont('Helvetica', 'B', 10);
            $pdf->SetXY(5, $linea);
            $pdf->Cell(90, 5, "Clave prepago: " . $claveprepago, 0, 0, 'L');
        }
        if (intval($saldoprepago) != 0) {
            $linea = validarSaltoPagina($linea, $pdf, $liq, $det, $orig, array(), $genrec, $nir, $nuc);
            $linea = $linea + 2;
            $pdf->SetFont('Helvetica', 'B', 10);
            $pdf->SetXY(5, $linea);
            $pdf->Cell(90, 5, "Saldo prepago: " . number_format($saldoprepago, "0"), 0, 0, 'L');
        }

        //
        if (!defined('SEPARAR_RECIBOS') || SEPARAR_RECIBOS == 'NO') {
            if ($servgob != 0) {
                $linea = validarSaltoPagina($linea, $pdf, $liq, $det, $orig, array(), $genrec, $nir, $nuc, $tiporecibo);
                $linea = $linea + 2;
                if ($_SESSION["generales"]["codigoempresa"] == '55') {

                    $txt = 'Le informamos que usted está pagando:';
                    $pdf->SetFont('Helvetica', '', 10);
                    $pdf->SetXY(5, $linea);
                    $pdf->MultiCell(180, 5, \funcionesGenerales::utf8_decode($txt), 0, 'J', 0);

                    if ($rec["tipogasto"] == '7') {
                        $linea = $linea + 5;
                        $txt = '- IMPUESTO DE REGISTRO  a favor de la Gobernación correspondiente: $' . number_format($servgob, 0);
                        $pdf->SetFont('Helvetica', '', 10);
                        $pdf->SetXY(5, $linea);
                        $pdf->MultiCell(180, 5, \funcionesGenerales::utf8_decode($txt), 0, 'J', 0);
                    } else {
                        $linea = $linea + 5;
                        $txt = '- IMPUESTO DE REGISTRO  a favor de la Gobernación de Antioquia: $' . number_format($servgob, 0);
                        $pdf->SetFont('Helvetica', '', 10);
                        $pdf->SetXY(5, $linea);
                        $pdf->MultiCell(180, 5, \funcionesGenerales::utf8_decode($txt), 0, 'J', 0);
                    }

                    $linea = $linea + 5;
                    $txt = '- TRÁMITE REGISTRAL  ante Cámara de Comercio Aburrá Sur:  $' . number_format($servcam, 0);
                    $pdf->SetFont('Helvetica', '', 10);
                    $pdf->SetXY(5, $linea);
                    $pdf->MultiCell(180, 5, \funcionesGenerales::utf8_decode($txt), 0, 'J', 0);

                    $linea = $linea + 5;
                    $pdf->Line(5, $linea, 190, $linea);

                    /*
                      $txt = 'Le informamos que usted está pagando: ue con este Recibo de Pago, por un total de $' . number_format($servtot, 0) . ', Usted no sólo está pagando ';
                      $txt .= 'lo correspondiente a su trámite registral ante la Cámara de Comercio Aburrá Sur, ';
                      $txt .= 'equivalente a $' . number_format($servcam, 0) . ', ';
                      $txt .= 'sino también el Impuesto de Registro para el ';
                      $txt .= 'Departamento de Antioquia, por valor de $' . number_format($servgob, 0) . ', con Nit 890.900.286-0, en calidad de Sujeto Activo.';
                     */
                } else {
                    $txt = 'Queremos informarle que con este Recibo de Pago, por un total de $' . number_format($servtot, 0) . ', Usted no sólo está pagando ';
                    $txt .= 'lo correspondiente a su trámite registral ante la Cámara de Comercio, ';
                    $txt .= 'equivalente a $' . number_format($servcam, 0) . ', ';
                    $txt .= 'sino también el Impuesto de  Registro para la ';
                    $txt .= 'Gobernación, por valor de $' . number_format($servgob, 0) . ', en calidad de sujeto activo.';

                    $pdf->SetFont('Helvetica', '', 10);
                    $pdf->SetXY(5, $linea);
                    $pdf->MultiCell(180, 5, \funcionesGenerales::utf8_decode($txt), 0, 'J', 0);
                    $linea = $pdf->GetY();
                    $linea = $linea + 5;
                    $pdf->Line(5, $linea, 190, $linea);
                }
            }
        }

        if (trim($liq["numeroradicacion"]) != '') {
            if (defined('URL_CONSULTA_SOLICITUDES') && trim(URL_CONSULTA_SOLICITUDES) != '') {
                $urlcon = URL_CONSULTA_SOLICITUDES;
                $urlcon = str_replace("[EMPRESA]", $_SESSION["generales"]["codigoempresa"], $urlcon);
                $urlcon = str_replace("[CODIGOBARRAS]", $liq["numeroradicacion"], $urlcon);
            } else {
                $urlcon = "https://sii.confecamaras.co/vista/plantilla/consultarSolicitudes.php?empresa=" . $_SESSION["generales"]["codigoempresa"] . "&radicado=" . $liq["numeroradicacion"];
                $urlcon = '';
            }
            $linea = validarSaltoPagina($linea, $pdf, $liq, $det, $orig, array(), $genrec, $nir, $nuc, $tiporecibo);
            $linea = $linea + 2;
            $txt = "";
            $txt .= "Para conocer el estado de su trámite por favor comuníquese con el número " . TELEFONO_ATENCION_USUARIOS;
            if ($urlcon != '') {
                $txt .= " y cite el nro. " . $liq["numeroradicacion"] . ". Puede igualmente dirigirse a " . $urlcon;
            } else {
                $txt .= " y cite el nro. " . $liq["numeroradicacion"] . ".";
            }
            $pdf->SetFont('Helvetica', '', 10);
            $pdf->SetXY(5, $linea);
            $pdf->MultiCell(180, 5, \funcionesGenerales::utf8_decode($txt), 0, 'C', 0);
            $linea = $pdf->GetY();
            $linea = $linea + 5;
            $pdf->Line(5, $linea, 190, $linea);
        }
        $numrec = $liq["numerorecibo"];
    }

    if (ltrim(trim($numliq), "0") == 0) {

        // $dimension = array(106, 140);
        $pdf = new PDFRecibo("Portrait", "mm");
        $pdf->AliasNbPages();
        $linea = encabezadosRecibo($pdf, array(), array(), $orig, $arreglo, $genrec, $nir, $nuc, $tiporecibo);
        foreach ($arreglo["renglones"] as $det1) {
            $linea = validarSaltoPagina($linea, $pdf, array(), array(), $orig, $arreglo, $genrec, $nir, $nuc, $tiporecibo);
            $linea = $linea + 3;
            $pdf->SetFont('Helvetica', '', 6);
            $pdf->SetXY(5, $linea);
            $pdf->Write(4, number_format($det1["cantidad"]));
            $pdf->SetFont('Helvetica', '', 6);
            $pdf->SetXY(10, $linea);
            $pdf->Write(4, $det1["servicio"]);
            $pdf->SetFont('Helvetica', '', 6);
            $pdf->SetXY(20, $linea);
            $pdf->Write(4, substr($det1["nombre"], 0, 15));
            if ($det1["valorbase"] != 0) {
                $pdf->SetFont('Helvetica', '', 6);
                $pdf->SetXY(42, $linea);
                $pdf->Cell(19, 4, "$" . number_format($det1["valorbase"]), 0, 0, 'R');
            }
            if ((trim($det1["ano"]) != '') || (trim($det1["ano"]) != '0000')) {
                $pdf->SetFont('Helvetica', '', 6);
                $pdf->SetXY(61, $linea);
                $pdf->Write(4, $det1["ano"]);
            }
            if ((trim($det1["expediente"]) != '') || (trim($det1["expediente"]) != '00000000')) {
                $pdf->SetFont('Helvetica', '', 6);
                $pdf->SetXY(69, $linea);
                $pdf->Write(4, $det1["expediente"]);
            }
            $pdf->SetFont('Helvetica', '', 6);
            $pdf->SetXY(79, $linea);
            $pdf->Cell(15, 4, "$" . number_format($det1["valor"]), 0, 0, 'R');
        }
        $linea = validarSaltoPagina($linea, $pdf, array(), array(), $orig, $arreglo, $genrec, $nir, $nuc, $tiporecibo);
        $linea = $linea + 5;
        $pdf->Line(5, $linea, 95, $linea);

        $linea = $linea + 2;
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetXY(55, $linea);
        $pdf->Write(4, "Valor Total.....");
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetXY(75, $linea);
        $pdf->Cell(15, 5, "$" . number_format($arreglo["valortotal"]), 0, 0, 'R');

        /*
          $linea = validarSaltoPagina($linea, $pdf, array(), array(), $orig, $arreglo, $genrec, $nir, $nuc, $tiporecibo);
          $linea = $linea + 2;
          $pdf->SetFont('Helvetica', 'B', 10);
          $pdf->SetXY(55, $linea);
          $pdf->Write(4, "Valor Descuento..");
          $pdf->SetFont('Helvetica', 'B', 10);
          $pdf->SetXY(75, $linea);
          $pdf->Cell(15, 5, "$" . number_format($arreglo["alertavalor"]), 0, 0, 'R');
         */

        $linea = validarSaltoPagina($linea, $pdf, array(), array(), $orig, $arreglo, $genrec, $nir, $nuc, $tiporecibo);
        $linea = $linea + 2;
        $pdf->SetFont('Helvetica', 'B', 6);
        $pdf->SetXY(55, $linea);
        $pdf->Write(4, "Valor IVA.....");
        $pdf->SetFont('Helvetica', 'B', 6);
        $pdf->SetXY(75, $linea);
        $pdf->Cell(15, 4, "$" . number_format($arreglo["valoriva"]), 0, 0, 'R');
        $linea = validarSaltoPagina($linea, $pdf, array(), array(), $orig, $arreglo, $genrec, $nir, $nuc, $tiporecibo);
        // $neto = $arreglo["valortotal"] - $arreglo["alertavalor"];
        $neto = $arreglo["valortotal"];
        $linea = $linea + 2;
        $pdf->SetFont('Helvetica', 'B', 6);
        $pdf->SetXY(55, $linea);
        $pdf->Write(4, "Valor NETO....");
        $pdf->SetFont('Helvetica', 'B', 6);
        $pdf->SetXY(75, $linea);
        $pdf->Cell(15, 4, "$" . number_format($neto), 0, 0, 'R');
        $linea = validarSaltoPagina($linea, $pdf, array(), array(), $orig, $arreglo, $genrec, $nir, $nuc, $tiporecibo);
        $linea = $linea + 3;
        $pdf->SetFont('Helvetica', 'B', 6);
        $pdf->SetXY(5, $linea);
        $pdf->Write(4, "Forma de Pago");
        if ($arreglo["formapago"] == '01') {
            $fpago = 'Pago en efectivo';
        }
        if ($arreglo["formapago"] == '02') {
            $fpago = 'Pago en cheque';
        }
        if ($arreglo["formapago"] == '03') {
            $fpago = 'Pago con T. DEBITO';
        }
        if ($arreglo["formapago"] == '04') {
            $fpago = 'Pago Sistema PSE / ACH';
        }
        if ($arreglo["formapago"] == '05') {
            $fpago = 'Pago Sistema VISA';
        }
        if ($arreglo["formapago"] == '06') {
            $fpago = 'Pago Sistema MASTERCARD';
        }
        if ($arreglo["formapago"] == '07') {
            $fpago = 'Pago Sistema AMERICAN';
        }
        if ($arreglo["formapago"] == '08') {
            $fpago = 'Pago Sistema DINERS';
        }
        if ($arreglo["formapago"] == '09') {
            $fpago = 'Pago Sistema CREDENCIAL';
        }
        if ($arreglo["formapago"] == '10') {
            $fpago = 'Pago Consignación';
        }
        if ($arreglo["formapago"] == '91') {
            $fpago = 'Con cargo a prepago';
        }
        if ($arreglo["formapago"] == '92') {
            $fpago = 'Con cargo al cupo de afiliado';
        }

        $linea = validarSaltoPagina($linea, $pdf, array(), array(), $orig, $arreglo, $genrec, $nir, $nuc, $tiporecibo);
        $linea = $linea + 3;
        $pdf->SetFont('Helvetica', 'B', 6);
        $pdf->SetXY(5, $linea);
        $pdf->Write(4, $fpago);
        $pdf->SetFont('Helvetica', 'B', 6);
        $pdf->SetXY(35, $linea);
        $pdf->Cell(15, 4, "$" . number_format($arreglo["valortotal"]), 0, 0, 'R');
        $linea = $linea + 3;
        if ($arreglo["formapago"] == '02') {
            $pdf->SetFont('Helvetica', 'B', 6);
            $pdf->SetXY(5, $linea);
            $pdf->Write(4, "Número del Cheque.....");
        } else {
            if ($arreglo["formapago"] != '01') {
                $pdf->SetFont('Helvetica', 'B', 6);
                $pdf->SetXY(5, $linea);
                $pdf->Write(4, "Número autorización.....");
            }
        }
        $pdf->SetFont('Helvetica', 'B', 6);
        $pdf->SetXY(35, $linea);
        $pdf->Cell(15, 4, $arreglo["numcheque"], 0, 0, 'R');
        $linea = $linea + 3;
        if (($arreglo["formapago"] == '02') || ($arreglo["formapago"] == '02') || ($arreglo["formapago"] == '04')) {
            $pdf->SetFont('Helvetica', 'B', 6);
            $pdf->SetXY(5, $linea);
            $pdf->Cell(45, 4, substr(retornarRegistroMysqliApi($dbx, 'bas_codban', "id='" . $liq["idcodban"] . "'", "nombre"), 0, 30), 0, 0, 'R');
        }
        $linea = validarSaltoPagina($linea, $pdf, array(), array(), $orig, $arreglo, $genrec, $nir, $nuc, $tiporecibo);
        $linea = $linea + 4;
        $pdf->Line(5, $linea, 95, $linea);
        if (trim($arreglo["facturacancelada"]) != '') {
            $linea = $linea + 3;
            $pdf->SetFont('Helvetica', '', 6);
            $pdf->SetXY(5, $linea);
            $pdf->Cell(90, 4, "Factura que se cancela o abona: " . $arreglo["facturacancelada"], 0, 0, 'L');
        }
        if (trim($claveprepago) != '') {
            $linea = $linea + 3;
            $pdf->SetFont('Helvetica', '', 6);
            $pdf->SetXY(5, $linea);
            $pdf->Cell(90, 4, "Clave prepago: " . $claveprepago, 0, 0, 'L');
        }
        if (intval($saldoprepago) != 0) {
            $linea = $linea + 3;
            $pdf->SetFont('Helvetica', '', 6);
            $pdf->SetXY(5, $linea);
            $pdf->Cell(90, 4, "Saldo prepago: " . $saldoprepago, 0, 0, 'L');
        }

        if (trim($arreglo["codbarras"]) != '') {
            // $linea=$linea+3;
            $linea = $linea + 3;
            $txt = "";
            $txt .= "Para conocer el estado de su trámite por favor comuníquese con el número " . TELEFONO_ATENCION_USUARIOS;
            $txt .= " y cite el nro. " . $arreglo["codbarras"] . ". Puede igualmente dirigirse a " . TIPO_HTTP . HTTP_HOST . "/cnr.php?em=" . $_SESSION["generales"]["codigoempresa"] . "&cb=" . $arreglo["codbarras"];
            $pdf->SetFont('Helvetica', '', 4);
            $pdf->SetXY(5, $linea);
            $pdf->MultiCell(90, 2, \funcionesGenerales::utf8_decode($txt), 0, 'J', 0);
            $linea = $pdf->GetY();
            $linea = $linea + 1;
            $pdf->Line(5, $linea, 95, $linea);
        }
        $numrec = $arreglo["numerorecibo"];
    }

    // Mensaje de envio de factura electrónica
    if ($neto != 0) {
        if (defined('CFE_FECHA_INICIAL') && CFE_FECHA_INICIAL != '' && date("Ymd") >= CFE_FECHA_INICIAL) {
            if ($factele == 'si') {
                $xdes = '';
                if (defined('CFE_EMAIL_ATENCION_USUARIOS') && CFE_EMAIL_ATENCION_USUARIOS != '') {
                    $xdes .= ' correo electrónico ' . CFE_EMAIL_ATENCION_USUARIOS;
                }
                if (defined('CFE_TELEFONO_ATENCION_USUARIOS') && CFE_TELEFONO_ATENCION_USUARIOS != '') {
                    if ($xdes != '') {
                        $xdes .= ' o al ';
                    }
                    $xdes .= 'No. ' . CFE_TELEFONO_ATENCION_USUARIOS;
                }
                if ($xdes == '') {
                    $xdes .= 'No. ' . TELEFONO_ATENCION_USUARIOS;
                }
                $linea = $linea + 5;
                //texto.envio.facturaelectronica
                $tefe = \funcionesGenerales::cambiarSustitutoHtmlBootstrap(retornarPantallaPredisenadaMysqliApi(null, 'texto.envio.facturaelectronica'));
                if (trim((string) $tefe) != '') {
                    $txt = str_replace("[EMAILCLIENTE]", $liq["email"], $tefe);
                } else {
                    if ($_SESSION["generales"]["codigoempresa"] == '55') {
                        $txt = "La factura electrónica correspondiente con este trámite será enviada al correo electrónico " . $liq["email"] . ". ";
                        $txt .= "En caso de no recibirla, por favor diligencie la información en el link http://reenviofactura.ccas.org.co, alternativamente por favor comunicarse al " . $xdes;
                    } else {
                        $txt = "La factura electrónica correspondiente con este trámite será enviada al correo electrónico " . $liq["email"] . ". ";
                        $txt .= "En caso que la factura electrónica no llegue al correo indicado, por favor comunicarse al " . $xdes;
                    }
                }
                $pdf->SetFont('Helvetica', '', 10);
                $pdf->SetXY(5, $linea);
                // $pdf->writeHTML($txt, true, false, true, false, 'C');
                // $pdf->MultiCell(180, 5, html_entity_decode(\funcionesGenerales::utf8_decode($txt)), 0, 'J', 0);
                $txt = \funcionesGenerales::reemplazarHtmlPdf($txt);
                $pdf->MultiCell(180, 5, \funcionesGenerales::utf8_decode($txt), 0, 'J', 0);
                $linea = $pdf->GetY();
                $linea = $linea + 4;
                $pdf->Line(5, $linea, 190, $linea);
            }
        }
    }

    //
    $tefr = \funcionesGenerales::cambiarSustitutoHtmlBootstrap(retornarPantallaPredisenadaMysqliApi(null, 'texto.final.recibo'));
    if (trim((string) $tefr) != '') {
        $txt = \funcionesGenerales::reemplazarHtmlPdf($tefr);
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetXY(5, $linea);
        $pdf->MultiCell(180, 5, \funcionesGenerales::utf8_decode($txt), 0, 'J', 0);
        $linea = $pdf->GetY();
        $linea = $linea + 4;
        $pdf->Line(5, $linea, 190, $linea);
    }

    //
    unset($liq);
    unset($det);
    unset($det1);

    // Genera la salida
    if ($tiposalida == 'D') {
        $pdf->Output('ReciboDeCaja.pdf', 'D');
        $name = '';
        return $name;
    } else {
        if ($tiposalida == 'T') {
            $pdf->Output($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . session_id() . '-recibo-' . $numliq . '.pdf', 'F');
            $name = 'tmp/' . session_id() . '-recibo-' . $numliq . '.pdf';
            return $name;
        } else {
            if (!is_dir(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"])) {
                mkdir(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"], 0777);
            }
            if (!is_dir(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/mreg/")) {
                mkdir(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/mreg/", 0777);
            }
            if (!is_dir(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/mreg/recibosCaja/")) {
                mkdir(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/mreg/recibosCaja/", 0777);
            }
            if (!is_dir(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/mreg/recibosCaja/" . date("Y"))) {
                mkdir(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/mreg/recibosCaja/" . date("Y"), 0777);
            }
            $name = PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/mreg/recibosCaja/" . date("Y") . "/" . $numliq . "-Recibo-" . $numrec . '-' . date("Ymd") . '-' . $tiporecibo . ".pdf";
            $name1 = PATH_RELATIVO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/mreg/recibosCaja/" . date("Y") . "/" . $numliq . "-Recibo-" . $numrec . '-' . date("Ymd") . '-' . $tiporecibo . ".pdf";
            $pdf->Output($name, "F");
            return $name1;
        }
    }
    exit();
}

function armarPdfRadicado($dbx, $numliq) {
    require_once ('generaGS1128.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/tcpdf_6.7.5/tcpdf.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/tcpdf_6.7.5/examples/lang/eng.php');
    ob_clean();

    if (!class_exists('PDFRadicado')) {

        class PDFRadicado extends TCPDF {
            
        }

    }

    //
    $liq = \funcionesRegistrales::retornarMregLiquidacion($dbx, $numliq);

    //
    $expbase = '';
    if (trim($liq["idexpedientebase"]) != '') {
        $expbase = $liq["idexpedientebase"];
    } else {
        if (trim($liq["idmatriculabase"]) != '') {
            $expbase = $liq["idmatriculabase"];
        } else {
            if (trim($liq["idproponentebase"]) != '') {
                $expbase = $liq["idproponentebase"];
            }
        }
    }

    // Imprime encabezados
    $pdf = new PDFRadicado(PDF_PAGE_ORIENTATION, PDF_UNIT, 'LETTER', true, 'UTF-8', false, true);
    // $pdf = new PDFRadicado("Portrait", "mm");
    $pdf->AddPage();

    // Arma el c&oacute;digo de barras
    $imagen = $_SESSION["generales"]["pathabsoluto"] . '/tmp/cbliq-' . $_SESSION["generales"]["codigoempresa"] . '-' . $numliq . '.png';
    generarGs1128cb($liq["numeroradicacion"], $imagen);
    $pdf->Image($imagen, 80, 40, 50, 20);

    $pdf->SetXY(10, 70);
    $pdf->SetFont('courier', '', 14);
    $pdf->writeHTML('<strong>SOPORTE DE RADICACION</strong>', true, false, true, false, 'C');
    $pdf->Ln();
    $pdf->SetFont('courier', '', 10);
    $pdf->writeHTML('<strong>' . RAZONSOCIAL . '</strong>', true, false, true, false, 'C');
    $pdf->Ln();
    $pdf->writeHTML('<strong>RADICADO NO. ' . $liq["numeroradicacion"] . '</strong>', true, false, true, false, 'C');
    $pdf->Ln();
    $pdf->writeHTML('<strong>RECIBO NO. ' . $liq["numerorecibo"] . '</strong>', true, false, true, false, 'C');
    $pdf->Ln();
    $pdf->writeHTML('<strong>NUMERO OPERACION : ' . $liq["numerooperacion"] . '</strong>', true, false, true, false, 'C');
    $pdf->Ln();
    $pdf->writeHTML('<strong>LIQUIDACION NO. ' . $liq["idliquidacion"] . '</strong>', true, false, true, false, 'C');
    $pdf->Ln();
    $pdf->writeHTML('<strong>RECUPERACION : ' . $liq["numerorecuperacion"] . '</strong>', true, false, true, false, 'C');
    $pdf->Ln();
    $pdf->writeHTML('<strong>FECHA : ' . \funcionesGenerales::mostrarFecha($liq["fecharecibo"]) . '</strong>', true, false, true, false, 'C');
    $pdf->Ln();
    $pdf->writeHTML('<strong>HORA : ' . \funcionesGenerales::mostrarHora($liq["horarecibo"]) . '</strong>', true, false, true, false, 'C');
    $pdf->Ln();
    $pdf->writeHTML('<strong>TIPO TRAMITE : ' . $liq["tipotramite"] . '</strong>', true, false, true, false, 'C');
    $pdf->Ln();
    if ($liq["subtipotramite"] != '') {
        $pdf->writeHTML('<strong>SUB TIPO TRAMITE : ' . $liq["subtipotramite"] . '</strong>', true, false, true, false, 'C');
        $pdf->Ln();
    }
    $pdf->writeHTML('<strong>EXPEDIENTE BASE : ' . $expbase . '</strong>', true, false, true, false, 'C');
    $pdf->Ln();
    $pdf->writeHTML('<strong>NOMBRE BASE : ' . $liq["nombrebase"] . '</strong>', true, false, true, false, 'C');
    $pdf->Ln();
    if (!defined('MANEJO_FOLIOS_HOJAS')) {
        define('MANEJO_FOLIOS_HOJAS', 'S');
    }
    if (MANEJO_FOLIOS_HOJAS == '' || MANEJO_FOLIOS_HOJAS == 'S') {
        if (intval($liq["cantidadfolios"]) != 0) {
            $pdf->writeHTML('<strong>FOLIOS : ' . $liq["cantidadfolios"] . '</strong>', true, false, true, false, 'C');
            $pdf->Ln();
        }
        if (intval($liq["cantidadhojas"]) != 0) {
            $pdf->writeHTML('<strong>HOJAS : ' . $liq["cantidadhojas"] . '</strong>', true, false, true, false, 'C');
            $pdf->Ln();
        }
    }
    $name = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-codigobarras-' . $numliq . '.pdf';
    $name1 = 'tmp/' . $_SESSION["generales"]["codigoempresa"] . '-codigobarras-' . $numliq . '.pdf';
    $pdf->Output($name, 'F');
    unset($pdf);
    return $name1;
}

?>