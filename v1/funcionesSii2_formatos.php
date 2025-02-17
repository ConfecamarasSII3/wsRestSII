<?php

function armarPdfVolantePagoBancos($mysqli, $liq, $liqdet, $valliq = 0, $tipo = 'justificado', $titulo = 'bancos', $textoBancos = '', $textoSoportes = '') {

    require_once ('fpdf153/fpdf.php');
    require_once ('fpdf153/fpdf_code128.php');
    require_once ('generaGS1128.php');

    //
    $fcorte = retornarRegistroMysqli2($mysqli, 'mreg_cortes_renovacion', "ano='" . date("Y") . "'","corte");
    
    //
    if (!defined('FPDF_FONTPATH')) {
        define('FPDF_FONTPATH', 'fpdf153/fonts/');
    }
    if (!class_exists('PDFVol')) {

        class PDFVol extends PDF_Code128 {

            var $angle = 0;

            function Rotate($angle, $x = -1, $y = -1) {
                if ($x == -1) {
                    $x = $this->x;
                }
                if ($y == -1) {
                    $y = $this->y;
                }
                if ($this->angle != 0) {
                    $this->_out('Q');
                }
                $this->angle = $angle;
                if ($angle != 0) {
                    $angle *= M_PI / 180;
                    $c = cos($angle);
                    $s = sin($angle);
                    $cx = $x * $this->k;
                    $cy = ($this->h - $y) * $this->k;
                    $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
                }
            }

            function RotatedText($x, $y, $txt, $angle) {
                $this->Rotate($angle, $x, $y);
                $this->Text($x, $y, $txt);
                $this->Rotate(0);
            }

        }

    }

    $pathout = PATH_ABSOLUTO_SITIO . '/tmp/';

    // $tipoimpresion = "prediligenciados";
    $pdf = new PDFVol("Portrait", "mm", "Letter");
    $pdf->AliasNbPages();

    //
    if (isset($liqdet[0]["expediente"])) {
        $matx = $liqdet[0]["expediente"];
    } else {
        if (isset($liqdet[1]["expediente"])) {
            $matx = $liqdet[1]["expediente"];
        }
    }
    if (!defined('DIGITOS_REFERENCIA_PAGO_BANCOS')) {
        define('DIGITOS_REFERENCIA_PAGO_BANCOS', '10');
    }
    if (DIGITOS_REFERENCIA_PAGO_BANCOS == '') {
        $dig = 10;
    } else {
        $dig = DIGITOS_REFERENCIA_PAGO_BANCOS;
    }

    switch ($dig) {
        case 8 :
            $ref = sprintf("%02s", $_SESSION["generales"]["codigoempresa"]) . sprintf("%06s", $liq["idliquidacion"]);
            break;
        case 9 :
            $ref = sprintf("%02s", $_SESSION["generales"]["codigoempresa"]) . sprintf("%07s", $liq["idliquidacion"]);
            break;
        case 10 :
            $ref = sprintf("%02s", $_SESSION["generales"]["codigoempresa"]) . sprintf("%08s", $liq["idliquidacion"]);
            break;
        case 11 :
            $ref = sprintf("%02s", $_SESSION["generales"]["codigoempresa"]) . sprintf("%09s", $liq["idliquidacion"]);
            break;
        case 12 :
            $ref = sprintf("%02s", $_SESSION["generales"]["codigoempresa"]) . sprintf("%010s", $liq["idliquidacion"]);
            break;
    }
    // $ref = sprintf("%02s", $_SESSION["generales"]["codigoempresa"]) . sprintf("%08s", $liq["idliquidacion"]);
    $nombre = utf8_decode($liq["nombrecliente"]) . ' ' . utf8_decode($liq["apellidocliente"]);
    $idex = $liq["identificacioncliente"];

    // 
    // Arma el código de barras
    if (defined('CODIGO_IAC') && trim(CODIGO_IAC) != '') {
        $imagen = $pathout . 'codbarras-' . session_id() . date("Ymd") . date("His") . '.png';
        if (date("Ymd") <= $fcorte) {
            generarGs1128(trim(CODIGO_IAC), $ref, $fcorte, $imagen, $valliq);
        } else {
            generarGs1128(trim(CODIGO_IAC), $ref, date("Y") . '1231', $imagen, $valliq);
        }
    } else {
        $imagen = '';
    }

    //
    $codigorecaudoraiz = retornarClaveValorSii2($mysqli, '90.33.49');
    $codigorecaudo = retornarClaveValorSii2($mysqli, '90.33.50');

    // Imprime encabezados
    $pdf->AddPage();
    $pdf->SetMargins(15, 25, 7);

    // Parte del cupon que es para el banco
    if (!file_exists('logos/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.jpg')) {
        $pdf->Image('logos/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.jpg', 10, 7, 18, 18);
    } else {
        if (!file_exists('logos/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.gif')) {
            $pdf->Image('logos/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.gif', 10, 7, 18, 18);
        }
    }
    
    // $pdf->Image('logos/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.jpg', 10, 7, 18, 18);

    // Primer código de barras
    if (trim($imagen) != '') {
        // $pdf->Image($imagen, 130, 7, 70, 17);
        $pdf->Image($imagen, 110, 7, 90, 19);
    }

    // Títulos del volante - primera parte
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetXY(30, 11);
    $pdf->Write(4, "VOLANTE PARA PAGO");

    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetXY(30, 16);
    $pdf->Write(4, substr(RAZONSOCIALSMS, 0, 45));
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetXY(30, 21);
    $pdf->Write(4, "NIT. " . NIT);

    // Imprime Cuadricula
    $pdf->Rect(25, 28, 175, 6);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetXY(27, 29);
    $pdf->Write(5, "DATOS DEL COMERCIANTE");

    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetXY(140, 36);
    $pdf->Write(5, "NUMERO RECUPERACION: " . $liq["numerorecuperacion"]);
    if (trim($codigorecaudo) != '') {
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetXY(120, 41);
        $pdf->Write(5, "CODIGO PAGO BALOTO: " . $codigorecaudoraiz . ' ' . $codigorecaudo . ' ' . $ref);
    }

    $pdf->Rect(25, 35, 175, 17);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetXY(27, 36);
    $pdf->Write(5, "NOMBRE O RAZON SOCIAL:");
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetXY(80, 36);
    $pdf->Write(5, substr($nombre, 0, 30));
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetXY(27, 41);
    $pdf->Write(5, "IDENTIFICACION:");
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetXY(80, 41);
    $pdf->Write(5, ltrim($idex, 0));
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetXY(27, 46);
    $pdf->Write(5, "EXPEDIENTE:");
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetXY(80, 46);
    $pdf->Write(5, $matx);

    $pdf->Rect(25, 53, 175, 6);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetXY(27, 54);
    $pdf->Write(5, "LIQUIDACION DEL SERVICIO");

    $pdf->Rect(25, 60, 175, 50);
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->SetXY(35, 61);
    $pdf->Write(5, "FECHA DE LA LIQUIDACION:");
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->SetXY(80, 61);
    $pdf->Write(5, mostrarFecha(date("Ymd")));
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->SetXY(35, 65);
    $pdf->Write(5, "HORA DE LA LIQUIDACION:");
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->SetXY(80, 65);
    $pdf->Write(5, mostrarHora(date("His")));
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->SetXY(35, 71);
    $pdf->Write(5, "CONCEPTOS");
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->SetXY(135, 71);
    $pdf->Write(5, "VALOR");

    $ultimoanoarenovar = '';
    $relServ = array();
    $iRel = 0;
    foreach ($liqdet as $det) {
        if (substr($det["idservicio"], 0, 6) == '010202') {
            if ($det["ano"] > $ultimoanoarenovar) {
                $ultimoanoarenovar = $det["ano"];
            }
        }
        if ($iRel == 0) {
            $iRel++;
            $relServ[$iRel]["idservicio"] = $det["idservicio"];
            $relServ[$iRel]["valor"] = $det["valorservicio"];
        } else {
            $i = 0;
            $j = 0;
            foreach ($relServ as $ser) {
                $j++;
                if ($ser["idservicio"] == $det["idservicio"]) {
                    $i = $j;
                }
            }
            if ($i == 0) {
                $iRel++;
                $relServ[$iRel]["idservicio"] = $det["idservicio"];
                $relServ[$iRel]["valor"] = $det["valorservicio"];
            } else {
                $relServ[$i]["valor"] = $relServ[$i]["valor"] + $det["valorservicio"];
            }
        }
    }

    //
    $y = 70;
    $total = 0;
    foreach ($relServ as $ser) {
        $nomserv = retornarRegistroMysqli2($mysqli, "mreg_servicios", "idservicio='" . $ser["idservicio"] . "'", "nombre");
        $y = $y + 4;
        $pdf->SetFont('Arial', '', 7);
        $pdf->SetXY(35, $y);
        $pdf->Write(5, $nomserv);
        $pdf->SetFont('Arial', '', 7);
        $pdf->SetXY(135, $y);
        $pdf->Write(5, number_format($ser["valor"], 0));
        $total = $total + $ser["valor"];
    }
    $y = $y + 4;
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->SetXY(35, $y);
    $pdf->Write(5, 'TOTAL');
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->SetXY(135, $y);
    $pdf->Write(5, number_format($total, 0));

    if ($ultimoanoarenovar != '') {
        $y = $y + 4;
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->SetXY(35, $y);
        $pdf->Write(5, utf8_decode('El comerciante quedará renovado al ') . $ultimoanoarenovar);
    }

    if ($textoSoportes != '') {
        $y = $y + 4;
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetXY(35, $y);
        $pdf->Write(4, utf8_decode($textoSoportes));
    }


    /*
      $y = $y + 6;
      $pdf->SetFont('Arial', 'B', 6);
      $pdf->SetXY(35, $y);
      $pdf->Write(5, utf8_decode('Señor comerciante, usted es responsable de la veracidad y exactitud de la información diligenciada en los formularios, solicitudes y anexos documentales relacionados con el trámite que está pagando.'));
      $y = $y + 10;
      $pdf->SetFont('Arial', 'B', 6);
      $pdf->SetXY(35, $y);
      $pdf->Write(5, 'NOMBRE: __________________________________________ FIRMA : ___________________________________ C.C. : __________________________________');
     */

    $pdf->Line(2, 113, 210, 113);

    // Segunda parte
    // Imprime Cuadricula
    // Títulos del volante - primera parte
    if (!file_exists('logos/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.jpg')) {
        $pdf->Image('logos/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.jpg', 10, 116, 18, 18);
    } else {
        if (!file_exists('logos/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.gif')) {
            $pdf->Image('logos/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.gif', 10, 116, 18, 18);
        }
    }
    // $pdf->Image('logos/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.jpg', 10, 116, 18, 18);

    // Primer código de barras
    if (trim($imagen) != '') {
        // $pdf->Image($imagen, 130, 116, 70, 17);
        $pdf->Image($imagen, 110, 116, 90, 19);
    }

    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetXY(30, 116);
    $pdf->Write(4, "VOLANTE PARA PAGO");

    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetXY(30, 121);
    $pdf->Write(4, substr(RAZONSOCIALSMS, 0, 45));
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetXY(30, 126);
    $pdf->Write(4, "NIT. " . NIT);

    $pdf->Rect(25, 137, 175, 6);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetXY(27, 138);
    $pdf->Write(5, "DATOS DEL COMERCIANTE");
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetXY(150, 138);
    $pdf->Write(5, "REFERENCIA: " . $ref);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetXY(140, 145);
    $pdf->Write(5, "NUMERO RECUPERACION: " . $liq["numerorecuperacion"]);
    if (trim($codigorecaudo) != '') {
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetXY(120, 150);
        $pdf->Write(5, "CODIGO PAGO BALOTO: " . $codigorecaudoraiz . ' ' . $codigorecaudo . ' ' . $ref);
    }

    $pdf->Rect(25, 144, 175, 17);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetXY(27, 145);
    $pdf->Write(5, "NOMBRE O RAZON SOCIAL:");
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetXY(80, 145);
    $pdf->Write(5, substr($nombre, 0, 30));
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetXY(27, 150);
    $pdf->Write(5, "IDENTIFICACION:");
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetXY(80, 150);
    $pdf->Write(5, ltrim($idex, 0));
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetXY(27, 155);
    $pdf->Write(5, "EXPEDIENTE:");
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetXY(80, 155);
    $pdf->Write(5, $matx);

    $pdf->Rect(25, 162, 175, 6);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetXY(27, 163);
    $pdf->Write(5, "LIQUIDACION DEL SERVICIO");

    $pdf->Rect(25, 169, 175, 50);
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->SetXY(35, 170);
    $pdf->Write(5, "FECHA DE LA LIQUIDACION:");
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->SetXY(80, 170);
    $pdf->Write(5, mostrarFecha(date("Ymd")));
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->SetXY(35, 175);
    $pdf->Write(5, "HORA DE LA LIQUIDACION:");
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->SetXY(80, 175);
    $pdf->Write(5, mostrarHora(date("His")));
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->SetXY(35, 180);
    $pdf->Write(5, "CONCEPTOS");
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->SetXY(135, 180);
    $pdf->Write(5, "VALOR");

    $relServ = array();
    $iRel = 0;
    foreach ($liqdet as $det) {
        if ($iRel == 0) {
            $iRel++;
            $relServ[$iRel]["idservicio"] = $det["idservicio"];
            $relServ[$iRel]["valor"] = $det["valorservicio"];
        } else {
            $i = 0;
            $j = 0;
            foreach ($relServ as $ser) {
                $j++;
                if ($ser["idservicio"] == $det["idservicio"]) {
                    $i = $j;
                }
            }
            if ($i == 0) {
                $iRel++;
                $relServ[$iRel]["idservicio"] = $det["idservicio"];
                $relServ[$iRel]["valor"] = $det["valorservicio"];
            } else {
                $relServ[$i]["valor"] = $relServ[$i]["valor"] + $det["valorservicio"];
            }
        }
    }

    //
    $y = 180;
    $total = 0;
    foreach ($relServ as $ser) {
        $nomserv = retornarRegistroMysqli2($mysqli, "mreg_servicios", "idservicio='" . $ser["idservicio"] . "'", "nombre");
        $y = $y + 4;
        $pdf->SetFont('Arial', '', 7);
        $pdf->SetXY(35, $y);
        $pdf->Write(5, $nomserv);
        $pdf->SetFont('Arial', '', 7);
        $pdf->SetXY(135, $y);
        $pdf->Write(5, number_format($ser["valor"], 0));
        $total = $total + $ser["valor"];
    }
    $y = $y + 4;
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->SetXY(35, $y);
    $pdf->Write(5, 'TOTAL');
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->SetXY(135, $y);
    $pdf->Write(5, number_format($total, 0));
    $pdf->Rect(10, 222, 190, 6);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetXY(27, 223);
    $pdf->Write(5, "SOLO PARA PAGO EN EFECTIVO O CON CHEQUE DE GERENCIA");
    $pdf->Rect(10, 229, 90, 6);
    $pdf->Rect(101, 229, 99, 6);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetXY(12, 230);
    $pdf->Write(5, "BANCO:");
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetXY(103, 230);
    $pdf->Write(5, "NRO. CHEQUE:");

    /*
      if (trim($imagen)!='') {
      $pdf->Image($imagen,10,237,70,17);
      }
     */

    $pdf->Rect(10, 236, 90, 6);
    $pdf->Rect(101, 236, 99, 6);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetXY(12, 237);
    $pdf->Write(5, "VALOR A PAGAR:");
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetXY(55, 237);
    $pdf->Write(5, "     $ " . number_format($liq["valortotal"], 0));
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetXY(102, 237);
    $pdf->Write(5, "FECHA DEL PAGO     AAAA [            ]   MM [       ] DD [     ]");

    /*
      $txt = '';
      $txt .= 'Señor Usuario Realice sus pagos en: Banco de Occidente, Corresponsales Bancarios (Éxito, Baloto y ';
      $txt .= 'Puntos ATH), próximamente nuevo sitio de recaudo Cooguasimales, o en cualquiera de ';
      $txt .= 'nuestras Sedes. Por favor tenga';
      $pdf->SetFont('Arial', '', 5);
      $pdf->SetXY(15, 242);
      $pdf->Write(4, utf8_decode($txt));
      $txt = 'en cuenta que el valor máximo que puede ser pagado a través de ';
      $txt .= 'coresponsales bancarios es de $400,000.oo para BALOTO y $10,000,000.oo en Almacenes Exito.';
      $pdf->SetFont('Arial', '', 5);
      $pdf->SetX(15, 246);
      $pdf->Write(4, utf8_decode($txt));
     */

    if (trim($textoBancos) != '') {
        $pdf->SetFont('Arial', '', 7);
        $pdf->SetXY(15, 242);
        $pdf->MultiCell(180, 3, utf8_decode($textoBancos), 0, 'J', 0);
    }

    if ($textoSoportes != '') {
        $y = $pdf->GetY();
        $y = $y + 3;
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetXY(35, $y);
        $pdf->Write(5, utf8_decode($textoSoportes));
    }



    //
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->RotatedText(15, 100, 'CUPON PARA EL CLIENTE', 90);
    $pdf->RotatedText(15, 211, 'CUPON PARA EL BANCO', 90);

    unlink($imagen);
    $pdf->_endpage();
    $name = $pathout . '/' . $_SESSION["generales"]["codigoempresa"] . '-volban-' . $liq["idliquidacion"] . ".pdf";
    $pdf->Output($name, "F");
    unset($pdf);
    return $name;
}

?>
