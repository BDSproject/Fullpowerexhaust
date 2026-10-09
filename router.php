<?php
/**
 * Router pour le serveur de dev PHP intégré (php -S).
 * Reproduit le comportement du rewrite Apache de PrestaShop :
 *  - sert directement les fichiers statiques (themes/, img/, js/, modules/, ...)
 *  - route tout le reste vers le contrôleur frontal (front office ou back office).
 *
 * Usage : php -d memory_limit=768M -S localhost:8000 router.php
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rawurldecode($uri);

// Back office : /admin123/... → admin123/index.php
// robots.txt + fichiers sitemap : servis directement depuis la racine
if ($uri === '/robots.txt') {
    $file = __DIR__ . '/robots.txt';
    if (is_file($file)) {
        return false; // le serveur intégré sert le fichier
    }
}
if ($uri === '/sitemap.xml') {
    $file = __DIR__ . '/1_index_sitemap.xml';
    if (is_file($file)) {
        header('Content-Type: application/xml');
        readfile($file);
        return true;
    }
}
if (preg_match('#^/[^/]*sitemap\.xml$#i', $uri)) {
    $file = __DIR__ . $uri;
    if (is_file($file)) {
        return false; // le serveur intégré sert le fichier
    }
}

// Les fichiers statiques (CSS/JS/images du thème admin) sont servis directement.
if (preg_match('#^/admin123(/|$)#', $uri)) {
    $adminFile = __DIR__ . $uri;
    if ($uri !== '/admin123' && $uri !== '/admin123/' && is_file($adminFile)) {
        return false; // le serveur intégré sert le fichier statique
    }
    $_SERVER['SCRIPT_NAME'] = '/admin123/index.php';
    $_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/admin123/index.php';
    require __DIR__ . '/admin123/index.php';
    return true;
}

// ── Réécriture d'images (rewrite Apache "image rewriting") ──────────────────
// PrestaShop réécrit les URLs d'images en /{id}{-type}/{rewrite}.{ext} quand
// les URLs simplifiées sont actives. On les remappe vers le fichier physique.
//   produit      : /7-default_md/nom.jpg        → img/p/7/7-default_md.jpg
//   catégorie    : /c/7-category_cover/nom.jpg  → img/c/7-category_cover.jpg
//   fabricant    : /m/1-small_default/nom.jpg   → img/m/1-small_default.jpg
//   fournisseur  : /s/1-small_default/nom.jpg   → img/su/1-small_default.jpg
//   magasin      : /st/1-stores_default/nom.jpg → img/st/1-stores_default.jpg
//
// ⚠️ Seules les images PRODUIT sont rangées dans des sous-dossiers
//    (img/p/3/3/1/331-product_main.jpg) ; toutes les autres sont à plat
//    (img/c/7-category_cover.jpg). Confondre les deux faisait répondre 404
//    à toutes les images de catégorie.
// Motifs calqués sur ceux que PrestaShop génère lui-même dans .htaccess
// (cf. Tools::generateHtaccess) : identifiant, suffixe « _thumb » éventuel
// puis type d’image.
$ext = '(?:jpe?g|webp|png|avif|gif)';
$imageRules = [
    // [motif, dossier cible, sous-dossiers ?]
    ["#^/c/([0-9]+)(|_thumb)((?:-[.*\w-]*)?)/[^/]+\.($ext)$#i", 'img/c/', false],
    ["#^/m/([0-9]+)(|_thumb)((?:-[.*\w-]*)?)/[^/]+\.($ext)$#i", 'img/m/', false],
    ["#^/su?/([0-9]+)(|_thumb)((?:-[.*\w-]*)?)/[^/]+\.($ext)$#i", 'img/su/', false],
    ["#^/st/([0-9]+)(|_thumb)((?:-[.*\w-]*)?)/[^/]+\.($ext)$#i", 'img/st/', false],
    ["#^/([0-9]+)((?:-[.*\w-]*)?)/[^/]+\.($ext)$#i", 'img/p/', true],
];

foreach ($imageRules as [$pattern, $dir, $withSubfolders]) {
    if (preg_match($pattern, $uri, $m)) {
        $id = $m[1];
        $name = $withSubfolders ? $m[1] . $m[2] : $m[1] . ($m[2] ?? '') . ($m[3] ?? '');
        $ext = strtolower($m[count($m) - 1]);
        $prefix = $withSubfolders ? implode('/', str_split($id)) . '/' : '';
        $file = __DIR__ . '/' . $dir . $prefix . $name . '.' . $ext;
        if (is_file($file)) {
            $mime = [
                'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
                'webp' => 'image/webp', 'avif' => 'image/avif', 'gif' => 'image/gif',
            ][$ext] ?? 'application/octet-stream';
            header('Content-Type: ' . $mime);
            header('Content-Length: ' . filesize($file));
            readfile($file);
            return true;
        }
    }
}

// Dossiers de ressources statiques servis directement
$staticPrefixes = ['/themes/', '/img/', '/js/', '/modules/', '/upload/', '/download/', '/mails/', '/pdf/'];
$isStatic = false;
foreach ($staticPrefixes as $prefix) {
    if (strpos($uri, $prefix) === 0) {
        $isStatic = true;
        break;
    }
}

$file = __DIR__ . $uri;
if ($isStatic && is_file($file)) {
    return false; // le serveur intégré sert le fichier
}

// Contrôleur frontal (front office)
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/index.php';
require __DIR__ . '/index.php';
return true;
