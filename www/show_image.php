<?php
  function watermark($watermark, $source, $position = 'bottomright', $direct_output = true) {
	$sourceType = strtolower(preg_replace("/.*\.(.*)/i", "$1", $source));
	if ($sourceType == 'jpg') $sourceType = 'jpeg';
	$watermarkType = strtolower(preg_replace("/.*\.(.*)/i", "$1", $watermark));
	if ($watermarkType == 'jpg') $watermarkType = 'jpeg';

	if (!empty($source) && @file_exists($source)) {
	  $outputType = $sourceType;
	  if ($outputType == 'gif') $outputType = 'png'; // Okay to remove after July 2004

//	  Derive function names
	  $createSource = 'imagecreatefrom' . $sourceType;
	  $showImage = 'image' . $outputType;
	  $createWatermark = 'imagecreatefrom' . $watermarkType;

//	  Load original and watermark to memory
	  $output = $createSource($source);
	  $logo = $createWatermark($watermark);
	  if (function_exists('ImageAlphaBlending')) {
		ImageAlphaBlending($output, true);
	  }

	  list($source_w, $source_h) = @getimagesize($source);
	  if ($position=='topright') {
		$x = $source_w - imagesx($logo);
		$y = 0;
	  } elseif ($position=='bottomright') {
		$x = $source_w - imagesx($logo);
		$y = $source_h - imagesy($logo);
	  } elseif ($position=='bottomleft') {
		$x = 0;
		$y = $source_h - imagesy($logo);
	  } elseif ($position=='center') {
		$x = ($source_w - imagesx($logo)) / 2;
		$y = ($source_h - imagesy($logo)) / 2;
	  } else {
		$x = 0;
		$y = 0;
	  }

//	  Display
	  imagecopy($output, $logo, $x, $y, 0, 0, imagesx($logo), imagesy($logo));
	  imagedestroy($logo);

	  if ($direct_output) {
		header('Content-Type: image/' . $outputType);
		header('Content-Disposition: filename=" ' . $source . '"');

		$showImage = 'image' . $outputType;
		if ($outputType == 'jpeg') {
		  $showImage($output, '', 90);
		} else {
		  $showImage($output);
		}

		imagedestroy($output);
	  } else {
		return array('image' => $output, 'type' => $outputType);
	  }
	}
  }

  if (!empty($_GET['image']) && @file_exists('images/' . $_GET['image'])) {
	$watermark = '';
	if (substr($_GET['image'], 0, 7)=='thumbs/' && file_exists('images/watermark.png')) {
	  $watermark = 'images/watermark.png';
	} elseif (substr($_GET['image'], 0, 4)=='big/' && file_exists('images/watermark_big.png')) {
	  $watermark = 'images/watermark_big.png';
	} elseif (substr($_GET['image'], 0, 7)=='middle/' && file_exists('images/watermark_middle.png')) {
	  $watermark = 'images/watermark_middle.png';
	}
	if (!empty($watermark) && strpos($_SERVER['HTTP_HOST'], 'setbook')!==false) {
	  $image_array = watermark($watermark, 'images/' . $_GET['image'], 'bottomright');
	} else {
	  readfile('images/' . $_GET['image']);
	}
  } else {
	header("HTTP/1.0 404 Not Found");
	header("Status: 404 Not Found");
  }

  die();
?>