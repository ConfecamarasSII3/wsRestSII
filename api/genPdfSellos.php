<?php

/**
 * 
 * @param type $dbx
 * @param type $registro
 * @param type $libro
 * @param type $numreg
 * @param type $fecha
 * @param type $hora
 * @param type $acto
 * @param type $txtacto
 * @param string $expediente
 * @param type $nombre
 * @param type $nit
 * @param type $usuarioFirma
 * @param type $codigolibro
 * @return boolean|string
 */
function generarSelloRegistros($dbx = null, $registro = '', $libro = '', $numreg = '', $fecha = '', $hora = '', $acto = '', $txtacto = '', $expediente = '', $nombre = '', $nit = '', $usuarioFirma = '', $codigolibro = '') {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/fpdf186/fpdf_protection.php');
    if (!defined('FPDF_FONTPATH')) {
        define('FPDF_FONTPATH', $_SESSION["generales"]["pathabsoluto"] . '/components/fpdf186/font/');
    }
    if (!class_exists('PDFSello')) {

        class PDFSello extends FPDF {
            
        }

    }

    // $dimension = array(220, 140);
    $pdf = new PDFSello("Portrait", "mm", 'Letter');
    $pdf->AliasNbPages();
    $pdf->AddPage();
    if (file_exists($_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . CODIGO_EMPRESA . '.jpg')) {
        $pdf->Image($_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . CODIGO_EMPRESA . '.jpg', 10, 7, 25, 25);
    }
    $pdf->SetXY(30, 50);
    $pdf->Ln();
    $pdf->Ln();

    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->MultiCell(180, 4, \funcionesGenerales::utf8_decode("CONSTANCIA DE INSCRIPCIÓN EN LOS REGISTROS PÚBLICOS QUE ADMINISTRA Y OPERA LA CÁMARA DE COMERCIO"), 0, "C");
    $pdf->Ln();
    $pdf->Ln();

    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->MultiCell(180, 4, RAZONSOCIAL, 0, 'C');
    $pdf->Ln();
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->Cell(180, 4, "Nit. " . NIT, 0, 0, 'C');
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();

    //    
    if (substr($libro, 0, 2) == 'RM') {
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->Cell(180, 4, "DEL REGISTRO MERCANTIL", 0, 0, "C");
    }

    if (substr($libro, 0, 2) == 'RE') {
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->Cell(180, 4, \funcionesGenerales::utf8_decode("DEL REGISTRO DE LAS ENTIDADES SIN ÁNIMO DE LUCRO"), 0, 0, "C");
    }
    
    if (substr($libro, 0, 2) == 'RP' || substr($libro, 0, 2) == 'PR') {
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->Cell(180, 4, \funcionesGenerales::utf8_decode("DEL REGISTRO DE PROPONENTES"), 0, 0, "C");
    }
    
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();

    //
    $librox = '';
    switch ($libro) {
        case "RE51" : $librox = 'I';
            break;
        case "RE52" : $librox = 'II';
            break;
        case "RE53" : $librox = 'III';
            break;
        case "RE54" : $librox = 'IV';
            break;
        case "RE55" : $librox = 'V';
            break;
        case "RM01" : $librox = 'I';
            break;
        case "RM02" : $librox = 'II';
            break;
        case "RM03" : $librox = 'II';
            break;
        case "RM04" : $librox = 'IV';
            break;
        case "RM05" : $librox = 'V';
            break;
        case "RM06" : $librox = 'VI';
            break;
        case "RM07" : $librox = 'VII';
            break;
        case "RM08" : $librox = 'VIII';
            break;
        case "RM09" : $librox = 'IX';
            break;
        case "RM10" : $librox = 'X';
            break;
        case "RM11" : $librox = 'XI';
            break;
        case "RM12" : $librox = 'XII';
            break;
        case "RM13" : $librox = 'XIII';
            break;
        case "RM14" : $librox = 'XIV';
            break;
        case "RM15" : $librox = 'XV';
            break;
        case "RM16" : $librox = 'XVI';
            break;
        case "RM17" : $librox = 'XVII';
            break;
        case "RM18" : $librox = 'XVIII';
            break;
        case "RM19" : $librox = 'XIX';
            break;
        case "RM20" : $librox = 'XX';
            break;
        case "RM21" : $librox = 'XXI';
            break;
        case "RM22" : $librox = 'XXII';
            break;
        case "RP01" : $librox = 'I';
            break;
    }
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->Cell(180, 4, "Libro: " . $librox, 0, 0, 'C');
    $pdf->Ln();

    //
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->Cell(180, 4, "Numero Registro: " . $numreg, 0, 0, 'C');
    $pdf->Ln();
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->Cell(180, 4, "Fecha: " . \funcionesGenerales::mostrarFecha($fecha), 0, 0, 'C');
    $pdf->Ln();
    if (trim($hora) != '') {
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->Cell(180, 4, "Hora: " . \funcionesGenerales::mostrarHora($hora), 0, 0, 'C');
        $pdf->Ln();
    }
    $pdf->Ln();

    //2017-11-30 WSIERRA:  Blanquear $expediente si cumple las siguientes opciones
    switch ($expediente) {
        case 'NUEVANAT':
        case 'NUEVAJUR':
        case 'NUEVAEST':
        case 'NUEVASUC':
        case 'NUEVAAGE':
        case 'NUEVAESA':
            $expediente = '';
            break;
        default:
            break;
    }
    //
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->Cell(180, 4, "Expediente: " . $expediente, 0, 0, 'C');
    $pdf->Ln();
    if (trim($nit) != '') {
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->Cell(180, 4, \funcionesGenerales::utf8_decode("Identificación: ") . $nit, 0, 0, 'C');
        $pdf->Ln();
    }
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->MultiCell(180, 4, "Nombre: " . \funcionesGenerales::utf8_decode($nombre), 0, 'C');
    $pdf->Ln();
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->Cell(180, 4, "Acto: " . $acto, 0, 0, 'C');
    $pdf->Ln();
    $pdf->Ln();
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->MultiCell(180, 4, "Noticia: " . \funcionesGenerales::utf8_decode($txtacto), 0, 'C');
    if ($codigolibro != '') {
        $pdf->MultiCell(180, 4, \funcionesGenerales::utf8_decode("Código del libro: ") . $codigolibro, 0, 'C');
    }
    $pdf->Ln();
    $pdf->Ln();
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->Cell(180, 4, "El secretario (o su delegado)", 0, 0, 'C');
    $pdf->Ln();
    $y = $pdf->GetY();

    //
    if (trim($usuarioFirma) == '') {
        if ($_SESSION["generales"]["codigousuario"] == '' || $_SESSION["generales"]["codigousuario"] == 'USUPUBXX') {
            $usuarioFirma = retornarClaveValorMysqliApi($dbx, '90.01.01');
        } else {
            $usuarioFirma = $_SESSION["generales"]["codigousuario"];
        }
    }

    $usuFirma = retornarRegistroMysqliApi($dbx, 'usuariosfirmas', "idusuario='" . $usuarioFirma . "'");
    if ($usuFirma === false || empty($usuFirma)) {
        $usuarioFirma = retornarClaveValorMysqliApi($dbx, '90.01.01');
        $usuFirma = retornarRegistroMysqliApi($dbx, 'usuariosfirmas', "idusuario='" . $usuarioFirma . "'");
    }

    if ($usuFirma === false || empty($usuFirma)) {
        return false;
    }

    $f = fopen($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . session_id() . '-firma.jpg', "wb");
    fwrite($f, $usuFirma["firma"]);
    fclose($f);
    unset($usuFirma);

    $pdf->Image($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . session_id() . '-firma.jpg', 90, $y, 20, 20);
    unlink($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . session_id() . '-firma.jpg');
    $name1 = $_SESSION["generales"]["codigoempresa"] . '-' . $registro . '-' . date("Ymd") . '-' . date("His") . '-' . $libro . '-' . $numreg . '.pdf';
    $name = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name1;
    $pdf->Output($name, "F");

    \logApi::general2('GeneracionSellosInscripciones_' . date("Ymd"), '', 'Sello - ' . $libro . '-' . $numreg . ', sellado por: ' . $usuarioFirma);
    return $name1;
}

?>