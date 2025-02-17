<?php

function armarPdfVolantePagoBancos($dbx, $liq, $liqdet) {
    require_once ('../components/fpdf186/fpdf.php');
    require_once ('../components/fpdf186/fpdf_code128.php');
    require_once ('generaGS1128.php');

    //
    $_SESSION["generales"]["corterenovacion"] = retornarRegistroMysqliApi($dbx, 'mreg_cortes_renovacion', "ano='" . date("Y") . "'", "corte");
    $_SESSION["generales"]["corterenovacionmesdia"] = substr($_SESSION["generales"]["corterenovacion"], 4, 4);

    if (!class_exists('PDFVol')) {

        // class PDFForProp extends FPDF_Protection {
        class PDFVol extends PDF_Code128 {

            var $angle = 0;

            function Rotate($angle, $x = -1, $y = -1) {
                if ($x == -1)
                    $x = $this->x;
                if ($y == -1)
                    $y = $this->y;
                if ($this->angle != 0)
                    $this->_out('Q');
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

    $pathout = '../tmp';
    $tipoimpresion = "prediligenciados";
    $pdf = new PDFVol("Portrait", "mm", "Letter");
    $pdf->AliasNbPages();

    //
    $matx = '';
    $ref = sprintf("%014s", $liq["idliquidacion"]);
    $nombre = $liq["nombrecliente"];
    $idex = $liq["identificacioncliente"];

    //
    if ($liq["tipotramite"] == 'matriculapnat' ||
            $liq["tipotramite"] == 'matriculacambidom' ||
            $liq["tipotramite"] == 'matriculaest' ||
            $liq["tipotramite"] == 'matriculasuc'
    ) {
        $matx = 'NUEVA MATRICULA';
        $ref = $liq["idliquidacion"];
        $nombre = $liq["nombrepnat"];
        $idex = $liq["idepnat"];
    }

    if ($liq["tipotramite"] == 'matriculapjur' ||
            $liq["tipotramite"] == 'matriculaesadl'
    ) {
        $matx = 'NUEVA MATRICULA';
        $ref = sprintf("%014s", $liq["idliquidacion"]);
        $nombre = $liq["nombrerepleg"];
        $idex = $liq["iderepleg"];
    }

    if ($liq["tipotramite"] == 'renovacionmatricula' ||
            $liq["tipotramite"] == 'renovacionesadl'
    ) {
        $matx = $liqdet[0]["expediente"];
        $ref = $liq["idliquidacion"];
        $nombre = $liq["nombrecliente"];
        $idex = $liq["identificacioncliente"];
    }

    // Arma el código de barras
    if (defined('CODIGO_IAC')) {
        $imagen = '../tmp/codbarras-' . session_id() . date("Ymd") . date("His") . '.png';
        if (date("md") <= $_SESSION["generales"]["corterenovacionmesdia"]) {
            generarGs1128(trim(CODIGO_IAC), $ref, date("Y") . $_SESSION["generales"]["corterenovacionmesdia"], $imagen);
        } else {
            generarGs1128(trim(CODIGO_IAC), $ref, date("Y") . '1231', $imagen);
        }
    } else {
        $imagen = '';
    }

    // Imprime encabezados   
    $pdf->AddPage();
    $pdf->SetMargins(15, 25, 7);

    // Parte del cupon que es para el cliente 
    if (file_exists('../images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.jpg')) {
        $pdf->Image('../images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.jpg', 10, 7, 18, 18);
    }
    // Primer código de barras
    if (trim($imagen) != '') {
        $pdf->Image($imagen, 130, 7, 80, 19);
    }

    // Títulos del volante
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetXY(30, 11);
    $pdf->Write(4, "VOLANTE PARA PAGO EN BANCOS");
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetXY(30, 16);
    $pdf->Write(4, RAZONSOCIALSMS);
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetXY(30, 21);
    $pdf->Write(4, "NIT. " . NIT);
    // $pdf->SetFont('Helvetica','',6);$pdf->SetXY(60,22);$pdf->Write(4,"Recuerde diligenciar las casillas que se encuentran en color gris");  
    // Imprime Cuadricula
    $pdf->Rect(10, 28, 190, 6);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(12, 29);
    $pdf->Write(5, "DATOS DEL COMERCIANTE");
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(140, 29);
    $pdf->Write(5, "NUMERO RECUPERACION: " . trim($liq["numerorecuperacion"]));

    $pdf->Rect(10, 35, 190, 17);
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(12, 36);
    $pdf->Write(5, "NOMBRE O RAZON SOCIAL:");
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(60, 36);
    $pdf->Write(5, substr($nombre, 0, 30));
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(12, 41);
    $pdf->Write(5, "IDENTIFICACION:");
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(60, 41);
    $pdf->Write(5, ltrim($idex, 0));
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(12, 46);
    $pdf->Write(5, "MATRICULA/PROPONENTE:");
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(60, 46);
    $pdf->Write(5, $matx);

    $pdf->Rect(10, 53, 190, 6);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(12, 54);
    $pdf->Write(5, "LIQUIDACION DEL SERVICIO");

    $pdf->Rect(10, 60, 190, 50);
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(20, 61);
    $pdf->Write(5, "FECHA DE LA LIQUIDACION:");
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(60, 61);
    $pdf->Write(5, \funcionesGenerales::mostrarFecha(date("Ymd")));
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(20, 65);
    $pdf->Write(5, "HORA DE LA LIQUIDACION:");
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(60, 65);
    $pdf->Write(5, \funcionesGenerales::mostrarHora(date("His")));
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(20, 71);
    $pdf->Write(5, "CONCEPTOS");
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(110, 71);
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
    $y = 74;
    $total = 0;
    foreach ($relServ as $ser) {
        $nomserv = retornarRegistroMysqliApi($dbx, 'mreg_servicios', "idservicio='" . $ser["idservicio"] . "'", "nombre");
        // $nomserv = retornarNombreServicioRegistros($ser["idservicio"]);
        $y = $y + 4;
        $pdf->SetFont('Helvetica', '', 7);
        $pdf->SetXY(20, $y);
        $pdf->Write(5, $nomserv);
        $pdf->SetFont('Helvetica', '', 7);
        $pdf->SetXY(110, $y);
        $pdf->Write(5, number_format($ser["valor"], 0));
        $total = $total + $ser["valor"];
    }
    $y = $y + 4;
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(20, $y);
    $pdf->Write(5, 'TOTAL');
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(110, $y);
    $pdf->Write(5, number_format($total, 0));

    $pdf->Line(2, 113, 210, 113);


    // Imprime Cuadricula
    $pdf->Rect(25, 116, 175, 6);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(27, 117);
    $pdf->Write(5, "DATOS DEL COMERCIANTE");
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(140, 117);
    $pdf->Write(5, "NUMERO RECUPERACION: " . trim($liq["numerorecuperacion"]));

    $pdf->Rect(25, 123, 175, 17);
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(27, 124);
    $pdf->Write(5, "NOMBRE O RAZON SOCIAL:");
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(80, 124);
    $pdf->Write(5, substr($nombre, 0, 30));
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(27, 129);
    $pdf->Write(5, "IDENTIFICACION:");
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(80, 129);
    $pdf->Write(5, ltrim($idex, 0));
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(27, 134);
    $pdf->Write(5, "MATRICULA/PROPONENTE:");
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(80, 134);
    $pdf->Write(5, $matx);

    $pdf->Rect(25, 141, 175, 6);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(27, 142);
    $pdf->Write(5, "LIQUIDACION DEL SERVICIO");

    $pdf->Rect(25, 148, 175, 50);
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(35, 149);
    $pdf->Write(5, "FECHA DE LA LIQUIDACION:");
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(80, 149);
    $pdf->Write(5, \funcionesGenerales::mostrarFecha(date("Ymd")));
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(35, 154);
    $pdf->Write(5, "HORA DE LA LIQUIDACION:");
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(80, 154);
    $pdf->Write(5, \funcionesGenerales::mostrarHora(date("His")));
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(35, 159);
    $pdf->Write(5, "CONCEPTOS");
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(135, 159);
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
    $y = 159;
    $total = 0;
    foreach ($relServ as $ser) {
        $nomserv = retornarRegistroMysqliApi($dbx, 'mreg_servicios', "idservicio='" . $ser["idservicio"] . "'", "nombre");
        // $nomserv = retornarNombreServicioRegistros($ser["idservicio"]);
        $y = $y + 4;
        $pdf->SetFont('Helvetica', '', 7);
        $pdf->SetXY(35, $y);
        $pdf->Write(5, $nomserv);
        $pdf->SetFont('Helvetica', '', 7);
        $pdf->SetXY(135, $y);
        $pdf->Write(5, number_format($ser["valor"], 0));
        $total = $total + $ser["valor"];
    }
    $y = $y + 4;
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(35, $y);
    $pdf->Write(5, 'TOTAL');
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(135, $y);
    $pdf->Write(5, number_format($total, 0));

    $pdf->Rect(10, 201, 190, 6);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(27, 202);
    $pdf->Write(5, "SI PAGA EN EFECTIVO, CHEQUE, TARJETA DEBITO O CREDITO");
    $pdf->Rect(10, 208, 140, 6);
    $pdf->Rect(151, 208, 49, 6);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(12, 209);
    $pdf->Write(5, "EN CHEQUE[  ], TAR.DEB[  ], TAR.CRE[  ], NOMBRE:");
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(152, 209);
    $pdf->Write(5, "NRO. CHEQUE:");

    if (trim($imagen) != '') {
        $pdf->Image($imagen, 10, 216, 80, 19);
    }

    $pdf->Rect(100, 216, 100, 6);
    $pdf->Rect(100, 223, 100, 6);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(102, 217);
    $pdf->Write(5, "VALOR A PAGAR:");
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetXY(165, 217);
    $pdf->Write(5, "     $ " . number_format($liq["valortotal"], 0));
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(102, 224);
    $pdf->Write(5, "FECHA DEL PAGO     AAAA [            ]   MM [       ] DD [     ]");

    $txt = '';
    if (($_SESSION["generales"]["codigoempresa"] == '27') ||
            ($_SESSION["generales"]["codigoempresa"] == 'XX')) {
        $txt = 'Para mayor comodidad pague en cualquier oficina de: Bancolombia, Banco Davivienda ';
        $txt .= '- Red Bancafe, Banco Agrario de Colombia, Banco de Bogotá, Banco de Occidente, ';
        $txt .= 'Helm Bank, Banco Colpatria, Coomeva Financiera.';
    }

    if (($_SESSION["generales"]["codigoempresa"] == '11') ||
            ($_SESSION["generales"]["codigoempresa"] == '98')) {
        $txt = 'Para mayor comodidad pague en cualquier oficina de Banco de Occidente, Corresponsales no bancarios, ';
        $txt .= 'EXITO o Baloto, o cualquier oficina de la Cámara de Comercio de Cúcuta.';
    }

    $pdf->SetFont('Helvetica', '', 5);
    $pdf->SetXY(10, 234);
    $pdf->Write(4, $txt);

    $txt = RAZONSOCIAL . ' - NIT: ' . NIT . ' - DIRECCION: ' . DIRECCION1 . ' - TELEFONO: ' . PBX;
    $pdf->SetFont('Helvetica', '', 5);
    $pdf->SetXY(10, 238);
    $pdf->Write(4, $txt);

    $pdf->SetFont('Helvetica', 'B', 16);
    $pdf->RotatedText(15, 190, 'CUPON PARA EL BANCO', 90);

    unlink($imagen);
    $pdf->_endpage();
    $name = $pathout . '/prediligenciados-volban-' . $liq["idliquidacion"] . ".pdf";
    $pdf->Output($name, "F");
    return $name;
    unset($pdf);
}

function armarPdfVolantePagoBancosOccidente($dbx, $liq, $liqdet, $valliq = 0) {
    require_once ('../components/fpdf186/fpdf.php');
    require_once ('../components/fpdf186/fpdf_code128.php');
    require_once ('generaGS1128.php');

    //
    $_SESSION["generales"]["corterenovacion"] = retornarRegistroMysqliApi($dbx, 'mreg_cortes_renovacion', "ano='" . date("Y") . "'", "corte");
    $_SESSION["generales"]["corterenovacionmesdia"] = substr($_SESSION["generales"]["corterenovacion"], 4, 4);

    if (!class_exists('PDFVol')) {

        // class PDFForProp extends FPDF_Protection {
        class PDFVol extends PDF_Code128 {

            var $angle = 0;

            function Rotate($angle, $x = -1, $y = -1) {
                if ($x == -1)
                    $x = $this->x;
                if ($y == -1)
                    $y = $this->y;
                if ($this->angle != 0)
                    $this->_out('Q');
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

    $pathout = '../tmp';
    $tipoimpresion = "prediligenciados";
    $pdf = new PDFVol("Portrait", "mm", "Letter");
    $pdf->AliasNbPages();

    //
    $matx = '';
    $ref = sprintf("%014s", $liq["idliquidacion"]);
    $nombre = $liq["nombrecliente"];
    $idex = $liq["identificacioncliente"];

    //
    if ($liq["tipotramite"] == 'matriculapnat' ||
            $liq["tipotramite"] == 'matriculacambidom' ||
            $liq["tipotramite"] == 'matriculaest' ||
            $liq["tipotramite"] == 'matriculasuc'
    ) {
        $matx = 'NUEVA MATRICULA';
        $ref = $liq["idliquidacion"];
        $nombre = $liq["nombrepnat"];
        $idex = $liq["idepnat"];
    }

    if ($liq["tipotramite"] == 'matriculapjur' ||
            $liq["tipotramite"] == 'matriculaesadl'
    ) {
        $matx = 'NUEVA MATRICULA';
        $ref = sprintf("%014s", $liq["idliquidacion"]);
        $nombre = $liq["nombrerepleg"];
        $idex = $liq["iderepleg"];
    }

    if ($liq["tipotramite"] == 'renovacionmatricula' ||
            $liq["tipotramite"] == 'renovacionesadl'
    ) {
        $matx = $liqdet[0]["expediente"];
        $ref = $liq["idliquidacion"];
        $nombre = $liq["nombrecliente"];
        $idex = $liq["identificacioncliente"];
    }

    // Arma el código de barras
    if (defined('CODIGO_IAC')) {
        $imagen = '../tmp/codbarras-' . session_id() . date("Ymd") . date("His") . '.png';
        if (date("md") <= $_SESSION["generales"]["corterenovacionmesdia"]) {
            generarGs1128(trim(CODIGO_IAC), $ref, date("Y") . $_SESSION["generales"]["corterenovacionmesdia"], $imagen, $valliq);
        } else {
            generarGs1128(trim(CODIGO_IAC), $ref, date("Y") . '1231', $imagen, $valliq);
        }
    } else {
        $imagen = '';
    }

    //
    $codigorecaudoraiz = retornarClaveValorMysqliApi($dbx, '90.33.49');
    $codigorecaudo = retornarClaveValorMysqliApi($dbx, '90.33.50');

    // Imprime encabezados
    $pdf->AddPage();
    $pdf->SetMargins(15, 25, 7);

    // Parte del cupon que es para el banco
    if (file_exists('../images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.jpg')) {
        $pdf->Image('../images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.jpg', 10, 7, 18, 18);
    }
    // Primer código de barras
    if (trim($imagen) != '') {
        $pdf->Image($imagen, 130, 7, 70, 17);
    }

    // Títulos del volante - primera parte
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetXY(30, 11);
    $pdf->Write(4, "VOLANTE PARA PAGO EN BANCOS");
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetXY(30, 16);
    $pdf->Write(4, RAZONSOCIALSMS);
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetXY(30, 21);
    $pdf->Write(4, "NIT. " . NIT);

    // Imprime Cuadricula
    $pdf->Rect(25, 28, 175, 6);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(27, 29);
    $pdf->Write(5, "DATOS DEL COMERCIANTE");
    /*
      if (trim($codigorecaudo)!='') {
      $pdf->SetFont('Helvetica','B',9);$pdf->SetXY(80,29);$pdf->Write(5,"CODIGO PAGO BALOTO: " . $codigorecaudo);
      }
     */
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(150, 29);
    $pdf->Write(5, "REFERENCIA: " . $ref);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(140, 36);
    $pdf->Write(5, "NUMERO RECUPERACION: " . $liq["numerorecuperacion"]);

    $pdf->Rect(25, 35, 175, 17);
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(27, 36);
    $pdf->Write(5, "NOMBRE O RAZON SOCIAL:");
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(80, 36);
    $pdf->Write(5, substr($nombre, 0, 30));
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(27, 41);
    $pdf->Write(5, "IDENTIFICACION:");
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(80, 41);
    $pdf->Write(5, ltrim($idex, 0));
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(27, 46);
    $pdf->Write(5, "MATRICULA/PROPONENTE:");
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(80, 46);
    $pdf->Write(5, $matx);

    $pdf->Rect(25, 53, 175, 6);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(27, 54);
    $pdf->Write(5, "LIQUIDACION DEL SERVICIO");

    $pdf->Rect(25, 60, 175, 50);
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(35, 61);
    $pdf->Write(5, "FECHA DE LA LIQUIDACION:");
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(80, 61);
    $pdf->Write(5, \funcionesGenerales::mostrarFecha(date("Ymd")));
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(35, 65);
    $pdf->Write(5, "HORA DE LA LIQUIDACION:");
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(80, 65);
    $pdf->Write(5, \funcionesGenerales::mostrarHora(date("His")));
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(35, 71);
    $pdf->Write(5, "CONCEPTOS");
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(135, 71);
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
    $y = 74;
    $total = 0;
    foreach ($relServ as $ser) {
        $nomserv = retornarRegistroMysqliApi($dbx, 'mreg_servicios', "idservicio='" . $ser["idservicio"] . "'", "nombre");
        // $nomserv = retornarNombreServicioRegistros($ser["idservicio"]);
        $y = $y + 4;
        $pdf->SetFont('Helvetica', '', 7);
        $pdf->SetXY(35, $y);
        $pdf->Write(5, $nomserv);
        $pdf->SetFont('Helvetica', '', 7);
        $pdf->SetXY(135, $y);
        $pdf->Write(5, number_format($ser["valor"], 0));
        $total = $total + $ser["valor"];
    }
    $y = $y + 4;
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(35, $y);
    $pdf->Write(5, 'TOTAL');
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(135, $y);
    $pdf->Write(5, number_format($total, 0));
    $pdf->Line(2, 113, 210, 113);


    // Segunda parte
    // Imprime Cuadricula
    // Títulos del volante - primera parte
    if (file_exists('../images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.jpg')) {
        $pdf->Image('../images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.jpg', 10, 116, 18, 18);
    }
    // Primer código de barras
    if (trim($imagen) != '') {
        $pdf->Image($imagen, 130, 116, 70, 17);
    }

    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetXY(30, 116);
    $pdf->Write(4, "VOLANTE PARA PAGO EN BANCOS");
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetXY(30, 121);
    $pdf->Write(4, RAZONSOCIALSMS);
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetXY(30, 126);
    $pdf->Write(4, "NIT. " . NIT);

    $pdf->Rect(25, 137, 175, 6);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(27, 138);
    $pdf->Write(5, "DATOS DEL COMERCIANTE");
    /*
      if (trim($codigorecaudo)!='') {
      $pdf->SetFont('Helvetica','B',9);$pdf->SetXY(80,138);$pdf->Write(5,"CODIGO PAGO BALOTO: " . $codigorecaudo);
      }
     */
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(150, 138);
    $pdf->Write(5, "REFERENCIA: " . $ref);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(140, 145);
    $pdf->Write(5, "NUMERO RECUPERACION: " . $liq["numerorecuperacion"]);

    $pdf->Rect(25, 144, 175, 17);
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(27, 145);
    $pdf->Write(5, "NOMBRE O RAZON SOCIAL:");
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(80, 145);
    $pdf->Write(5, substr($nombre, 0, 30));
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(27, 150);
    $pdf->Write(5, "IDENTIFICACION:");
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(80, 150);
    $pdf->Write(5, ltrim($idex, 0));
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(27, 155);
    $pdf->Write(5, "MATRICULA/PROPONENTE:");
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(80, 155);
    $pdf->Write(5, $matx);

    $pdf->Rect(25, 162, 175, 6);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(27, 163);
    $pdf->Write(5, "LIQUIDACION DEL SERVICIO");

    $pdf->Rect(25, 169, 175, 50);
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(35, 170);
    $pdf->Write(5, "FECHA DE LA LIQUIDACION:");
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(80, 170);
    $pdf->Write(5, \funcionesGenerales::mostrarFecha(date("Ymd")));
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(35, 175);
    $pdf->Write(5, "HORA DE LA LIQUIDACION:");
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(80, 175);
    $pdf->Write(5, \funcionesGenerales::mostrarHora(date("His")));
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(35, 180);
    $pdf->Write(5, "CONCEPTOS");
    $pdf->SetFont('Helvetica', 'B', 7);
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
        $nomserv = retornarRegistroMysqliApi($dbx, 'mreg_servicios', "idservicio='" . $ser["idservicio"] . "'", "nombre");
        // $nomserv = retornarNombreServicioRegistros($ser["idservicio"]);
        $y = $y + 4;
        $pdf->SetFont('Helvetica', '', 7);
        $pdf->SetXY(35, $y);
        $pdf->Write(5, $nomserv);
        $pdf->SetFont('Helvetica', '', 7);
        $pdf->SetXY(135, $y);
        $pdf->Write(5, number_format($ser["valor"], 0));
        $total = $total + $ser["valor"];
    }
    $y = $y + 4;
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(35, $y);
    $pdf->Write(5, 'TOTAL');
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(135, $y);
    $pdf->Write(5, number_format($total, 0));

    $pdf->Rect(10, 222, 190, 6);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(27, 223);
    $pdf->Write(5, "SOLO PARA PAGO EN EFECTIVO O CON CHEQUE DE GERENCIA");
    $pdf->Rect(10, 229, 90, 6);
    $pdf->Rect(101, 229, 99, 6);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(12, 230);
    $pdf->Write(5, "BANCO:");
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(103, 230);
    $pdf->Write(5, "NRO. CHEQUE:");

    /*
      if (trim($imagen)!='') {
      $pdf->Image($imagen,10,237,70,17);
      }
     */

    $pdf->Rect(10, 236, 90, 6);
    $pdf->Rect(101, 236, 99, 6);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(12, 237);
    $pdf->Write(5, "VALOR A PAGAR:");
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetXY(55, 237);
    $pdf->Write(5, "     $ " . number_format($liq["valortotal"], 0));
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(102, 237);
    $pdf->Write(5, "FECHA DEL PAGO     AAAA [            ]   MM [       ] DD [     ]");

    //
    $txt = '';

    //
    if ($_SESSION["generales"]["codigoempresa"] == '11' ||
            $_SESSION["generales"]["codigoempresa"] == '20' ||
            $_SESSION["generales"]["codigoempresa"] == '98'
    ) {
        $txt = 'Para mayor comodidad pague en cualquier oficina de Banco de Occidente, Corresponsales no bancarios, ';
        $txt .= 'EXITO o Baloto, o cualquier oficina de la Cámara de Comercio de Cúcuta.';
    }

    //
    if (($_SESSION["generales"]["codigoempresa"] == '27') ||
            ($_SESSION["generales"]["codigoempresa"] == 'XX')) {
        $txt = 'Para mayor comodidad pague en cualquier oficina de: Bancolombia, Banco Davivienda ';
        $txt .= '- Red Bancafe, Banco Agrario de Colombia, Banco de Bogotá, Banco de Occidente, ';
        $txt .= 'Helm Bank, Banco Colpatria, Coomeva Financiera.';
    }


    $pdf->SetFont('Helvetica', '', 5);
    $pdf->SetXY(10, 242);
    $pdf->Write(4, $txt);

    if (trim($codigorecaudo) != '') {
        $ix = $ix = $pdf->GetY() + 4;
        $pdf->Rect(10, $ix, 190, 6);
        $ix++;
        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->SetXY(70, $ix);
        $pdf->Write(5, "CODIGO PAGO BALOTO: " . $codigorecaudoraiz . ' ' . $codigorecaudo . ' ' . $ref);
    }


    // $txt=RAZONSOCIAL.' - NIT: '.NIT.' - DIRECCION: '.DIRECCION1. ' - TELEFONO: '.PBX;
    // $pdf->SetFont('Helvetica','',5);$pdf->SetXY(10,249);$pdf->Write(4,$txt);

    $pdf->SetFont('Helvetica', 'B', 16);
    $pdf->RotatedText(15, 100, 'CUPON PARA EL CLIENTE', 90);
    $pdf->RotatedText(15, 211, 'CUPON PARA EL BANCO', 90);

    unlink($imagen);
    $pdf->_endpage();
    $name = $pathout . '/prediligenciados-volban-' . $liq["idliquidacion"] . ".pdf";
    $pdf->Output($name, "F");
    unset($pdf);
    return $name;
}

function armarPdfVolantePagoBancosCucuta($dbx, $liq, $liqdet, $valliq = 0) {
    require_once ('../components/fpdf186/fpdf.php');
    require_once ('../components/fpdf186/fpdf_code128.php');
    require_once ('generaGS1128.php');

    //
    $_SESSION["generales"]["corterenovacion"] = retornarRegistroMysqliApi($dbx, 'mreg_cortes_renovacion', "ano='" . date("Y") . "'", "corte");
    $_SESSION["generales"]["corterenovacionmesdia"] = substr($_SESSION["generales"]["corterenovacion"], 4, 4);

    if (!class_exists('PDFVol')) {

        // class PDFForProp extends FPDF_Protection { 
        class PDFVol extends PDF_Code128 {

            var $angle = 0;

            function Rotate($angle, $x = -1, $y = -1) {
                if ($x == -1)
                    $x = $this->x;
                if ($y == -1)
                    $y = $this->y;
                if ($this->angle != 0)
                    $this->_out('Q');
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

    $pathout = '../tmp';
    $tipoimpresion = "prediligenciados";
    $pdf = new PDFVol("Portrait", "mm", "Letter");
    $pdf->AliasNbPages();

    //
    $matx = '';
    $ref = sprintf("%014s", $liq["idliquidacion"]);
    $nombre = $liq["nombrecliente"];
    $idex = $liq["identificacioncliente"];

    //
    if ($liq["tipotramite"] == 'matriculapnat' ||
            $liq["tipotramite"] == 'matriculacambidom' ||
            $liq["tipotramite"] == 'matriculaest' ||
            $liq["tipotramite"] == 'matriculasuc'
    ) {
        $matx = 'NUEVA MATRICULA';
        $ref = $liq["idliquidacion"];
        $nombre = $liq["nombrepnat"];
        $idex = $liq["idepnat"];
    }

    if ($liq["tipotramite"] == 'matriculapjur' ||
            $liq["tipotramite"] == 'matriculaesadl'
    ) {
        $matx = 'NUEVA MATRICULA';
        $ref = sprintf("%014s", $liq["idliquidacion"]);
        $nombre = $liq["nombrerepleg"];
        $idex = $liq["iderepleg"];
    }

    if ($liq["tipotramite"] == 'renovacionmatricula' ||
            $liq["tipotramite"] == 'renovacionesadl'
    ) {
        $matx = $liqdet[0]["expediente"];
        $ref = $liq["idliquidacion"];
        $nombre = $liq["nombrecliente"];
        $idex = $liq["identificacioncliente"];
    }

    // Arma el código de barras
    if (defined('CODIGO_IAC')) {
        $imagen = '../tmp/codbarras-' . session_id() . date("Ymd") . date("His") . '.png';
        if (date("md") <= $_SESSION["generales"]["corterenovacionmesdia"]) {
            generarGs1128(trim(CODIGO_IAC), $_SESSION["generales"]["codigoempresa"] . sprintf("%011s", $ref), date("Y") . $_SESSION["generales"]["corterenovacionmesdia"], $imagen, $valliq);
        } else {
            generarGs1128(trim(CODIGO_IAC), $_SESSION["generales"]["codigoempresa"] . sprintf("%011s", $ref), date("Y") . '1231', $imagen, $valliq);
        }
    } else {
        $imagen = '';
    }

    //
    $codigorecaudoraiz = retornarClaveValorMysqliApi($dbx, '90.33.49');
    $codigorecaudo = retornarClaveValorMysqliApi($dbx, '90.33.50');

    // Imprime encabezados
    $pdf->AddPage();
    $pdf->SetMargins(15, 25, 7);

    // Parte del cupon que es para el banco
    if (file_exists('../images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.jpg')) {
        $pdf->Image('../images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.jpg', 10, 7, 18, 18);
    }
    // Primer código de barras
    if (trim($imagen) != '') {
        $pdf->Image($imagen, 130, 7, 70, 17);
    }

    // Títulos del volante - primera parte
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetXY(30, 11);
    $pdf->Write(4, "VOLANTE PARA PAGO EN BANCOS");
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetXY(30, 16);
    $pdf->Write(4, RAZONSOCIALSMS);
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetXY(30, 21);
    $pdf->Write(4, "NIT. " . NIT);

    // Imprime Cuadricula
    $pdf->Rect(25, 28, 175, 6);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(27, 29);
    $pdf->Write(5, "DATOS DEL COMERCIANTE");
    /*
      if (trim($codigorecaudo)!='') {
      $pdf->SetFont('Helvetica','B',9);$pdf->SetXY(80,29);$pdf->Write(5,"CODIGO PAGO BALOTO: " . $codigorecaudo);
      }
     */
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(150, 29);
    $pdf->Write(5, "REFERENCIA: " . $_SESSION["generales"]["codigoempresa"] . sprintf("%011s", $ref));
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(140, 36);
    $pdf->Write(5, "NUMERO RECUPERACION: " . $liq["numerorecuperacion"]);

    $pdf->Rect(25, 35, 175, 17);
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(27, 36);
    $pdf->Write(5, "NOMBRE O RAZON SOCIAL:");
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(80, 36);
    $pdf->Write(5, substr($nombre, 0, 30));
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(27, 41);
    $pdf->Write(5, "IDENTIFICACION:");
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(80, 41);
    $pdf->Write(5, ltrim($idex, 0));
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(27, 46);
    $pdf->Write(5, "MATRICULA/PROPONENTE:");
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(80, 46);
    $pdf->Write(5, $matx);

    $pdf->Rect(25, 53, 175, 6);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(27, 54);
    $pdf->Write(5, "LIQUIDACION DEL SERVICIO");

    $pdf->Rect(25, 60, 175, 50);
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(35, 61);
    $pdf->Write(5, "FECHA DE LA LIQUIDACION:");
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(80, 61);
    $pdf->Write(5, \funcionesGenerales::mostrarFecha(date("Ymd")));
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(35, 65);
    $pdf->Write(5, "HORA DE LA LIQUIDACION:");
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(80, 65);
    $pdf->Write(5, \funcionesGenerales::mostrarHora(date("His")));
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(35, 71);
    $pdf->Write(5, "CONCEPTOS");
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(135, 71);
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
    $y = 74;
    $total = 0;
    foreach ($relServ as $ser) {
        $nomserv = retornarRegistroMysqliApi($dbx, 'mreg_servicios', "idservicio='" . $ser["idservicio"] . "'", "nombre");
        // $nomserv = retornarNombreServicioRegistros($ser["idservicio"]);
        $y = $y + 4;
        $pdf->SetFont('Helvetica', '', 7);
        $pdf->SetXY(35, $y);
        $pdf->Write(5, $nomserv);
        $pdf->SetFont('Helvetica', '', 7);
        $pdf->SetXY(135, $y);
        $pdf->Write(5, number_format($ser["valor"], 0));
        $total = $total + $ser["valor"];
    }
    $y = $y + 4;
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(35, $y);
    $pdf->Write(5, 'TOTAL');
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(135, $y);
    $pdf->Write(5, number_format($total, 0));
    $pdf->Line(2, 113, 210, 113);



    // Segunda parte
    // Imprime Cuadricula
    // Títulos del volante - primera parte
    if (file_exists('../images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.jpg')) {
        $pdf->Image('../images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.jpg', 10, 116, 18, 18);
    }
    // Primer código de barras
    if (trim($imagen) != '') {
        $pdf->Image($imagen, 130, 116, 70, 17);
    }

    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetXY(30, 116);
    $pdf->Write(4, "VOLANTE PARA PAGO EN BANCOS");
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetXY(30, 121);
    $pdf->Write(4, RAZONSOCIALSMS);
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetXY(30, 126);
    $pdf->Write(4, "NIT. " . NIT);

    $pdf->Rect(25, 137, 175, 6);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(27, 138);
    $pdf->Write(5, "DATOS DEL COMERCIANTE");
    /*
      if (trim($codigorecaudo)!='') {
      $pdf->SetFont('Helvetica','B',9);$pdf->SetXY(80,138);$pdf->Write(5,"CODIGO PAGO BALOTO: " . $codigorecaudo);
      }
     */
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(150, 138);
    $pdf->Write(5, "REFERENCIA: " . $_SESSION["generales"]["codigoempresa"] . sprintf("%011s", $ref));
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(140, 145);
    $pdf->Write(5, "NUMERO RECUPERACION: " . $liq["numerorecuperacion"]);

    $pdf->Rect(25, 144, 175, 17);
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(27, 145);
    $pdf->Write(5, "NOMBRE O RAZON SOCIAL:");
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(80, 145);
    $pdf->Write(5, substr($nombre, 0, 30));
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(27, 150);
    $pdf->Write(5, "IDENTIFICACION:");
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(80, 150);
    $pdf->Write(5, ltrim($idex, 0));
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(27, 155);
    $pdf->Write(5, "MATRICULA/PROPONENTE:");
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(80, 155);
    $pdf->Write(5, $matx);

    $pdf->Rect(25, 162, 175, 6);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(27, 163);
    $pdf->Write(5, "LIQUIDACION DEL SERVICIO");

    $pdf->Rect(25, 169, 175, 50);
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(35, 170);
    $pdf->Write(5, "FECHA DE LA LIQUIDACION:");
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(80, 170);
    $pdf->Write(5, \funcionesGenerales::mostrarFecha(date("Ymd")));
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(35, 175);
    $pdf->Write(5, "HORA DE LA LIQUIDACION:");
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(80, 175);
    $pdf->Write(5, \funcionesGenerales::mostrarHora(date("His")));
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(35, 180);
    $pdf->Write(5, "CONCEPTOS");
    $pdf->SetFont('Helvetica', 'B', 7);
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
        $nomserv = retornarRegistroMysqliApi($dbx, 'mreg_servicios', "idservicio='" . $ser["idservicio"] . "'", "nombre");
        // $nomserv = retornarNombreServicioRegistros($ser["idservicio"]);
        $y = $y + 4;
        $pdf->SetFont('Helvetica', '', 7);
        $pdf->SetXY(35, $y);
        $pdf->Write(5, $nomserv);
        $pdf->SetFont('Helvetica', '', 7);
        $pdf->SetXY(135, $y);
        $pdf->Write(5, number_format($ser["valor"], 0));
        $total = $total + $ser["valor"];
    }
    $y = $y + 4;
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(35, $y);
    $pdf->Write(5, 'TOTAL');
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(135, $y);
    $pdf->Write(5, number_format($total, 0));
    $pdf->Rect(10, 222, 190, 6);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(27, 223);
    $pdf->Write(5, "SOLO PARA PAGO EN EFECTIVO O CON CHEQUE DE GERENCIA");
    $pdf->Rect(10, 229, 90, 6);
    $pdf->Rect(101, 229, 99, 6);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(12, 230);
    $pdf->Write(5, "BANCO:");
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(103, 230);
    $pdf->Write(5, "NRO. CHEQUE:");

    /*
      if (trim($imagen)!='') {
      $pdf->Image($imagen,10,237,70,17);
      }
     */

    $pdf->Rect(10, 236, 90, 6);
    $pdf->Rect(101, 236, 99, 6);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(12, 237);
    $pdf->Write(5, "VALOR A PAGAR:");
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetXY(55, 237);
    $pdf->Write(5, "     $ " . number_format($liq["valortotal"], 0));
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(102, 237);
    $pdf->Write(5, "FECHA DEL PAGO     AAAA [            ]   MM [       ] DD [     ]");

    //
    $txt = '';

    //
    if ($_SESSION["generales"]["codigoempresa"] == '11') {
        $txt = 'Para mayor comodidad pague en cualquier oficina de Banco de Occidente, Corresponsales no bancarios, ';
        $txt .= 'EXITO o Baloto, o cualquier oficina de la Cámara de Comercio de Cúcuta.';
    }

    //
    if ($_SESSION["generales"]["codigoempresa"] == '27') {
        $txt = 'Para mayor comodidad pague en cualquier oficina de: Bancolombia, Banco Davivienda ';
        $txt .= '- Red Bancafe, Banco Agrario de Colombia, Banco de Bogotá, Banco de Occidente, ';
        $txt .= 'Helm Bank, Banco Colpatria, Coomeva Financiera.';
    }


    $pdf->SetFont('Helvetica', '', 5);
    $pdf->SetXY(10, 242);
    $pdf->Write(4, $txt);

    if (trim($codigorecaudo) != '') {
        $ix = $ix = $pdf->GetY() + 4;
        $pdf->Rect(10, $ix, 190, 6);
        $ix++;
        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->SetXY(70, $ix);
        $pdf->Write(5, "CODIGO PAGO BALOTO: " . $codigorecaudoraiz . ' ' . $codigorecaudo . ' ' . $ref);
    }


    // $txt=RAZONSOCIAL.' - NIT: '.NIT.' - DIRECCION: '.DIRECCION1. ' - TELEFONO: '.PBX;
    // $pdf->SetFont('Helvetica','',5);$pdf->SetXY(10,249);$pdf->Write(4,$txt);

    $pdf->SetFont('Helvetica', 'B', 16);
    $pdf->RotatedText(15, 100, 'CUPON PARA EL CLIENTE', 90);
    $pdf->RotatedText(15, 211, 'CUPON PARA EL BANCO', 90);

    unlink($imagen);
    $pdf->_endpage();
    $name = $pathout . '/prediligenciados-volban-' . $liq["idliquidacion"] . ".pdf";
    $pdf->Output($name, "F");
    return $name;
}

/**
 * 
 * @param 	int 	$liq		Número de la liquidación
 * @param 	array 	$liqdet		Detalle de la liquidacion
 * @param 	double 	$valliq		Valor de la liquidacion
 * @param 	string 	$tipo		Justificado: organiza referencia a 13 dígitos (Antecede el número de la Cámara de Comercio, sinjustificar: organiza la referencia a 6 dígitos
 * @return 	string	$name		Nombre del pdf
 */
function armarPdfVolantePagoBancosCucutaNuevo($dbx, $liq, $liqdet, $valliq = 0, $tipo = 'justificado', $titulo = 'bancos', $textoBancos = '', $textoSoportes = '',$nroVolante = '') {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/fpdf186/fpdf.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/generaGS1128.php');
    $_SESSION["generales"]["corterenovacion"] = retornarRegistroMysqliApi($dbx, 'mreg_cortes_renovacion', "ano='" . date("Y") . "'", "corte");
    $_SESSION["generales"]["corterenovacionmesdia"] = substr($_SESSION["generales"]["corterenovacion"], 4, 4);
    if (ACUERDO_BANCOS_RECIBIR_FORMULARIOS != 'S') {
        $textoSoportes = '';
    }
    if (!class_exists('PDFVol1')) {

        // class PDFForProp extends FPDF_Protection {
        class PDFVol1 extends FPDF {

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

    $pathout = $_SESSION["generales"]["pathabsoluto"] . '/tmp';
    // $tipoimpresion = "prediligenciados";
    $pdf = new PDFVol1("Portrait", "mm", "Letter");
    $pdf->AliasNbPages();

    //
    if (isset($liqdet[0]["expediente"])) {
        $matx = $liqdet[0]["expediente"];
        $nomx = $liqdet[0]["nombre"]; 
    } else {
        if (isset($liqdet[1]["expediente"])) {
            $matx = $liqdet[1]["expediente"];
            $nomx = $liqdet[1]["nombre"]; 
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
    $nombre = \funcionesGenerales::utf8_decode($liq["nombrecliente"]) . ' ' . \funcionesGenerales::utf8_decode($liq["apellidocliente"]);
    $idex = $liq["identificacioncliente"];

    // 
    // Arma el código de barras
    if (defined('CODIGO_IAC') && trim(CODIGO_IAC) != '') {
        $imagen = $_SESSION["generales"]["pathabsoluto"] . '/tmp/codbarras-' . session_id() . date("Ymd") . date("His") . '.png';
        if (date("md") <= $_SESSION["generales"]["corterenovacionmesdia"]) {
            generarGs1128(trim(CODIGO_IAC), $ref, date("Y") . $_SESSION["generales"]["corterenovacionmesdia"], $imagen, $valliq);
        } else {
            generarGs1128(trim(CODIGO_IAC), $ref, date("Y") . '1231', $imagen, $valliq);
        }
    } else {
        $imagen = '';
    }

    //
    $codigorecaudoraiz = retornarClaveValorMysqliApi($dbx, '90.33.49');
    $codigorecaudo = retornarClaveValorMysqliApi($dbx, '90.33.50');
    $codigorecaudoefecty = retornarClaveValorMysqliApi($dbx, '90.33.52');
    $codigorecaudobancol = retornarClaveValorMysqliApi($dbx, '90.33.54');
    
    // Imprime encabezados
    $pdf->AddPage();
    $pdf->SetMargins(15, 25, 7);

    // Parte del cupon que es para el banco
    if (file_exists($_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.jpg')) {
        $pdf->Image($_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.jpg', 10, 7, 18, 18);
    }
    // Primer código de barras
    if (trim($imagen) != '') {
        // $pdf->Image($imagen, 130, 7, 70, 17);
        $pdf->Image($imagen, 110, 7, 90, 19);
    }

    // Títulos del volante - primera parte
    $pdf->SetFont('Helvetica', '', 8);
    $pdf->SetXY(30, 11);
    $pdf->Write(4, "VOLANTE PARA PAGO");

    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(30, 16);
    $pdf->Write(4, substr(RAZONSOCIALSMS, 0, 45));
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(30, 21);
    $pdf->Write(4, "NIT. " . NIT);

    // Imprime Cuadricula
    $pdf->Rect(25, 28, 175, 6);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(27, 29);
    $pdf->Write(5, "DATOS DEL COMERCIANTE / CLIENTE");

    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(140, 36);
    $pdf->Write(5, "NUMERO RECUPERACION: " . $liq["numerorecuperacion"]);
    if (trim($codigorecaudo) != '') {
        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->SetXY(116, 41);
        $pdf->Write(5, "CODIGO PAGO BALOTO: " . $codigorecaudoraiz . ' ' . $codigorecaudo . ' ' . $ref);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Helvetica', '', 7);
        $pdf->SetXY(116, 45);
        if ($codigorecaudoefecty != '') {
            $pdf->Write(5, "CONVENIO EFECTY: " . $codigorecaudoefecty . ' REF: ' . $ref);
        }
        $pdf->SetXY(116, 48);
        $pdf->Write(5, \funcionesGenerales::utf8_decode("de lo contrario el pago no se asentará correctamente"));
        $pdf->SetXY(116, 49);
        $pdf->SetTextColor(0, 0, 0);
    }

    $pdf->Rect(25, 35, 175, 17);
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(27, 36);
    $pdf->Write(5, "NOMBRE DEL CLIENTE:");
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(80, 36);
    $pdf->Write(5, substr($nombre, 0, 30));
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(27, 41);
    $pdf->Write(5, "IDENTIFICACION DEL CLIENTE:");
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(80, 41);
    $pdf->Write(5, ltrim($idex, 0));
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(27, 46);
    $pdf->Write(5, "EXPEDIENTE:");
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(80, 46);
    $pdf->Write(5, $matx . ' '  . \funcionesGenerales::utf8_decode($nomx));

    $pdf->Rect(25, 53, 175, 6);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(27, 54);
    $pdf->Write(5, "LIQUIDACION DEL SERVICIO");

    $pdf->Rect(25, 60, 175, 50);
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(35, 61);
    $pdf->Write(5, "FECHA DE LA LIQUIDACION:");
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(80, 61);
    $pdf->Write(5, \funcionesGenerales::mostrarFecha(date("Ymd")));
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(35, 65);
    $pdf->Write(5, "HORA DE LA LIQUIDACION:");
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(80, 65);
    $pdf->Write(5, \funcionesGenerales::mostrarHora(date("His")));
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(35, 71);
    $pdf->Write(5, "CONCEPTOS");
    $pdf->SetFont('Helvetica', 'B', 7);
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
        $nomserv = retornarRegistroMysqliApi($dbx, 'mreg_servicios', "idservicio='" . $ser["idservicio"] . "'", "nombre");
        // $nomserv = retornarNombreServicioRegistros($ser["idservicio"]);
        $y = $y + 4;
        $pdf->SetFont('Helvetica', '', 7);
        $pdf->SetXY(35, $y);
        $pdf->Write(5, $nomserv);
        $pdf->SetFont('Helvetica', '', 7);
        $pdf->SetXY(135, $y);
        $pdf->Write(5, number_format($ser["valor"], 0));
        $total = $total + $ser["valor"];
    }
    $y = $y + 4;
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(35, $y);
    $pdf->Write(5, 'TOTAL');
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(135, $y);
    $pdf->Write(5, number_format($total, 0));

    if ($ultimoanoarenovar != '') {
        $y = $y + 4;
        $pdf->SetFont('Helvetica', 'B', 7);
        $pdf->SetXY(35, $y);
        $pdf->Write(5, \funcionesGenerales::utf8_decode('El comerciante quedará renovado al ') . $ultimoanoarenovar);
    }

    if ($textoSoportes != '') {
        $y = $y + 4;
        $pdf->SetFont('Helvetica', 'B', 8);
        $pdf->SetXY(35, $y);
        $pdf->Write(4, \funcionesGenerales::utf8_decode($textoSoportes));
    }


    /*
      $y = $y + 6;
      $pdf->SetFont('Helvetica', 'B', 6);
      $pdf->SetXY(35, $y);
      $pdf->Write(5, \funcionesGenerales::utf8_decode('Señor comerciante, usted es responsable de la veracidad y exactitud de la información diligenciada en los formularios, solicitudes y anexos documentales relacionados con el trámite que está pagando.'));
      $y = $y + 10;
      $pdf->SetFont('Helvetica', 'B', 6);
      $pdf->SetXY(35, $y);
      $pdf->Write(5, 'NOMBRE: __________________________________________ FIRMA : ___________________________________ C.C. : __________________________________');
     */

    if ($nroVolante != '') {
        $y = 110;
        $pdf->SetFont('Helvetica', 'B', 6);
        $pdf->SetXY(15, $y);
        $pdf->Write(4, \funcionesGenerales::utf8_decode('Código de control del volante : ' . $nroVolante));
    }
    
    //
    $pdf->Line(2, 113, 210, 113);

    // Segunda parte
    // Imprime Cuadricula
    // Títulos del volante - primera parte
    if (file_exists($_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.jpg')) {
        $pdf->Image($_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.jpg', 10, 116, 18, 18);
    }
    // $pdf->Image('../../images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.jpg', 10, 116, 18, 18);
    // Primer código de barras
    if (trim($imagen) != '') {
        // $pdf->Image($imagen, 130, 116, 70, 17);
        $pdf->Image($imagen, 110, 116, 90, 19);
    }

    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(30, 116);
    $pdf->Write(4, "VOLANTE PARA PAGO");

    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(30, 121);
    $pdf->Write(4, substr(RAZONSOCIALSMS, 0, 45));
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(30, 126);
    $pdf->Write(4, "NIT. " . NIT);

    $pdf->Rect(25, 137, 175, 6);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(27, 138);
    $pdf->Write(5, "DATOS DEL COMERCIANTE / CLIENTE");
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(150, 138);
    $pdf->Write(5, "REFERENCIA: " . $ref);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(140, 145);
    $pdf->Write(5, "NUMERO RECUPERACION: " . $liq["numerorecuperacion"]);
    if (trim($codigorecaudo) != '') {
        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->SetXY(100, 148);
        // $pdf->Write(5, "CODIGO PAGO BALOTO: " . $codigorecaudoraiz . ' ' . $codigorecaudo . ' ' . $ref);
        $pdf->Write(5, "CONVENIO BALOTO: " . $codigorecaudoraiz . ' ' . $codigorecaudo . ' REF: ' . $ref);
        $pdf->SetTextColor(0,0,0);
        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->SetXY(100, 152);
        if ($codigorecaudoefecty != '') {
            $pdf->Write(5, "CONVENIO EFECTY: " . $codigorecaudoefecty . ' REF: ' . $ref);        $pdf->SetXY(116, 49);
        }
        $pdf->SetTextColor(0,0,0);
    }
    if ($codigorecaudobancol != '') {
        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->SetXY(100, 156);
        if ($codigorecaudoefecty != '') {
            $pdf->Write(5, "CONVENIO BANCOLOMBIA: " . $codigorecaudobancol . ' REF: ' . $ref);        $pdf->SetXY(116, 49);
        }
        $pdf->SetTextColor(0,0,0);
    }
    
    $pdf->Rect(25, 144, 175, 17);
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(27, 145);
    $pdf->Write(5, "NOMBRE DEL CLIENTE:");
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(80, 145);
    $pdf->Write(5, substr($nombre, 0, 30));
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(27, 150);
    $pdf->Write(5, "IDENTIFICACION:");
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(80, 150);
    $pdf->Write(5, ltrim($idex, 0));
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(27, 155);
    $pdf->Write(5, "EXPEDIENTE:");
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(80, 155);
    $pdf->Write(5, $matx . ' ' . \funcionesGenerales::utf8_decode($nomx));

    $pdf->Rect(25, 162, 175, 6);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(27, 163);
    $pdf->Write(5, "LIQUIDACION DEL SERVICIO");

    $pdf->Rect(25, 169, 175, 50);
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(35, 170);
    $pdf->Write(5, "FECHA DE LA LIQUIDACION:");
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(80, 170);
    $pdf->Write(5, \funcionesGenerales::mostrarFecha(date("Ymd")));
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(35, 175);
    $pdf->Write(5, "HORA DE LA LIQUIDACION:");
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(80, 175);
    $pdf->Write(5, \funcionesGenerales::mostrarHora(date("His")));
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(35, 180);
    $pdf->Write(5, "CONCEPTOS");
    $pdf->SetFont('Helvetica', 'B', 7);
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
        $nomserv = retornarRegistroMysqliApi($dbx, 'mreg_servicios', "idservicio='" . $ser["idservicio"] . "'", "nombre");
        // $nomserv = retornarNombreServicioRegistros($ser["idservicio"]);
        $y = $y + 4;
        $pdf->SetFont('Helvetica', '', 7);
        $pdf->SetXY(35, $y);
        $pdf->Write(5, $nomserv);
        $pdf->SetFont('Helvetica', '', 7);
        $pdf->SetXY(135, $y);
        $pdf->Write(5, number_format($ser["valor"], 0));
        $total = $total + $ser["valor"];
    }
    $y = $y + 4;
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(35, $y);
    $pdf->Write(5, 'TOTAL');
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(135, $y);
    $pdf->Write(5, number_format($total, 0));
    $pdf->Rect(10, 222, 190, 6);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(27, 223);
    $pdf->Write(5, "SOLO PARA PAGO EN EFECTIVO O CON CHEQUE DE GERENCIA");
    $pdf->Rect(10, 229, 90, 6);
    $pdf->Rect(101, 229, 99, 6);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(12, 230);
    $pdf->Write(5, "BANCO:");
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(103, 230);
    $pdf->Write(5, "NRO. CHEQUE:");

    /*
      if (trim($imagen)!='') {
      $pdf->Image($imagen,10,237,70,17);
      }
     */

    $pdf->Rect(10, 236, 90, 6);
    $pdf->Rect(101, 236, 99, 6);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(12, 237);
    $pdf->Write(5, "VALOR A PAGAR:");
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetXY(55, 237);
    $pdf->Write(5, "     $ " . number_format($liq["valortotal"], 0));
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(102, 237);
    $pdf->Write(5, "FECHA DEL PAGO     AAAA [            ]   MM [       ] DD [     ]");

    /*
      $txt = '';
      $txt .= 'Señor Usuario Realice sus pagos en: Banco de Occidente, Corresponsales Bancarios (Éxito, Baloto y ';
      $txt .= 'Puntos ATH), próximamente nuevo sitio de recaudo Cooguasimales, o en cualquiera de ';
      $txt .= 'nuestras Sedes. Por favor tenga';
      $pdf->SetFont('Helvetica', '', 5);
      $pdf->SetXY(15, 242);
      $pdf->Write(4, \funcionesGenerales::utf8_decode($txt));
      $txt = 'en cuenta que el valor máximo que puede ser pagado a través de ';
      $txt .= 'coresponsales bancarios es de $400,000.oo para BALOTO y $10,000,000.oo en Almacenes Exito.';
      $pdf->SetFont('Helvetica', '', 5);
      $pdf->SetX(15, 246);
      $pdf->Write(4, \funcionesGenerales::utf8_decode($txt));
     */

    if (trim($textoBancos) != '') {
        $pdf->SetFont('Helvetica', '', 7);
        $pdf->SetXY(15, 242);
        $pdf->MultiCell(180, 3, \funcionesGenerales::utf8_decode($textoBancos), 0, 'J', 0);
    }

    if ($textoSoportes != '') {
        $y = $pdf->GetY();
        $y = $y + 3;
        $pdf->SetFont('Helvetica', 'B', 8);
        $pdf->SetXY(35, $y);
        $pdf->Write(5, \funcionesGenerales::utf8_decode($textoSoportes));
    }

    if ($nroVolante != '') {
        $y = $pdf->GetY();
        $y = $y + 1;
        $pdf->SetFont('Helvetica', 'B', 6);
        $pdf->SetXY(15, $y);
        $pdf->Write(4, \funcionesGenerales::utf8_decode('Código de control del volante : ' . $nroVolante));
    }

    //
    $pdf->SetFont('Helvetica', 'B', 16);
    $pdf->RotatedText(15, 100, 'CUPON PARA EL CLIENTE', 90);
    $pdf->RotatedText(15, 211, 'CUPON PARA EL BANCO', 90);

    unlink($imagen);
    $name = $pathout . '/' . $_SESSION["generales"]["codigoempresa"] . '-volban-' . $liq["idliquidacion"] . ".pdf";
    $pdf->Output($name, "F");
    unset($pdf);
    return $name;
}

function armarPdfVolantePagoBancosValledupar($dbx, $liq, $liqdet, $valliq = 0) {
    require_once ('../components/fpdf186/fpdf.php');
    require_once ('../components/fpdf186/fpdf_code128.php');
    require_once ('generaGS1128.php');

    //
    $_SESSION["generales"]["corterenovacion"] = retornarRegistroMysqliApi($dbx, 'mreg_cortes_renovacion', "ano='" . date("Y") . "'", "corte");
    $_SESSION["generales"]["corterenovacionmesdia"] = substr($_SESSION["generales"]["corterenovacion"], 4, 4);

    if (!defined('FPDF_FONTPATH')) {
        define('FPDF_FONTPATH', '../components/fpdf186/font/');
    }
    if (!class_exists('PDFVol')) {

        // class PDFForProp extends FPDF_Protection {
        class PDFVol extends PDF_Code128 {

            var $angle = 0;

            function Rotate($angle, $x = -1, $y = -1) {
                if ($x == -1)
                    $x = $this->x;
                if ($y == -1)
                    $y = $this->y;
                if ($this->angle != 0)
                    $this->_out('Q');
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

    $pathout = '../tmp';
    $tipoimpresion = "prediligenciados";
    $pdf = new PDFVol("Portrait", "mm", "Letter");
    $pdf->AliasNbPages();

    //
    $matx = '';
    $ref = $liq["idliquidacion"];
    $nombre = $liq["nombrecliente"];
    $idex = $liq["identificacioncliente"];

    //
    if ($liq["tipotramite"] == 'matriculapnat' ||
            $liq["tipotramite"] == 'matriculacambidom' ||
            $liq["tipotramite"] == 'matriculaest' ||
            $liq["tipotramite"] == 'matriculasuc'
    ) {
        $matx = 'NUEVA MATRICULA';
        $ref = $liq["idliquidacion"];
        $nombre = $liq["nombrepnat"];
        $idex = $liq["idepnat"];
    }

    if ($liq["tipotramite"] == 'matriculapjur' ||
            $liq["tipotramite"] == 'matriculaesadl'
    ) {
        $matx = 'NUEVA MATRICULA';
        $ref = $liq["idliquidacion"];
        $nombre = $liq["nombrerepleg"];
        $idex = $liq["iderepleg"];
    }

    if ($liq["tipotramite"] == 'renovacionmatricula' ||
            $liq["tipotramite"] == 'renovacionesadl'
    ) {
        $matx = $liqdet[0]["expediente"];
        $ref = $liq["idliquidacion"];
        $nombre = $liq["nombrecliente"];
        $idex = $liq["identificacioncliente"];
    }

    // Arma el código de barras - Cuando se trate de matrículas
    if ($liq["tipotramite"] == 'matriculapnat' ||
            $liq["tipotramite"] == 'matriculacambidom' ||
            $liq["tipotramite"] == 'matriculaest' ||
            $liq["tipotramite"] == 'matriculasuc' ||
            $liq["tipotramite"] == 'matriculapjur' ||
            $liq["tipotramite"] == 'matriculaesadl') {
        $imagen = '';
        if (defined('CODIGO_BARRAS_MATRICULA')) {
            if (trim(CODIGO_BARRAS_MATRICULA) != '') {
                $imagen = '../tmp/codbarras-' . session_id() . date("Ymd") . date("His") . '.png';
                if (date("md") <= $_SESSION["generales"]["corterenovacionmesdia"]) {
                    generarGs1128(trim(CODIGO_BARRAS_MATRICULA), $_SESSION["generales"]["codigoempresa"] . sprintf("%011s", $ref), date("Y") . $_SESSION["generales"]["corterenovacionmesdia"], $imagen, $valliq);
                } else {
                    generarGs1128(trim(CODIGO_BARRAS_MATRICULA), $_SESSION["generales"]["codigoempresa"] . sprintf("%011s", $ref), date("Y") . '1231', $imagen, $valliq);
                }
            }
        }
    }

    // Arma el código de barras - Cuando se trate de renovaciones
    if ($liq["tipotramite"] == 'renovacionmatricula' ||
            $liq["tipotramite"] == 'renovacionesadl') {
        $imagen = '';
        if (defined('CODIGO_BARRAS_RENOVACION')) {
            if (trim(CODIGO_BARRAS_RENOVACION) != '') {
                $imagen = '../tmp/codbarras-' . session_id() . date("Ymd") . date("His") . '.png';
                if (date("md") <= $_SESSION["generales"]["corterenovacionmesdia"]) {
                    generarGs1128(trim(CODIGO_BARRAS_RENOVACION), $_SESSION["generales"]["codigoempresa"] . sprintf("%011s", $ref), date("Y") . $_SESSION["generales"]["corterenovacionmesdia"], $imagen, $valliq);
                } else {
                    generarGs1128(trim(CODIGO_BARRAS_RENOVACION), $_SESSION["generales"]["codigoempresa"] . sprintf("%011s", $ref), date("Y") . '1231', $imagen, $valliq);
                }
            }
        }
    }

    // Arma el código de barras - Cuando se trate de proponentes
    if ($liq["tipotramite"] == 'inscripcionproponente' ||
            $liq["tipotramite"] == 'renovacionproponente' ||
            $liq["tipotramite"] == 'actualizacionproponente' ||
            $liq["tipotramite"] == 'cambidomproponente') {
        $imagen = '';
        if (defined('CODIGO_BARRAS_PROPONENTES')) {
            if (trim(CODIGO_BARRAS_PROPONENTES) != '') {
                $imagen = '../tmp/codbarras-' . session_id() . date("Ymd") . date("His") . '.png';
                if (date("md") <= $_SESSION["generales"]["corterenovacionmesdia"]) {
                    generarGs1128(trim(CODIGO_BARRAS_PROPONENTES), $_SESSION["generales"]["codigoempresa"] . sprintf("%011s", $ref), date("Y") . $_SESSION["generales"]["corterenovacionmesdia"], $imagen, $valliq);
                } else {
                    generarGs1128(trim(CODIGO_BARRAS_PROPONENTES), $_SESSION["generales"]["codigoempresa"] . sprintf("%011s", $ref), date("Y") . '1231', $imagen, $valliq);
                }
            }
        }
    }

    // Cuando se recauda a través de Baloto o éxito.
    $codigorecaudoraiz = retornarClaveValorMysqliAp | i($dbx, '90.33.49');
    $codigorecaudo = retornarClaveValorMysqliApi($dbx, '90.33.50');

    // Imprime encabezados
    $pdf->AddPage();
    $pdf->SetMargins(15, 25, 7);

    // Parte del cupon que es para el banco
    if (file_exists('../images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.jpg')) {
        $pdf->Image('../images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.jpg', 10, 7, 18, 18);
    }
    // Primer código de barras
    if (trim($imagen) != '') {
        $pdf->Image($imagen, 130, 7, 70, 17);
    }

    // Títulos del volante - primera parte
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetXY(30, 11);
    $pdf->Write(4, "VOLANTE PARA PAGO EN BANCOS");
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetXY(30, 16);
    $pdf->Write(4, RAZONSOCIALSMS);
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetXY(30, 21);
    $pdf->Write(4, "NIT. " . NIT);

    // Imprime Cuadricula
    $pdf->Rect(25, 28, 175, 6);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(27, 29);
    $pdf->Write(5, "DATOS DEL COMERCIANTE");
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(150, 29);
    $pdf->Write(5, "REFERENCIA: " . $_SESSION["generales"]["codigoempresa"] . sprintf("%011s", $ref));
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(140, 36);
    $pdf->Write(5, "NUMERO RECUPERACION: " . $liq["numerorecuperacion"]);

    $pdf->Rect(25, 35, 175, 17);
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(27, 36);
    $pdf->Write(5, "NOMBRE O RAZON SOCIAL:");
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(80, 36);
    $pdf->Write(5, substr($nombre, 0, 30));
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(27, 41);
    $pdf->Write(5, "IDENTIFICACION:");
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(80, 41);
    $pdf->Write(5, ltrim($idex, 0));
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(27, 46);
    $pdf->Write(5, "MATRICULA/PROPONENTE:");
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(80, 46);
    $pdf->Write(5, $matx);

    $pdf->Rect(25, 53, 175, 6);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(27, 54);
    $pdf->Write(5, "LIQUIDACION DEL SERVICIO");

    $pdf->Rect(25, 60, 175, 50);
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(35, 61);
    $pdf->Write(5, "FECHA DE LA LIQUIDACION:");
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(80, 61);
    $pdf->Write(5, \funcionesGenerales::mostrarFecha(date("Ymd")));
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(35, 65);
    $pdf->Write(5, "HORA DE LA LIQUIDACION:");
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(80, 65);
    $pdf->Write(5, \funcionesGenerales::mostrarHora(date("His")));
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(35, 71);
    $pdf->Write(5, "CONCEPTOS");
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(135, 71);
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
    $y = 74;
    $total = 0;
    foreach ($relServ as $ser) {
        $nomserv = retornarRegistroMysqliApi($dbx, 'mreg_servicios', "idservicio='" . $ser["idservicio"] . "'", "nombre");
        // $nomserv = retornarNombreServicioRegistros($ser["idservicio"]);
        $y = $y + 4;
        $pdf->SetFont('Helvetica', '', 7);
        $pdf->SetXY(35, $y);
        $pdf->Write(5, $nomserv);
        $pdf->SetFont('Helvetica', '', 7);
        $pdf->SetXY(135, $y);
        $pdf->Write(5, number_format($ser["valor"], 0));
        $total = $total + $ser["valor"];
    }
    $y = $y + 4;
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(35, $y);
    $pdf->Write(5, 'TOTAL');
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(135, $y);
    $pdf->Write(5, number_format($total, 0));
    $pdf->Line(2, 113, 210, 113);


    // Segunda parte
    // Imprime Cuadricula
    // Títulos del volante - primera parte
    if (file_exists('../images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.jpg')) {
        $pdf->Image('../images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.jpg', 10, 116, 18, 18);
    }
    // Primer código de barras
    if (trim($imagen) != '') {
        $pdf->Image($imagen, 130, 116, 70, 17);
    }

    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetXY(30, 116);
    $pdf->Write(4, "VOLANTE PARA PAGO EN BANCOS");
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetXY(30, 121);
    $pdf->Write(4, RAZONSOCIALSMS);
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetXY(30, 126);
    $pdf->Write(4, "NIT. " . NIT);

    $pdf->Rect(25, 137, 175, 6);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(27, 138);
    $pdf->Write(5, "DATOS DEL COMERCIANTE");
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(150, 138);
    $pdf->Write(5, "REFERENCIA: " . $_SESSION["generales"]["codigoempresa"] . sprintf("%011s", $ref));
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(140, 145);
    $pdf->Write(5, "NUMERO RECUPERACION: " . $liq["numerorecuperacion"]);

    $pdf->Rect(25, 144, 175, 17);
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(27, 145);
    $pdf->Write(5, "NOMBRE O RAZON SOCIAL:");
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(80, 145);
    $pdf->Write(5, substr($nombre, 0, 30));
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(27, 150);
    $pdf->Write(5, "IDENTIFICACION:");
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(80, 150);
    $pdf->Write(5, ltrim($idex, 0));
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(27, 155);
    $pdf->Write(5, "MATRICULA/PROPONENTE:");
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetXY(80, 155);
    $pdf->Write(5, $matx);

    $pdf->Rect(25, 162, 175, 6);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(27, 163);
    $pdf->Write(5, "LIQUIDACION DEL SERVICIO");

    $pdf->Rect(25, 169, 175, 50);
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(35, 170);
    $pdf->Write(5, "FECHA DE LA LIQUIDACION:");
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(80, 170);
    $pdf->Write(5, \funcionesGenerales::mostrarFecha(date("Ymd")));
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(35, 175);
    $pdf->Write(5, "HORA DE LA LIQUIDACION:");
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(80, 175);
    $pdf->Write(5, \funcionesGenerales::mostrarHora(date("His")));
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(35, 180);
    $pdf->Write(5, "CONCEPTOS");
    $pdf->SetFont('Helvetica', 'B', 7);
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
        $nomserv = retornarRegistroMysqliApi($dbx, 'mreg_servicios', "idservicio='" . $ser["idservicio"] . "'", "nombre");
        // $nomserv = retornarNombreServicioRegistros($ser["idservicio"]);
        $y = $y + 4;
        $pdf->SetFont('Helvetica', '', 7);
        $pdf->SetXY(35, $y);
        $pdf->Write(5, $nomserv);
        $pdf->SetFont('Helvetica', '', 7);
        $pdf->SetXY(135, $y);
        $pdf->Write(5, number_format($ser["valor"], 0));
        $total = $total + $ser["valor"];
    }
    $y = $y + 4;
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(35, $y);
    $pdf->Write(5, 'TOTAL');
    $pdf->SetFont('Helvetica', 'B', 7);
    $pdf->SetXY(135, $y);
    $pdf->Write(5, number_format($total, 0));

    $pdf->Rect(10, 222, 190, 6);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(27, 223);
    $pdf->Write(5, "SOLO PARA PAGO EN EFECTIVO O CON CHEQUE DE GERENCIA");
    $pdf->Rect(10, 229, 90, 6);
    $pdf->Rect(101, 229, 99, 6);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(12, 230);
    $pdf->Write(5, "BANCO:");
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(103, 230);
    $pdf->Write(5, "NRO. CHEQUE:");

    /*
      if (trim($imagen)!='') {
      $pdf->Image($imagen,10,237,70,17);
      }
     */

    $pdf->Rect(10, 236, 90, 6);
    $pdf->Rect(101, 236, 99, 6);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(12, 237);
    $pdf->Write(5, "VALOR A PAGAR:");
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetXY(55, 237);
    $pdf->Write(5, "     $ " . number_format($liq["valortotal"], 0));
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetXY(102, 237);
    $pdf->Write(5, "FECHA DEL PAGO     AAAA [            ]   MM [       ] DD [     ]");

    //
    $txt = 'Para mayor comodidad pague en cualquier oficina del Banco de occidente.';

    $pdf->SetFont('Helvetica', '', 5);
    $pdf->SetXY(10, 242);
    $pdf->Write(4, $txt);

    // Imprime solamente si existe código de recaudo en claves - valor
    if (trim($codigorecaudo) != '') {
        $ix = $ix = $pdf->GetY() + 4;
        $pdf->Rect(10, $ix, 190, 6);
        $ix++;
        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->SetXY(70, $ix);
        $pdf->Write(5, "CODIGO PAGO BALOTO: " . $codigorecaudoraiz . ' ' . $codigorecaudo . ' ' . $ref);
    }

    $pdf->SetFont('Helvetica', 'B', 16);
    $pdf->RotatedText(15, 100, 'CUPON PARA EL CLIENTE', 90);
    $pdf->RotatedText(15, 211, 'CUPON PARA EL BANCO', 90);

    if (trim($imagen) != '') {
        if (file_exists($imagen)) {
            unlink($imagen);
        }
    }
    $pdf->_endpage();
    $name = $pathout . '/prediligenciados-volban-' . $liq["idliquidacion"] . ".pdf";
    $pdf->Output($name, "F");
    unset($pdf);
    return $name;
}

function armarPdfVolantePagoBaloto($dbx = null, $liq = array(), $liqdet = array(), $valliq = 0, $aleatorio = '') {
    require_once ('../components/fpdf186/fpdf.php');
    require_once ('../components/fpdf186/fpdf_code128.php');
    require_once ('generaGS1128.php');

    //
    $_SESSION["generales"]["corterenovacion"] = retornarRegistroMysqliApi($dbx, 'mreg_cortes_renovacion', "ano='" . date("Y") . "'", "corte");
    $_SESSION["generales"]["corterenovacionmesdia"] = substr($_SESSION["generales"]["corterenovacion"], 4, 4);


    if (!defined('FPDF_FONTPATH')) {
        define('FPDF_FONTPATH', '../components/fpdf186/font/');
    }
    if (!class_exists('PDFVol')) {

        // class PDFForProp extends FPDF_Protection {
        class PDFVol extends PDF_Code128 {

            var $angle = 0;

            function Rotate($angle, $x = -1, $y = -1) {
                if ($x == -1)
                    $x = $this->x;
                if ($y == -1)
                    $y = $this->y;
                if ($this->angle != 0)
                    $this->_out('Q');
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

    $pathout = '../tmp';
    $tipoimpresion = "prediligenciados";
    $pdf = new PDFVol("Portrait", "mm", "Letter");
    $pdf->AliasNbPages();

    //
    $matx = '';
    $ref = sprintf("%02s", $_SESSION["generales"]["codigoempresa"]) . sprintf("%06s", $liq["idliquidacion"]);
    $nombre = $liq["nombrecliente"];
    $idex = $liq["identificacioncliente"];

    //
    if ($liq["tipotramite"] == 'matriculapnat' ||
            $liq["tipotramite"] == 'matriculacambidom' ||
            $liq["tipotramite"] == 'matriculaest' ||
            $liq["tipotramite"] == 'matriculasuc'
    ) {
        $matx = 'NUEVA MATRICULA';
        $nombre = $liq["nombrepnat"];
        $idex = $liq["idepnat"];
    }

    if ($liq["tipotramite"] == 'matriculapjur' ||
            $liq["tipotramite"] == 'matriculaesadl'
    ) {
        $matx = 'NUEVA MATRICULA';
        $nombre = $liq["nombrerepleg"];
        $idex = $liq["iderepleg"];
    }

    if ($liq["tipotramite"] == 'renovacionmatricula' ||
            $liq["tipotramite"] == 'renovacionesadl'
    ) {
        $matx = $liqdet[0]["expediente"];
        $nombre = $liq["nombrecliente"];
        $idex = $liq["identificacioncliente"];
    }

    //
    $codigorecaudoraiz = retornarClaveValorMysqliApi($dbx, '90.33.49');
    $codigorecaudo = retornarClaveValorMysqliApi($dbx, '90.33.50');

    // Imprime encabezados
    $pdf->AddPage();
    $pdf->SetMargins(15, 25, 7);

    // Parte del cupon que es para el banco
    if (file_exists('../images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.jpg')) {
        $pdf->Image('../images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.jpg', 10, 7, 18, 18);
    }
    // $pdf->Image('../../images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.jpg', 10, 7, 18, 18);
    // Títulos del volante - primera parte
    $pdf->SetFont('Helvetica', 'B', 12);
    $pdf->SetXY(30, 11);
    $pdf->Write(4, "!!! IMPORTANTE !!!");
    $pdf->Ln();
    $pdf->SetFont('Helvetica', 'B', 12);
    $pdf->SetX(30);
    $pdf->MultiCell(170, 5, 'POR FAVOR NO DESECHE ESTE INSTRUCTIVO HASTA TANTO SU RENOVACIÓN NO SE CONFIRME.');
    $pdf->Ln();
    $pdf->Ln();

    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(30);
    $pdf->Write(4, "INSTRUCCIONES PARA PAGO A TRAVÉS DE BALOTO");
    $pdf->Ln();
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(30);
    $pdf->Write(4, RAZONSOCIALSMS);
    $pdf->Ln();
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(30);
    $pdf->Write(4, "NIT. " . NIT);
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();

    //
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(30);
    $pdf->Write(5, "Apreciado Usuario");
    $pdf->Ln();
    $pdf->Ln();

    //
    $txt = 'Por favor diríjase a la taquilla de BALOTO más cercana a su oficina o lugar de residencia e indíquele a la persona que le atiende ';
    $txt .= 'que va a pagar un servicio a nombre de la ' . RAZONSOCIAL . '. En dicho punto le solicitarán ';
    $txt .= 'el número de la referencia de pago y el valor a pagar. Para el efecto indíquele los siguientes datos:';
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(30);
    $pdf->MultiCell(170, 5, $txt);
    $pdf->Ln();

    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(30);
    $pdf->Write(5, "CODIGO PAGO BALOTO: " . $codigorecaudoraiz . ' ' . $codigorecaudo . ' ' . $ref);
    $pdf->Ln();
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(30);
    $pdf->Write(5, "VALOR A PAGAR : $" . number_format($valliq, 0)) . '.oo';
    $pdf->Ln();
    $pdf->Ln();
    $pdf->SetFont('Helvetica', 'B', 12);
    $pdf->SetX(30);
    $pdf->Write(5, "*** IMPORTANTE *** PARA ACTIVAR EL PAGO DE SU RENOVACION ***");
    $pdf->Ln();
    $pdf->Ln();

    //
    $urlx = TIPO_HTTP . HTTP_HOST;
    $urlx = str_replace(array(":80", ":443"), "", $urlx);
    $txt = 'Al día siguiente a la realización de su pago ingrese al sitio ' . $urlx . '/cpb.php e indique allí los siguientes ';
    $txt .= 'datos: ';
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(30);
    $pdf->MultiCell(170, 5, $txt);
    $pdf->Ln();


    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(30);
    $pdf->Write(5, "1.- Indique el número de este volante : " . $aleatorio);
    $pdf->Ln();
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(30);
    $pdf->Write(5, "2.- Indique el número del tiquete de pago.");
    $pdf->Ln();

    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(30);
    $pdf->Write(5, "3.- El valor pagado");
    $pdf->Ln();
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(30);
    $pdf->Write(5, "4.- Indique el correo electrónico al cual se enviarán los soportes de la transacción");
    $pdf->Ln();
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(30);
    $pdf->Write(5, "5.- Número telefónico para comunicarnos en caso de inconvenientes.");
    $pdf->Ln();

    $pdf->Ln();

    //
    $txt = 'El sistema le solicitará aceptar los términos de uso del servicio y le enviará un mensaje de datos al correo electrónico reportado con los soportes ';
    $txt .= 'del pago realizado. Esta confirmación se constituye en el soporte legal a través del cual usted acepta que la información digitada en los ';
    $txt .= 'formularios que realizó virtualmente es veraz y que está de acuerdo con ella, y sustituye de esta forma la presentación en físico de los mismos.';
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(30);
    $pdf->MultiCell(170, 5, $txt);
    $pdf->Ln();

    //
    $txt = 'Recibidos los soportes del pago, su renovación quedará sentada en el Registro Mercantil que administra la Cámara de Comercio. ';
    $txt .= 'A partir de este momento podrá solicitar los certificados que usted requiera.';
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(30);
    $pdf->MultiCell(170, 5, $txt);
    $pdf->Ln();

    $txt = 'Por favor tener en cuenta que el valor máximo de la transacción que puede ser recibido por los corresponsales bancarios es: ';
    $pdf->SetFont('Helvetica', 'B', 12);
    $pdf->SetX(30);
    $pdf->MultiCell(170, 5, $txt);
    $pdf->Ln();
    $pdf->Ln();
    $pdf->SetFont('Helvetica', 'B', 12);
    $pdf->SetX(30);
    $pdf->Write(5, "EN PUNTOS BALOTO HASTA $400,000.oo");
    $pdf->Ln();
    $pdf->Ln();
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(30);
    $pdf->Write(5, "Cordialmente,");
    $pdf->Ln();
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(30);
    $pdf->Write(5, "Dirección de Registros Públicos");
    $pdf->Ln();
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(30);
    $pdf->Write(5, "Cámara de Comercio.");

    $pdf->_endpage();
    $name = $pathout . '/' . $_SESSION["generales"]["codigoempresa"] . '-volbaloto-' . $aleatorio . ".pdf";
    $pdf->Output($name, "F");
    unset($pdf);
    return $name;
    exit();
}

function armarPdfVolantePagoExito($dbx = null, $liq = array(), $liqdet = array(), $valliq = 0, $aleatorio = '') {
    require_once ('../components/fpdf186/fpdf.php');
    require_once ('../components/fpdf186/fpdf_code128.php');
    require_once ('generaGS1128.php');

    //
    $_SESSION["generales"]["corterenovacion"] = retornarRegistroMysqliApi($dbx, 'mreg_cortes_renovacion', "ano='" . date("Y") . "'", "corte");
    $_SESSION["generales"]["corterenovacionmesdia"] = substr($_SESSION["generales"]["corterenovacion"], 4, 4);

    if (!defined('FPDF_FONTPATH')) {
        define('FPDF_FONTPATH', '../components/fpdf186/font/');
    }
    if (!class_exists('PDFVolE')) {

        // class PDFForProp extends FPDF_Protection {
        class PDFVolE extends PDF_Code128 {

            var $angle = 0;

            function Rotate($angle, $x = -1, $y = -1) {
                if ($x == -1)
                    $x = $this->x;
                if ($y == -1)
                    $y = $this->y;
                if ($this->angle != 0)
                    $this->_out('Q');
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

    $pathout = '../tmp';
    $tipoimpresion = "prediligenciados";
    $pdf = new PDFVolE("Portrait", "mm", "Letter");
    $pdf->AliasNbPages();

    //
    $matx = '';
    $ref = sprintf("%02s", $_SESSION["generales"]["codigoempresa"]) . sprintf("%06s", $liq["idliquidacion"]);
    $nombre = $liq["nombrecliente"];
    $idex = $liq["identificacioncliente"];

    //
    if ($liq["tipotramite"] == 'matriculapnat' ||
            $liq["tipotramite"] == 'matriculacambidom' ||
            $liq["tipotramite"] == 'matriculaest' ||
            $liq["tipotramite"] == 'matriculasuc') {
        $matx = 'NUEVA MATRICULA';
        $nombre = $liq["nombrepnat"];
        $idex = $liq["idepnat"];
    }

    if ($liq["tipotramite"] == 'matriculapjur' ||
            $liq["tipotramite"] == 'matriculaesadl') {
        $matx = 'NUEVA MATRICULA';
        $nombre = $liq["nombrerepleg"];
        $idex = $liq["iderepleg"];
    }

    if ($liq["tipotramite"] == 'renovacionmatricula' ||
            $liq["tipotramite"] == 'renovacionesadl') {
        $matx = $liqdet[0]["expediente"];
        $nombre = $liq["nombrecliente"];
        $idex = $liq["identificacioncliente"];
    }

    //
    $codigorecaudoraiz = retornarClaveValorMysqliApi($dbx, '90.33.49');
    $codigorecaudo = retornarClaveValorMysqliApi($dbx, '90.33.50');

    // Imprime encabezados
    $pdf->AddPage();
    $pdf->SetMargins(15, 25, 7);

    // Parte del cupon que es para el banco
    if (file_exists('../images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.jpg')) {
        $pdf->Image('../images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.jpg', 10, 7, 18, 18);
    }
    // $pdf->Image('../../images/logocamara' . $_SESSION["generales"]["codigoempresa"] . '.jpg', 10, 7, 18, 18);
    // Títulos del volante - primera parte
    $pdf->SetFont('Helvetica', 'B', 12);
    $pdf->SetXY(30, 11);
    $pdf->Write(4, "!!! IMPORTANTE !!!");
    $pdf->Ln();
    $pdf->SetFont('Helvetica', 'B', 12);
    $pdf->SetX(30);
    $pdf->MultiCell(170, 5, 'POR FAVOR NO DESECHE ESTE INSTRUCTIVO HASTA TANTO SU RENOVACIÓN NO SE CONFIRME.');
    $pdf->Ln();
    $pdf->Ln();

    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(30);
    $pdf->Write(4, "INSTRUCCIONES PARA PAGO A TRAVÉS DE ALMACENES EXITO");
    $pdf->Ln();
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(30);
    $pdf->Write(4, RAZONSOCIAL);
    $pdf->Ln();
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(30);
    $pdf->Write(4, "NIT. " . NIT);
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();

    //
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(30);
    $pdf->Write(5, "Apreciado Usuario");
    $pdf->Ln();
    $pdf->Ln();

    //
    $txt = 'Por favor diríjase al Almacen Éxito más cercano a su oficina o lugar de residencia e indíquele a la persona que le atiende ';
    $txt .= 'que va a pagar un servicio a nombre de la ' . RAZONSOCIAL . '. La persona le solicitará el volante de pago.';
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(30);
    $pdf->MultiCell(170, 5, $txt);
    $pdf->Ln();
    $pdf->Ln();
    $pdf->SetFont('Helvetica', 'B', 12);
    $pdf->SetX(30);
    $pdf->Write(5, "*** IMPORTANTE *** PARA ACTIVAR EL PAGO DE SU RENOVACION ***");
    $pdf->Ln();
    $pdf->Ln();

    //
    $urlx = TIPO_HTTP . HTTP_HOST;
    $urlx = str_replace(array(":80", ":443"), "", $urlx);
    $txt = 'Al día siguiente a la realización de su pago ingrese al sitio ' . $urlx . '/cpb.php e indique allí los siguientes ';
    $txt .= 'datos: ';
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(30);
    $pdf->MultiCell(170, 5, $txt);
    $pdf->Ln();


    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(30);
    $pdf->Write(5, "1.- Indique el número de este volante : " . $aleatorio);
    $pdf->Ln();
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(30);
    $pdf->Write(5, "2.- Indique el número del tiquete de pago.");
    $pdf->Ln();

    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(30);
    $pdf->Write(5, "3.- El valor pagado");
    $pdf->Ln();
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(30);
    $pdf->Write(5, "4.- Indique el correo electrónico al cual se enviarán los soportes de la transacción");
    $pdf->Ln();
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(30);
    $pdf->Write(5, "5.- Número telefónico para comunicarnos en caso de inconvenientes.");
    $pdf->Ln();

    $pdf->Ln();

    //
    $txt = 'El sistema le solicitará aceptar los términos de uso del servicio y le enviará un mensaje de datos al correo electrónico reportado con los soportes ';
    $txt .= 'del pago realizado. Esta confirmación se constituye en el soporte legal a través del cual usted acepta que la información digitada en los ';
    $txt .= 'formularios que realizó virtualmente es veraz y que está de acuerdo con ella, y sustituye de esta forma la presentación en físico de los mismos.';
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(30);
    $pdf->MultiCell(170, 5, $txt);
    $pdf->Ln();

    //
    $txt = 'Recibidos los soportes del pago, su renovación quedará sentada en el Registro Mercantil que administra la Cámara de Comercio. ';
    $txt .= 'A partir de este momento podrá solicitar los certificados que usted requiera.';
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(30);
    $pdf->MultiCell(170, 5, $txt);
    $pdf->Ln();

    $txt = 'Por favor tener en cuenta que el valor máximo de la transacción que puede ser recibido por los corresponsales bancarios es: ';
    $pdf->SetFont('Helvetica', 'B', 12);
    $pdf->SetX(30);
    $pdf->MultiCell(170, 5, $txt);
    $pdf->Ln();
    $pdf->Ln();
    $pdf->SetFont('Helvetica', 'B', 12);
    $pdf->SetX(30);
    $pdf->Write(5, "EN ALMACENES EXITO HASTA $10,000,000.oo");
    $pdf->Ln();
    $pdf->Ln();
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(30);
    $pdf->Write(5, "Cordialmente,");
    $pdf->Ln();
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(30);
    $pdf->Write(5, "Dirección de Registros Públicos");
    $pdf->Ln();
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(30);
    $pdf->Write(5, "Cámara de Comercio.");

    $pdf->_endpage();
    $name = $pathout . '/' . $_SESSION["generales"]["codigoempresa"] . '-volexito-' . $aleatorio . ".pdf";
    $pdf->Output($name, "F");
    unset($pdf);
    return $name;
}

?>