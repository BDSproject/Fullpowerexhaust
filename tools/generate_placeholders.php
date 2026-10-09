<?php
/**
 * FULLPOWEREXHAUST — visuels par défaut (« Aucune image disponible ») au thème sombre
 * ---------------------------------------------------------------------------
 * PrestaShop fournit des visuels de remplacement BLANCS. Sur un thème sombre,
 * chaque produit ou catégorie sans photo affichait donc un grand rectangle
 * blanc avec « No image available ».
 *
 * Ce script régénère tous les fichiers `<iso>-default-<type>.jpg` des dossiers
 * img/p, img/c, img/m, img/su et img/st avec :
 *   - un fond #141414 (couleur « carte » du thème),
 *   - un pictogramme discret, sans texte (donc valable pour les 4 langues).
 *
 * Les dimensions d’origine sont conservées (elles correspondent aux types
 * d’images configurés dans PrestaShop).
 *
 * Usage : php tools/generate_placeholders.php [--dry]
 */

$root = dirname(__DIR__);
$dry = in_array('--dry', $argv, true);

const BG = [0x14, 0x14, 0x14];         // --fpe-card
const GLYPH = [0x3a, 0x3a, 0x3a];      // pictogramme
const GLYPH_ACCENT = [0x5a, 0x1a, 0x22]; // léger rappel du rouge de marque

/**
 * Dessine un pictogramme « photo » centré, proportionnel à la taille demandée.
 */
function drawGlyph($im, int $w, int $h): void
{
    $glyph = imagecolorallocate($im, ...GLYPH);
    $accent = imagecolorallocate($im, ...GLYPH_ACCENT);

    // Taille du pictogramme : 40 % du plus petit côté, bornée
    $size = (int) max(24, min($w, $h) * 0.4);
    $size = min($size, (int) ($w * 0.7), (int) ($h * 0.7));

    $x = (int) (($w - $size) / 2);
    $y = (int) (($h - $size) / 2);

    // Cadre
    imagesetthickness($im, max(2, (int) ($size / 26)));
    imagerectangle($im, $x, $y, $x + $size, $y + $size, $glyph);

    // « Soleil »
    $sun = (int) ($size * 0.13);
    imagefilledellipse($im, $x + (int) ($size * 0.30), $y + (int) ($size * 0.30), $sun * 2, $sun * 2, $glyph);

    // « Montagne » (deux triangles)
    $base = $y + (int) ($size * 0.80);
    imagefilledpolygon($im, [
        $x + (int) ($size * 0.10), $base,
        $x + (int) ($size * 0.42), $y + (int) ($size * 0.42),
        $x + (int) ($size * 0.74), $base,
    ], 3, $accent);
    imagefilledpolygon($im, [
        $x + (int) ($size * 0.44), $base,
        $x + (int) ($size * 0.68), $y + (int) ($size * 0.55),
        $x + (int) ($size * 0.92), $base,
    ], 3, $glyph);
}

$dirs = ['img/p', 'img/c', 'img/m', 'img/su', 'img/st', 'img'];
$done = 0;
$skipped = 0;

foreach ($dirs as $dir) {
    $path = "$root/$dir";
    if (!is_dir($path)) {
        continue;
    }
    foreach (glob("$path/*-default-*.jpg") as $file) {
        $info = @getimagesize($file);
        if (!$info) {
            ++$skipped;
            continue;
        }
        [$w, $h] = $info;
        if ($w < 8 || $h < 8) {
            ++$skipped;
            continue;
        }
        if ($dry) {
            ++$done;
            continue;
        }

        $im = imagecreatetruecolor($w, $h);
        imagefill($im, 0, 0, imagecolorallocate($im, ...BG));
        drawGlyph($im, $w, $h);
        imagejpeg($im, $file, 92);
        ++$done;
    }
}

echo ($dry ? '[SIMULATION] ' : '') . "Visuels par défaut régénérés : $done\n";
if ($skipped) {
    echo "Ignorés : $skipped\n";
}
