<?php

// Archivos
$archivoCSV = 'productos.csv';
$archivoTXT = 'nombres_imagenes.txt';
$archivoSalida = 'productos_con_imagenes.csv';

// Umbral de similitud
$umbralSimilitud = 10;

// Función para cargar los nombres de las imágenes y sus SKU desde el archivo de texto
function cargarNombresImagenes($archivoTXT) {
    $nombres = [];
    $lines = file($archivoTXT, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $nombreImagen = trim($line);
        $nombres[] = $nombreImagen;
    }
    return $nombres;
}

// Función para actualizar el archivo CSV con los nombres de imágenes correspondientes
function actualizarCSVConImagenes($archivoCSV, $archivoSalida, $nombresImagenes, $umbralSimilitud) {
    $archivoEntrada = fopen($archivoCSV, 'r');
    $archivoSalida = fopen($archivoSalida, 'w');
    
    // Leer encabezados del archivo original
    $header = fgetcsv($archivoEntrada);
    $columnaImagenes = array_search('Imágenes', $header); // Obtener el índice de la columna 'Imagenes'
    if ($columnaImagenes === false) {
        fclose($archivoEntrada);
        fclose($archivoSalida);
        die('La columna "Imagenes" no existe en el archivo CSV.');
    }
    
    fputcsv($archivoSalida, $header);
    
    while (($fila = fgetcsv($archivoEntrada)) !== false) {
        $sku = $fila[2]; // Suponiendo que la columna del SKU está en la posición 0
        
        $bestMatch = '';
        $bestMatchPercentage = 0;
        
        foreach ($nombresImagenes as $nombreImagen) {
            $similitud = similar_text($sku, $nombreImagen, $percentage);
            if ($percentage > $umbralSimilitud && $percentage > $bestMatchPercentage) {
                $bestMatch = $nombreImagen;
                $bestMatchPercentage = $percentage;
            }
        }
        
        $fila[$columnaImagenes] = $bestMatch;
        fputcsv($archivoSalida, $fila);
    }
    
    fclose($archivoEntrada);
    fclose($archivoSalida);
}

// Cargar nombres de imágenes y actualizar el archivo CSV
$nombresImagenes = cargarNombresImagenes($archivoTXT);
actualizarCSVConImagenes($archivoCSV, $archivoSalida, $nombresImagenes, $umbralSimilitud);

echo 'Proceso completado.';

?>
