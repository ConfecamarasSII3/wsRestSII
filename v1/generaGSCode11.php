<?php

function generarGsCode11 ($valor,$name) {
	$class_dir = 'barcodegen/class';
	$code = 'code11'; // Code 11
	$f1 = 'Arial.ttf'; // Arial
	$f2 = 8; // Tamano
	$o = 1; // Orientacion horizontal
	$dpi = 72; // DPIs
	$t = 50; // Thickness
	$r = 2; // Resolucion
	$rot = 0; // Rotacion horizontal
	// $a1 = '';
	// $a2 = 'C'; // Inicia con Code128-C
	// $a3 = '';
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
	$code_generated->parse($valor);	
	$drawing = new BCGDrawing($name, $color_white);
	$drawing->setBarcode($code_generated);
	$drawing->setRotationAngle($rot);
	$drawing->setDPI($dpi == 'null' ? null : (int)$dpi);
	$drawing->draw();
	$drawing->finish(intval($o));
	unset ($drawing);
	unset ($color_black);
	unset ($color_white);
	unset ($codebar);
	unset ($font);
}
?>