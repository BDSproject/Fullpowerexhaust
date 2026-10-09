<?php
/**
 * FULLPOWEREXHAUST — complète les traductions manquantes
 * ---------------------------------------------------------------------------
 * S'appuie sur var/audit-translations.json (produit par tools/audit-translations.php)
 * et écrit les <trans-unit> absents dans les catalogues XLIFF de chaque langue.
 *
 * Usage : php tools/fix_missing_translations.php [--dry]
 */

$root = dirname(__DIR__);
$dry = in_array('--dry', $argv, true);

$auditFile = $root . '/var/audit-translations.json';
if (!is_file($auditFile)) {
    exit("Lancez d'abord : php tools/audit-translations.php\n");
}
$audit = json_decode(file_get_contents($auditFile), true);

/**
 * Dictionnaire des traductions manquantes.
 * Clé = chaîne source telle qu'écrite dans le template.
 */
$T = [
    /* ── Domaine du thème ────────────────────────────────────────────── */
    'Rechercher un produit…' => ['fr-FR' => 'Rechercher un produit…', 'en-US' => 'Search for a product…', 'es-ES' => 'Buscar un producto…', 'it-IT' => 'Cerca un prodotto…'],
    'Langue' => ['fr-FR' => 'Langue', 'en-US' => 'Language', 'es-ES' => 'Idioma', 'it-IT' => 'Lingua'],

    /* ── Catalogue ───────────────────────────────────────────────────── */
    'Regular price: ' => ['fr-FR' => 'Prix normal : ', 'en-US' => 'Regular price: ', 'es-ES' => 'Precio normal: ', 'it-IT' => 'Prezzo normale: '],
    'Price: ' => ['fr-FR' => 'Prix : ', 'en-US' => 'Price: ', 'es-ES' => 'Precio: ', 'it-IT' => 'Prezzo: '],
    'Marque' => ['fr-FR' => 'Marque', 'en-US' => 'Brand', 'es-ES' => 'Marca', 'it-IT' => 'Marca'],
    'Toutes les marques' => ['fr-FR' => 'Toutes les marques', 'en-US' => 'All brands', 'es-ES' => 'Todas las marcas', 'it-IT' => 'Tutti i marchi'],
    'If this is a recurring problem, please [1]contact us[/1].' => [
        'fr-FR' => 'Si le problème persiste, merci de [1]nous contacter[/1].',
        'en-US' => 'If this is a recurring problem, please [1]contact us[/1].',
        'es-ES' => 'Si el problema persiste, [1]contáctanos[/1].',
        'it-IT' => 'Se il problema persiste, [1]contattaci[/1].',
    ],
    'Available quantity to return:' => ['fr-FR' => 'Quantité disponible pour le retour :', 'en-US' => 'Available quantity to return:', 'es-ES' => 'Cantidad disponible para devolver:', 'it-IT' => 'Quantità disponibile per il reso:'],
    'Select quantity to return' => ['fr-FR' => 'Sélectionnez la quantité à retourner', 'en-US' => 'Select quantity to return', 'es-ES' => 'Seleccione la cantidad a devolver', 'it-IT' => 'Seleziona la quantità da rendere'],
    'Latest arrivals' => ['fr-FR' => 'Derniers arrivages', 'en-US' => 'Latest arrivals', 'es-ES' => 'Últimas novedades', 'it-IT' => 'Ultimi arrivi'],
    'Remove %1$s filter: %2$s' => ['fr-FR' => 'Retirer le filtre %1$s : %2$s', 'en-US' => 'Remove %1$s filter: %2$s', 'es-ES' => 'Eliminar el filtro %1$s: %2$s', 'it-IT' => 'Rimuovi il filtro %1$s: %2$s'],
    'Search products...' => ['fr-FR' => 'Rechercher des produits…', 'en-US' => 'Search products...', 'es-ES' => 'Buscar productos...', 'it-IT' => 'Cerca prodotti...'],
    'Clear search' => ['fr-FR' => 'Effacer la recherche', 'en-US' => 'Clear search', 'es-ES' => 'Borrar la búsqueda', 'it-IT' => 'Cancella la ricerca'],
    'Special deals' => ['fr-FR' => 'Offres spéciales', 'en-US' => 'Special deals', 'es-ES' => 'Ofertas especiales', 'it-IT' => 'Offerte speciali'],
    'All discounts' => ['fr-FR' => 'Toutes les réductions', 'en-US' => 'All discounts', 'es-ES' => 'Todos los descuentos', 'it-IT' => 'Tutti gli sconti'],
    'Clients also bought' => ['fr-FR' => 'Les clients ont aussi acheté', 'en-US' => 'Clients also bought', 'es-ES' => 'Los clientes también compraron', 'it-IT' => 'I clienti hanno acquistato anche'],

    /* ── Actions ─────────────────────────────────────────────────────── */
    'Back to Shipping' => ['fr-FR' => 'Retour à la livraison', 'en-US' => 'Back to Shipping', 'es-ES' => 'Volver al envío', 'it-IT' => 'Torna alla spedizione'],
    'Connexion' => ['fr-FR' => 'Connexion', 'en-US' => 'Log in', 'es-ES' => 'Iniciar sesión', 'it-IT' => 'Accedi'],

    /* ── Global ──────────────────────────────────────────────────────── */
    'Product availability:' => ['fr-FR' => 'Disponibilité du produit :', 'en-US' => 'Product availability:', 'es-ES' => 'Disponibilidad del producto:', 'it-IT' => 'Disponibilità del prodotto:'],
    'Menu' => ['fr-FR' => 'Menu', 'en-US' => 'Menu', 'es-ES' => 'Menú', 'it-IT' => 'Menu'],
    'Change language' => ['fr-FR' => 'Changer de langue', 'en-US' => 'Change language', 'es-ES' => 'Cambiar idioma', 'it-IT' => 'Cambia lingua'],
    'Product filters' => ['fr-FR' => 'Filtres produits', 'en-US' => 'Product filters', 'es-ES' => 'Filtros de productos', 'it-IT' => 'Filtri prodotto'],
    'result' => ['fr-FR' => 'résultat', 'en-US' => 'result', 'es-ES' => 'resultado', 'it-IT' => 'risultato'],
    'results' => ['fr-FR' => 'résultats', 'en-US' => 'results', 'es-ES' => 'resultados', 'it-IT' => 'risultati'],
    '(No option selected)' => ['fr-FR' => '(Aucune option sélectionnée)', 'en-US' => '(No option selected)', 'es-ES' => '(Ninguna opción seleccionada)', 'it-IT' => '(Nessuna opzione selezionata)'],
    'Close search' => ['fr-FR' => 'Fermer la recherche', 'en-US' => 'Close search', 'es-ES' => 'Cerrar la búsqueda', 'it-IT' => 'Chiudi la ricerca'],

    /* ── Panier / commande ───────────────────────────────────────────── */
    'Added to your cart' => ['fr-FR' => 'Ajouté à votre panier', 'en-US' => 'Added to your cart', 'es-ES' => 'Añadido a tu carrito', 'it-IT' => 'Aggiunto al carrello'],

    /* ── Compte client ───────────────────────────────────────────────── */
    'Mon compte' => ['fr-FR' => 'Mon compte', 'en-US' => 'My account', 'es-ES' => 'Mi cuenta', 'it-IT' => 'Il mio account'],
    'Your password is too weak' => ['fr-FR' => 'Votre mot de passe est trop faible', 'en-US' => 'Your password is too weak', 'es-ES' => 'Tu contraseña es demasiado débil', 'it-IT' => 'La password è troppo debole'],
    'Your password is valid' => ['fr-FR' => 'Votre mot de passe est valide', 'en-US' => 'Your password is valid', 'es-ES' => 'Tu contraseña es válida', 'it-IT' => 'La password è valida'],
    'Your password length is invalid' => ['fr-FR' => 'La longueur de votre mot de passe est invalide', 'en-US' => 'Your password length is invalid', 'es-ES' => 'La longitud de tu contraseña no es válida', 'it-IT' => 'La lunghezza della password non è valida'],
    'View and manage your available vouchers and discount codes for use on your next purchase.' => [
        'fr-FR' => 'Consultez et gérez vos bons de réduction disponibles pour votre prochain achat.',
        'en-US' => 'View and manage your available vouchers and discount codes for use on your next purchase.',
        'es-ES' => 'Consulta y gestiona tus vales y códigos de descuento disponibles para tu próxima compra.',
        'it-IT' => 'Consulta e gestisci i tuoi buoni e codici sconto disponibili per il prossimo acquisto.',
    ],
    'Here is a list of pending merchandise returns.' => [
        'fr-FR' => 'Voici la liste des retours de marchandise en cours.',
        'en-US' => 'Here is a list of pending merchandise returns.',
        'es-ES' => 'Aquí tienes la lista de devoluciones pendientes.',
        'it-IT' => "Ecco l'elenco dei resi in sospeso.",
    ],
    'Add a new address to your account.' => [
        'fr-FR' => 'Ajoutez une nouvelle adresse à votre compte.',
        'en-US' => 'Add a new address to your account.',
        'es-ES' => 'Añade una nueva dirección a tu cuenta.',
        'it-IT' => 'Aggiungi un nuovo indirizzo al tuo account.',
    ],
    'Products details' => ['fr-FR' => 'Détails des produits', 'en-US' => 'Products details', 'es-ES' => 'Detalles de los productos', 'it-IT' => 'Dettagli dei prodotti'],
    'Order totals' => ['fr-FR' => 'Totaux de la commande', 'en-US' => 'Order totals', 'es-ES' => 'Totales del pedido', 'it-IT' => "Totali dell'ordine"],
    'Update your personal information to keep your account details up to date.' => [
        'fr-FR' => 'Mettez à jour vos informations personnelles pour garder votre compte à jour.',
        'en-US' => 'Update your personal information to keep your account details up to date.',
        'es-ES' => 'Actualiza tu información personal para mantener tu cuenta al día.',
        'it-IT' => 'Aggiorna i tuoi dati personali per mantenere il tuo account aggiornato.',
    ],
    'Manage your addresses for billing and delivery to streamline your future orders.' => [
        'fr-FR' => 'Gérez vos adresses de facturation et de livraison pour simplifier vos prochaines commandes.',
        'en-US' => 'Manage your addresses for billing and delivery to streamline your future orders.',
        'es-ES' => 'Gestiona tus direcciones de facturación y envío para agilizar tus próximos pedidos.',
        'it-IT' => 'Gestisci i tuoi indirizzi di fatturazione e spedizione per semplificare i prossimi ordini.',
    ],
    'Toggle your account links' => ['fr-FR' => 'Afficher/masquer les liens de votre compte', 'en-US' => 'Toggle your account links', 'es-ES' => 'Mostrar u ocultar los enlaces de tu cuenta', 'it-IT' => 'Mostra o nascondi i link del tuo account'],

    /* ── Formulaires ─────────────────────────────────────────────────── */
    '-- choose a product --' => ['fr-FR' => '-- choisir un produit --', 'en-US' => '-- choose a product --', 'es-ES' => '-- elegir un producto --', 'it-IT' => '-- scegli un prodotto --'],
    'Return notes' => ['fr-FR' => 'Notes de retour', 'en-US' => 'Return notes', 'es-ES' => 'Notas de devolución', 'it-IT' => 'Note di reso'],

    /* ── Menu ────────────────────────────────────────────────────────── */
    'Main navigation' => ['fr-FR' => 'Navigation principale', 'en-US' => 'Main navigation', 'es-ES' => 'Navegación principal', 'it-IT' => 'Navigazione principale'],
    'Open mobile menu' => ['fr-FR' => 'Ouvrir le menu mobile', 'en-US' => 'Open mobile menu', 'es-ES' => 'Abrir el menú móvil', 'it-IT' => 'Apri il menu mobile'],
    'Go back to main menu' => ['fr-FR' => 'Revenir au menu principal', 'en-US' => 'Go back to main menu', 'es-ES' => 'Volver al menú principal', 'it-IT' => 'Torna al menu principale'],

    /* ── Module : avis produits ──────────────────────────────────────── */
    'Rated' => ['fr-FR' => 'Noté', 'en-US' => 'Rated', 'es-ES' => 'Valorado', 'it-IT' => 'Valutato'],
    'out of 5 stars based on ' => ['fr-FR' => 'sur 5 étoiles, sur la base de ', 'en-US' => 'out of 5 stars based on ', 'es-ES' => 'sobre 5 estrellas basado en ', 'it-IT' => 'su 5 stelle basato su '],
    'review(s)' => ['fr-FR' => 'avis', 'en-US' => 'review(s)', 'es-ES' => 'opinión(es)', 'it-IT' => 'recensione/i'],
    'out of 5 stars' => ['fr-FR' => 'sur 5 étoiles', 'en-US' => 'out of 5 stars', 'es-ES' => 'sobre 5 estrellas', 'it-IT' => 'su 5 stelle'],
    'useful review(s). Mark this review as useful.' => ['fr-FR' => 'avis utile(s). Marquez cet avis comme utile.', 'en-US' => 'useful review(s). Mark this review as useful.', 'es-ES' => 'opinión(es) útil(es). Marca esta opinión como útil.', 'it-IT' => 'recensione/i utile/i. Contrassegna questa recensione come utile.'],
    'not useful review(s). Mark this review as not useful.' => ['fr-FR' => 'avis inutile(s). Marquez cet avis comme inutile.', 'en-US' => 'not useful review(s). Mark this review as not useful.', 'es-ES' => 'opinión(es) no útil(es). Marca esta opinión como no útil.', 'it-IT' => 'recensione/i inutile/i. Contrassegna questa recensione come inutile.'],
    'Rating for ' => ['fr-FR' => 'Note pour ', 'en-US' => 'Rating for ', 'es-ES' => 'Valoración de ', 'it-IT' => 'Valutazione per '],
    'Title of your review' => ['fr-FR' => 'Titre de votre avis', 'en-US' => 'Title of your review', 'es-ES' => 'Título de tu opinión', 'it-IT' => 'Titolo della tua recensione'],
    'Send review' => ['fr-FR' => "Envoyer l'avis", 'en-US' => 'Send review', 'es-ES' => 'Enviar opinión', 'it-IT' => 'Invia recensione'],
    'Product comments pagination' => ['fr-FR' => 'Pagination des avis produits', 'en-US' => 'Product comments pagination', 'es-ES' => 'Paginación de opiniones de productos', 'it-IT' => 'Paginazione delle recensioni'],
    'Go to page %pageCount%' => ['fr-FR' => 'Aller à la page %pageCount%', 'en-US' => 'Go to page %pageCount%', 'es-ES' => 'Ir a la página %pageCount%', 'it-IT' => 'Vai alla pagina %pageCount%'],

    /* ── Module : alertes e-mail ─────────────────────────────────────── */
    'Manage your mail alerts to stay informed about product availability and updates.' => [
        'fr-FR' => 'Gérez vos alertes e-mail pour rester informé de la disponibilité des produits et des nouveautés.',
        'en-US' => 'Manage your mail alerts to stay informed about product availability and updates.',
        'es-ES' => 'Gestiona tus alertas por correo para mantenerte informado sobre la disponibilidad y las novedades de los productos.',
        'it-IT' => 'Gestisci i tuoi avvisi e-mail per restare informato sulla disponibilità dei prodotti e sugli aggiornamenti.',
    ],
    'You do not have any mail alerts.' => [
        'fr-FR' => "Vous n'avez aucune alerte e-mail.",
        'en-US' => 'You do not have any mail alerts.',
        'es-ES' => 'No tienes ninguna alerta por correo.',
        'it-IT' => 'Non hai alcun avviso e-mail.',
    ],

    /* ── Module : newsletter ─────────────────────────────────────────── */
    'Subscribe to our newsletter' => ['fr-FR' => 'Inscrivez-vous à notre newsletter', 'en-US' => 'Subscribe to our newsletter', 'es-ES' => 'Suscríbete a nuestro boletín', 'it-IT' => 'Iscriviti alla nostra newsletter'],
];

