<?php
require('../../includes/fpdf153/fpdf.php');

class PDF extends FPDF {
	//Cabecera de página
	function Header() {
    	$this->Image('../../images/logocamara'.$_SESSION["generales"]["codigoempresa"].'.jpg',10,8,20);
    	
    	// Cuando sea portrait
    	if ($this->DefOrientation=='P') {
    		$this->SetFont('Arial','B',8);$this->Cell(170,8,RAZONSOCIAL,0,0,'C');
    		$this->SetFont('Arial','',8);$this->Cell(10,8,date("Y/m/d"),0,0,'R');
    		$this->Ln(4);
    		$this->SetFont('Arial','B',8);$this->Cell(170,8,$_SESSION["pdf"]["tituloinforme"],0,0,'C');
    		$this->SetFont('Arial','',8);$this->Cell(10,8,'Página '.$this->PageNo().'/{nb}',0,0,'C');
    		if (isset($_SESSION["pdf"]["tituloinforme2"])) {
    			if (trim($_SESSION["pdf"]["tituloinforme2"])!='') {
    				$this->Ln(4);
    				$this->SetFont('Arial','B',8);$this->Cell(170,8,$_SESSION["pdf"]["tituloinforme2"],0,0,'C');    		
    			}
    		}
    		if (isset($_SESSION["pdf"]["tituloinforme3"])) {
    			if (trim($_SESSION["pdf"]["tituloinforme3"])!='') {
    				$this->Ln(4);
    				$this->SetFont('Arial','B',8);$this->Cell(170,8,$_SESSION["pdf"]["tituloinforme3"],0,0,'C');    		
    			}
    		}
    		if (isset($_SESSION["pdf"]["tituloinforme4"])) {
    			if (trim($_SESSION["pdf"]["tituloinforme4"])!='') {
    				$this->Ln(4);
    				$this->SetFont('Arial','B',8);$this->Cell(170,8,$_SESSION["pdf"]["tituloinforme4"],0,0,'C');    		
    			}
    		}
    		if (isset($_SESSION["pdf"]["tituloinforme5"])) {
    			if (trim($_SESSION["pdf"]["tituloinforme5"])!='') {
    				$this->Ln(4);
    				$this->SetFont('Arial','B',8);$this->Cell(170,8,$_SESSION["pdf"]["tituloinforme5"],0,0,'C');    		
    			}
    		}    	
    		
    		$this->Ln(10);
    		$y=$this->GetY()+5;    	
    		$this->Line(5,$y,200,$y);
    		$this->Ln(5);
    	}
    	
    	// Cuando Sea Legal y LandScape
    	if (($this->DefOrientation=='L') && ($this->TxtFormat=='legal')) {
    		$this->SetFont('Arial','B',8);$this->Cell(320,8,RAZONSOCIAL,0,0,'C');
    		$this->SetFont('Arial','',8);$this->Cell(10,8,date("Y/m/d"),0,0,'R');
    		$this->Ln(4);
    		$this->SetFont('Arial','B',8);$this->Cell(320,8,$_SESSION["pdf"]["tituloinforme"],0,0,'C');
    		$this->SetFont('Arial','',8);$this->Cell(10,8,'Página '.$this->PageNo().'/{nb}',0,0,'C');
    		if (isset($_SESSION["pdf"]["tituloinforme2"])) {
    			if (trim($_SESSION["pdf"]["tituloinforme2"])!='') {
    				$this->Ln(4);
    				$this->SetFont('Arial','B',8);$this->Cell(320,8,$_SESSION["pdf"]["tituloinforme2"],0,0,'C');    		
    			}
    		}
    		if (isset($_SESSION["pdf"]["tituloinforme3"])) {
    			if (trim($_SESSION["pdf"]["tituloinforme3"])!='') {
    				$this->Ln(4);
    				$this->SetFont('Arial','B',8);$this->Cell(320,8,$_SESSION["pdf"]["tituloinforme3"],0,0,'C');    		
    			}
    		}
    		if (isset($_SESSION["pdf"]["tituloinforme4"])) {
    			if (trim($_SESSION["pdf"]["tituloinforme4"])!='') {
    				$this->Ln(4);
    				$this->SetFont('Arial','B',8);$this->Cell(320,8,$_SESSION["pdf"]["tituloinforme4"],0,0,'C');    		
    			}
    		}    	
    		$this->Ln(10);
    		$y=$this->GetY()+5;    	
    		$this->Line(5,$y,340,$y);
    		$this->Ln(5);
    	}
    	
    	// Cuando sea diferente de Legal y landscape
		if (($this->DefOrientation=='L') && ($this->TxtFormat!='legal')) {
    		$this->SetFont('Arial','B',8);$this->Cell(280,8,RAZONSOCIAL,0,0,'C');
    		$this->SetFont('Arial','',8);$this->Cell(10,8,date("Y/m/d"),0,0,'R');
    		$this->Ln(4);
    		$this->SetFont('Arial','B',8);$this->Cell(280,8,$_SESSION["pdf"]["tituloinforme"],0,0,'C');
    		$this->SetFont('Arial','',8);$this->Cell(10,8,'Página '.$this->PageNo().'/{nb}',0,0,'C');
    		if (isset($_SESSION["pdf"]["tituloinforme2"])) {
    			if (trim($_SESSION["pdf"]["tituloinforme2"])!='') {
    				$this->Ln(4);
    				$this->SetFont('Arial','B',8);$this->Cell(280,8,$_SESSION["pdf"]["tituloinforme2"],0,0,'C');    		
    			}
    		}
    		if (isset($_SESSION["pdf"]["tituloinforme3"])) {
    			if (trim($_SESSION["pdf"]["tituloinforme3"])!='') {
    				$this->Ln(4);
    				$this->SetFont('Arial','B',8);$this->Cell(280,8,$_SESSION["pdf"]["tituloinforme3"],0,0,'C');    		
    			}
    		}
    		if (isset($_SESSION["pdf"]["tituloinforme4"])) {
    			if (trim($_SESSION["pdf"]["tituloinforme4"])!='') {
    				$this->Ln(4);
    				$this->SetFont('Arial','B',8);$this->Cell(280,8,$_SESSION["pdf"]["tituloinforme4"],0,0,'C');    		
    			}
    		}    	
    		$this->Ln(10);
    		$y=$this->GetY()+5;    	
    		$this->Line(5,$y,300,$y);
    		$this->Ln(5);
    	}    	
    	
	}

