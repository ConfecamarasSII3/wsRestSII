<?php

function unescape($cadena_entrada) {
	$cadena_salida="";
	$longitud=strlen($cadena_entrada);
	if(($longitud%3)==0) {
		for($cuenta=0; $cuenta<$longitud; $cuenta+=3) {
			$cadena_salida.=chr(hexdec(substr($cadena_entrada,$cuenta+1,2)));
		}
		return $cadena_salida;
	} else{
		return "Cadena no valida";
	}
}

function generarGs1128 ($iac,$ref,$fec,$name,$valor=0) {
	$class_dir = 'barcodegen/class';
	$code = 'gs1128'; // EAN 128
	$f1 = 'Arial.ttf'; // Arial
	$f2 = 8; // Tama&ntilde;o
	$o = 1; // Orientaci&oacute;n horizontal
	$dpi = 72; // DPIs
	$t = 70; // Thickness
	$r = 1; // Resolucion
	$rot = 0; // Rotaci&oacute;n horizontal
	$a1 = '';
	$a2 = 'C'; // Inicia con Code128-C
	$a3 = '';
	// $text = '415'.$iac.'8020'.$ref.unescape('%1D').'96'.$fec;	
	// if (doubleval($valor)!=0) {
	//	$text = '415'.$iac.'8020'.$ref.unescape('%1D').'3900'.$valor.unescape('%1D').'96'.$fec;
	// } else {
	if ($valor == 0) {
		$text = '415'.$iac.'8020'.sprintf("%08s",$ref).unescape('%1D').'96'.$fec;
	} else {
		$text = '415'.$iac.unescape('%1D').'8020'.sprintf("%08s",$ref).unescape('%1D').'3900'.sprintf("%08s",intval($valor)).unescape('%1D').'96'.$fec;
	}
	//}
	
	// 
	require_once($class_dir . '/BCGColor.php');
	require_once($class_dir . '/BCGBarcode.php');
	require_once($class_dir . '/BCGDrawing.php');
	require_once($class_dir . '/BCGFont.php');
	require_once($class_dir . '/BCG' . $code . '.barcode.php');
	
	if($f1 !== '0' && $f1 !== '-1' && intval($f2) >= 1) {
		$font = new BCGFont($class_dir . '/font/' . $f1, intval($f2));
	} else {
		$font = 0;
	}
	$color_black = new BCGColor(0, 0, 0);
	$color_white = new BCGColor(255, 255, 255);
	$codebar = 'BCG' . $code;
	$code_generated = new $codebar();
	if(isset($a1) && intval($a1) === 1) {
		$code_generated->setChecksum(true);
	}
	if(isset($a2) && !empty($a2)) {
		$code_generated->setStart($a2);
	}
	if(isset($a3) && !empty($a3)) {
		$code_generated->setLabel($a3);
	}
	$code_generated->setThickness($t);
	$code_generated->setScale($r);
	$code_generated->setBackgroundColor($color_white);
	$code_generated->setForegroundColor($color_black);
	$code_generated->setFont($font);
	$code_generated->parse($text);	
	$drawing = new BCGDrawing($name, $color_white);
	$drawing->setBarcode($code_generated);
	$drawing->setRotationAngle($rot);
	$drawing->setDPI($dpi == 'null' ? null : (int)$dpi);
	$drawing->draw();
	/*
	if(intval($o) === 1) {
		header('Content-Type: image/png');
	} elseif(intval($o) === 2) {
		header('Content-Type: image/jpeg');
	} elseif(intval($o) === 3) {
		header('Content-Type: image/gif');
	}
	*/
	$drawing->finish(intval($o));
	unset ($drawing);
	unset ($color_black);
	unset ($color_white);
	unset ($codebar);
	unset ($font);
}
?>