<?php
/**
 * FULLPOWEREXHAUST — régénération des vignettes au fond du thème
 * ---------------------------------------------------------------------------
 * PrestaShop génère les vignettes en complétant l'image avec du BLANC
 * (classes/ImageManager.php → imagefilledrectangle(..., 255, 255, 255)).
 * Sur un thème sombre, toute image dont le ratio diffère de celui du type
 * apparaît donc dans un cadre blanc : fiche produit, bannière de catégorie,
 * visuels de sous-catégories…
 *
 * Ce script régénère TOUTES les vignettes (produits, catégories, fabricants,
 * fournisseurs, magasins) en complétant avec la couleur de fond du thème.
 * La photo n'est jamais rognée : elle est simplement centrée sur un fond
 * qui se confond avec la page.
 *
 * La liste des fichiers à traiter est déduite du disque (et non de la base) :
 * chaque original `{id}.jpg` est accompagné de ses déclinaisons
 * `{id}-{type}.jpg` / `{id}_thumb-{type}.jpg`, dont on reprend les dimensions.
 *
 * Usage :
 *   php tools/regenerate_thumbnails.php [--dry] [--color=0d0d0d] [--only=img/c]
 *
 * ⚠️ Le back-office (« Regénérer les miniatures ») réintroduira du blanc.
 *    Relancez ce script après toute régénération depuis l'administration.
 */

$root = dirname(__DIR__);
$dry = in_array('--dry', $argv, true);

$color = '0d0d0d';
$only = null;
foreach ($argv as $a) {
    if (str_starts_with($a, '--color=')) {
        $color = ltrim(substr($a, 8), '#');
    }
    if (str_starts_with($a, '--only=')) {
        $only = rtrim(substr($a, 7), '/');
    }
}

$r = hexdec(substr($color, 0, 2));
$g = hexdec(substr($color, 2, 2));
$b = hexdec(substr($color, 4, 2));

echo "Couleur de complément : #$color\n";
if ($dry) {
    echo "[SIMULATION]\n";
}

/**
 * Régénère une vignette : complète l'original avec la couleur demandée.
 */
function regenerate(string $source, string $dest, int $r, int $g, int $b, bool $dry): string
{
    $info = @getimagesize($dest);
    if (!$info) {
        return 'skip';
    }
    [$tw, $th] = $info;

    $src = @imagecreatefromstring(file_get_contents($source));
    if (!$src) {
        return 'error';
    }
    $sw = imagesx($src);
    $sh = imagesy($src);

    // Ratio identique → PrestaShop n'a rien complété, rien à faire
    if (abs(($sw / $sh) - ($tw / $th)) < 0.002) {

        return 'same';
    }

    if ($dry) {

        return 'done';
    }

    $canvas = imagecreatetruecolor($tw, $th);
    imagefill($canvas, 0, 0, imagecolorallocate($canvas, $r, $g, $b));

    $scale = min($tw / $sw, $th / $sh);
    $nw = (int) round($sw * $scale);
    $nh = (int) round($sh * $scale);
    imagecopyresampled(
        $canvas, $src,
        (int) round(($tw - $nw) / 2), (int) round(($th - $nh) / 2), 0, 0,
        $nw, $nh, $sw, $sh
    );

    $ext = strtolower(pathinfo($dest, PATHINFO_EXTENSION));
    $ext === 'png' ? imagepng($canvas, $dest) : imagejpeg($canvas, $dest, 90);

    return 'done';
}

$dirs = ['img/p', 'img/c', 'img/m', 'img/su', 'img/st'];
$stats = ['done' => 0, 'same' => 0, 'skip' => 0, 'error' => 0, 'orig' => 0];

foreach ($dirs as $dir) {
    if ($only !== null && $dir !== $only) {
        continue;
    }
    $base = "$root/$dir";
    if (!is_dir($base)) {
        continue;
    }

    // Toutes les vignettes : {id}-{type}.jpg ou {id}_thumb-{type}.jpg
    foreach (glob("$base/*-*.jpg") as $thumb) {
        $name = basename($thumb, '.jpg');

        // Identifiant de l'image d'origine
        if (!preg_match('/^(.+?)(_thumb)?-[^-]+$/', $name, $m)) {
            ++$stats['skip'];
            continue;
        }
        $id = $m[1];
        $original = "$base/$id.jpg";
        if (!is_file($original)) {
            ++$stats['skip'];
            continue;
        }
        ++$stats['orig'];
        $result = regenerate($original, $thumb, $r, $g, $b, $dry);
        ++$stats[$result];
    }
}

echo ($dry ? '[SIMULATION] ' : '') . "Vignettes régénérées : {$stats['done']}\n";
echo "Ratio déjà identique (ignorées) : {$stats['same']}\n";
echo "Sans original (ignorées)        : {$stats['skip']}\n";
if ($stats['error']) {
    echo "Erreurs                         : {$stats['error']}\n";
}
