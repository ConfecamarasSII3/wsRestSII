<?php

/**
 * Función que imprime el recibo de caja y/o factura de venta a crédito
 * -
 * @param 	string		$numliq		Número de liquidación
 */
function encabezadosReciboSii($pdf, $liq, $det, $orig, $arreglo = array(), $genrec = '') {
    require_once ('../../../configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');

    $pdf->AddPage();
    if (!defined('IMPRIMIR_LOGO_EN_RECIBO')) {
        define('IMPRIMIR_LOGO_EN_RECIBO', 'S');
    }
    if (IMPRIMIR_LOGO_EN_RECIBO == 'S') {
        if (file_exists($_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . CODIGO_EMPRESA . '.jpg')) {
            $pdf->Image($_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . CODIGO_EMPRESA . '.jpg', 10, 7, 25, 25);
        }
        // $pdf->Image($_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . CODIGO_EMPRESA . '.jpg', 10, 7, 25, 25);
    }
    if ($orig != 'SI') {
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetXY(30, 9);
        $pdf->Cell(10);
        $pdf->Cell(50, 4, '*** ESTA ES UNA COPIA DEL ORIGINAL***', 0, 0, 'R');
    }
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->SetXY(30, 12);
    $pdf->Cell(10);
    $pdf->Cell(50, 4, RAZONSOCIAL_RESUMIDA, 0, 0, 'R');
    // $longnit=strlen(NIT);
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->SetXY(30, 15);
    $pdf->Cell(10);
    $pdf->Cell(50, 4, "Nit. " . NIT, 0, 0, 'R');
    if (!empty($liq)) {
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetXY(30, 18);
        $pdf->Cell(10);
        $pdf->Cell(50, 4, "RECIBO No. " . $liq["numerorecibo"], 0, 0, 'R');
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetXY(30, 21);
        $pdf->Cell(10);
        $pdf->Cell(50, 4, "Documento equivalente a la factura No " . $liq["numerorecibo"], 0, 0, 'R');
    } else {
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetXY(30, 18);
        $pdf->Cell(10);
        $pdf->Cell(50, 4, "RECIBO No. " . $arreglo["numerorecibo"], 0, 0, 'R');
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetXY(30, 21);
        $pdf->Cell(10);
        $pdf->Cell(50, 4, "Documento equivalente a la factura No " . $arreglo["numerorecibo"], 0, 0, 'R');
    }
    if (!empty($liq)) {
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(30, 27);
        $pdf->Cell(10);
        $pdf->Cell(50, 4, utf8_decode("Nro. operación. " . $liq["numerooperacion"]), 0, 0, 'R');
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(30, 30);
        $pdf->Cell(10);
        $pdf->Cell(50, 4, utf8_decode("Nro. liquidación virtual. " . $liq["idliquidacion"]), 0, 0, 'R');
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(30, 33);
        $pdf->Cell(10);
        $pdf->Cell(50, 4, "Fecha y hora. " . \funcionesSii2::mostrarFecha($liq["fecharecibo"]) . " - " . \funcionesSii2::mostrarHora($liq["horarecibo"]), 0, 0, 'R');
    } else {
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(30, 27);
        $pdf->Cell(10);
        $pdf->Cell(50, 4, "Nro. operación. " . $arreglo["numerooperacion"], 0, 0, 'R');
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(30, 33);
        $pdf->Cell(10);
        $pdf->Cell(50, 4, "Fecha y hora. " . \funcionesSii2::mostrarFecha($arreglo["fecharecibo"]) . " - " . \funcionesSii2::mostrarHora($arreglo["horarecibo"]), 0, 0, 'R');
    }
    $pdf->SetFont('Arial', '', 6);
    $pdf->SetXY(30, 36);
    $pdf->Cell(10);
    $pdf->Cell(50, 4, "Recibo expedido en forma virtual", 0, 0, 'R');
    if (!empty($liq)) {
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(30, 39);
        $pdf->Cell(10);
        $pdf->Cell(50, 4, utf8_decode("Nro. recuperación. " . $liq["numerorecuperacion"]), 0, 0, 'R');
    } else {
        if (trim($arreglo["numerorecuperacion"]) != '') {
            $pdf->SetFont('Arial', '', 6);
            $pdf->SetXY(30, 39);
            $pdf->Cell(10);
            $pdf->Cell(50, 4, utf8_decode("Nro. recuperación. " . $arreglo["numerorecuperacion"]), 0, 0, 'R');
        }
    }
    if (isset($_SESSION["generales"]["escajero"])) {
        if ($_SESSION["generales"]["escajero"] == 'SI') {
            $pdf->SetFont('Arial', '', 6);
            $pdf->SetXY(30, 42);
            $pdf->Cell(10);
            $pdf->Cell(50, 4, "Cajero: " . $_SESSION["generales"]["codigousuario"], 0, 0, 'R');
        }
    }
    if (!empty($liq)) {
        if (trim($liq["nombrepagador"]) == '') {
            $pdf->SetFont('Arial', '', 6);
            $pdf->SetXY(5, 45);
            $pdf->Write(4, "Nombre: " . utf8_decode($liq["nombrecliente"]) . ' ' . utf8_decode($liq["apellidocliente"]));
        } else {
            $pdf->SetFont('Arial', '', 6);
            $pdf->SetXY(5, 45);
            $pdf->Write(4, "Nombre: " . utf8_decode($liq["apellidopagador"]) . ' ' . utf8_decode($liq["nombrepagador"]));
        }
    } else {
        if (isset($arreglo["nombrepagador"])) {
            if (trim($arreglo["nombrepagador"]) == '') {
                $pdf->SetFont('Arial', '', 6);
                $pdf->SetXY(5, 45);
                $pdf->Write(4, "Nombre: " . $arreglo["nombre"]);
            } else {
                $pdf->SetFont('Arial', '', 6);
                $pdf->SetXY(5, 45);
                $pdf->Write(4, "Nombre: " . $arreglo["apellidopagador"] . ' ' . $arreglo["nombrepagador"]);
            }
        } else {
            $pdf->SetFont('Arial', '', 6);
            $pdf->SetXY(5, 45);
            $pdf->Write(4, "Nombre: " . $arreglo["nombre"]);
        }
    }
    if (!empty($liq)) {
        if (trim($liq["nombrepagador"]) == '') {
            if ($liq["idtipoidentificacioncliente"] != '2') {
                $pdf->SetFont('Arial', '', 6);
                $pdf->SetXY(5, 48);
                $pdf->Write(4, "Identificacion: " . number_format($liq["identificacioncliente"], 0));
            } else {
                $pdf->SetFont('Arial', '', 6);
                $pdf->SetXY(5, 48);
                $pdf->Write(4, "Identificacion: " . $liq["identificacioncliente"]);
            }
        } else {
            if ($liq["tipoidentificacionpagador"] != '2') {
                $pdf->SetFont('Arial', '', 6);
                $pdf->SetXY(5, 48);
                $pdf->Write(4, "Identificacion: " . number_format($liq["identificacionpagador"]));
            } else {
                $pdf->SetFont('Arial', '', 6);
                $pdf->SetXY(5, 48);
                $pdf->Write(4, "Identificacion: " . $liq["identificacionpagador"]);
            }
        }
    } else {
        if (isset($arreglo["nombrepagador"])) {
            if (trim($arreglo["nombrepagador"]) == '') {
                if ($arreglo["tipoid"] != '2') {
                    $pdf->SetFont('Arial', '', 6);
                    $pdf->SetXY(5, 48);
                    $pdf->Write(4, "Identificacion: " . number_format($arreglo["identificacion"]));
                } else {
                    $pdf->SetFont('Arial', '', 6);
                    $pdf->SetXY(5, 48);
                    $pdf->Write(4, "Identificacion: " . $arreglo["identificacion"]);
                }
            } else {
                if ($arreglo["tipoidentificacionpagador"] != '2') {
                    $pdf->SetFont('Arial', '', 6);
                    $pdf->SetXY(5, 48);
                    $pdf->Write(4, "Identificacion: " . number_format($arreglo["identificacionpagador"]));
                } else {
                    $pdf->SetFont('Arial', '', 6);
                    $pdf->SetXY(5, 48);
                    $pdf->Write(4, "Identificacion: " . $arreglo["identificacionpagador"]);
                }
            }
        } else {
            if ($arreglo["tipoid"] != '2') {
                $pdf->SetFont('Arial', '', 6);
                $pdf->SetXY(5, 48);
                $pdf->Write(4, "Identificacion: " . number_format($arreglo["identificacion"]));
            } else {
                $pdf->SetFont('Arial', '', 6);
                $pdf->SetXY(5, 48);
                $pdf->Write(4, "Identificacion: " . $arreglo["identificacion"]);
            }
        }
    }
    if (!empty($liq)) {
        if (trim($liq["direccionpagador"]) == '') {
            $pdf->SetFont('Arial', '', 6);
            $pdf->SetXY(5, 51);
            $pdf->Write(4, utf8_decode("Dirección: " . $liq["direccion"]));
            $pdf->SetFont('Arial', '', 6);
            $pdf->SetXY(5, 54);
            $pdf->Write(4, utf8_decode("Teléfono: " . $liq["telefono"]));
        } else {
            $pdf->SetFont('Arial', '', 6);
            $pdf->SetXY(5, 51);
            $pdf->Write(4, utf8_decode("Dirección: " . $liq["direccionpagador"]));
            $pdf->SetFont('Arial', '', 6);
            $pdf->SetXY(5, 54);
            $pdf->Write(4, utf8_decode("Teléfono: " . $liq["telefonopagador"]));
        }
    } else {
        if (isset($arreglo["direccionpagador"])) {
            if (trim($arreglo["direccionpagador"]) == '') {
                $pdf->SetFont('Arial', '', 6);
                $pdf->SetXY(5, 51);
                $pdf->Write(4, utf8_decode("Dirección: " . $liq["direccion"]));
                $pdf->SetFont('Arial', '', 6);
                $pdf->SetXY(5, 54);
                $pdf->Write(4, utf8_decode("Teléfono: " . $liq["telefono"]));
            } else {
                $pdf->SetFont('Arial', '', 6);
                $pdf->SetXY(5, 51);
                $pdf->Write(4, utf8_decode("Dirección: " . $liq["direccionpagador"]));
                $pdf->SetFont('Arial', '', 6);
                $pdf->SetXY(5, 54);
                $pdf->Write(4, utf8_decode("Teléfono: " . $liq["telefonopagador"]));
            }
        } else {
            if (!isset($arreglo["direccion"])) {
                $arreglo["direccion"] = '';
            }
            if (!isset($arreglo["telefono"])) {
                $arreglo["telefono"] = '';
            }
            $pdf->SetFont('Arial', '', 6);
            $pdf->SetXY(5, 51);
            $pdf->Write(4, utf8_decode("Dirección: " . $arreglo["direccion"]));
            $pdf->SetFont('Arial', '', 6);
            $pdf->SetXY(5, 54);
            $pdf->Write(4, utf8_decode("Teléfono: " . $arreglo["telefono"]));
        }
    }

    $linea = 57;
    $linea = $linea + 3;
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->SetXY(5, $linea);
    $pdf->Write(4, "Cant");
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->SetXY(10, $linea);
    $pdf->Write(4, "Servicio");
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->SetXY(20, $linea);
    $pdf->Write(4, utf8_decode("Descripción"));
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->SetXY(42, $linea);
    $pdf->Write(4, "    Base/Activos");
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->SetXY(61, $linea);
    $pdf->Write(4, utf8_decode("Año"));
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->SetXY(69, $linea);
    $pdf->Write(4, "Mat/Ins");
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->SetXY(79, $linea);
    $pdf->Write(4, "    Valor");
    $linea = $linea + 4;
    $pdf->Line(5, $linea, 95, $linea);
    return $linea;
}


