<?php

/**
 * 
 * @param type $dbx
 * @param type $numrec
 * @param type $numliq
 * @param type $txtFirmaElectronica
 * @param type $txtFirmaManuscrita
 * @param type $nombreFirmante
 * @param type $numIdFirmante
 * @param type $fechaimprimir
 * @return string
 */
function armarPdfFormatoAfiliacion($dbx = null, $numrec = '', $numliq = 0, $txtFirmaElectronica = '', $txtFirmaManuscrita = '', $nombreFirmante = '', $numIdFirmante = '', $fechaimprimir = '') {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/tcpdf_6.7.5/tcpdf.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/components/tcpdf_6.7.5/examples/lang/eng.php');
    // require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/Encoding.php');

    if (!class_exists('PDFCerti')) {

        class PDFCerti extends TCPDF {
            public $fechaimprimir = '';
            
            function Header() {
                $i = 0;
                // $this->AddPage();
                // $this->SetMargins(20, 40, 10);
                if (file_exists($_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . CODIGO_EMPRESA . '.jpg')) {
                    $this->Image($_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . CODIGO_EMPRESA . '.jpg', 150, 10, 35, 28);
                }
                // $this->Image($_SESSION["generales"]["pathabsoluto"] . '/images/logocamara' . CODIGO_EMPRESA . '.jpg', 150, 20, 45, 28);
                $i = $i + 5;
                $this->SetFont('Helvetica', 'B', 10);
                $this->SetXY(20, $i);
                $this->Cell(100, 4, '', 0, 0, 'L');
                $i = $i + 5;
                $this->SetFont('Helvetica', 'B', 10);
                $this->SetXY(20, $i);
                $this->Cell(100, 4, '', 0, 0, 'L');
                $this->Ln();
                $i = $this->GetY();
                $this->SetFont('Helvetica', 'B', 10);
                $this->SetXY(20, $i);
                if ($this->fechaimprimir == '') {
                    $this->Cell(100, 4, retornarNombreMunicipioMysqliApi(null,MUNICIPIO) . ', ' . \funcionesGenerales::mostrarFechaLetras(date("Ymd")), 0, 0, 'L');
                } else {
                    $this->Cell(100, 4, retornarNombreMunicipioMysqliApi(null,MUNICIPIO) . ', ' . \funcionesGenerales::mostrarFechaLetras($this->fechaimprimir), 0, 0, 'L');
                }
                $this->Ln();
                $this->Ln();
                $i = $this->GetY();
                $this->SetFont('Helvetica', 'B', 9);
                $this->SetXY(20, $i);
                $this->Cell(100, 4, 'Ref. FORMATO RENOVACION AFILIACION.', 0, 0, 'L');
                $this->Ln();
                $i = $this->GetY();
                $this->SetFont('Helvetica', 'B', 9);
                $this->SetXY(20, $i);
                $this->Cell(100, 4, ('Número de recuperación : ') . $_SESSION["tramite"]["numerorecuperacion"], 0, 0, 'L');
                $this->Ln();
                $this->Ln();
                $i = $this->GetY();
                $this->SetY(i);
            }

        }

    }

    //echo "entro a pdf armar mutacion<br>";
    // Imprime encabezados
    $pdf = new PDFCerti(PDF_PAGE_ORIENTATION, PDF_UNIT, 'LETTER', true, 'UTF-8', false, true);
    $pdf->fechaimprimir = $fechaimprimir;
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('SII');
    $pdf->SetTitle('Solicitud');
    $pdf->SetSubject('Solicitud');
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    $pdf->SetMargins(20, 40, 10);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    $pdf->SetAutoPageBreak(true, 15);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    $pdf->setLanguageArray($l);
    $pdf->AddPage();
    // $pdf->titulo();
    $pdf->SetFont('Helvetica', 'B', 10);

    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(20);
    $pdf->Cell(100, 4, ('Señor(es)'), 0, 0, 'L');
    $pdf->Ln();
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetX(20);
    $pdf->Cell(100, 4, RAZONSOCIAL_RESUMIDA, 0, 0, 'L');
    $pdf->Ln();
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetX(20);
    $pdf->Cell(100, 4, ('Departamento de Registros Públicos'), 0, 0, 'L');
    $pdf->Ln();
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetX(20);
    $pdf->Cell(100, 4, retornarNombreMunicipioMysqliApi(null,MUNICIPIO), 0, 0, 'L');
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();

    //
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(20);
    $pdf->writeHTML('FORMATO DE RENOVACION DE AFILIACION', true, false, true, false, 'C');
    $pdf->Ln();

    //
    if ($_SESSION["tramite"]["fecharecibo"] != '') {
        $tx = '<strong>Fecha de pago :</strong> ' . $_SESSION["tramite"]["fecharecibo"] . '<br>';
        $tx .= '<strong>Número del recibo de pago :</strong> ' . $_SESSION["tramite"]["numerorecibo"] . '<br>';
    } else {
        $tx = '<strong>Fecha de solicitud :</strong> ' . date("Y-m-d") . '<br>';
    }
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(20);
    $pdf->writeHTML($tx, true, false, true, false, 'J');
    $pdf->Ln();

    //
    $tx = '<strong>Razón social o nombre :</strong> ' . $_SESSION["formulario"]["datos"]["nombre"] . '<br>';
    $tx .= '<strong>Nit o identificación :</strong> ' . $_SESSION["formulario"]["datos"]["identificacion"] . '<br>';
    $tx .= '<strong>Matrícula :</strong> ' . $_SESSION["formulario"]["datos"]["matricula"] . '<br>';
    $tx .= '<strong>Fecha de matrícula :</strong> ' . \funcionesGenerales::mostrarFecha($_SESSION["formulario"]["datos"]["fechamatricula"]) . '<br>';
    $tx .= '<strong>Email :</strong> ' . $_SESSION["formulario"]["datos"]["emailcom"] . '<br>';
    $tx .= '<strong>Teléfonos :</strong> ' . $_SESSION["formulario"]["datos"]["telcom1"] . ' ' . $_SESSION["formulario"]["datos"]["telcom2"] . ' ' . $_SESSION["formulario"]["datos"]["celcom"] . '<br>';
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(20);
    $pdf->writeHTML($tx, true, false, true, false, 'J');
    $pdf->Ln();

    //
    if (isset($_SESSION["formulario"]["datos"]["replegal"]) && !empty($_SESSION["formulario"]["datos"]["replegal"])) {
        $tx = '<strong>Representantes legales</strong><br>';
        foreach ($_SESSION["formulario"]["datos"]["replegal"] as $rep) {
            $tide = '';
            switch ($rep["idtipoidentificacionreplegal"]) {
                case "1" : $tide = 'CC';
                    break;
                case "2" : $tide = 'NIT';
                    break;
                case "3" : $tide = 'TI';
                    break;
                case "4" : $tide = 'CE';
                    break;
                case "5" : $tide = 'PASS';
                    break;
                case "E" : $tide = 'DE';
                    break;
            }
            $tx .= $tide . ' - ' . $rep["identificacionreplegal"] . ' ' . $rep["nombrereplegal"] . '<br>';
        }
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetX(20);
        $pdf->writeHTML($tx, true, false, true, false, 'J');
        $pdf->Ln();
    }
    $pdf->Ln();

    //
    $tx = '<strong>CLAUSULA DE AUTORIZACIÓN : </strong>';
    $tx .= 'Con el fin de desarrollar mi relación como afiliado y así posibilitar mi ';
    $tx .= 'contacto para obtener los beneficios que tengo, autorizo: <br><br>';
    $tx .= '- El uso de mis datos para las funciones públicas y privadas de la entidad.<br><br>';
    $tx .= '- Mantener mi información personal durante el tiempo de mi afiliación y los cuatro años siguientes a mi desafiliación.<br><br>';
    $tx .= '- Que la Cámara de Comercio efectue las verificaciones necesarias para cumplir con los requisitos exigidos en la Ley 1727 del 11 de junio de 2014 y<br><br>';
    $tx .= '- Declaro que soy titular de la información reportada en este formulario para autorizar el tratamiento de los datos que he suministrado de forma voluntaria, completa, confiable, veraz, exacta y verídica.';
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(20);
    $pdf->writeHTML($tx, true, false, true, false, 'J');
    $pdf->Ln();
    $pdf->Ln();

    $tx = '<strong>MANIFIESTO : </strong>';
    $tx .= 'En calidad de representante legal o comerciante inscrito, manifiesto bajo la gravedad de juramento que:<br><br>';
    $tx .= 'a) Tengo inscritos en el registro mercantil todos los actos, libros y documentos respecto de los cuales la Ley me exige esa formalidad.<br><br>';
    $tx .= 'b) Cumplo con la obligación legal de llevar la contabilidad regular en debida forma y de conservar la correspondencia y demás documentos relacionados con mi negocio o mis actividades reportadas en el m omento de la matrícula.<br><br>';
    $tx .= 'c) Manifiesto que no he ejecutado ningún acto de competencia desleal, entendida dicha competencia desleal como todo acto o hecho que se realice en el mercado con fines concurrenciales, cuando resulte contrario a las sanas costumbres mercantiles, al principio de la buena fe comercial, a los usos honestos en materia industrial o comercial, o bien cuando esté encaminado a afectar o afecte la libre decisión del comprador o consumidor, o el funcionamiento concurrencial del mercado.<br><br>';
    $tx .= 'd) Autorizo a la Cámara de Comercio de Ibagué para que haga las comprobaciones que considere necesarias, en cualquier tiempo y entiendo que puedo perder la calidad de afiliado en los casos previstos en la Ley, en especial cuando no cumpla con las obligaciones de comerciante o los requisitos establecidos en el reglamento de afiliados de la entidad.<br><br>';
    $tx .= 'e) Acepto que conozco y cumplo el reglamento de afiliados<br><br>';
    $tx .= 'f) Acepto que la falsedad en los datos que se suministren, será sancionada de acuerdo cal Código penal y que la Cámara está obligada a formular denuncia ante el juez competente.<br><br>';
    $tx .= 'g) Acepto que la firma del, formulario hace entender que las afirmaciones aquí contenidas se hacen bajo la gravedad de juramento y además que conozco el reglamento de afiliados y acepto cada una de sus estipulaciones.<br><br>';
    $tx .= 'Acredito que no me encuentro incurso en ninguna de las siguientes circunstancias:<br><br>';
    $tx .= 'a) No he sido sancionado en procesos de responsabilidad disciplinaria con destitución o inhabilidad para el ejercicio de las funciones públicas,<br><br>';
    $tx .= 'b) No he sido condenado penalmente por delitos dolosos,<br><br>';
    $tx .= 'c) No he sido condenado en procesos de responsabilidad fiscal,<br><br>';
    $tx .= 'd) No he sido excluido o suspendido del ejercicio profesional del comercio o de mi actividad profesional,<br><br>';
    $tx .= 'e) No estoy incluido en listas inhibitorias por lavado de acivos, financiación del terrorismo o cualquier actividad ilícita.';
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(20);
    $pdf->writeHTML($tx, true, false, true, false, 'J');
    $pdf->Ln();
    $pdf->Ln();

    //
    $tx = '<strong>Entiendo que el hecho de pagar el valor correspondiente a la cuota anual de afiliación no implica su aceptación inmediata, toda vez que esta decisión corresponde al comité de afiliación de la Cámara de Comercio.</strong>';
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetX(20);
    $pdf->writeHTML($tx, true, false, true, false, 'J');
    $pdf->Ln();
    $pdf->Ln();
    

    if (trim($txtFirmaElectronica) == '' && $txtFirmaManuscrita == '') {
        //Firmado manual
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetX(20);
        $pdf->Cell(190, 4, 'Nombre: _______________________________________', 0, 0, 'L', 0);
        $pdf->Ln();
        $pdf->Ln();
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetX(20);
        $pdf->Cell(190, 4, ('Identificación: _______________________________________'), 0, 0, 'L', 0);
        $pdf->Ln();
        $pdf->Ln();
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetX(20);
        $pdf->Cell(190, 4, 'Firma: _______________________________________', 0, 0, 'L', 0);
    }
    if (trim($txtFirmaElectronica) != '') {
        //firmado electrónico
        $pdf->SetFont('Helvetica', 'B', 8);
        $pdf->SetX(20);
        $pdf->MultiCell(180, 4, ($txtFirmaElectronica), 0, 'J', 0);
    }
    if (trim($txtFirmaManuscrita) != '') {
        //Firmado manual
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetX(20);
        $pdf->Cell(190, 4, 'Nombre: ' . $nombreFirmante, 0, 0, 'L', 0);
        $pdf->Ln();
        $pdf->Ln();
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetX(20);
        $pdf->Cell(180, 4, ('Identificación: ' . $numIdFirmante), 0, 0, 'L', 0);
        $pdf->Ln();
        $pdf->Ln();
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetX(20);
        $pdf->Cell(180, 4, 'Firma: ', 0, 0, 'L', 0);
        $posY = $pdf->GetY();
        $tmpfile = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . rand(1000000, 9999999) . '-' . date("Ymd") . '-' . date("His") . '.jpg';
        $f = fopen($tmpfile, "wb");
        fwrite($f, base64_decode($txtFirmaManuscrita));
        fclose($f);
        $pdf->Image($tmpfile, 40, $posY, 40, 30);
        unlink($tmpfile);
    }

    $pdf->Ln();
    $pdf->Ln();



    $name = session_id() . '-FormatoRenovacionAfiliacion-' . date("Ymd") . '-' . date("His") . '.pdf';
    $pdf->Output($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name, "F");
    return $name;
}

?>