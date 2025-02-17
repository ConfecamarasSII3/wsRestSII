<?php

function armarPdfProofOfRegistration($dbx, $tipoimpresion = '', $liq = array(), $regs = array(), $regssucesivas = array(), $faboCert, $abgCert, $qr) {
    require_once ('../configuracion/generales.php');
    require_once ('../configuracion/parametros' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ('../components/tcpdf_6.7.5/tcpdf.php');
    require_once ('../components/tcpdf_6.7.5/examples/lang/eng.php');


    if (!class_exists('PDFCerti')) {

        class PDFCerti extends TCPDF {

            public $banner = '';
            public $tit1 = '';
            public $tit2 = '';
            public $tit3 = '';
            public $tit4 = '';
            public $pagina = 0;
            public $presentacion = 0;
            public $tipoimpresion = '';

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

                $this->SetMargins(20, 50, 7);
                $this->SetTextColor(0, 0, 0);
                $this->SetXY(20, 40);

                //
                $this->SetFontSize(9);
                $this->SetTextColor(139, 0, 0);
                $this->writeHTML('<strong>' . $this->tit1 . '</strong>', true, false, true, false, 'C');
                // $this->writeHTML('<strong>' . $this->tit2 . '</strong>', true, false, true, false, 'C');
                // $this->writeHTML('<strong>' . $this->tit3 . '</strong>', true, false, true, false, 'C');
                // $this->writeHTML('<strong>' . $this->tit4 . '</strong>', true, false, true, false, 'C');
                $this->SetTextColor(0, 0, 0);

                $this->SetFontSize(25);
                $this->SetTextColor(255, 192, 203);
                // $this->RotatedText(20, 230, 'CAMARA DE COMERCIO E INDUSTRIA DE TEGUCIGALPA', 45);
                /*
                  if ($this->tipoimpresion == 'consulta') {
                  $this->RotatedText(55, 195, 'CONSULTA SIN VALIDEZ LEGAL', 45);
                  } else {
                  // $this->RotatedText(70, 200, 'PRESENTACION NO. ' . $this->presentacion, 45);
                  if ($this->tipoimpresion == 'preparacion' || $this->tipoimpresion == 'view') {
                  $this->RotatedText(55, 195, 'CERTIFICADO DE MUESTRA', 45);
                  }
                  }
                 */

                $this->SetTextColor(139, 0, 0);
                $this->SetFontSize(9);
                $this->setXY(20, 60);
            }

            public function Footer() {
                $this->SetY(-10);
                $this->Cell(0, 10, 'Página ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
                $this->pagina++;
            }

        }

    }

    $pdf = new PDFCerti(PDF_PAGE_ORIENTATION, PDF_UNIT, 'LETTER', true, 'UTF-8', false, true);
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetAutoPageBreak(true, 15);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    $pdf->setLanguageArray($l);
    $pdf->SetFont('dejavusans', '', 9);

    //
    $pdf->banner = $_SESSION["generales"]["pathabsoluto"] . '/' . BANNER_EMPRESA;
    $pdf->tit1 = 'CONSTANCIA DE INSCRIPCIÓN';
    $pdf->presentacion = $liq["idliquidacion"];
    $pdf->tipoimpresion = $tipoimpresion;

    //
    $pdf->AddPage();

    //
    if (empty($regssucesivas)) {
        $cuerpo = str_replace("<p><br></p>", "", \funcionesGenerales::retornarPantallaPredisenada($dbx, 'form.certificate.proof.of.registration'));
    } else {
        $cuerpo = str_replace("<p><br></p>", "", \funcionesGenerales::retornarPantallaPredisenada($dbx, 'form.certificate.proof.of.registration.officer'));
    }

    //
    $register = '';
    $tomo = '';
    $dateregistration = '';
    $book = '';
    $idup = 0;
    $txt = '';
    foreach ($regs as $r) {
        if ($r["libroinscripciones"] <= 'RM08') {
            $idup++;
            if ($idup > 1) {
                $txt .= '<hr>';
            } else {
                $txt .= '<br>';
                $register = $r["registroinscripciones"];
                $tomo = $r["tomoinscripciones"];
                $dateregistration = $r["fecharegistroinscripciones"];
                $book = $r["libroinscripciones"];
            }
            if ($r["matriculainscripciones"] != '') {
                $txt .= '<strong>Matrícula:</strong> ' . $r["matriculainscripciones"] . '<br>';
            }
            $txt .= '<strong>Razón social o denominación social:</strong> ' . $r["nombreinscripciones"] . '<br>';
            $txt .= '<strong>Tipo de documento :</strong> ' . $r["idtipodocinscripciones"] . ' ' . retornarRegistroTablasMysqliApi($dbx, 'tipodocreg', $r["idtipodocinscripciones"], "descripcion") . '<br>';
            if ($r["numdocinscripciones"] == '' || $r["numdocinscripciones"] == 'N/A') {
                $txt .= '<strong>Número de documento :</strong> Sin número asignado<br>';
            } else {
                $txt .= '<strong>Número de documento :</strong> ' . $r["numdocinscripciones"] . '<br>';
            }
            $txt .= '<strong>Fecha de documento :</strong> ' . \funcionesGenerales::mostrarFecha2($r["fecdocinscripciones"]) . '<br>';
            if ($r["idorigendocinscripciones"] != '') {
                $txt .= '<strong>Origen de documento :</strong> ' . $r["idorigendocinscripciones"] . ' ' . retornarRegistroTablasMysqliApi($dbx, 'origenes', $r["idorigendocinscripciones"], "descripcion") . '<br>';
            }
            $txt .= '<strong>Municipio de documento :</strong> ' . $r["mundocinscripciones"] . ' ' . retornarRegistroTablasMysqliApi($dbx, 'municipios', $r["mundocinscripciones"], "descripcion") . '<br>';
            $txt .= '<strong>Fecha de inscripción en el Registro :</strong> ' . \funcionesGenerales::mostrarFecha2($r["fecharegistroinscripciones"]) . '<br>';
            $txt .= '<strong>Número de inscripción :</strong> ' . $r["libroinscripciones"] . ' - ' . $r["tomoinscripciones"] . ' - ' . $r["registroinscripciones"] . '<br>';
            $txt .= '<strong>Acto inscrito :</strong> ' . $r["noticiainscripciones"] . '<br>';
        }
    }

    if (!empty($regssucesivas)) {
        $txt .= '<br>Así mismo, constan las siguientes anotaciones marginales:<br><br>';
        $i = 0;
        foreach ($regssucesivas as $rs) {
            $i++;
            // $txt .= \funcionesGenerales::armarLiteral ($i) . '. ';
            $txt .= $i . '. ';
            $txt .= 'Bajo el No. ' . $rs["registroinscripciones"] . ' ';
            if ($rs["tomoinscripciones"] != '999') {
                $txt .= 'del tomo ' . $rs["tomoinscripciones"] . ' ';
            }
            $txt .= 'del libro ' . retornarRegistroTablasMysqliApi($dbx, 'libros', $rs["libroinscripciones"], "descripcion") . ' ';
            $txt .= 'a los ' . \funcionesGenerales::mostrarFechaLetras1($rs["fecharegistroinscripciones"]) . ', ';
            $txt .= 'se inscribió : ' . retornarRegistroMysqliApi($dbx, 'maestroactos', "idlibro='" . $rs["libroinscripciones"] . "' and idacto='" . $rs["actoinscripciones"] . "'", "nombre");
            $txt .= '<br><br>';
        }
    }

    //
    $datoscliente = '';
    $datoscliente .= ('<strong>Nombre o razón social del solicitante:</strong> ' . $liq["nombrecompletopagadorliq"]);
    $datoscliente .= '<br>';
    $datoscliente .= ('<strong>Presentación No. :</strong> ' . $liq["idliquidacion"]) . '<br>';
    $datoscliente .= ('<strong>Fecha de la presentación :</strong> ' . \funcionesGenerales::mostrarFecha2($liq["fechaliq"]));

    //
    //
    $cuerpo = str_replace("[CERTIFICA]", '<p align="center"><strong>CERTIFICA</strong></p>', $cuerpo);
    $cuerpo = str_replace("[DATACLIENT]", $datoscliente, $cuerpo);
    $cuerpo = str_replace("[IDSOLICITUD]", $liq["idliquidacion"], $cuerpo);
    $cuerpo = str_replace("[DATEPRESENTATION]", \funcionesGenerales::mostrarFecha2($liq["fechareciboliq"]), $cuerpo);
    $cuerpo = str_replace("[DATEREGISTRATION]", \funcionesGenerales::mostrarFecha2($dateregistration), $cuerpo);
    $cuerpo = str_replace("[REGISTERNUMBER]", $register, $cuerpo);
    $cuerpo = str_replace("[BOOKTOMO]", $tomo, $cuerpo);
    $cuerpo = str_replace("[NUMOFICIO]", $liq["transacciones"][1]["numerooficiotra"], $cuerpo);
    $cuerpo = str_replace("[ORIGENPRESENTACION]", $liq["transacciones"][1]["entidadoficiotra"], $cuerpo);
    $cuerpo = str_replace("[FECHAOFICIO]", \funcionesGenerales::mostrarFecha2($liq["transacciones"][1]["fechaoficiotra"]), $cuerpo);
    $cuerpo = str_replace("[BOOKDESCRIPTION]", retornarRegistroTablasMysqliApi($dbx, 'libros', $book, "descripcion"), $cuerpo);
    $cuerpo = str_replace("[CERTIFICATIONTEXT]", $txt, $cuerpo);


    //
    $cuerpo = str_replace("[DATEEXPEDITION]", \funcionesGenerales::mostrarFechaLetras1(date("Ymd")), $cuerpo);


    $pdf->writeHTML($cuerpo, true, false, true, false, 'J');
    $pdf->Ln();

    if ($faboCert != '') {
        $linea = $pdf->GetY() + 1;
        $pdf->SetXY(20, $linea);
        $pdf->Image($faboCert, 85, $linea, 35, 0, 'png');
    } else {
        $linea = $pdf->GetY() + 1;
    }

    //
    $linea = $linea + 30;
    $pdf->SetFont('dejavusans', 'B', 10);
    $pdf->SetXY(20, $linea);
    $pdf->Cell(170, 4, \funcionesGenerales::utf8_decode('FIRMA DEL REGISTRADOR(A)'), 0, 0, 'C');

    $linea = $linea + 4;
    $pdf->SetXY(20, $linea);
    $pdf->Image($qr, 85, $linea, 40, 40, 'png');

    //
    //
    $name = session_id() . '-CertificateProofOfRegistration-' . date("Ymd") . '-' . date("His") . '.pdf';
    $pdf->Output($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name, "F");
    return $name;
}

/**
 * 
 * @param type $dbx
 * @param type $tipoimpresion
 * @param type $liq
 * @param type $regs
 * @param type $faboCert
 * @param type $abgCert
 * @param type $qr
 * @return string
 */
function armarPdfFullCopy($dbx, $tipoimpresion = '', $liq = array(), $regs = array(), $faboCert, $abgCert, $qr) {
    require_once ('../configuracion/generales.php');
    require_once ('../configuracion/parametros' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ('../components/tcpdf_6.7.5/tcpdf.php');
    require_once ('../components/tcpdf_6.7.5/examples/lang/eng.php');


    if (!class_exists('PDFCerti')) {

        class PDFCerti extends TCPDF {

            public $banner = '';
            public $tit1 = '';
            public $tit2 = '';
            public $tit3 = '';
            public $tit4 = '';
            public $pagina = 0;
            public $presentacion = 0;
            public $tipoimpresion = '';

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

                $this->SetMargins(20, 50, 7);
                $this->SetTextColor(0, 0, 0);
                $this->SetXY(20, 40);

                //
                $this->SetFontSize(9);
                $this->SetTextColor(139, 0, 0);
                $this->writeHTML('<strong>' . $this->tit1 . '</strong>', true, false, true, false, 'C');
                //$this->writeHTML('<strong>' . $this->tit2 . '</strong>', true, false, true, false, 'C');
                // $this->writeHTML('<strong>' . $this->tit3 . '</strong>', true, false, true, false, 'C');
                // $this->writeHTML('<strong>' . $this->tit4 . '</strong>', true, false, true, false, 'C');
                $this->SetTextColor(0, 0, 0);

                $this->SetFontSize(25);
                $this->SetTextColor(255, 192, 203);
                $this->SetTextColor(139, 0, 0);
                $this->SetFontSize(9);
                $this->setXY(20, 60);
            }

            public function Footer() {
                $this->SetY(-10);
                $this->Cell(0, 10, 'Página ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
                $this->pagina++;
            }

        }

    }

    $pdf = new PDFCerti(PDF_PAGE_ORIENTATION, PDF_UNIT, 'LETTER', true, 'UTF-8', false, true);
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetAutoPageBreak(true, 15);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    $pdf->setLanguageArray($l);
    $pdf->SetFont('dejavusans', '', 9);

    //
    $pdf->banner = $_SESSION["generales"]["pathabsoluto"] . '/' . BANNER_EMPRESA;
    $pdf->tit1 = 'COPIA INTEGRA DE INSCRIPCION';
    $pdf->presentacion = $liq["idliquidacion"];
    $pdf->tipoimpresion = $tipoimpresion;

    //
    $pdf->AddPage();

    //
    $cuerpo = str_replace("<p><br></p>", "", \funcionesGenerales::retornarPantallaPredisenada($dbx, 'form.certificate.full.copy.without.enrollment'));

    //
    $enrollment = '';
    $register = '';
    $tomo = '';
    $dateregistration = '';
    $book = '';
    $businessname = '';
    $idup = 0;
    $txt = '';
    foreach ($regs as $r) {
        if ($r["libroinscripciones"] <= 'RM08') {
            $idup++;
            if ($idup > 1) {
                $txt .= '<hr>';
            } else {
                $register = $r["registroinscripciones"];
                $tomo = $r["tomoinscripciones"];
                $dateregistration = $r["fecharegistroinscripciones"];
                $book = $r["libroinscripciones"];
                $enrollment = $r["matriculainscripciones"];
                $businessname = $r["nombreinscripciones"];
                if ($r["nombrecomercialinscripciones"] != '') {
                    $businessname .= ' / ' . $r["nombrecomercialinscripciones"];
                }
                $act = $r["actoinscripciones"];
            }
            if ($r["matriculainscripciones"] != '') {
                $txt .= '<strong>Matrícula:</strong> ' . $r["matriculainscripciones"] . '<br>';
            }
            $txt .= '<strong>Razón social o nombre:</strong> ' . $r["nombreinscripciones"] . '<br>';
            $txt .= '<strong>Tipo de documento :</strong> ' . $r["idtipodocinscripciones"] . ' ' . retornarRegistroTablasMysqliApi($dbx, 'tipodocreg', $r["idtipodocinscripciones"], "descripcion") . '<br>';
            if ($r["numdoinscripciones"] == '' || $r["numdoinscripciones"] == 'N/A') {
                $txt .= '<strong>Número de documento :</strong> Sin número asignado<br>';
            } else {
                $txt .= '<strong>Número de documento :</strong> ' . $r["numdoinscripciones"] . '<br>';
            }
            $txt .= '<strong>Fecha de documento :</strong> ' . \funcionesGenerales::mostrarFecha2($r["fecdocinscripciones"]) . '<br>';
            if ($r["idorigendocinscripciones"] != '') {
                $txt .= '<strong>Origen de documento :</strong> ' . $r["idorigendocinscripciones"] . ' ' . retornarRegistroTablasMysqliApi($dbx, 'origenes', $r["idorigendocinscripciones"], "descripcion") . '<br>';
            }
            $txt .= '<strong>Municipio de documento :</strong> ' . $r["mundocinscripciones"] . ' ' . retornarRegistroTablasMysqliApi($dbx, 'municipios', $r["mundocinscripciones"], "descripcion") . '<br>';
            $txt .= '<strong>Acto inscrito :</strong> ' . $r["noticiainscripciones"] . '<br>';
        }
    }

    //
    $datoscliente = '';
    if ($liq["razonsocialliq"] != '') {
        $datoscliente .= ('<strong>Razón social o nombre:</strong> ' . $liq["nombrecompletopagadorliq"]);
    }
    $datoscliente .= '<br>';
    $datoscliente .= ('<strong>Presentación No. :</strong> ' . $liq["idliquidacion"]) . '<br>';
    $datoscliente .= ('<strong>Fecha de la presentación :</strong> ' . \funcionesGenerales::mostrarFecha2($liq["fechaliq"]));

    //
    $ttomo = '';
    if ($tomo != '999') {
        $ttomo = 'del tomo ' . $tomo;
    }

    //
    $cuerpo = str_replace("[CERTIFICA]", '<p align="center"><strong>CERTIFICA</strong></p>', $cuerpo);
    $cuerpo = str_replace("[DATACLIENT]", $datoscliente, $cuerpo);
    $cuerpo = str_replace("[IDSOLICITUD]", $liq["idliquidacion"], $cuerpo);
    $cuerpo = str_replace("[DATEREGISTRATION]", \funcionesGenerales::mostrarFecha2($dateregistration), $cuerpo);
    $cuerpo = str_replace("[REGISTERNUMBER]", $register, $cuerpo);
    $cuerpo = str_replace("[BOOKTOMO]", $ttomo, $cuerpo);
    $cuerpo = str_replace("[BOOKDESCRIPTION]", retornarRegistroTablasMysqliApi($dbx, 'libros', $book, "descripcion"), $cuerpo);
    $cuerpo = str_replace("[ENROLLMENTNUMBER]", $enrollment, $cuerpo);
    $cuerpo = str_replace("[BUSINESSNAME]", $businessname, $cuerpo);

    //
    $cuerpo = str_replace("[DATEEXPEDITION]", \funcionesGenerales::mostrarFechaLetras1(date("Ymd")), $cuerpo);


    $pdf->writeHTML($cuerpo, true, false, true, false, 'J');
    $pdf->Ln();

    if ($faboCert != '') {
        $linea = $pdf->GetY() + 1;
        $pdf->SetXY(20, $linea);
        $pdf->Image($faboCert, 85, $linea, 35, 0, 'png');
    } else {
        $linea = $pdf->GetY() + 1;
    }

    //
    $linea = $linea + 30;
    $pdf->SetFont('dejavusans', 'B', 10);
    $pdf->SetXY(20, $linea);
    $pdf->Cell(170, 4, \funcionesGenerales::utf8_decode('FIRMA DEL REGISTRADOR(A)'), 0, 0, 'C');

    $linea = $linea + 5;
    $pdf->SetXY(20, $linea);
    $pdf->Image($qr, 85, $linea, 45, 45, 'png');

    if ($act == '0040' || $act == '0240' || $act == '5500') {
        $inscs = retornarRegistrosMysqliApi($dbx, 'inscripciones', "matriculainscripciones='" . $enrollment . "'", "fecharegistroinscripciones,horaregistroinscripciones");
        if ($inscs && !empty($inscs)) {
            $pdf->tit1 = 'ANOTACIONES MARGINALES';
            $pdf->AddPage();
            $cuerpo = '';
            $ix = 0;
            foreach ($inscs as $rx) {
                $ix++;
                $txt = $ix . '<strong>.) Registro: </strong>' . $rx["libroinscripciones"] . ' - ' . $rx["tomoinscripciones"] . ' - ' . $rx["registroinscripciones"] . '<br>';
                $txt .= '<strong>Fecha de registro: </strong>' . \funcionesGenerales::mostrarFecha2($rx["fecharegistroinscripciones"]) . '<br>';
                $txt .= '<strong>Noticia: </strong>' . $rx["noticiainscripciones"] . '<br>';
                $txt .= '<hr>';
                $pdf->SetFont('dejavusans', '', 9);
                $pdf->writeHTML($txt, true, false, true, false, 'L');
            }
        }
    }

    //
    //
    $name = session_id() . '-CertificateFullCopyWithEnrollment-' . date("Ymd") . '-' . date("His") . '.pdf';
    $pdf->Output($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name, "F");
    return $name;
}

function armarPdfOpenCertification($dbx, $temx, $faboCert, $abgCert, $qr) {
    require_once ('../configuracion/generales.php');
    require_once ('../configuracion/parametros' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ('../components/tcpdf_6.7.5/tcpdf.php');
    require_once ('../components/tcpdf_6.7.5/examples/lang/eng.php');


    if (!class_exists('PDFCerti')) {

        class PDFCerti extends TCPDF {

            public $banner = '';
            public $tit1 = '';
            public $tit2 = '';
            public $tit3 = '';
            public $tit4 = '';
            public $pagina = 0;
            public $presentacion = 0;
            public $tipoimpresion = '';

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

                $this->SetMargins(20, 50, 7);
                $this->SetTextColor(0, 0, 0);
                $this->SetXY(20, 40);

                //
                $this->SetFontSize(9);
                $this->SetTextColor(139, 0, 0);
                $this->writeHTML('<strong>' . $this->tit1 . '</strong>', true, false, true, false, 'C');
                //$this->writeHTML('<strong>' . $this->tit2 . '</strong>', true, false, true, false, 'C');
                // $this->writeHTML('<strong>' . $this->tit3 . '</strong>', true, false, true, false, 'C');
                // $this->writeHTML('<strong>' . $this->tit4 . '</strong>', true, false, true, false, 'C');
                $this->SetTextColor(0, 0, 0);

                $this->SetFontSize(25);
                $this->SetTextColor(255, 192, 203);
                $this->SetTextColor(139, 0, 0);
                $this->SetFontSize(9);
                $this->setXY(20, 60);
            }

            public function Footer() {
                $this->SetY(-10);
                $this->Cell(0, 10, 'Página ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
                $this->pagina++;
            }

        }

    }

    $pdf = new PDFCerti(PDF_PAGE_ORIENTATION, PDF_UNIT, 'LETTER', true, 'UTF-8', false, true);
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetAutoPageBreak(true, 15);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    $pdf->setLanguageArray($l);
    $pdf->SetFont('dejavusans', '', 9);

    //
    $pdf->banner = $_SESSION["generales"]["pathabsoluto"] . '/' . BANNER_EMPRESA;
    $pdf->tit1 = 'CERTIFICACION ESPECIAL';
    $pdf->presentacion = $temx["idliquidacionofisol"];

    //
    $pdf->AddPage();

    //
    $cuerpo = stripslashes($temx["escrituraofisol"]);
    $pdf->writeHTML($cuerpo, true, false, true, false, 'J');
    $pdf->Ln();

    if ($faboCert != '') {
        $linea = $pdf->GetY() + 1;
        $pdf->SetXY(20, $linea);
        $pdf->Image($faboCert, 85, $linea, 35, 0, 'png');
    } else {
        $linea = $pdf->GetY() + 1;
    }

    //
    $linea = $linea + 30;
    $pdf->SetFont('dejavusans', 'B', 10);
    $pdf->SetXY(20, $linea);
    // $pdf->Cell(170, 4, \funcionesGenerales::utf8_decode('FIRMA DEL REGISTRADOR(A)'), 0, 0, 'C');
    $pdf->Cell(170, 4, \funcionesGenerales::utf8_decode($abgCert), 0, 0, 'C');

    $linea = $linea + 5;
    $pdf->SetXY(20, $linea);
    $pdf->Image($qr, 85, $linea, 45, 45, 'png');

    //
    //
    $name = session_id() . '-CertificacionAbierta-' . date("Ymd") . '-' . date("His") . '.pdf';
    $pdf->Output($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name, "F");
    return $name;
}

function armarPdfExistenceAndLegalRepresentation($dbx, $tipoimpresion = '', $liq = array(), $exp = array(), $faboCert, $abgCert, $qr) {
    require_once ('../configuracion/generales.php');
    require_once ('../configuracion/parametros' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ('../components/tcpdf_6.7.5/tcpdf.php');
    require_once ('../components/tcpdf_6.7.5/examples/lang/eng.php');


    if (!class_exists('PDFCerti')) {

        class PDFCerti extends TCPDF {

            public $banner = '';
            public $tit1 = '';
            public $tit2 = '';
            public $tit3 = '';
            public $tit4 = '';
            public $pagina = 0;
            public $presentacion = 0;
            public $tipoimpresion = '';

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

                $this->SetMargins(20, 50, 7);
                $this->SetTextColor(0, 0, 0);
                $this->SetXY(20, 40);

                //
                $this->SetFontSize(12);
                $this->SetTextColor(139, 0, 0);
                $this->writeHTML('<strong>' . $this->tit1 . '</strong>', true, false, true, false, 'C');
                $this->SetFontSize(25);
                $this->SetTextColor(255, 192, 203);
                /*
                  if ($this->tipoimpresion == 'consulta') {
                  $this->RotatedText(55, 195, 'CONSULTA SIN VALIDEZ LEGAL', 45);
                  } else {
                  if ($this->tipoimpresion == 'preparacion' || $this->tipoimpresion == 'view') {
                  $this->RotatedText(55, 195, 'CERTIFICADO DE MUESTRA', 45);
                  }
                  }
                 */
                $this->SetTextColor(139, 0, 0);
                $this->SetFontSize(9);
                $this->setXY(20, 40);
            }

            public function Footer() {
                $this->SetY(-10);
                $this->Cell(0, 10, 'Página ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
                $this->pagina++;
            }

        }

    }

    $pdf = new PDFCerti(PDF_PAGE_ORIENTATION, PDF_UNIT, 'LETTER', true, 'UTF-8', false, true);
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetAutoPageBreak(true, 15);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    $pdf->setLanguageArray($l);
    $pdf->SetFont('dejavusans', '', 9);

    //
    $pdf->banner = $_SESSION["generales"]["pathabsoluto"] . '/' . BANNER_EMPRESA;
    $pdf->tit1 = 'CERTIFICADO DE INSCRIPCIÓN Y REPRESENTACION LEGAL';
    if (!empty($liq)) {
        $pdf->presentacion = $liq["idliquidacion"];
    } else {
        $pdf->presentacion = 'Consulta sin validez legal';
    }
    $pdf->tipoimpresion = $tipoimpresion;

    //
    $pdf->AddPage();

    //
    $orden = retornarRegistrosMysqliApi($dbx, 'orden_certificas', "tipocertificado='CEREXI'", "orden");
    foreach ($orden as $ord) {
        if ($ord["estado"] == 'A') {
            $rut = $ord["rutina"];
            eval("$rut");
        }
    }
    unset($orden);

    $linea1 = $pdf->GetY();
    if ($linea1 > 220) {
        $pdf->AddPage();
    }

    //
    $txt = '<br><br><br><br><br><br><br><br>';
    $pdf->writeHTML($txt, true, false, true, false, 'L');
    $linea = $pdf->GetY();
    $pdf->SetXY(60, $linea);
    $pdf->Cell(120, 4, $abgCert, 0, 0, 'L');
    $pdf->ln();
    $linea = $pdf->GetY();
    $pdf->SetXY(60, $linea);
    $pdf->Cell(120, 4, utf8_decode('FIRMA DEL REGISTRADOR(A)'), 0, 0, 'L');

    //
    $linea = $pdf->GetY() - 35;
    $lineafirma = $linea;
    $pdf->SetXY(20, $linea);
    $pdf->Image($qr, 130, $linea, 25, 25, 'png');

    if ($faboCert != '') {
        $pdf->SetXY(20, $lineafirma);
        $pdf->Image($faboCert, 80, $lineafirma, 30, 30, 'png');
    }


    //
    $name = session_id() . '-CertificateExistenceAndLegalRepresentation-' . $exp["matricula"] . '-' . date("Ymd") . '-' . date("His") . '.pdf';
    $pdf->Output($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name, "F");
    return $name;
}

function armarEncabezadosCeyrl($dbx, $pdf, $exp) {
    $txt = '<strong>Matrícula No. : </strong>' . $exp["matricula"] . '<br>';
    $txt .= '<strong>Denominación o razón social : </strong>' . $exp["razonsocial"];
    if ($exp["nombrecomercial"] != '') {
        $txt .= ' / ' . $exp["nombrecomercial"];
    }
    $txt .= '<br><br>';
    $pdf->writeHTML($txt, true, false, true, false, 'J');

    $txt = 'DE ACUERDO CON LA SOLICITUD PRESENTADA Y EN BASE A LA INFORMACIÓN REGISTRAL ';
    $txt .= 'QUE CONSTA EN EL SISTEMA DEL REGISTRO MERCANTIL DEL DEPARTAMENTO DE FRANCISCO ';
    $txt .= 'MORAZÁN, SEGÚN MATRÍCULA RELACIONADA SE EMITE EL SIGUIENTE CERTIFICADO REGISTRAL<br>';
    $pdf->writeHTML($txt, true, false, true, false, 'J');
}

function armarConstitucionCeyrl($dbx, $pdf, $exp) {
    $cant = 0;
    foreach ($exp["inscripciones"] as $insc) {
        if ($insc["grupoactoinscripciones"] == '005') {
            $cant++;
            if ($cant === 1) {
                $txt = '<strong>CERTIFICA - CONSTITUCIÓN</strong><br>';
                $pdf->writeHTML($txt, true, false, true, false, 'C');
            }
            $txt = 'CONSTITUCIÓN: ' . armarDescripciones($dbx, $insc, $exp, 'CONSTITUCION') . '<br>';
            $pdf->writeHTML($txt, true, false, true, false, 'J');
        }
    }
}

function armarVigenciaCeyrl($dbx, $pdf, $exp) {
    $cant = 0;
    $txt = '<strong>CERTIFICA - VIGENCIA</strong><br>';
    $pdf->writeHTML($txt, true, false, true, false, 'C');
    if ($exp["fecvigencia"] == '99999999' || $exp["fecvigencia"] == '99990909') {
        $txt = '<strong>VIGENCIA :</strong> QUE EL TÉRMINO DE DURACIÓN ES INDEFINIDO<br>';
        $pdf->writeHTML($txt, true, false, true, false, 'J');
    } else {
        if ($exp["fecvigencia"] === '') {
            $txt = '<strong>VIGENCIA :</strong> NO SE REPORTÓ EN FORMA EXPRESA EL TÉRMINO DE DURACIÓN.<br>';
            $pdf->writeHTML($txt, true, false, true, false, 'J');
        } else {
            if ($exp["fecvigencia"] < date("Ymd")) {
                $txt = '<strong>VIGENCIA :</strong> QUE EL TÉRMINO DE DURACIÓN ERA HASTA EL ' . \funcionesGenerales::mostrarFechaLetrasDescripciones($exp["fecvigencia"]) . '<br>';
                $pdf->writeHTML($txt, true, false, true, false, 'J');
            } else {
                $txt = '<strong>VIGENCIA :</strong> QUE EL TÉRMINO DE DURACIÓN ES HASTA EL ' . \funcionesGenerales::mostrarFechaLetrasDescripciones($exp["fecvigencia"]) . '<br>';
                $pdf->writeHTML($txt, true, false, true, false, 'J');
            }
        }
    }
}

function armarCapitalCeyrl($dbx, $pdf, $exp) {
    $capfin = false;
    foreach ($exp["capitales"] as $cap) {
        $capfin = $cap;
    }
    if ($capfin === false) {
        $txt = '<strong>CERTIFICA - CAPITALES</strong><br>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');
        $txt = '<strong>CAPITAL: </strong>NO SE REPORTÓ EL CAPITAL EN FORMA EXPRESA.<br>';
        $pdf->writeHTML($txt, true, false, true, false, 'J');
    } else {
        if ($capfin["nomanifestadocap"] == 'S') {
            $txt = '<strong>CERTIFICA - CAPITALES</strong><br>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $txt = '<strong>CAPITAL: </strong>EN LOS DOCUMENTOS DE CONSTITUCIÓN Y/O REFORMA, EL CAPITAL ES NO MANIFESTADO<br>';
            $pdf->writeHTML($txt, true, false, true, false, 'J');
        } else {
            $txt = '<strong>CERTIFICA - CAPITALES</strong><br>';
            $pdf->writeHTML($txt, true, false, true, false, 'C');
            $txt = '';
            if ($capfin["valorsocialcap"] != 0) {
                $txt .= '<strong>CAPITAL SOCIAL : ' . SIGNO_MONEDA . ' </strong>' . number_format($capfin["valorsocialcap"], 2) . '<br>';
            }
            if ($capfin["valorsocialminimocap"] != 0) {
                $txt .= '<strong>CAPITAL MÍNIMO : ' . SIGNO_MONEDA . ' </strong>' . number_format($capfin["valorsocialminimocap"], 2) . '<br>';
            }
            if ($capfin["valorsocialmaximocap"] != 0) {
                $txt .= '<strong>CAPITAL MÁXIMO : ' . SIGNO_MONEDA . ' </strong>' . number_format($capfin["valorsocialmaximocap"], 2) . '<br>';
            }
            $pdf->writeHTML($txt, true, false, true, false, 'J');
        }
    }

    $siSocios = 'no';
    if ($exp["organizacion"] != '07' && $exp["organizacion"] != '10') {
        foreach ($exp["vinculos"] as $vin) {
            if ($vin["tipovinc"] == 'SOC') {
                $siSocios = 'si';
            }
        }
    }

    // **************************************************************** //
    // 2020-02-05: JINT: Se forza no para que no imprima los socios.    
    // **************************************************************** //
    $siSocios = 'no';
    if ($siSocios == 'si') {
        $txt = '<table>';
        foreach ($exp["vinculos"] as $vin) {
            if ($vin["tipovinc"] == 'SOC') {
                $txt .= '<tr>';
                $txt .= '<td width="50%">';
                $txt .= $vin["razonsocialvinc"];
                $txt .= '</td>';
                $txt .= '<td width="10%">';
                $txt .= retornarNombreTablaBasicaMysqliApi($dbx, 'tipoidentificacion', $vin["idclasevinc"], "campo1");
                $txt .= '</td>';
                $txt .= '<td width="20%">';
                $txt .= $vin["numidvinc"];
                $txt .= '</td>';
                $txt .= '<td width="20%">';
                if ($vin["valorrefvinc"] != 0) {
                    $txt .= SIGNO_MONEDA . ' ' . $vin["valorrefvinc"];
                } else {
                    $txt .= SIGNO_MONEDA . ' ' . $vin["valorconstvinc"];
                }
                $txt .= '</td>';
                $txt .= '</tr>';
            }
        }
        $txt .= '</table>';
        $pdf->writeHTML($txt, true, false, true, false, 'J');
    }
}

function armarRepresentacionLegalCeyrl($dbx, $pdf, $exp) {

    $cant = 0;
    $siRL = 'no';
    foreach ($exp["vinculoscert"] as $vin) {
        if ($vin["tipovinc"] == 'RLP' || $vin["tipovinc"] == 'RLS') {
            $siRL = 'si';
        }
    }
    if ($siRL == 'si') {
        $txt = '<strong>CERTIFICA - REPRESENTANTES LEGALES</strong><br>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');

        $vinccert = array();
        foreach ($exp["vinculoscert"] as $vin) {
            if ($vin["tipovinc"] == 'RLP' || $vin["tipovinc"] == 'RLS') {
                if (!isset($vinccert[$vin["registrovinc"]])) {
                    $vinccert[$vin["registrovinc"]] = array();
                }
                $vinccert[$vin["registrovinc"]][] = $vin;
            }
        }
        foreach ($vinccert as $insc1 => $renglones) {
            list ($xlib, $xtom, $xreg, $xdup) = explode("-", $insc1);
            $insc = false;
            foreach ($exp["inscripciones"] as $ins) {
                if ($ins["libroinscripciones"] == $xlib &&
                        $ins["tomoinscripciones"] == $xtom &&
                        $ins["registroinscripciones"] == $xreg &&
                        $ins["dupliinscripciones"] == $xdup) {
                    $insc = $ins;
                }
            }
            $txt = armarDescripciones($dbx, $insc, $exp, 'NOMBRAMIENTOS-RL') . '<br><br>';
            foreach ($renglones as $ren) {
                $txt .= '<table>';
                $txt .= '<tr>';
                $txt .= '<td width="20%">';
                if ($ren["ps"] == 'P') {
                    $txt .= 'PRINCIPAL';
                }
                if ($ren["ps"] == 'S') {
                    $txt .= 'SUPLENTE';
                }
                if ($ren["ps"] != 'P' && $ren["ps"] != 'S') {
                    $txt .= '';
                }
                $txt .= '</td>';
                $txt .= '<td width="40%">';
                $txt .= $ren["descargovinc"];
                $txt .= '</td>';
                $txt .= '<td width="2%">';
                $txt .= '&nbsp;';
                $txt .= '</td>';
                $txt .= '<td width="38%">';
                $txt .= $ren["razonsocialvinc"];
                $txt .= '</td>';
                $txt .= '</tr>';
                $txt .= '</table>';
            }
            $pdf->writeHTML($txt, true, false, true, false, 'J');
        }
    }
}

function armarJuntaDirectivaCeyrl($dbx, $pdf, $exp) {

    $cant = 0;
    $siRL = 'no';
    foreach ($exp["vinculoscert"] as $vin) {
        if ($vin["tipovinc"] == 'JDP' || $vin["tipovinc"] == 'JDS') {
            $siRL = 'si';
        }
    }
    if ($siRL == 'si') {
        $txt = '<strong>CERTIFICA - CONSEJO DE ADMINISTRACIÓN / JUNTA DIRECTIVA</strong><br>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');

        $vinccert = array();
        foreach ($exp["vinculoscert"] as $vin) {
            if ($vin["tipovinc"] == 'JDP' || $vin["tipovinc"] == 'JDS') {
                if (!isset($vinccert[$vin["registrovinc"]])) {
                    $vinccert[$vin["registrovinc"]] = array();
                }
                $vinccert[$vin["registrovinc"]][] = $vin;
            }
        }
        foreach ($vinccert as $insc1 => $renglones) {
            list ($xlib, $xtom, $xreg, $xdup) = explode("-", $insc1);
            $insc = false;
            foreach ($exp["inscripciones"] as $ins) {
                if ($ins["libroinscripciones"] == $xlib &&
                        $ins["tomoinscripciones"] == $xtom &&
                        $ins["registroinscripciones"] == $xreg &&
                        $ins["dupliinscripciones"] == $xdup) {
                    $insc = $ins;
                }
            }
            $txt = armarDescripciones($dbx, $insc, $exp, 'NOMBRAMIENTOS-JD') . '<br><br>';
            foreach ($renglones as $ren) {
                $txt .= '<table>';
                $txt .= '<tr>';
                $txt .= '<td width="20%">';
                if ($ren["ps"] == 'P') {
                    $txt .= 'PRINCIPAL';
                }
                if ($ren["ps"] == 'S') {
                    $txt .= 'SUPLENTE';
                }
                if ($ren["ps"] != 'P' && $ren["ps"] != 'S') {
                    $txt .= '';
                }
                $txt .= '</td>';
                $txt .= '<td width="40%">';
                $txt .= $ren["descargovinc"];
                $txt .= '</td>';
                $txt .= '<td width="2%">';
                $txt .= '&nbsp;';
                $txt .= '</td>';
                $txt .= '<td width="38%">';
                $txt .= $ren["razonsocialvinc"];
                $txt .= '</td>';
                $txt .= '</tr>';
                $txt .= '</table>';
            }
            $pdf->writeHTML($txt, true, false, true, false, 'J');
        }
    }
}

function armarComisarios($dbx, $pdf, $exp) {

    $cant = 0;
    $siRL = 'no';
    foreach ($exp["vinculoscert"] as $vin) {
        if ($vin["tipovinc"] == 'CMP' || $vin["tipovinc"] == 'CMS') {
            $siRL = 'si';
        }
    }
    if ($siRL == 'si') {
        $txt = '<strong>CERTIFICA - COMISARIOS</strong><br>';
        $pdf->writeHTML($txt, true, false, true, false, 'C');

        $vinccert = array();
        foreach ($exp["vinculoscert"] as $vin) {
            if ($vin["tipovinc"] == 'CMP' || $vin["tipovinc"] == 'CMS') {
                if (!isset($vinccert[$vin["registrovinc"]])) {
                    $vinccert[$vin["registrovinc"]] = array();
                }
                $vinccert[$vin["registrovinc"]][] = $vin;
            }
        }
        foreach ($vinccert as $insc1 => $renglones) {
            list ($xlib, $xtom, $xreg, $xdup) = explode("-", $insc1);
            $insc = false;
            foreach ($exp["inscripciones"] as $ins) {
                if ($ins["libroinscripciones"] == $xlib &&
                        $ins["tomoinscripciones"] == $xtom &&
                        $ins["registroinscripciones"] == $xreg &&
                        $ins["dupliinscripciones"] == $xdup) {
                    $insc = $ins;
                }
            }
            $txt = armarDescripciones($dbx, $insc, $exp, 'NOMBRAMIENTOS-COM') . '<br><br>';
            foreach ($renglones as $ren) {
                $txt .= '<table>';
                $txt .= '<tr>';
                $txt .= '<td width="20%">';
                if ($ren["ps"] == 'P') {
                    $txt .= 'PRINCIPAL';
                }
                if ($ren["ps"] == 'S') {
                    $txt .= 'SUPLENTE';
                }
                if ($ren["ps"] != 'P' && $ren["ps"] != 'S') {
                    $txt .= '';
                }
                $txt .= '</td>';
                $txt .= '<td width="40%">';
                $txt .= $ren["descargovinc"];
                $txt .= '</td>';
                $txt .= '<td width="2%">';
                $txt .= '&nbsp;';
                $txt .= '</td>';
                $txt .= '<td width="38%">';
                $txt .= $ren["razonsocialvinc"];
                $txt .= '</td>';
                $txt .= '</tr>';
                $txt .= '</table>';
            }
            $pdf->writeHTML($txt, true, false, true, false, 'J');
        }
    }
}

function armarCertificaTextualCeyrl($dbx, $pdf, $exp, $tipo, $titulo = '') {
    $cant = 0;
    foreach ($exp["crt"] as $crt => $contenido) {
        if (retornarRegistroTablasMysqliApi($dbx, 'codigoscertificas', $crt, "campo3") == $tipo) {
            $cant++;
            if ($cant === 1) {
                if ($titulo == '') {
                    $txt = '<strong>CERTIFICA</strong><br>';
                } else {
                    $txt = '<strong>CERTIFICA - ' . $titulo . '</strong><br>';
                }
                $pdf->writeHTML($txt, true, false, true, false, 'C');
            }
            $txt = strtoupper($contenido) . '<br>';
            $pdf->writeHTML($txt, true, false, true, false, 'J');
        }
    }
}

function armarFinalCertificadoCeyrlOriginal($dbx, $pdf, $nomabo) {
    // if ($pdf->tipoimpresion != 'consulta' && $pdf->tipoimpresion != 'preparacion' && $pdf->tipoimpresion != 'view') {
    $txt = '<strong>CERTIFICA - FUNDAMENTO LEGAL DEL CERTIFICADO</strong><br>';
    $pdf->writeHTML($txt, true, false, true, false, 'C');
    $txt = 'FIRMA EL PRESENTE CERTIFICADO EL/LA ABOGADO/A ' . $nomabo . ', REGISTRADOR/A ADJUNTO/A DEL REGISTRO MERCANTIL ';
    $txt .= 'DE FRANCISCO MORAZÁN, ADMINISTRADO POR LA CÁMARA DE COMERCIO E INDUSTRIA DE TEGUCIGALPA (CCIT), CENTRO ASOCIADO ';
    $txt .= 'AL INSTITUTO DE LA PROPIEDAD.<br><br>';
    $txt .= 'FUNDAMENTOS DE DERECHO, ARTÍCULO 407 DEL CÓDIGO DEL COMERCIO, ARTÍCULO 69 DEL REGLAMENTO DE LA LEY DE PROPIEDAD.<br><br>';
    $txt .= 'TEGUCIGALPA MUNICIPIO DEL DISTRITO CENTRAL<br>';
    $txt .= '<strong>FECHA Y HORA DE EXPEDICIÓN:</strong> ' . \funcionesGenerales::mostrarFechaLetrasDescripciones(date("Ymd")) . ' a las ' . date("H:i:s");
    $pdf->writeHTML($txt, true, false, true, false, 'J');
    // }
}

function armarFinalCertificadoCeyrl($dbx, $pdf, $nomabo) {
    // if ($pdf->tipoimpresion != 'consulta' && $pdf->tipoimpresion != 'preparacion' && $pdf->tipoimpresion != 'view') {
    $txt = '<strong>FUNDAMENTO LEGAL DEL CERTIFICADO</strong><br>';
    $pdf->writeHTML($txt, true, false, true, false, 'C');
    $txt = 'FIRMA EL PRESENTE CERTIFICADO EL/LA REGISTRADOR/A ADJUNTO/A DEL REGISTRO MERCANTIL ';
    $txt .= 'DE FRANCISCO MORAZÁN, ADMINISTRADO POR LA CÁMARA DE COMERCIO E INDUSTRIA DE TEGUCIGALPA (CCIT), CENTRO ASOCIADO ';
    $txt .= 'AL INSTITUTO DE LA PROPIEDAD.<br><br>';
    $txt .= 'FUNDAMENTOS DE DERECHO, ARTÍCULO 407 DEL CÓDIGO DEL COMERCIO, ARTÍCULO 69 DEL REGLAMENTO DE LA LEY DE PROPIEDAD.<br><br>';
    $txt .= 'TEGUCIGALPA MUNICIPIO DEL DISTRITO CENTRAL<br>';
    $txt .= '<strong>FECHA Y HORA DE EXPEDICIÓN:</strong> ' . \funcionesGenerales::mostrarFechaLetrasDescripciones(date("Ymd")) . ' a las ' . date("H:i:s");
    $pdf->writeHTML($txt, true, false, true, false, 'J');
    // }
}

function armarDescripciones($dbx, $insc, $exp, $tipo = '') {
    if ($tipo == 'NOMBRAMIENTOS-JD' ||
            $tipo == 'NOMBRAMIENTOS-COM' ||
            $tipo == 'NOMBRAMIENTOS-RL') {
        $txt = '';
        /*
          $txt = 'NOMBRAN ';
          if ($tipo == 'NOMBRAMIENTOS-JD') {
          $txt .= 'CONSEJO DE ADMINISTRACIÓN / JUNTA DIRECTIVA';
          }
          if ($tipo == 'NOMBRAMIENTOS-COM') {
          $txt .= 'COMISARIOS';
          }
          if ($tipo == 'NOMBRAMIENTOS-RL') {
          $txt .= 'REPRESENTANTES LEGALES';
          }
         */
        $txt .= 'BAJO EL NO. ' . $insc["registroinscripciones"];
        if ($insc["tomoinscripciones"] != '999') {
            $txt .= ' DEL TOMO NO. ' . $insc["tomoinscripciones"];
        }
        $txt .= ' DEL ' . retornarRegistroTablasMysqliApi($dbx, 'libros', $insc["libroinscripciones"], "descripcion");
        $txt .= ', FUERON NOMBRADOS : ';
    } else {
        $txt = 'QUE POR ' . retornarRegistroTablasMysqliApi($dbx, 'tipodocreg', $insc["idtipodocinscripciones"], "descripcion");
        if ($insc["numdocinscripciones"] != '' && $insc["numdocinscripciones"] != 'N/A' && $insc["numdocinscripciones"] != 'NA') {
            $txt .= ' NO. ' . $insc["numdocinscripciones"] . ' ';
        }
        if ($insc["idorigendocinscripciones"] != '') {
            $txt .= ' DE ' . retornarRegistroTablasMysqliApi($dbx, 'origenes', $insc["idorigendocinscripciones"], "descripcion");
        } else {
            if ($insc["origendocinscripciones"] != '') {
                $txt .= ' DE ' . $insc["origendocinscripciones"];
            }
        }
        if ($insc["mundocinscripciones"] != '') {
            $txt .= ' DE ' . retornarRegistroTablasMysqliApi($dbx, 'municipios', $insc["mundocinscripciones"], "descripcion");
        }
        $txt .= ' DEL ' . \funcionesGenerales::mostrarFechaLetrasDescripciones($insc["fecdocinscripciones"]);
        $txt .= ', INSCRITA EL ' . \funcionesGenerales::mostrarFechaLetrasDescripciones($insc["fecharegistroinscripciones"]);
        $txt .= ' EN EL TOMO ' . $insc["tomoinscripciones"];
        $txt .= ' BAJO EL NO. ' . $insc["registroinscripciones"];
        $txt .= ' DEL ' . retornarRegistroTablasMysqliApi($dbx, 'libros', $insc["libroinscripciones"], "descripcion") . ',';
        if ($tipo == 'CONSTITUCION') {
            if ($exp["organizacion"] == '01') {
                $txt .= ' SE INSCRIBIO LA DECLARACIÓN DEL COMERCIANTE INDIVIDUAL DENOMINADO ' . $insc["nombreinscripciones"];
            }
            if ($exp["organizacion"] > '02') {
                $txt .= ' SE CONSTITUYÓ LA SOCIEDAD MERCANTIL DENOMINADA ' . $insc["nombreinscripciones"];
            }
            if ($insc["nombrecomercialinscripciones"] != '') {
                $txt .= ' / ' . $insc["nombrecomercialinscripciones"];
            }
        }
        if ($tipo == 'NOMBRAMIENTOS') {
            $txt .= ' FUERON NOMBRADOS:';
        }
    }

    return strtoupper($txt);
}

?>