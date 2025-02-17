<?php

/**
 * 
 * @param type $dbx
 * @param type $numrec
 * @param type $numliq
 * @param type $activos
 * @param type $tipoimpresion
 * @return string
 */
function armarPdfFormatoActivosPropietario($dbx = null, $numrec = '', $numliq = 0, $activos = 0, $tipoimpresion = '') {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/tcpdf_6.7.5/tcpdf.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/tcpdf_6.7.5/examples/lang/eng.php');
    // require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/Encoding.php');

    if (!class_exists('PDFCerti')) {

        class PDFFormProp extends TCPDF {

            public $fechaimprimir = '';

            function Header() {
                $i = 0;
                $i = $i + 5;
                $this->SetFont('Helvetica', 'B', 10);
                $this->SetXY(20, $i);
                $this->Cell(100, 4, '', 0, 0, 'L');
                $i = $i + 15;
                $this->SetFont('Helvetica', 'B', 10);
                $this->SetXY(20, $i);
                $this->Cell(100, 4, '', 0, 0, 'L');
                $this->Ln();
                $i = $this->GetY();
                $this->SetFont('Helvetica', 'B', 10);
                $this->SetXY(20, $i);
                if ($this->fechaimprimir == '') {
                    $this->Cell(100, 4, retornarNombreMunicipioMysqliApi(null, MUNICIPIO) . ', ' . \funcionesGenerales::mostrarFechaLetras(date("Ymd")), 0, 0, 'L');
                } else {
                    $this->Cell(100, 4, retornarNombreMunicipioMysqliApi(null, MUNICIPIO) . ', ' . \funcionesGenerales::mostrarFechaLetras($this->fechaimprimir), 0, 0, 'L');
                }
                $this->Ln();
                $this->Ln();
                $i = $this->GetY();
                $this->SetFont('Helvetica', 'B', 9);
                $this->SetXY(20, $i);
                $this->Cell(100, 4, 'Ref. DECLARACIÓN DE ACTIVOS', 0, 0, 'L');
                $this->Ln();
                $i = $this->GetY();
                $this->SetFont('Helvetica', 'B', 9);
                $this->SetXY(20, $i);
                $this->Cell(100, 4, 'Número de recuperación : ' . $_SESSION["tramite"]["numerorecuperacion"], 0, 0, 'L');
                $this->Ln();
                $this->Ln();
                $this->Ln();
                $this->Ln();
                $i = $this->GetY();
                $this->SetY($i);
            }

        }

    }

    //echo "entro a pdf armar mutacion<br>";
    // Imprime encabezados
    $pdf = new PDFFormProp(PDF_PAGE_ORIENTATION, PDF_UNIT, 'LETTER', true, 'UTF-8', false, true);
    $pdf->fechaimprimir = $fechaimprimir;
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('SII');
    $pdf->SetTitle('Solicitud');
    $pdf->SetSubject('Solicitud');
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    $pdf->SetMargins(20, 60, 10);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    $pdf->SetAutoPageBreak(true, 15);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    $pdf->setLanguageArray($l);
    $pdf->AddPage();

    //
    $pdf->SetFont('Helvetica', '', 18);
    $pdf->SetX(20);
    $pdf->Ln();
    $pdf->Ln();
    $pdf->writeHTML('DECLARACIÓN DE ACTIVOS ORDINARIOS DE LA EMPRESA MATRIZ PARA EFECTOS DE LOS DERECHOS POR REGISTRO DE MATRÍCULA O RENOVACIÓN DE ESTABLECIMIENTOS DE COMERCIO, SUCURSALES Y AGENCIAS', true, false, true, false, 'C');
    $pdf->Ln();
    $pdf->Ln();

    //
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(20);
    $tx = 'De conformidad con el artículo 2.2.2.46.1.2. del Decreto 1074 de 2015, para efectos de los derechos por registro de matrícula o renovación de establecimientos de comercio, sucursales y agencias, declaro que el Activo Total de la empresa matriz es el siguiente: ';
    $pdf->writeHTML($tx, true, false, true, false, 'J');
    $pdf->Ln();
    $pdf->Ln();

    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(20);
    $tx = '<strong>* Activo Total $' . number_format($activos) . '</strong><br>';
    $pdf->writeHTML($tx, true, false, true, false, 'C');
    $pdf->Ln();

    //Firmado manual
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(20);
    if ($tipoimpresion == 'renovacion') {
        $pdf->writeHTML('La información aquí suministrada hace parte del trámite de renovación, de establecimiento de comercio, sucursal o agencia solicitado y se entiende debidamente firmado con la suscripción del formulario RUES presentado para el efecto.', true, false, true, false, 'J');
    } else {
        $pdf->writeHTML('La información aquí suministrada hace parte del trámite de matrícula, de establecimiento de comercio, sucursal o agencia solicitado y se entiende debidamente firmado con la suscripción del formulario RUES presentado para el efecto.', true, false, true, false, 'J');
    } 
    
    $pdf->Ln();
    $pdf->Ln();

    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(20);
    $pdf->writeHTML('*Nota: Expresar la cifra en pesos colombianos, sin decimales. Los datos deben corresponder a los estados financieros con corte al 31 de diciembre del año anterior, aún en el caso de que se haga cortes semestrales, o al balance de apertura.', true, false, true, false, 'J');
    $pdf->Ln();
    $pdf->Ln();

    $name = session_id() . '-FormatoActivosPropietario-' . date("Ymd") . '-' . date("His") . '.pdf';
    $pdf->Output($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name, "F");
    return $name;
}

?>