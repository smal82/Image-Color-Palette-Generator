<?php

ini_set('memory_limit', '1G');  // Imposta il limite di memoria a 1 GB

?>
<?php
// Funzione per generare un nome casuale per la cartella
function generateRandomString($length = 10) {
    return substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, $length);
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

// Caricamento dell'immagine
if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $tmp_name = $_FILES['image']['tmp_name'];
    $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $allowed_ext = ['jpg', 'jpeg', 'png'];

    if (in_array(strtolower($file_ext), $allowed_ext)) {
        // Crea una cartella con un nome casuale
        $random_dir = $upload_dir . generateRandomString();
        mkdir($random_dir, 0755, true);
        $image_path = $random_dir . '/' . uniqid() . '.' . $file_ext;

        // Salva l'immagine caricata nella cartella
        move_uploaded_file($tmp_name, $image_path);

        // Carica l'immagine per elaborare i colori
        $image = imagecreatefromstring(file_get_contents($image_path));

        // Estrai i 20 colori più rilevanti e unici (filtrando colori simili)
        $colors = getTopColors($image, 20);

        // Crea una nuova immagine per la palette con i rettangoli colorati (40px di altezza ciascuno)
        $palette_height = 40 * count($colors);
        $palette_width = 300; // Puoi personalizzare la larghezza della palette
        $palette_image = imagecreatetruecolor($palette_width, $palette_height);

        // Imposta il percorso del font Arial (assicurati che il file esista sul server)
        $font_path = __DIR__ . '/arial.ttf';
        $font_size = 12;

        // Aggiungi i rettangoli di colore uno sotto l'altro senza spazi
        $y = 0;
        foreach ($colors as $color) {
            $rect_color = imagecolorallocate($palette_image, $color['red'], $color['green'], $color['blue']);
            imagefilledrectangle($palette_image, 0, $y, $palette_width, $y + 40, $rect_color);
            $hex_color = sprintf("#%02x%02x%02x", $color['red'], $color['green'], $color['blue']);

            // Calcola la luminosità del colore per determinare il colore del testo
            if (isColorDark($color['red'], $color['green'], $color['blue'])) {
                $text_color = imagecolorallocate($palette_image, 255, 255, 255); // Bianco per colori scuri
            } else {
                $text_color = imagecolorallocate($palette_image, 0, 0, 0); // Nero per colori chiari
            }

            // Scrivi il testo esadecimale usando il font Arial
            imagettftext($palette_image, $font_size, 0, 10, $y + 28, $text_color, $font_path, $hex_color);

            $y += 40;
        }

        // Salva l'immagine della palette nella cartella
        $palette_path = $random_dir . '/palette_image.png';
        imagepng($palette_image, $palette_path);

        // Libera la memoria
        imagedestroy($palette_image);
        imagedestroy($image);

        // Visualizza l'immagine caricata e la palette all'utente (senza includere l'immagine nella palette)
        echo "<img src='$image_path' alt='Uploaded Image' style='max-width:300px; height:auto;'><br/><h2>Palette dei colori</h2>";
        echo "<img src='$palette_path' alt='Palette Image' style='max-width:300px; height:auto;'>";
    } else {
        echo "Formato immagine non supportato. Solo JPG, JPEG, PNG sono ammessi.";
    }
} else {
    echo "Errore durante il caricamento dell'immagine.";
}

// Funzione per estrarre i 20 colori più rilevanti e unici, filtrando colori simili
function getTopColors($image, $count) {
    $width = imagesx($image);
    $height = imagesy($image);
    $colors = [];

    // Itera su tutti i pixel dell'immagine
    for ($y = 0; $y < $height; $y++) {
        for ($x = 0; $x < $width; $x++) {
            $color_index = imagecolorat($image, $x, $y);
            $rgb = imagecolorsforindex($image, $color_index);
            $rgb_key = sprintf('%02x%02x%02x', $rgb['red'], $rgb['green'], $rgb['blue']);

            // Evita duplicati: salva solo colori unici
            if (!isset($colors[$rgb_key])) {
                $colors[$rgb_key] = [
                    'red' => $rgb['red'],
                    'green' => $rgb['green'],
                    'blue' => $rgb['blue'],
                    'count' => 1
                ];
            } else {
                $colors[$rgb_key]['count']++;
            }
        }
    }

    // Ordina i colori per frequenza (più frequenti prima)
    usort($colors, function($a, $b) {
        return $b['count'] - $a['count'];
    });

    // Filtra i colori simili
    $filtered_colors = [];
    foreach ($colors as $color) {
        $is_similar = false;

        foreach ($filtered_colors as $fcolor) {
            if (colorDistance($color, $fcolor) < 50) { // Soglia di similitudine
                $is_similar = true;
                break;
            }
        }

        if (!$is_similar) {
            $filtered_colors[] = $color;
        }

        // Se abbiamo già abbastanza colori, interrompiamo
        if (count($filtered_colors) >= $count) {
            break;
        }
    }

    return $filtered_colors;
}

// Funzione per calcolare la distanza tra due colori
function colorDistance($color1, $color2) {
    return sqrt(
        pow($color1['red'] - $color2['red'], 2) +
        pow($color1['green'] - $color2['green'], 2) +
        pow($color1['blue'] - $color2['blue'], 2)
    );
}

// Funzione per verificare se il colore è scuro (calcoliamo la luminosità)
function isColorDark($r, $g, $b) {
    // Calcola la luminosità secondo il modello di percezione dei colori
    $luminosity = (0.299 * $r + 0.587 * $g + 0.114 * $b);
    return $luminosity < 128; // Soglia per determinare se un colore è scuro
}
?>





