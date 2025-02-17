<?php

function armarPdfSolicitudCancelacion($dbx = null, $txtFirmaElectronica = '', $xIdentificacion = '', $xNombre = '') {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/fpdf186/fpdf.php');

    // Define llamado a libreria fpdf
    if (!defined('FPDF_FONTPATH')) {
        define('FPDF_FONTPATH', $_SESSION["generales"]["pathabsoluto"] . '/components/fpdf186/font/');
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
                $i = $i + 5;
                $this->SetFont('Helvetica', 'B', 9);
                $this->SetXY(12, $i);
                $this->Cell(100, 4, '', 0, 0, 'L');
                $i = $i + 5;
                $this->SetFont('Helvetica', 'B', 9);
                $this->SetXY(12, $i);
                $this->Cell(100, 4, '', 0, 0, 'L');
                $this->Ln();
                $i = $this->GetY() + 5;
                $this->SetFont('Helvetica', 'B', 9);
                $this->SetXY(12, $i);
                $this->Cell(100, 4, retornarNombreMunicipioMysqliApi($dbx, MUNICIPIO) . ', ' . \funcionesGenerales::mostrarFechaLetras(date("Ymd")), 0, 0, 'L');
                $this->Ln();
                $this->Ln();
                $i = $this->GetY() + 10;
                $this->SetFont('Helvetica', 'B', 9);
                $this->SetXY(12, $i);
                $this->Cell(100, 4, \funcionesGenerales::utf8_decode('Ref. SOLICITUD DE CANCELACIÓN'), 0, 0, 'L');
                $this->Ln();
                $i = $this->GetY();
                $this->SetFont('Helvetica', 'B', 9);
                $this->SetXY(12, $i);
                $this->Cell(100, 4, \funcionesGenerales::utf8_decode('Número de recuperación : ') . $_SESSION["tramite"]["numerorecuperacion"], 0, 0, 'L');
                $this->Ln();
                $this->Ln();
            }

        }

    }


    // Imprime encabezados
    $pdf = new PDFCerti("Portrait", "mm", "Letter");
    $pdf->AliasNbPages();
    $pdf->titulo();
    $pdf->SetFont('Helvetica', 'B', 10);

    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(12);
    $pdf->Cell(100, 4, \funcionesGenerales::utf8_decode('Señor(es)'), 0, 0, 'L');
    $pdf->Ln();
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetX(12);
    $pdf->MultiCell(190, 4, \funcionesGenerales::utf8_decode(RAZONSOCIAL), 0, 'J', 0);
    // $pdf->Cell(100, 4, \funcionesGenerales::utf8_decode(RAZONSOCIAL), 0, 0, 'L');
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetX(12);
    $pdf->Cell(100, 4, \funcionesGenerales::utf8_decode('Departamento de Registros Públicos'), 0, 0, 'L');
    $pdf->Ln();
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetX(12);
    $pdf->Cell(100, 4, retornarNombreMunicipioMysqliApi($dbx, MUNICIPIO), 0, 0, 'L');
    $pdf->Ln();
    $pdf->Ln();

    /*
    $txttipo = retornarRegistroMysqliApi($dbx, 'mreg_tipoidentificacion', "id='" . $_SESSION["tramite"]["idtipoidentificacioncliente"] . "'", "descripcion");
    if ($_SESSION["tramite"]["organizacionbase"] == '01') {
        $tx = 'Yo, ' . $_SESSION["tramite"]["nombrecliente"] . ' identificado con ' .
                $txttipo .
                ' número ' . $_SESSION["tramite"]["identificacioncliente"] .
                ' actuando en nombre propio me permito solicitar la cancelación de mi matrícula mercantil No. ' . $_SESSION["tramite"]["matriculabase"] .
                ' en atención a la siguiente situación:' . chr(13) . chr(10) . chr(13) . chr(10);
        $tx .= 'Motivo : ' . retornarRegistroMysqliApi($dbx, 'mreg_motivos_cancelacion', "id='" . $_SESSION["tramite"]["idmotivocancelacion"] . "'", "descripcion") . chr(13) . chr(10);
        $tx .= 'Observaciones : ' . $_SESSION["tramite"]["motivocancelacion"];
    }

    if ($_SESSION["tramite"]["organizacionbase"] == '02') {
        $tx = 'Yo, ' . $_SESSION["tramite"]["nombrecliente"] . ' identificado con ' .
                $txttipo .
                ' número ' . $_SESSION["tramite"]["identificacioncliente"] .
                ' en calidad de propietario del establecimiento ' . $_SESSION["generales"]["expediente"]["nombre"] .
                ' matriculado bajo el número ' . $_SESSION["tramite"]["matriculabase"] .
                ', atentamente solicito la cancelación de la matrícula mercantil del establecimiento en cuestión, atendiendo las siguientes razones:' . chr(13) . chr(10) . chr(13) . chr(10);
        $tx .= 'Motivo : ' . retornarRegistroMysqliApi($dbx, 'mreg_motivos_cancelacion', "id='" . $_SESSION["tramite"]["idmotivocancelacion"] . "'", "descripcion") . chr(13) . chr(10);
        $tx .= 'Observaciones : ' . $_SESSION["tramite"]["motivocancelacion"];
    }
    */
    
    if ($xNombre == '') {
        $xNombre = $_SESSION["tramite"]["apellido1firmante"];
        if (trim($_SESSION["tramite"]["apellido2firmante"]) != '') {
            $xNombre .= ' ' . $_SESSION["tramite"]["apellido2firmante"];
        }
        if (trim($_SESSION["tramite"]["nombre1firmante"]) != '') {
            $xNombre .= ' ' . $_SESSION["tramite"]["nombre1firmante"];
        }
        if (trim($_SESSION["tramite"]["nombre2firmante"]) != '') {
            $xNombre .= ' ' . $_SESSION["tramite"]["nombre2firmante"];
        }
        if (trim($xNombre) == '') {
            $xNombre = $_SESSION["tramite"]["nombrefirmante"];
        }
    }
    
    //
    if ($xIdentificacion == '') {
        $xIdenticacion = $_SESSION["tramite"]["identificacionfirmante"];
    }
    
    //
    $tx = 'Yo, ' . $xNombre . ' identificado con ' .
            ' el número de identificación ' . $xIdentificacion .
            ', atentamente solicito la cancelación de la matrícula mercantil del establecimiento denominado ' . $_SESSION["tramite"]["nombrebase"] . 
            ' matriculado bajo el número ' . $_SESSION["tramite"]["idmatriculabase"] .
            ', atendiendo las siguientes razones:' . chr(13) . chr(10) . chr(13) . chr(10);
    $tx .= 'Motivo : ' . retornarRegistroMysqliApi($dbx, 'mreg_motivos_cancelacion', "id='" . $_SESSION["tramite"]["idmotivocancelacion"] . "'", "descripcion") . chr(13) . chr(10);
    $tx .= 'Observaciones : ' . $_SESSION["tramite"]["motivocancelacion"];

    $tx = \funcionesGenerales::utf8_decode($tx);
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(12);
    $pdf->MultiCell(190, 4, $tx, 0, 'J', 0);
    $pdf->Ln();
    $pdf->Ln();

    if (trim($txtFirmaElectronica) == '') {
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetX(12);
        $pdf->MultiCell(190, 4, 'Nombre: _______________________________________', 0, 'J', 0);
        $pdf->Ln();
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetX(12);
        $pdf->MultiCell(190, 4, \funcionesGenerales::utf8_decode('Identificación: _______________________________________'), 0, 'J', 0);
        $pdf->Ln();
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetX(12);
        $pdf->MultiCell(190, 4, 'Firma: _______________________________________', 0, 'J', 0);
    } else {
        //firmado electronico
        $pdf->SetFont('Helvetica', 'B', 8);
        $pdf->SetX(12);
        $pdf->MultiCell(190, 4, \funcionesGenerales::utf8_decode($txtFirmaElectronica), 0, 'C', 0);
    }
    $pdf->Ln();
    $pdf->Ln();

    /*
      $tx = "Atención: Si está actuando por poder especial anexe su original o fotocopia autenticada. " .
      "En el poder deben especificarse claramente cuales son las facultades otorgadas al apoderado. ";


      $tx = \funcionesGenerales::utf8_decode($tx);
      $pdf->SetFont('Helvetica', '', 10);
      $pdf->SetX(12);
      $pdf->MultiCell(190, 4, $tx, 0, 'J', 0);
      $pdf->Ln();
      $pdf->Ln();
     */

    $name1 = session_id() . '-SolicitudCancelacion.pdf';
    $name = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name1;
    $pdf->Output($name, "F");
    return $name1;
}

?>