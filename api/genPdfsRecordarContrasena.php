<?php

function armarPdfRecordarContrasenaApi($mysqli,$contrasena, $arrUsu) {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/fpdf186/fpdf.php');

    // Define llamado a libreria fpdf
    if (!defined('FPDF_FONTPATH')) {
        define('FPDF_FONTPATH', $_SESSION["generales"]["pathabsoluto"] . '/includes/fpdf186/font/');
    }
    if (!class_exists('PDFCerti')) {

        class PDFCerti extends FPDF {

            function salto($lin, $arrDoc) {
                $lin1 = $this->GetY();
                $lin1 = $lin1 + $lin;
                if ($lin1 > 250) {
                    $this->titulo($arrDoc);
                    $lin1 = $this->GetY();
                }
                $this->Sety($lin1);
            }

            function titulo() {
                $i = 20;
                $this->AddPage();
                $this->SetMargins(10, 20, 7);
                    if (file_exists($_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . CODIGO_EMPRESA . '.jpg')) {
                        $this->Image($_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . CODIGO_EMPRESA . '.jpg', 150, 20, 45, 28);
                    }
                // $this->Image($_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . CODIGO_EMPRESA . '.jpg', 150, 20, 45, 28);
                $i = $i + 5;
                $this->SetFont('Arial', 'B', 9);
                $this->SetXY(12, $i);
                $this->Cell(100, 4, '', 0, 0, 'L');
                $i = $i + 5;
                $this->SetFont('Arial', 'B', 9);
                $this->SetXY(12, $i);
                $this->Cell(100, 4, '', 0, 0, 'L');
                $this->Ln();
                $i = $this->GetY();
                $this->SetFont('Arial', 'B', 9);
                $this->SetXY(12, $i);
                $this->Cell(100, 4, retornarNombreMunicipioMysqliApi($mysqli,MUNICIPIO) . ', ' . \funcionesGenerales::mostrarFechaLetras(date("Ymd")), 0, 0, 'L');
                $this->Ln();
                $this->Ln();
                $i = $this->GetY();
                $this->SetFont('Arial', 'B', 9);
                $this->SetXY(12, $i);
                $this->Cell(100, 4, 'Ref. SOLICITUD DE CAMBIO DE CONTRASENA PARA FIRMADO ELECTRONICO', 0, 0, 'L');
                $this->Ln();
                $this->Ln();
            }

        }

    }


    // Imprime encabezados
    $pdf = new PDFCerti("Portrait", "mm", "Letter");
    $pdf->AliasNbPages();
    $pdf->titulo();
    $pdf->SetFont('Arial', 'B', 10);

    $pdf->SetFont('Arial', '', 10);
    $pdf->SetX(12);
    $pdf->Cell(100, 4, \funcionesGenerales::utf8_decode('Señor(a)'), 0, 0, 'L');
    $pdf->Ln();
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetX(12);
    $pdf->Cell(100, 4, \funcionesGenerales::utf8_decode($arrUsu["nombre"]), 0, 0, 'L');
    $pdf->Ln();
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Ln();
    $pdf->Ln();
    
    $txt = 'De acuerdo con su solicitud hemos generado una nueva contraseña segura para el firmado de trámites que se presenten  ';
    $txt .= 'en forma electrónica y NO PRESENCIAL y que afecten los registros que administra la Cámara de Comercio.';
    $txt = \funcionesGenerales::utf8_decode($txt);

    $pdf->SetFont('Arial', '', 10);
    $pdf->SetX(12);
    $pdf->MultiCell(190, 4, $txt, 0, 'J', 0);
    $pdf->Ln();
    $pdf->Ln();
    
    $txt = 'Le recordamos que el uso que se haga de la misma es personal e intransferible.  ';
    $txt = \funcionesGenerales::utf8_decode($txt);
    $pdf->SetFont('Arial', '', 10);
    $pdf->SetX(12);
    $pdf->MultiCell(190, 4, $txt, 0, 'J', 0);
    $pdf->Ln();
    $pdf->Ln();

    $txt = 'La contraseña generada es:';
    $txt = \funcionesGenerales::utf8_decode($txt);
    $pdf->SetFont('Arial', '', 10);
    $pdf->SetX(12);
    $pdf->MultiCell(190, 4, $txt, 0, 'C', 0);
    $pdf->Ln();
    $pdf->Ln();
    
    $txt = $contrasena;
    $txt = \funcionesGenerales::utf8_decode($txt);
    $pdf->SetFont('Arial', '', 10);
    $pdf->SetX(12);
    $pdf->MultiCell(190, 4, $txt, 0, 'C', 0);
    $pdf->Ln();
    $pdf->Ln();
      
    $txt = 'Cordialmente ';
    $txt = \funcionesGenerales::utf8_decode($txt);
    $pdf->SetFont('Arial', '', 10);
    $pdf->SetX(12);
    $pdf->MultiCell(190, 4, $txt, 0, 'J', 0);
    $pdf->Ln();
    $pdf->Ln();
    
    $txt = 'Dirección de Registros Públicos ';
    $txt = \funcionesGenerales::utf8_decode($txt);
    $pdf->SetFont('Arial', '', 10);
    $pdf->SetX(12);
    $pdf->MultiCell(190, 4, $txt, 0, 'J', 0);
    $pdf->Ln();
    
    $txt = RAZONSOCIAL;
    $txt = \funcionesGenerales::utf8_decode($txt);
    $pdf->SetFont('Arial', '', 10);
    $pdf->SetX(12);
    $pdf->MultiCell(190, 4, $txt, 0, 'J', 0);
    $pdf->Ln();
    
    


    $name1 = 'tmp/' . $arrUsu["identificacion"] . '-ContrasenaSegura.pdf';
    $name = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $arrUsu["identificacion"] . '-ContrasenaSegura.pdf';
    $pdf->Output($name, "F");
    return $name1;
}

?>