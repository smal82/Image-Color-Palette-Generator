<?php

ini_set('memory_limit', '1G');  // Imposta il limite di memoria a 1 GB

?>
<?php
// Funzione per generare un nome casuale per le cartelle
function generateRandomString($length = 10) {
    return substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length / strlen($x)))), 1, $length);
}

// Pulizia delle cartelle più vecchie di 1 ora nella directory uploads
$upload_dir = 'uploads/';
$now = time();

foreach (glob($upload_dir . '*', GLOB_ONLYDIR) as $dir) {
    if ($now - filemtime($dir) > 3600) {
        array_map('unlink', glob("$dir/*.*")); // Rimuove i file nella cartella
        rmdir($dir); // Rimuove la cartella
    }
}

// Funzione per ottenere i colori dominanti da un'immagine
function getTopColors($image, $num_colors = 20) {
    $width = imagesx($image);
    $height = imagesy($image);
    $total_pixels = $width * $height;

    $color_counts = [];
    for ($x = 0; $x < $width; $x++) {
        for ($y = 0; $y < $height; $y++) {
            $color_index = imagecolorat($image, $x, $y);
            $rgb = imagecolorsforindex($image, $color_index);

            // Convert the RGB array into a hexadecimal color string
            $hex = sprintf("#%02x%02x%02x", $rgb['red'], $rgb['green'], $rgb['blue']);

            if (!isset($color_counts[$hex])) {
                $color_counts[$hex] = 0;
            }

            $color_counts[$hex]++;
        }
    }

    // Ordina i colori in base alla frequenza, dal più comune al meno comune
    arsort($color_counts);

    // Prendi i primi $num_colors colori e calcola la percentuale
    $top_colors = [];
    foreach (array_slice($color_counts, 0, $num_colors, true) as $color => $count) {
        $percentage = ($count / $total_pixels) * 100;
        $top_colors[] = [
            'color' => $color,
            'percentage' => round($percentage, 2) // Arrotonda la percentuale a 2 cifre decimali
        ];
    }

    return $top_colors;
}

// Controlla se un'immagine è stata caricata
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    die('Errore durante il caricamento del file');
}

$image_path = $_FILES['image']['tmp_name'];
$image_type = $_FILES['image']['type'];

// Crea l'immagine dalla risorsa caricata
switch ($image_type) {
    case 'image/jpeg':
        $image = imagecreatefromjpeg($image_path);
        break;
    case 'image/png':
        $image = imagecreatefrompng($image_path);
        break;
    default:
        die('Formato immagine non supportato. Usa JPG o PNG.');
}

// Ottieni i colori dominanti
$top_colors = getTopColors($image);

// Crea una nuova immagine per la palette
$rect_height = 50;
$palette_width = 400;
$palette_height = count($top_colors) * $rect_height;
$palette_image = imagecreatetruecolor($palette_width, $palette_height);

// Percorso del font Open Sans
$font_path = __DIR__ . '/OpenSans-Regular.ttf';

// Verifica che il font esista
if (!file_exists($font_path)) {
    die('Font non trovato. Assicurati che OpenSans-Regular.ttf sia presente.');
}

// Riempi lo sfondo della palette con bianco
$background_color = imagecolorallocate($palette_image, 255, 255, 255);
imagefill($palette_image, 0, 0, $background_color);

// Per ogni colore, disegna il rettangolo e aggiungi il testo con la percentuale
foreach ($top_colors as $index => $color_data) {
    $color_hex = $color_data['color'];
    $percentage = $color_data['percentage'];

    // Converti il colore esadecimale in RGB
    sscanf($color_hex, "#%02x%02x%02x", $r, $g, $b);
    $color = imagecolorallocate($palette_image, $r, $g, $b);

    // Determina se il testo deve essere bianco o nero in base alla luminosità del colore
    $brightness = ($r * 299 + $g * 587 + $b * 114) / 1000;
    $text_color = ($brightness > 128) ? imagecolorallocate($palette_image, 0, 0, 0) : imagecolorallocate($palette_image, 255, 255, 255);

    // Disegna il rettangolo del colore
    imagefilledrectangle($palette_image, 0, $index * $rect_height, $palette_width, ($index + 1) * $rect_height, $color);

    // Scrivi il valore esadecimale e la percentuale
    $text = $color_hex . ' (' . $percentage . '%)';
    imagettftext($palette_image, 12, 0, 10, ($index + 1) * $rect_height - 10, $text_color, $font_path, $text);
}

// Genera un nome casuale per la cartella e crea la directory
$folder_name = generateRandomString();
$folder_path = __DIR__ . '/uploads/' . $folder_name;
if (!mkdir($folder_path, 0755, true)) {
    die('Errore nella creazione della cartella.');
}

// Salva l'immagine della palette
$palette_image_path = $folder_path . '/palette_image.png';
imagepng($palette_image, $palette_image_path);

// Salva l'immagine originale nella stessa cartella
$original_image_path = $folder_path . '/' . basename($_FILES['image']['name']);
move_uploaded_file($_FILES['image']['tmp_name'], $original_image_path);

// Libera la memoria
imagedestroy($palette_image);
imagedestroy($image);

// Restituisci il percorso delle immagini generate
$response = [
    'original_image' => 'uploads/' . $folder_name . '/' . basename($_FILES['image']['name']),
    'palette_image' => 'uploads/' . $folder_name . '/palette_image.png'
];

echo json_encode($response);

?>






