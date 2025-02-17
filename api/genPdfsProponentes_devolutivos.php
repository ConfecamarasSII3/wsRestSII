<?php
/**
 * Función que genera el pdf con el formulario del proponente
 *
 * @param 		array		$devolucion	Arreglo con los datos de la devoluciOn
 *
 */
function armarPdfDevolutivo($datos) {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/fpdf186/fpdf.php');

    // Define llamado a librería fpdf
    if (!defined('FPDF_FONTPATH')) {
        define('FPDF_FONTPATH', $_SESSION["generales"]["pathabsoluto"] . '/includes/fpdf186/font/');
    }
    if (!class_exists('PDFDevol')) {

        class PDFDevol extends FPDF {

            function salto($lin) {
                $lin1 = $this->GetY();
                $lin1 = $lin1 + $lin;
                if ($lin1 > 250) {
                    $this->titulo();
                    $lin1 = 40;
                }
                $this->Sety($lin1);
            }

            function titulo() {
                $this->AddPage();
                $this->SetMargins(10, 25, 7);
                $this->Image($_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . CODIGO_EMPRESA . '.jpg', 10, 7, 20, 20);
                $this->Image($_SESSION["generales"]["pathabsoluto"] . '/images/sii/logoconfe.jpg', 180, 9, 20, 14);
                $this->SetFont('Helvetica', 'B', 7);
                $this->SetXY(12, 10);
                $this->Cell(50);
                $this->Cell(100, 4, RAZONSOCIAL, 0, 0, 'C');
                $this->SetFont('Helvetica', 'B', 7);
                $this->SetXY(12, 14);
                $this->Cell(50);
                $this->Cell(100, 4, "REGISTRO UNICO EMPRESARIAL", 0, 0, 'C');
                $this->SetFont('Helvetica', 'B', 7);
                $this->SetXY(12, 18);
                $this->Cell(48);
                $this->Cell(100, 4, "DEVOLUCION DE SOLICITUDES DE INSCRIPCIÓN Y REGISTRO", 0, 0, 'C');
                $this->SetFont('Helvetica', 'B', 7);
                $this->SetXY(12, 22);
                $this->Cell(48);
                $this->Cell(100, 4, "FECHA Y HORA DE GENERACIÓN: " . date("Y/m/d") . " - " . date("H:s:i"), 0, 0, 'C');
                $this->SetFont('Helvetica', 'B', 7);
                $this->SetXY(12, 26);
                $this->Cell(48);
                $this->Cell(100, 4, "CAMARA " . CODIGO_EMPRESA . " AÑO: " . date("Y"), 0, 0, 'C');
            }

        }

    }

    // Imprime encabezados
    $pdf = new PDFDevol("Portrait", "mm", "Letter");
    $pdf->AliasNbPages();
    $pdf->titulo();

    // Datos de la persona que hace el trAmite
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetXY(150, 36);
    $pdf->Write(5, "Devolución No. " . $datos["numerodevolucion"]);
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetXY(150, 40);
    $pdf->Write(5, "Fecha. " . $datos["fechadevolucion"]);
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetXY(150, 44);
    $pdf->Write(5, "Hora. " . $datos["horadevolucion"]);
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetXY(150, 48);
    $pdf->Write(5, "Radicado. " . $datos["idradicacion"]);
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetXY(10, 54);
    $pdf->Write(5, "Señor(es)");
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetXY(10, 58);
    $pdf->Write(5, \funcionesGenerales::utf8_decode($datos["razonsocial"]));

    $pdf->salto(10);
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(10);
    $pdf->Write(5, "Estimado(s) señor(es):");

    $pdf->salto(10);
    $txt = "Nos permitimos informarle que revisada la solicitud de inscripción y registro de documentos ";
    $txt.='por usted(es) radicada no ha podido registrarse ya que se presentaron inconsistencias ';
    $txt.='que nos obligan a devolver el trámite';
    $pdf->SetX(10);
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->MultiCell(185, 5, $txt, 0, 'J');

    $pdf->salto(5);

    $txt = strip_tags(cambiarSustitutoHtml(retornarPantallaPredisenada('mreg.DevolutivoProponentes.Encabezado')));
    $txt = str_replace("&nbsp;", " ", $txt);
    $txt = str_replace("&aacute;", "á", $txt);
    $txt = str_replace("&eacute;", "é", $txt);
    $txt = str_replace("&iacute;", "í", $txt);
    $txt = str_replace("&oacute;", "ó", $txt);
    $txt = str_replace("&uacute;", "ú", $txt);

    // $txt='De acuerdo con lo establecido en el Decreto 4881 de diciembre 31 de 2008 ';
    // $txt.='y demas normas que rigen el Registro de los Proponentes, ';
    // $txt.='las razones para la devolucion de su solicitud son las siguientes:';
    $pdf->SetX(10);
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->MultiCell(185, 5, $txt, 0, 'J');

    foreach ($datos["parrafos"] as $par) {
        $pdf->salto(5);
        $pdf->SetX(10);
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->MultiCell(185, 5, '-- ' . $par, 0, 'J');
    }

    if (trim($datos["observaciones"]) != '') {
        $txt1 = strip_tags($datos["observaciones"]);
        $pdf->salto(5);
        $txt = "Adicionalmente el profesional que hizo el estudio pertinente realizó las ";
        $txt.='siguientes observaciones:';
        $pdf->SetX(10);
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->MultiCell(185, 5, $txt, 0, 'J');
        $pdf->salto(5);
        $pdf->SetX(10);
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->MultiCell(185, 5, trim($txt1), 0, 'J');
    }

    // Nombre del abogado o persona que realizo el estudio
    $pdf->salto(10);
    $pdf->SetX(10);
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->MultiCell(185, 5, 'Documento estudiado por:', 0, 'J');
    $pdf->salto(20);
    $pdf->SetX(10);
    $pdf->SetFont('Helvetica', '', 10);
    if (isset ($datos["nombreusuario"])) {    	
    	$pdf->MultiCell(185, 5, $datos["nombreusuario"], 0, 'J');
    } else {
    	$pdf->MultiCell(185, 5, $_SESSION["generales"]["nombreusuario"], 0, 'J');
    }

    // Utiliza el control I cuando se desea salvar el formulario en el repositorio de imagenes pdf
    // Almacena en repositorio/XX/mreg/checkListProponentes/AAAA/numliq-CheckList-AAAAMMDD.pdf
    if (!is_dir(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"])) {
        mkdir(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"], 0777);
    }
    if (!is_dir(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/mreg/")) {
        mkdir(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/mreg/", 0777);
    }
    if (!is_dir(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/mreg/devolutivos/")) {
        mkdir(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/mreg/devolutivos/", 0777);
    }
    if (!is_dir(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/mreg/devolutivos/" . date("Y"))) {
        mkdir(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/mreg/devolutivos/" . date("Y"), 0777);
    }
    $name = PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/mreg/devolutivos/" . date("Y") . "/" . $datos["numerodevolucion"] . "-Devolutivo-" . date("Ymd") . ".pdf";
    $name1 = "mreg/devolutivos/" . date("Y") . "/" . $datos["numerodevolucion"] . "-Devolutivo-" . date("Ymd") . ".pdf";
    $pdf->Output($name, "F");
    return $name1;
}
?>