function validarSaltoPaginaSii($linea, $pdf, $liq, $det, $orig, $arreglo = array(), $genrec = '') {
    if ($linea > 107) {
        $linea = $linea + 3;
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetXY(5, $linea);
        $pdf->Write(5, '****** CONTINUA ******');
        $linea = encabezadosReciboSii($pdf, $liq, $det, $orig, $arreglo, $genrec);
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
function armarPdfReciboSii($dbx,$numliq, $tiposalida = 'D', $orig = 'SI', $arreglo = array(), $claveprepago = '', $saldoprepago = '', $genrec = '') {
    require_once ('../../../configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ('fpdf153/fpdf_protection.php');
    if (!defined('FPDF_FONTPATH')) {
        define('FPDF_FONTPATH', 'fpdf153/fonts/');
    }
    if (!class_exists('PDFRecibo')) {

        class PDFRecibo extends FPDF {
            
        }

    }

    if ($numliq != 0) {

        $liq = retornarRegistroMysqli2($dbx,'mreg_liquidacion',"idliquidacion=" . $numliq);
        $det = retornarRegistrosMysqli2($dbx,'mreg_liquidaciondetalle',"idliquidacion=" . $numliq,"idsec");
        // Imprime encabezados
        $dimension = array(106, 140);
        $pdf = new PDFRecibo("Portrait", "mm", $dimension);
        // $pdf->SetProtection();
        $pdf->AliasNbPages();
        $linea = encabezadosReciboSii($pdf, $liq, $det, $orig, array(), $genrec);
        foreach ($det as $det1) {
            $linea = validarSaltoPaginaSii($linea, $pdf, $liq, $det, $orig, array(), $genrec);
            $linea = $linea + 3;
            $pdf->SetFont('Arial', '', 6);
            $pdf->SetXY(5, $linea);
            $pdf->Write(4, number_format($det1["cantidad"]));
            $pdf->SetFont('Arial', '', 6);
            $pdf->SetXY(10, $linea);
            $pdf->Write(4, $det1["idservicio"]);
            $pdf->SetFont('Arial', '', 6);
            $pdf->SetXY(20, $linea);
            $pdf->Write(4, substr(retornarRegistroMysqli2($dbx,'mreg_servicios',"idservicio='" . $det1["idservicio"] . "'","nombre"), 0, 15));
            if ($det1["valorbase"] != 0) {
                $pdf->SetFont('Arial', '', 6);
                $pdf->SetXY(42, $linea);
                $pdf->Cell(19, 4, "$" . number_format($det1["valorbase"]), 0, 0, 'R');
            }
            if ((trim($det1["ano"]) != '') || (trim($det1["ano"]) != '0000')) {
                $pdf->SetFont('Arial', '', 6);
                $pdf->SetXY(61, $linea);
                $pdf->Write(4, $det1["ano"]);
            }
            if ((trim($det1["expediente"]) != '') || (trim($det1["expediente"]) != '00000000')) {
                $pdf->SetFont('Arial', '', 6);
                $pdf->SetXY(69, $linea);

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

                $pdf->Write(4, $det1["expediente"]);
            }
            $pdf->SetFont('Arial', '', 6);
            $pdf->SetXY(79, $linea);
            $pdf->Cell(15, 4, "$" . number_format($det1["valorservicio"], 2), 0, 0, 'R');
        }
        validarSaltoPaginaSii($linea, $pdf, $liq, $det, $orig, array(), $genrec);
        $linea = $linea + 4;
        $pdf->Line(5, $linea, 95, $linea);
        $linea = $linea + 2;
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetXY(55, $linea);
        $pdf->Write(4, "Valor Total.....");
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetXY(75, $linea);
        $pdf->Cell(15, 4, "$" . number_format($liq["valortotal"]), 0, 0, 'R');
        validarSaltoPaginaSii($linea, $pdf, $liq, $det, $orig, array(), $genrec);
        $linea = $linea + 2;
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetXY(55, $linea);
        $pdf->Write(4, "Valor Descuento..");
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetXY(75, $linea);
        $pdf->Cell(15, 4, "$" . number_format($liq["alertavalor"]), 0, 0, 'R');
        validarSaltoPaginaSii($linea, $pdf, $liq, $det, $orig, $genrec);
        $linea = $linea + 2;
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetXY(55, $linea);
        $pdf->Write(4, "Valor IVA.....");
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetXY(75, $linea);
        $pdf->Cell(15, 4, "$" . number_format($liq["valoriva"]), 0, 0, 'R');
        validarSaltoPaginaSii($linea, $pdf, $liq, $det, $orig, array(), $genrec);
        $neto = $liq["valortotal"] - $liq["alertavalor"];
        $linea = $linea + 2;
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetXY(55, $linea);
        $pdf->Write(4, "Valor NETO....");

        //
        if ($liq["cargogastoadministrativo"] == 'SI' || $liq["cargoafiliacion"] == 'SI' || $liq["cargoentidadoficial"] == 'SI'
        // || $liq["pagoprepago"] == 0
        ) {
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(75, $linea);
            $pdf->Cell(15, 4, '0', 0, 0, 'R');
        } else {
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(75, $linea);
            $pdf->Cell(15, 4, "$" . number_format($neto), 0, 0, 'R');
        }

        //
        validarSaltoPaginaSii($linea, $pdf, $liq, $det, $orig, array(), $genrec);
        $linea = $linea + 3;
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetXY(5, $linea);
        $pdf->Write(4, "Forma de Pago");
        if ($liq["cargogastoadministrativo"] == 'SI') {
            validarSaltoPaginaSii($linea, $pdf, $liq, $det, $orig, array(), $genrec);
            $linea = $linea + 3;
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(5, $linea);
            $pdf->Write(4, "*** SIN COSTO PARA EL CLIENTE ***");
            // $pdf->SetFont('Arial','B',6);$pdf->SetXY(35,$linea);$pdf->Cell(15,4,"$".number_format($liq["pagoefectivo"]),0,0,'R');			
        }
        if ($liq["cargoentidadoficial"] == 'SI') {
            validarSaltoPaginaSii($linea, $pdf, $liq, $det, $orig, array(), $genrec);
            $linea = $linea + 3;
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(5, $linea);
            $pdf->Write(4, "*** SIN COSTO PARA LA ENTIDAD OFICIAL ***");
            // $pdf->SetFont('Arial','B',6);$pdf->SetXY(35,$linea);$pdf->Cell(15,4,"$".number_format($liq["pagoefectivo"]),0,0,'R');			
        }
        if ($liq["cargoafiliacion"] == 'SI') {
            validarSaltoPaginaSii($linea, $pdf, $liq, $det, $orig, array(), $genrec);
            $linea = $linea + 3;
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(5, $linea);
            $pdf->Write(4, "*** CON CARGO AL CUPO DEL AFILIADO ***");
            // $pdf->SetFont('Arial','B',6);$pdf->SetXY(35,$linea);$pdf->Cell(15,4,"$".number_format($liq["pagoefectivo"]),0,0,'R');			
        }
        if ($liq["pagoefectivo"] != 0) {
            validarSaltoPaginaSii($linea, $pdf, $liq, $det, $orig, array(), $genrec);
            $linea = $linea + 3;
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(5, $linea);
            $pdf->Write(4, "Pago en Efectivo.....");
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(35, $linea);
            $pdf->Cell(15, 4, "$" . number_format($liq["pagoefectivo"]), 0, 0, 'R');
        }
        if ($liq["pagocheque"] != 0) {
            validarSaltoPaginaSii($linea, $pdf, $liq, $det, $orig, array(), $genrec);
            $linea = $linea + 3;
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(5, $linea);
            $pdf->Write(4, "Pago en Cheque.....");
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(35, $linea);
            $pdf->Cell(15, 4, "$" . number_format($liq["pagocheque"]), 0, 0, 'R');
            $linea = $linea + 3;
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(5, $linea);
            $pdf->Write(4, utf8_decode("Número del Cheque....."));
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(35, $linea);
            $pdf->Cell(15, 4, $liq["numerocheque"], 0, 0, 'R');
            $linea = $linea + 3;
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(5, $linea);
            $pdf->Cell(45, 4, substr(retornarRegistrosMysqli2($dbx,'bas_codban', "id='" . $liq["idcodban"] . "'","nombre"), 0, 30), 0, 0, 'R');
        }
        if ($liq["pagoconsignacion"] != 0) {
            validarSaltoPaginaSii($linea, $pdf, $liq, $det, $orig, array(), $genrec);
            $linea = $linea + 3;
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(5, $linea);
            $pdf->Write(4, utf8_decode("Pago Consignación....."));
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(35, $linea);
            $pdf->Cell(15, 4, "$" . number_format($liq["pagoconsignacion"]), 0, 0, 'R');
        }
        if ($liq["pagovisa"] != 0) {
            validarSaltoPaginaSii($linea, $pdf, $liq, $det, $orig, array(), $genrec);
            $linea = $linea + 3;
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(5, $linea);
            $pdf->Write(4, "Pago Sistema VISA.....");
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(35, $linea);
            $pdf->Cell(15, 4, "$" . number_format($liq["pagovisa"]), 0, 0, 'R');
            $linea = $linea + 3;
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(5, $linea);
            $pdf->Write(4, utf8_decode("Número de autorización....."));
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(35, $linea);
            $pdf->Cell(15, 4, $liq["numeroautorizacion"], 0, 0, 'R');
        }
        if ($liq["pagoach"] != 0) {
            validarSaltoPaginaSii($linea, $pdf, $liq, $det, $orig, array(), $genrec);
            $linea = $linea + 3;
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(5, $linea);
            $pdf->Write(4, "Pago Sistema ACH.....");
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(35, $linea);
            $pdf->Cell(15, 4, "$" . number_format($liq["pagoach"]), 0, 0, 'R');
            $linea = $linea + 3;
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(5, $linea);
            $pdf->Write(4, utf8_decode("Número de autorización....."));
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(35, $linea);
            $pdf->Cell(15, 4, $liq["numeroautorizacion"], 0, 0, 'R');
        }
        if ($liq["pagomastercard"] != 0) {
            validarSaltoPaginaSii($linea, $pdf, $liq, $det, $orig, array(), $genrec);
            $linea = $linea + 3;
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(5, $linea);
            $pdf->Write(4, "Pago Sistema MASTERCARD.....");
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(35, $linea);
            $pdf->Cell(15, 4, "$" . number_format($liq["pagomastercard"]), 0, 0, 'R');
            $linea = $linea + 3;
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(5, $linea);
            $pdf->Write(4, utf8_decode("Número de autorización....."));
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(35, $linea);
            $pdf->Cell(15, 4, $liq["numeroautorizacion"], 0, 0, 'R');
        }
        if ($liq["pagoamerican"] != 0) {
            validarSaltoPaginaSii($linea, $pdf, $liq, $det, $orig, array(), $genrec);
            $linea = $linea + 3;
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(5, $linea);
            $pdf->Write(4, "Pago Sistema American Express.....");
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(45, $linea);
            $pdf->Cell(15, 4, "$" . number_format($liq["pagoamerican"]), 0, 0, 'R');
            $linea = $linea + 3;
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(5, $linea);
            $pdf->Write(4, utf8_decode("Número de autorización....."));
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(35, $linea);
            $pdf->Cell(15, 4, $liq["numeroautorizacion"], 0, 0, 'R');
        }
        if ($liq["pagocredencial"] != 0) {
            validarSaltoPaginaSii($linea, $pdf, $liq, $det, $orig, array(), $genrec);
            $linea = $linea + 3;
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(5, $linea);
            $pdf->Write(4, "Pago Sistema Credencial.....");
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(45, $linea);
            $pdf->Cell(15, 4, "$" . number_format($liq["pagocredencial"]), 0, 0, 'R');
            $linea = $linea + 3;
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(5, $linea);
            $pdf->Write(4, utf8_decode("Número de autorización....."));
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(35, $linea);
            $pdf->Cell(15, 4, $liq["numeroautorizacion"], 0, 0, 'R');
        }
        if ($liq["pagodiners"] != 0) {
            validarSaltoPaginaSii($linea, $pdf, $liq, $det, $orig, array(), $genrec);
            $linea = $linea + 3;
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(5, $linea);
            $pdf->Write(4, "Pago Sistema Diners.....");
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(45, $linea);
            $pdf->Cell(15, 4, "$" . number_format($liq["pagodiners"]), 0, 0, 'R');
            $linea = $linea + 3;
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(5, $linea);
            $pdf->Write(4, utf8_decode("Número de autorización....."));
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(35, $linea);
            $pdf->Cell(15, 4, $liq["numeroautorizacion"], 0, 0, 'R');
        }
        if ($liq["pagotdebito"] != 0) {
            validarSaltoPaginaSii($linea, $pdf, $liq, $det, $orig, array(), $genrec);
            $linea = $linea + 3;
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(5, $linea);
            $pdf->Write(4, utf8_decode("Pago Tarjetas Débito....."));
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(45, $linea);
            $pdf->Cell(15, 4, "$" . number_format($liq["pagotdebito"]), 0, 0, 'R');
            $linea = $linea + 3;
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(5, $linea);
            $pdf->Write(4, utf8_decode("Número de autorización....."));
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(35, $linea);
            $pdf->Cell(15, 4, $liq["numeroautorizacion"], 0, 0, 'R');
        }
        if ($liq["pagoprepago"] != 0) {
            validarSaltoPaginaSii($linea, $pdf, $liq, $det, $orig, array(), $genrec);
            $linea = $linea + 3;
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(5, $linea);
            $pdf->Write(4, utf8_decode("CON CARGO AL CUPO DE PREPAGO ..."));
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(75, $linea);
            $pdf->Cell(15, 4, "$" . number_format($liq["pagoprepago"]), 0, 0, 'R');
        }

        validarSaltoPaginaSii($linea, $pdf, $liq, $det, $orig, array(), $genrec);
        $linea = $linea + 4;
        $pdf->Line(5, $linea, 95, $linea);
        if (trim($claveprepago) != '') {
            $linea = $linea + 3;
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(5, $linea);
            $pdf->Cell(90, 4, "Clave prepago: " . $claveprepago, 0, 0, 'L');
        }
        if (trim($saldoprepago) != '') {
            $linea = $linea + 3;
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(5, $linea);
            $pdf->Cell(90, 4, "Saldo prepago: " . number_format($saldoprepago, "0"), 0, 0, 'L');
        }

        if (trim($liq["numeroradicacion"]) != '') {
            $linea = $linea + 3;
            $txt = "";
            $txt .= "Para conocer el estado de su trámite por favor comuníquese con el número " . TELEFONO_ATENCION_USUARIOS;
            $txt .= " y cite el nro. " . $liq["numeroradicacion"] . ". Puede igualmente dirigirse a " . TIPO_HTTP . HTTP_HOST . "/cnr.php?em=" . $_SESSION["generales"]["codigoempresa"] . "&cb=" . $liq["numeroradicacion"];
            $pdf->SetFont('Arial', '', 4);
            $pdf->SetXY(5, $linea);
            $pdf->MultiCell(90, 2, utf8_decode($txt), 0, 'J', 0);
            $linea = $pdf->GetY();
            $linea = $linea + 1;
            $pdf->Line(5, $linea, 95, $linea);
        }
        $numrec = $liq["numerorecibo"];
        unset($liq);
        unset($det);
        unset($det1);
    } else {

        $dimension = array(106, 140);
        $pdf = new PDFRecibo("Portrait", "mm", $dimension);
        $pdf->AliasNbPages();
        $linea = encabezadosReciboSii($pdf, array(), array(), $orig, $arreglo, $genrec);
        foreach ($arreglo["renglones"] as $det1) {
            $linea = validarSaltoPaginaSii($linea, $pdf, array(), array(), $orig, $arreglo, $genrec);
            $linea = $linea + 3;
            $pdf->SetFont('Arial', '', 6);
            $pdf->SetXY(5, $linea);
            $pdf->Write(4, number_format($det1["cantidad"]));
            $pdf->SetFont('Arial', '', 6);
            $pdf->SetXY(10, $linea);
            $pdf->Write(4, $det1["servicio"]);
            $pdf->SetFont('Arial', '', 6);
            $pdf->SetXY(20, $linea);
            $pdf->Write(4, substr($det1["nombre"], 0, 15));
            if ($det1["valorbase"] != 0) {
                $pdf->SetFont('Arial', '', 6);
                $pdf->SetXY(42, $linea);
                $pdf->Cell(19, 4, "$" . number_format($det1["valorbase"]), 0, 0, 'R');
            }
            if ((trim($det1["ano"]) != '') || (trim($det1["ano"]) != '0000')) {
                $pdf->SetFont('Arial', '', 6);
                $pdf->SetXY(61, $linea);
                $pdf->Write(4, $det1["ano"]);
            }
            if ((trim($det1["expediente"]) != '') || (trim($det1["expediente"]) != '00000000')) {
                $pdf->SetFont('Arial', '', 6);
                $pdf->SetXY(69, $linea);
                $pdf->Write(4, $det1["expediente"]);
            }
            $pdf->SetFont('Arial', '', 6);
            $pdf->SetXY(79, $linea);
            $pdf->Cell(15, 4, "$" . number_format($det1["valor"]), 0, 0, 'R');
        }
        validarSaltoPaginaSii($linea, $pdf, array(), array(), $orig, $arreglo, $genrec);
        $linea = $linea + 4;
        $pdf->Line(5, $linea, 95, $linea);
        $linea = $linea + 2;
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetXY(55, $linea);
        $pdf->Write(4, "Valor Total.....");
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetXY(75, $linea);
        $pdf->Cell(15, 4, "$" . number_format($arreglo["valortotal"]), 0, 0, 'R');
        validarSaltoPaginaSii($linea, $pdf, array(), array(), $orig, $arreglo, $genrec);
        $linea = $linea + 2;
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetXY(55, $linea);
        $pdf->Write(4, "Valor Descuento..");
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetXY(75, $linea);
        $pdf->Cell(15, 4, "$" . number_format($arreglo["alertavalor"]), 0, 0, 'R');
        validarSaltoPaginaSii($linea, $pdf, array(), array(), $orig, $arreglo, $genrec);
        $linea = $linea + 2;
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetXY(55, $linea);
        $pdf->Write(4, "Valor IVA.....");
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetXY(75, $linea);
        $pdf->Cell(15, 4, "$" . number_format($arreglo["valoriva"]), 0, 0, 'R');
        validarSaltoPaginaSii($linea, $pdf, array(), array(), $orig, $arreglo, $genrec);
        $neto = $arreglo["valortotal"] - $arreglo["alertavalor"];
        $linea = $linea + 2;
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetXY(55, $linea);
        $pdf->Write(4, "Valor NETO....");
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetXY(75, $linea);
        $pdf->Cell(15, 4, "$" . number_format($neto), 0, 0, 'R');
        validarSaltoPaginaSii($linea, $pdf, array(), array(), $orig, $arreglo, $genrec);
        $linea = $linea + 3;
        $pdf->SetFont('Arial', 'B', 6);
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

        validarSaltoPaginaSii($linea, $pdf, array(), array(), $orig, $arreglo, $genrec);
        $linea = $linea + 3;
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetXY(5, $linea);
        $pdf->Write(4, $fpago);
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetXY(35, $linea);
        $pdf->Cell(15, 4, "$" . number_format($arreglo["valortotal"]), 0, 0, 'R');
        $linea = $linea + 3;
        if ($arreglo["formapago"] == '02') {
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(5, $linea);
            $pdf->Write(4, "Número del Cheque.....");
        } else {
            if ($arreglo["formapago"] != '01') {
                $pdf->SetFont('Arial', 'B', 6);
                $pdf->SetXY(5, $linea);
                $pdf->Write(4, "Número autorización.....");
            }
        }
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->SetXY(35, $linea);
        $pdf->Cell(15, 4, $arreglo["numcheque"], 0, 0, 'R');
        $linea = $linea + 3;
        if (($arreglo["formapago"] == '02') || ($arreglo["formapago"] == '02') || ($arreglo["formapago"] == '04')) {
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY(5, $linea);
            $pdf->Cell(45, 4, substr(retornarRegistroMysqli2($dbx,'bas_codban',"id='" . $liq["idcodban"] . "'","nombre"), 0, 30), 0, 0, 'R');
        }
        validarSaltoPaginaSii($linea, $pdf, array(), array(), $orig, $arreglo, $genrec);
        $linea = $linea + 4;
        $pdf->Line(5, $linea, 95, $linea);
        if (trim($arreglo["facturacancelada"]) != '') {
            $linea = $linea + 3;
            $pdf->SetFont('Arial', '', 6);
            $pdf->SetXY(5, $linea);
            $pdf->Cell(90, 4, "Factura que se cancela o abona: " . $arreglo["facturacancelada"], 0, 0, 'L');
        }
        if (trim($claveprepago) != '') {
            $linea = $linea + 3;
            $pdf->SetFont('Arial', '', 6);
            $pdf->SetXY(5, $linea);
            $pdf->Cell(90, 4, "Clave prepago: " . $claveprepago, 0, 0, 'L');
        }
        if (trim($saldoprepago) != '') {
            $linea = $linea + 3;
            $pdf->SetFont('Arial', '', 6);
            $pdf->SetXY(5, $linea);
            $pdf->Cell(90, 4, "Saldo prepago: " . $saldoprepago, 0, 0, 'L');
        }

        if (trim($arreglo["codbarras"]) != '') {
            // $linea=$linea+3;
            $linea = $linea + 3;
            $txt = "";
            $txt .= "Para conocer el estado de su trámite por favor comuníquese con el número " . TELEFONO_ATENCION_USUARIOS;
            $txt .= " y cite el nro. " . $arreglo["codbarras"] . ". Puede igualmente dirigirse a " . TIPO_HTTP . HTTP_HOST . "/cnr.php?em=" . $_SESSION["generales"]["codigoempresa"] . "&cb=" . $arreglo["codbarras"];
            $pdf->SetFont('Arial', '', 4);
            $pdf->SetXY(5, $linea);
            $pdf->MultiCell(90, 2, utf8_decode($txt), 0, 'J', 0);
            $linea = $pdf->GetY();
            $linea = $linea + 1;
            $pdf->Line(5, $linea, 95, $linea);
        }
        $numrec = $arreglo["numerorecibo"];
    }

    // Genera la salida
    if ($tiposalida == 'D') {
        $pdf->Output('ReciboDeCaja.pdf', 'D');
        $name = '';
        return $name;
    } else {
        if ($tiposalida == 'T') {
            $pdf->Output('../../../tmp/' . session_id() . '-recibo-' . $numliq . '.pdf', 'F');
            $name = '../../../tmp/' . session_id() . '-recibo-' . $numliq . '.pdf';
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
            $name = PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/mreg/recibosCaja/" . date("Y") . "/" . $numliq . "-Recibo-" . $numrec . '-' . date("Ymd") . ".pdf";
            $name1 = '../../../' . PATH_RELATIVO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/mreg/recibosCaja/" . date("Y") . "/" . $numliq . "-Recibo-" . $numrec . '-' . date("Ymd") . ".pdf";
            $pdf->Output($name, "F");
            return $name1;
        }
    }
    exit();
}

function armarPdfRadicadoSii($dbx,$numliq) {
    require_once ('generaGSCode11.php');
    require_once ('tcpdf_6.2.13/tcpdf.php');
    require_once ('tcpdf_6.2.13/examples/lang/eng.php');
    ob_clean();

    if (!class_exists('PDFRadicado')) {

        class PDFRadicado extends TCPDF {
            
        }

    }

    //
    $liq = \funcionesSii2::retornarMregLiquidacionSii($dbx,$numliq);

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
    $imagen = '../../../tmp/cbliq-' . $_SESSION["generales"]["codigoempresa"] . '-' . $numliq . '.png';
    generarGsCode11($liq["numeroradicacion"], $imagen);
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
    $pdf->writeHTML('<strong>FECHA : ' . \funcionesSii2::mostrarFecha($liq["fecharecibo"]) . '</strong>', true, false, true, false, 'C');
    $pdf->Ln();
    $pdf->writeHTML('<strong>HORA : ' . \funcionesSii2::mostrarHora($liq["horarecibo"]) . '</strong>', true, false, true, false, 'C');
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
    $name1 = '../../../tmp/' . $_SESSION["generales"]["codigoempresa"] . '-codigobarras-' . $numliq . '.pdf';
    $pdf->Output($name, 'F');
    unset($pdf);
    return $name1;
}

?>