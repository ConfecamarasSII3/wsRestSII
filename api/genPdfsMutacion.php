<?php

function armarPdfMutacion($dbx = null, $numrec = '', $numliq = 0, $txtFirmaElectronica = '') {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/fpdf186/fpdf.php');

    // Define llamado a libreria fpdf
    if (!defined('FPDF_FONTPATH')) {
        define('FPDF_FONTPATH', $_SESSION["generales"]["pathabsoluto"] . '/omponents/fpdf186/font/');
    }
    if (!class_exists('PDFCerti')) {

        class PDFMuta extends FPDF {

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
                $this->SetFont('Helvetica', 'B', 9);
                $this->SetXY(12, $i);
                $this->Cell(100, 4, '', 0, 0, 'L');
                $i = $i + 5;
                $this->SetFont('Helvetica', 'B', 9);
                $this->SetXY(12, $i);
                $this->Cell(100, 4, '', 0, 0, 'L');
                $this->Ln();
                $i = $this->GetY();
                $this->SetFont('Helvetica', 'B', 9);
                $this->SetXY(12, $i);
                $this->Cell(100, 4, retornarNombreMunicipioMysqliApi($dbx,MUNICIPIO) . ', ' . \funcionesGenerales::mostrarFechaLetras(date("Ymd")), 0, 0, 'L');
                $this->Ln();
                $this->Ln();
                $i = $this->GetY();
                if (!isset($_SESSION["formulario"]["actualizacionciiuversion4"])) {
                    $_SESSION["formulario"]["actualizacionciiuversion4"] = 'NO';
                }
                if ($_SESSION["formulario"]["actualizacionciiuversion4"] == 'SI') {
                    $this->SetFont('Helvetica', 'B', 9);
                    $this->SetXY(12, $i);
                    $this->Cell(100, 4, 'Ref. CAMBIO EN EL CODIGO CIIU POR ACTUALIZACIÓN A LA VERSION 4.', 0, 0, 'L');
                    $this->Ln();
                    $i = $this->GetY();
                    $this->SetFont('Helvetica', 'B', 9);
                    $this->SetXY(12, $i);
                    $this->Cell(100, 4, \funcionesGenerales::utf8_decode('Número de recuperación : ') . $_SESSION["formulario"]["numrec"], 0, 0, 'L');
                } else {
                    $this->SetFont('Helvetica', 'B', 9);
                    $this->SetXY(12, $i);
                    $this->Cell(100, 4, 'Ref. SOLICITUD DE CAMBIO/ADICION DE ACTIVIDAD ECONOMICA.', 0, 0, 'L');
                    $this->Ln();
                    $i = $this->GetY();
                    $this->SetFont('Helvetica', 'B', 9);
                    $this->SetXY(12, $i);
                    $this->Cell(100, 4, \funcionesGenerales::utf8_decode('Número de recuperación : ') . $_SESSION["formulario"]["numrec"], 0, 0, 'L');
                }
                $this->Ln();
                $this->Ln();
            }

        }

    }


    // Imprime encabezados
    $pdf = new PDFMuta("Portrait", "mm", "Letter");
    $pdf->AliasNbPages();
    $pdf->titulo();
    $pdf->SetFont('Helvetica', 'B', 10);

    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(12);
    $pdf->Cell(100, 4, \funcionesGenerales::utf8_decode('Señor(es)'), 0, 0, 'L');
    $pdf->Ln();
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetX(12);
    $pdf->Cell(100, 4, RAZONSOCIAL, 0, 0, 'L');
    $pdf->Ln();
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetX(12);
    $pdf->Cell(100, 4, \funcionesGenerales::utf8_decode('Departamento de Registros Públicos'), 0, 0, 'L');
    $pdf->Ln();
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetX(12);
    $pdf->Cell(100, 4, retornarNombreMunicipioMysqliApi($dbx,MUNICIPIO), 0, 0, 'L');
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();

    if ($_SESSION["formulario"]["datos"]["organizacion"] == '01') {
        $tx = 'Yo, ' . $_SESSION["formulario"]["datos"]["nombre"] . ' identificado con ' .
                retornarNombreTablasSirepMysqliApi($dbx,'38', $_SESSION["formulario"]["datos"]["tipoidentificacion"]) .
                ' número ' . $_SESSION["formulario"]["datos"]["identificacion"] .
                ' matriculado el registro mercantil bajo el número ' . $_SESSION["formulario"]["datos"]["matricula"] .
                ' obrando en mi propio nombre, atentamente solicito ';
        if ($_SESSION["formulario"]["actualizacionciiuversion4"] == 'SI') {
            $tx .= ' el cambio de mi actividad económica por actualización a la versión 4 A.C., la cual a partir ' .
                    ' de la fecha aparecerá así: ';
        } else {
            $tx .= ' el cambio de mi actividad económica, la cual a partir ' .
                    ' de la fecha aparecerá así: ';
        }
    }
    if ($_SESSION["formulario"]["datos"]["organizacion"] == '02') {
        if (trim($_SESSION["formulario"]["datos"]["propietarios"][1]["nombrepropietario"]) == '') {
            $_SESSION["formulario"]["datos"]["propietarios"][1]["nombrepropietario"] = '_______________________________________________';
            $_SESSION["formulario"]["datos"]["propietarios"][1]["identificacionpropietario"] = '________________________';
            $txttipo = '_______';
        } else {
            $txttipo = retornarNombreTablasSirepMysqliApi($dbx,'38', $_SESSION["formulario"]["datos"]["propietarios"][1]["idtipoidentificacionpropietario"]);
        }

        $tx = 'Yo, ' . $_SESSION["formulario"]["datos"]["propietarios"][1]["nombrepropietario"] . ' identificado con ' .
                $txttipo .
                ' número ' . $_SESSION["formulario"]["datos"]["propietarios"][1]["identificacionpropietario"] .
                ' en calidad de propietario del establecimiento ' . $_SESSION["formulario"]["datos"]["nombre"] .
                ' matriculado bajo el número ' . $_SESSION["formulario"]["datos"]["matricula"] .
                ' atentamente solicito ';
        if ($_SESSION["formulario"]["actualizacionciiuversion4"] == 'SI') {
            $tx .= ' el cambio de la actividad económica por actualización a la versión 4 A.C., la cual a partir ' .
                    ' de la fecha aparecerá así: ';
        } else {
            $tx .= ' el cambio de la actividad económica, la cual a partir ' .
                    ' de la fecha aparecerá así: ';
        }
    }
    if (($_SESSION["formulario"]["datos"]["organizacion"] > '02') && ($_SESSION["formulario"]["datos"]["categoria"] == '1')) {
        if ($txtFirmaElectronica == "") {
            $_SESSION["formulario"]["datos"]["replegal"][1]["nombrereplegal"] = '_______________________________________________';
            $_SESSION["formulario"]["datos"]["replegal"][1]["identificacionreplegal"] = '________________________';
            $txttipo = '_______';
        } else {
            $txttipo = retornarNombreTablasSirepMysqliApi($dbx,'38', $_SESSION["tramite"]["tipoidefirmante"]);
            $_SESSION["formulario"]["datos"]["replegal"][1]["nombrereplegal"] = $_SESSION["tramite"]["nombre1firmante"] . ' ' . $_SESSION["tramite"]["nombre2firmante"] . ' ' . $_SESSION["tramite"]["apellido1firmante"] . ' ' . $_SESSION["tramite"]["apellido2firmante"];
            $_SESSION["formulario"]["datos"]["replegal"][1]["identificacionreplegal"] = $_SESSION["tramite"]["identificacionfirmante"];
        }
        $tx = 'Yo, ' . $_SESSION["formulario"]["datos"]["replegal"][1]["nombrereplegal"] . ' identificado con ' .
                $txttipo .
                ' número ' . $_SESSION["formulario"]["datos"]["replegal"][1]["identificacionreplegal"] .
                ' en calidad de representante legal de la persona jurídica ' . $_SESSION["formulario"]["datos"]["nombre"] .
                ' matriculada/inscrita bajo el número ' . $_SESSION["formulario"]["datos"]["matricula"] .
                ' atentamente solicito ';
        if ($_SESSION["formulario"]["actualizacionciiuversion4"] == 'SI') {
            $tx .= ' el cambio de la actividad económica por actualización a la versión 4 A.C., la cual a partir ' .
                    ' de la fecha aparecerá así: ';
        } else {
            $tx .= ' el cambio de la actividad económica, la cual a partir ' .
                    ' de la fecha aparecerá así: ';
        }
    }
    if (($_SESSION["formulario"]["datos"]["organizacion"] > '02') && ($_SESSION["formulario"]["datos"]["categoria"] == '2')) {
        if
        ((!isset($_SESSION["tramite"]["identificacionfirmante"]))) {
            $_SESSION["formulario"]["datos"]["replegal"][1]["nombrereplegal"] = '_______________________________________________';
            $_SESSION["formulario"]["datos"]["replegal"][1]["identificacionreplegal"] = '________________________';
            $txttipo = '_______';
        } else {
            $txttipo = retornarNombreTablasSirepMysqliApi($dbx,'38', $_SESSION["tramite"]["tipoidefirmante"]);
            $_SESSION["formulario"]["datos"]["replegal"][1]["nombrereplegal"] = $_SESSION["tramite"]["nombre1firmante"] . ' ' . $_SESSION["tramite"]["nombre2firmante"] . ' ' . $_SESSION["tramite"]["apellido1firmante"] . ' ' . $_SESSION["tramite"]["apellido2firmante"];
            $_SESSION["formulario"]["datos"]["replegal"][1]["identificacionreplegal"] = $_SESSION["tramite"]["identificacionfirmante"];
        }
        $tx = 'Yo, ' . $_SESSION["formulario"]["datos"]["replegal"][1]["nombrereplegal"] . ' identificado con ' .
                $txttipo .
                ' número ' . $_SESSION["formulario"]["datos"]["replegal"][1]["identificacionreplegal"] .
                ' en calidad de representante legal de la persona jurídica ' . $_SESSION["formulario"]["datos"]["nombre"] .
                ' matriculada bajo el número ' . $_SESSION["formulario"]["datos"]["matricula"] .
                ' atentamente solicito ';
        if ($_SESSION["formulario"]["actualizacionciiuversion4"] == 'SI') {
            $tx .= ' el cambio de la actividad económica por actualización a la versión 4 A.C., la cual a partir ' .
                    ' de la fecha aparecerá así: ';
        } else {
            $tx .= ' el cambio de la actividad económica, la cual a partir ' .
                    ' de la fecha aparecerá así: ';
        }
    }
    if (($_SESSION["formulario"]["datos"]["organizacion"] > '02') && ($_SESSION["formulario"]["datos"]["categoria"] == '3')) {
        if (
                (!isset($_SESSION["formulario"]["datos"]["nombreadministrador"])) ||
                (trim($_SESSION["formulario"]["datos"]["nombreadministrador"]))) {
            $_SESSION["formulario"]["datos"]["nombreadministrador"] = '_______________________________________________';
            $_SESSION["formulario"]["datos"]["identificacionadministrador"] = '________________________';
            $txttipo = '_______';
        } else {
            $txttipo = retornarNombreTablasSirepMysqliApi($dbx,'38', $_SESSION["formulario"]["datos"]["idtipoidentificacionadministrador"]);
        }
        $tx = 'Yo, ' . $_SESSION["formulario"]["datos"]["nombreadministrador"] . ' identificado con ' .
                $txttipo .
                ' número ' . $_SESSION["formulario"]["datos"]["identificacionadministrador"] .
                ' en calidad de administrador de la agencia ' . $_SESSION["formulario"]["datos"]["nombre"] .
                ' matriculada bajo el número ' . $_SESSION["formulario"]["datos"]["matricula"] .
                ' atentamente solicito ';
        if ($_SESSION["formulario"]["actualizacionciiuversion4"] == 'SI') {
            $tx .= ' el cambio de la actividad económica por actualización a la versión 4 A.C., la cual a partir ' .
                    ' de la fecha aparecerá así: ';
        } else {
            $tx .= ' el cambio de la actividad económica, la cual a partir ' .
                    ' de la fecha aparecerá así: ';
        }
    }


    $tx = \funcionesGenerales::utf8_decode($tx);

    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(12);
    $pdf->MultiCell(190, 4, $tx, 0, 'J', 0);
    $pdf->Ln();
    $pdf->Ln();

    //
    $txtCiiu1 = $_SESSION["formulario"]["datos"]["ciius"][1];
    $txtCiiu2 = $_SESSION["formulario"]["datos"]["ciius"][2];
    $txtCiiu3 = $_SESSION["formulario"]["datos"]["ciius"][3];
    $txtCiiu4 = $_SESSION["formulario"]["datos"]["ciius"][4];

    $tem = retornarRegistrosMysqliApi($dbx,'bas_ciius', "1=1", "idciiunum", 0, 0, array(), 'replicabatch');
    foreach ($tem as $t) {
        if ($t["idciiu"] == $_SESSION["formulario"]["datos"]["ciius"][1]) {
            $txtCiiu1 .= ' *** ' . $t["descripcion"];
        }
        if ($t["idciiu"] == $_SESSION["formulario"]["datos"]["ciius"][2]) {
            $txtCiiu2 .= ' *** ' . $t["descripcion"];
        }

        if ($t["idciiu"] == $_SESSION["formulario"]["datos"]["ciius"][3]) {
            $txtCiiu3 .= ' *** ' . $t["descripcion"];
        }

        if ($t["idciiu"] == $_SESSION["formulario"]["datos"]["ciius"][4]) {
            $txtCiiu4 .= ' *** ' . $t["descripcion"];
        }
    }
    unset($tem);

    //

    $pdf->Ln();
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(12);
    $pdf->MultiCell(190, 4, 'CIIU Principal: ' . $txtCiiu1, 0, 'J', 0);
    $pdf->Ln();

    if (trim($_SESSION["formulario"]["datos"]["feciniact1"]) != '') {
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetX(12);
        $pdf->MultiCell(190, 4, 'Fecha de inicio de la actividad principal: ' . $_SESSION["formulario"]["datos"]["feciniact1"], 0, 'J', 0);
        $pdf->Ln();
    }

    if (trim($_SESSION["formulario"]["datos"]["ciius"][2]) != '') {
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetX(12);
        $pdf->MultiCell(190, 4, 'CIIU Secundario: ' . $txtCiiu2, 0, 'J', 0);
        $pdf->Ln();
    }

    if (trim($_SESSION["formulario"]["datos"]["feciniact2"]) != '') {
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetX(12);
        $pdf->MultiCell(190, 4, 'Fecha de inicio de la actividad secundaria: ' . $_SESSION["formulario"]["datos"]["feciniact2"], 0, 'J', 0);
        $pdf->Ln();
    }

    if (trim($_SESSION["formulario"]["datos"]["ciius"][3]) != '') {
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetX(12);
        $pdf->MultiCell(190, 4, 'Otras Actividades: ' . $txtCiiu3, 0, 'J', 0);
        $pdf->Ln();
    }

    if (trim($_SESSION["formulario"]["datos"]["ciius"][4]) != '') {
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetX(12);
        $pdf->MultiCell(190, 4, 'Otras Actividades: ' . $txtCiiu4, 0, 'J', 0);
        $pdf->Ln();
    }

    if (trim($_SESSION["formulario"]["datos"]["desactiv"]) != '') {
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetX(12);
        $pdf->MultiCell(190, 4, 'Descripción de la actividad: ' . $_SESSION["formulario"]["datos"]["desactiv"], 0, 'J', 0);
        $pdf->Ln();
    }

    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();

    if (trim($txtFirmaElectronica) == '') {
        //Firmado manual
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetX(12);
        $pdf->MultiCell(190, 4, 'Nombre: _______________________________________', 0, 'J', 0);
        $pdf->Ln();
        $pdf->Ln();
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetX(12);
        $pdf->MultiCell(190, 4, \funcionesGenerales::utf8_decode('Identificación: _______________________________________'), 0, 'J', 0);
        $pdf->Ln();
        $pdf->Ln();
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetX(12);
        $pdf->MultiCell(190, 4, 'Firma: _______________________________________', 0, 'J', 0);
    } else {
        //firmado electrónico
        $pdf->SetFont('Helvetica', 'B', 8);
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
      $pdf->SetFont('Helvetica', '', 10);
      $pdf->SetX(12);
      $pdf->MultiCell(190, 4, $tx, 0, 'J', 0);
      $pdf->Ln();
      $pdf->Ln();
      }
     */

    $tx = "Atención: Si está actuando por poder especial anexe su original o fotocopia autenticada. " .
            "En el poder deben especificarse claramente cuales son las facultades otorgadas al apoderado. " .
            "correspondiente.";

    $tx = \funcionesGenerales::utf8_decode($tx);


    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(12);
    $pdf->MultiCell(190, 4, $tx, 0, 'J', 0);
    $pdf->Ln();
    $pdf->Ln();

    $name = session_id() . '-Mutacion-' . date("Ymd") . '-' . date("His") . '.pdf';
    $name1 = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name;
    $pdf->Output($name1, "F");
    return $name;
}

?>