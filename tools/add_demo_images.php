<?php
/**
 * FULLPOWEREXHAUST — attache des images aux produits de démo.
 * Usage : php -d memory_limit=768M tools/add_demo_images.php
 */

if (PHP_SAPI !== 'cli') {
    die('CLI only');
}

require_once __DIR__ . '/../config/config.inc.php';

// id_product => fichier image local
$map = [
    1 => '/tmp/fpe_imgs/p1.jpg',
    2 => '/tmp/fpe_imgs/p2.jpg',
    3 => '/tmp/fpe_imgs/p3.jpg',
    4 => '/tmp/fpe_imgs/p4.jpg',
    5 => '/tmp/fpe_imgs/p5.jpg',
    6 => '/tmp/fpe_imgs/p4.jpg',
];

$imageTypes = ImageType::getImagesTypes('products');

foreach ($map as $idProduct => $src) {
    if (!is_file($src)) {
        echo "✖ source introuvable : {$src}\n";
        continue;
    }

    // Ne pas dupliquer si le produit a déjà une image de couverture
    $hasCover = (bool) Db::getInstance()->getValue(
        'SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'image WHERE id_product = ' . (int) $idProduct . ' AND cover = 1'
    );
    if ($hasCover) {
        echo "— produit #{$idProduct} a déjà une image, ignoré\n";
        continue;
    }

    $image = new Image();
    $image->id_product = $idProduct;
    $image->position = 1;
    $image->cover = 1;

    if (!$image->add()) {
        echo "✖ échec création image pour produit #{$idProduct}\n";
        continue;
    }

    $base = $image->getPathForCreation(); // ex: img/p/1/1

    if (!copy($src, $base . '.jpg')) {
        echo "✖ échec copie vers {$base}.jpg\n";
        continue;
    }

    foreach ($imageTypes as $type) {
        ImageManager::resize(
            $base . '.jpg',
            $base . '-' . $type['name'] . '.jpg',
            (int) $type['width'],
            (int) $type['height']
        );
    }

    echo "✔ image #{$image->id} → produit #{$idProduct}\n";
}

echo "Terminé.\n";
