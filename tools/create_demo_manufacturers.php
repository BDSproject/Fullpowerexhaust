<?php
/**
 * FULLPOWEREXHAUST — crée les marques (manufacturers) et les associe aux
 * produits de démo pour que la carte produit affiche la marque en rouge.
 */

if (PHP_SAPI !== 'cli') {
    die('CLI only');
}

require_once __DIR__ . '/../config/config.inc.php';

$langId = (int) Configuration::get('PS_LANG_DEFAULT');

// brand => [product references]
$brands = [
    'BMW'        => ['PE-1000'],
    'Audi'       => ['PE-1001'],
    'Volkswagen' => ['PE-1002'],
    'Peugeot'    => ['PE-1003'],
    'Mercedes'   => ['PE-1004'],
    'Renault'    => ['PE-1005'],
];

$idByBrand = [];

foreach ($brands as $brand => $refs) {
    $idManufacturer = (int) Manufacturer::getIdByName($brand);
    if (!$idManufacturer) {
        $m = new Manufacturer();
        $m->name = $brand;
        $m->active = 1;
        $m->add();
        $idManufacturer = (int) $m->id;
    }

    $idByBrand[$brand] = $idManufacturer;

    foreach ($refs as $ref) {
        $idProduct = (int) Db::getInstance()->getValue(
            'SELECT id_product FROM ' . _DB_PREFIX_ . 'product WHERE reference = "' . pSQL($ref) . '"'
        );
        if ($idProduct) {
            Db::getInstance()->update('product', ['id_manufacturer' => $idManufacturer], 'id_product = ' . $idProduct);
            echo "✔ {$brand} → produit #{$idProduct}\n";
        }
    }
}

echo "Marques créées/associées.\n";
