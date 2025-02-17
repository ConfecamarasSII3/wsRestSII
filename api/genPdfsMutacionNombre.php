<?php

function armarPdfMutacionNombre($dbx,$numrec, $numliq, $txtFirmaElectronica = '') {
    require_once ('../componentes/fpdf186/fpdf.php');

    // Define llamado a libreria fpdf
    if (!defined('FPDF_FONTPATH')) {
        define('FPDF_FONTPATH','../componentes/fpdf186/fonts/');
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
                $this->Cell(100, 4, retornarNombreMunicipioMysqliApi(null,MUNICIPIO) . ', ' . \funcionesGenerales::mostrarFechaLetraS(date("Ymd")), 0, 0, 'L');
                $this->Ln();
                $this->Ln();
                $i = $this->GetY();
                $this->SetFont('Arial', 'B', 9);
                $this->SetXY(12, $i);
                $this->Cell(100, 4, 'Ref. SOLICITUD DE CAMBIO DE NOMBRE', 0, 0, 'L');
                $this->Ln();
                $i = $this->GetY();
                $this->SetFont('Arial', 'B', 9);
                $this->SetXY(12, $i);
                $this->Cell(100, 4, \funcionesGenerales::utf8_decode('Número de recuperación : ') . $_SESSION["formulario"]["numrec"], 0, 0, 'L');
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
    $pdf->Cell(100, 4, \funcionesGenerales::utf8_decode('Señor(es)'), 0, 0, 'L');
    $pdf->Ln();
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetX(12);
    $pdf->Cell(100, 4, \funcionesGenerales::utf8_decode(RAZONSOCIAL), 0, 0, 'L');
    $pdf->Ln();
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetX(12);
    $pdf->Cell(100, 4, \funcionesGenerales::utf8_decode('Departamento de Registros Públicos'), 0, 0, 'L');
    $pdf->Ln();
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetX(12);
    $pdf->Cell(100, 4, retornarNombreMunicipioMysqliApi(null,MUNICIPIO), 0, 0, 'L');
    $pdf->Ln();
    $pdf->Ln();

    //
    if ($_SESSION["formulario"]["datos"]["organizacion"] == '02') {
        if (trim($_SESSION["formulario"]["datos"]["propietarios"][1]["nombrepropietario"]) == '') {

            $_SESSION["formulario"]["datos"]["propietarios"][1]["nombrepropietario"] = '_______________________________________________';
            $_SESSION["formulario"]["datos"]["propietarios"][1]["identificacionpropietario"] = '________________________';
            $txttipo = '_______';
        } else {

            $txttipo = retornarNombreTablasSirepMysqliApi(null,'38', $_SESSION["formulario"]["datos"]["propietarios"][1]["idtipoidentificacionpropietario"]);
        }

        $tx = '_Yo, ' . $_SESSION["formulario"]["datos"]["propietarios"][1]["nombrepropietario"] . ' identificado con ' .
                $txttipo .
                ' número ' . $_SESSION["formulario"]["datos"]["propietarios"][1]["identificacionpropietario"] .
                ' en calidad de propietario del establecimiento ' . $_SESSION["formulario"]["datos"]["nombre"] .
                ' matriculado bajo el número ' . $_SESSION["formulario"]["datos"]["matricula"] .
                ' atentamente solicito el cambio de nombre de mi establecimiento por :';
    }


    $tx = \funcionesGenerales::utf8_decode($tx);

    $pdf->SetFont('Arial', '', 10);
    $pdf->SetX(12);
    $pdf->MultiCell(190, 4, $tx, 0, 'J', 0);
    $pdf->Ln();
    $pdf->Ln();

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetX(12);
    $pdf->MultiCell(190, 4, \funcionesGenerales::utf8_decode($_SESSION["formulario"]["datos"]["nuevonombre"]), 0, 'C', 0);
    $pdf->Ln();

    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();


    if (trim($txtFirmaElectronica) == '') {
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetX(12);
        $pdf->MultiCell(190, 4, 'Nombre: _______________________________________', 0, 'J', 0);
        $pdf->Ln();
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetX(12);
        $pdf->MultiCell(190, 4, \funcionesGenerales::utf8_decode('Identificación: _______________________________________'), 0, 'J', 0);
        $pdf->Ln();
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetX(12);
        $pdf->MultiCell(190, 4, 'Firma: _______________________________________', 0, 'J', 0);
    } else {
        //firmado electronico
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetX(12);
        $pdf->MultiCell(190, 4, \funcionesGenerales::utf8_decode($txtFirmaElectronica), 0, 'C', 0);
    }
    $pdf->Ln();
    $pdf->Ln();

    /*
    if (trim($txtFirmaElectronica) == '') {
        $tx = "Importante: Se debe reconocer el contenido del documento y la firma del solicitante ante notario o hacer presentación " .
            "personal ante el secretario de la Cámara de Comercio, ya que este documento debe inscribirse en el Registro " .
            "correspondiente.";
        $tx = \funcionesGenerales::utf8_decode($tx);
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetX(12);
        $pdf->MultiCell(190, 4, $tx, 0, 'J', 0);
        $pdf->Ln();
    }
    */

    $tx = "Atención: Si está actuando por poder especial anexe su original o fotocopia autenticada. " .
            "En el poder deben especificarse claramente cuales son las facultades otorgadas al apoderado. " .
            "correspondiente.";


    $tx = \funcionesGenerales::utf8_decode($tx);

    $pdf->SetFont('Arial', '', 10);
    $pdf->SetX(12);
    $pdf->MultiCell(190, 4, $tx, 0, 'J', 0);
    $pdf->Ln();
    $pdf->Ln();

    $name = '../tmp/' . session_id() . '-MutacionNombre.pdf';
    $pdf->Output($name, "F");
    return $name;
}

?>