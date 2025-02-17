<?php


function armarPdfDevolucion($dbx, $liq, $trans, $dev, $exp, $fabo, $abg, $qr) {
    require_once ('../configuracion/generales.php');
    require_once ('../configuracion/parametros' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ('../components/tcpdf_6.7.5/tcpdf.php');
    require_once ('../components/tcpdf_6.7.5/examples/lang/eng.php');
    
    if (!class_exists('PDFDevolution')) {

        class PDFDevolution extends TCPDF {

            public $banner = '';
            public $tit1 = '';
            public $tit2 = '';
            public $tit3 = '';
            public $tit4 = '';
            public $idliquidacion = '';
            public $pagina = 0;

            /* Funcion para rotar un txto */

            public function Rotate($angle, $x = -1, $y = -1) {
                if ($x == - 1)
                    $x = $this->x;
                if ($y == - 1)
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
                    $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm', $c, $s, - $s, $c, $cx, $cy, - $cx, - $cy));
                }
            }

            /* Funcion que imprime texto rotado */

            public function RotatedText($x, $y, $txt, $angle = 0) {
                $this->Rotate($angle, $x, $y);
                $this->Text($x, $y, $txt);
                $this->Rotate(0);
            }

            public function Header() {
                
                $bMargin = $this->getBreakMargin();
                $auto_page_break = $this->AutoPageBreak;
                $this->SetAutoPageBreak(false, 0);
                $this->Image($this->banner, 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 0);
                $this->SetAutoPageBreak($auto_page_break, $bMargin);
                $this->setPageMark();
                
                $this->SetMargins(20, 65, 7);
                $this->SetTextColor(0, 0, 0);
                $this->SetXY(20, 40);

                //
                $this->SetFontSize(9);
                $this->SetTextColor(139, 0, 0);
                $this->writeHTML('<strong>' . $this->tit1 . '</strong>', true, false, true, false, 'C');
                $this->writeHTML('<strong>' . $this->tit2 . '</strong>', true, false, true, false, 'C');
                $this->writeHTML('<strong>' . $this->tit3 . '</strong>', true, false, true, false, 'C');
                $this->writeHTML('<strong>' . $this->tit4 . '</strong>', true, false, true, false, 'C');
                $this->Ln();
                $this->Ln();
                $this->writeHTML('<strong>  Presentación No. ' . $this->idliquidacion . '</strong>', true, false, true, false, 'C');
                $this->Ln();
                $this->SetTextColor(0, 0, 0);                
            }

            public function Footer() {
                $this->SetY(-10);
                $this->Cell(0, 10, 'Página ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
                $this->pagina++;
            }

        }

    }
    
    $pdf = new PDFDevolution(PDF_PAGE_ORIENTATION, PDF_UNIT, 'LETTER', true, 'UTF-8', false, true);
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    $pdf->SetMargins(20, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetAutoPageBreak(true, 15);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    $pdf->setLanguageArray($l);
    $pdf->SetFont('dejavusans', '', 9);

    //
    $pdf->banner = $_SESSION["generales"]["pathabsoluto"] . '/' . BANNER_EMPRESA;
    $pdf->tit1 = 'CÁMARA DE COMERCIO E INDUSTRIA DE TEGUCIGALPA';
    $pdf->tit2 = 'CENTRO ASOCIADO';
    $pdf->tit3 = 'REGISTRO MERCANTIL DE FRANCISCO MORAZÁN';
    $pdf->tit4 = 'RESOLUCIÓN DENEGATORIA DE INSCRIPCIÓN';
    $pdf->idliquidacion = $liq["idliquidacion"];

    //
    $pdf->AddPage();
        
    if ($dev["fechadev"] == '') {
        $dev["fechadev"] = date ("Ymd");
    }
    $tDev = retornarRegistroTablasMysqliApi($dbx, 'tipodevolucion', $dev["tipodev"]);
    $cuerpo = '<strong>El Registro Mercantil de Francisco Morazán de la ciudad de Tegucigalpa a los ' . \funcionesGenerales::mostrarFechaLetras1($dev["fechadev"]) . '.</strong>';
    $cuerpo .= '<br><br>';
    $cuerpo .= '<strong>Resuelve denegar en forma ' . str_replace("<p><br></p>","",$tDev["campo1"]) . '</strong>';
    $cuerpo .= '<br>';
    $cuerpo .= '<hr>';
    $cuerpo .= '<br>';
    $cuerpo .= '<strong>En virtud de : </strong><br>';
    $cuerpo .= str_replace (array("<strong>","</strong>"),"",$dev["observacionesdev"]);
    $cuerpo .= '<br>';
    $cuerpo .= '<hr>';
    $cuerpo .= '<br>';
    $cuerpo .= '<strong>Adicionalmente a las razones y fundamentos expuestos, se basa la presente en los artículos 402, 403 y demás aplicables del Código del Comercio</strong>';
    $linea = $pdf->GetY() + 10;
    $pdf->SetXY(20, $linea);
    $pdf->writeHTML('<strong>' . $cuerpo . '</strong>', true, false, true, false, 'J');
    $pdf->Ln();
           
    $lineaqr = $pdf->GetY();
    if ($fabo != '') {
        $linea = $pdf->GetY();
        $pdf->SetXY(20, $linea);
        $pdf->Image($fabo, 60, $linea, 30, 30, 'png');
        $linea = $pdf->GetY();
    } else {
        $linea = $pdf->GetY();
    }

    //
    $linea = $linea + 30;
    
    /*
    $pdf->SetFont('dejavusans', '', 9);
    $pdf->SetXY(20, $linea);
    $pdf->Cell(120, 4, \funcionesGenerales::utf8_decode($abg), 0, 0, 'L');
    $linea = $linea + 4;
    */
    $pdf->SetFont('dejavusans', 'B', 9);
    $pdf->SetXY(20, $linea);
    // $pdf->Cell(120, 4, \funcionesGenerales::utf8_decode('FIRMA DEL REGISTRADOR(A)'), 0, 0, 'L');
    $pdf->Cell(120, 4, \funcionesGenerales::utf8_decode($abg), 0, 0, 'L');
    $lineafinal = $pdf->GetY() + 10;

    //
    $pdf->SetY($lineaqr);
    $pdf->Image($qr, 150, $lineaqr, 30, 30, 'png');
     
    //
    $cuerpo = '<strong>NOTA:</strong>';
    $cuerpo .= ' Todo lo enmendado, adicionado, apostillado o entrelineado deberá realizarse con el mismo medio utilizado en la elaboración ';
    $cuerpo .= 'del instrumento y salvarse al final del mismo. Artículo 15 Párrafo segundo, Código del Notariado.';
    $pdf->SetXY(20, $lineafinal);
    $pdf->writeHTML('<strong>' . $cuerpo . '</strong>', true, false, true, false, 'J');
    $pdf->Ln();
    
    //
    $name = session_id() . '-Devolucion-' . date("Ymd") . '-' . date("His") . '.pdf';
    $pdf->Output($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name,"F");
    return $name;
}

?>