/* ---------------------------------------------------------------------- */
/* Écriture dans les catalogues XLIFF                                      */
/* ---------------------------------------------------------------------- */
function xlfPath(string $root, string $locale, string $domain): string
{
    $base = implode('', array_map('ucfirst', explode('.', $domain)));

    return "$root/translations/$locale/$base.$locale.xlf";
}

function esc(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_XML1, 'UTF-8');
}

$added = 0;
$missingDict = [];
$touched = [];

foreach ($audit as $locale => $domains) {
    foreach ($domains as $domain => $rows) {
        foreach ($rows as $row) {
            $source = $row['source'];

            // Traduction fournie par le dictionnaire…
            if (isset($T[$source][$locale])) {
                $target = $T[$source][$locale];
            } elseif ($locale === 'fr-FR' && $domain === 'Shop.Theme.FullPowerExhaust') {
                // …ou chaîne déjà rédigée en français dans le template :
                // on l’inscrit explicitement dans le catalogue FR.
                $target = $source;
            } else {
                $missingDict[$locale][$domain][] = $source;
                continue;
            }

            $path = xlfPath($root, $locale, $domain);
            if (!is_file($path)) {
                // Catalogue absent : on le crée (thème FullPowerExhaust en FR).
                $header = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
                    . '<xliff xmlns="urn:oasis:names:tc:xliff:document:1.2" version="1.2">' . "\n"
                    . '  <file original="' . esc($domain) . '.xlf" source-language="en" target-language="' . esc(substr($locale, 0, 2)) . '" datatype="plaintext">' . "\n"
                    . "    <body>\n    </body>\n  </file>\n</xliff>\n";
                if (!$dry) {
                    @mkdir(dirname($path), 0755, true);
                    file_put_contents($path, $header);
                }
            }

            $content = is_file($path) ? file_get_contents($path) : '';
            $id = md5($source);

            if (strpos($content, 'id="' . $id . '"') !== false) {
                continue;   // déjà présent
            }

            $unit = '      <trans-unit id="' . $id . '" approved="yes">' . "\n"
                . '        <source>' . esc($source) . '</source>' . "\n"
                . '        <target state="final">' . esc($target) . '</target>' . "\n"
                . '      </trans-unit>' . "\n";

            $content = preg_replace('/(\s*)<\/body>/', "\n" . $unit . '    </body>', $content, 1);
            if (!$dry) {
                file_put_contents($path, $content);
            }
            $touched[$locale][$domain] = ($touched[$locale][$domain] ?? 0) + 1;
            ++$added;
        }
    }
}

echo ($dry ? '[SIMULATION] ' : '') . "Traductions ajoutées : $added\n\n";
foreach ($touched as $locale => $domains) {
    echo "── $locale\n";
    foreach ($domains as $d => $n) {
        printf("   %-42s %d\n", $d, $n);
    }
}

if ($missingDict) {
    echo "\n⚠️  Chaînes sans traduction dans le dictionnaire (à compléter) :\n";
    foreach ($missingDict as $locale => $domains) {
        foreach ($domains as $d => $sources) {
            foreach (array_unique($sources) as $s) {
                echo "   [$locale][$d] $s\n";
            }
        }
    }
}