	//Pie de página
	function Footer() {
    	$this->SetY(-15);
    	$y=$this->GetY();
    	if ($this->DefOrientation=='P') {
    		$this->Line(10,$y,200,$y);
    		$this->SetFont('Arial','I',8);
    		$this->Cell(180,4,NOMBRE_SISTEMA,0,1,'C');
    		$this->Cell(180,4,'Derechos reservados de '.NOMBRE_CASA_SOFTWARE,0,1,'C');
    	}
    	if (($this->DefOrientation=='L') && ($this->TxtFormat=='legal')) {
    		$this->Line(10,$y,340,$y);
    		$this->SetFont('Arial','I',8);
    		$this->Cell(320,4,NOMBRE_SISTEMA,0,1,'C');
    		$this->Cell(320,4,'Derechos reservados de '.NOMBRE_CASA_SOFTWARE,0,1,'C');
    	}
    	if (($this->DefOrientation=='L') && ($this->TxtFormat!='legal')) {
    		$this->Line(10,$y,300,$y);
    		$this->SetFont('Arial','I',8);
    		$this->Cell(280,4,NOMBRE_SISTEMA,0,1,'C');
    		$this->Cell(280,4,'Derechos reservados de '.NOMBRE_CASA_SOFTWARE,0,1,'C');
    	}
    }
}
?>