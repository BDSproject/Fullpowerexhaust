<?php
/**
 * FULLPOWEREXHAUST — complète les traductions manquantes (boutons, libellés,
 * aria-labels) dans les 4 langues pour les domaines du thème.
 *
 * Usage : php -d memory_limit=768M tools/translate_buttons.php
 */

if (PHP_SAPI !== 'cli') {
    die('CLI only');
}

require_once __DIR__ . '/../config/config.inc.php';

// source => [fr, en, es, it]
$translations = [
    // ── Shop.Theme.Actions ────────────────────────────────────────────────
    'Shop.Theme.Actions' => [
        'Remove image' => ['Supprimer l\'image', 'Remove image', 'Eliminar imagen', 'Rimuovi immagine'],
        'Save customization' => ['Enregistrer la personnalisation', 'Save customization', 'Guardar personalización', 'Salva personalizzazione'],
        'Address details:' => ['Détails de l\'adresse :', 'Address details:', 'Detalles de la dirección:', 'Dettagli dell\'indirizzo:'],
        'Show cart details' => ['Afficher le détail du panier', 'Show cart details', 'Ver detalles del carrito', 'Mostra dettagli carrello'],
        'Edit addresses' => ['Modifier les adresses', 'Edit addresses', 'Editar direcciones', 'Modifica indirizzi'],
        'Edit your delivery address' => ['Modifier l\'adresse de livraison', 'Edit your delivery address', 'Editar dirección de entrega', 'Modifica indirizzo di consegna'],
        'Delete your delivery address' => ['Supprimer l\'adresse de livraison', 'Delete your delivery address', 'Eliminar dirección de entrega', 'Elimina indirizzo di consegna'],
        'Edit your invoice address' => ['Modifier l\'adresse de facturation', 'Edit your invoice address', 'Editar dirección de facturación', 'Modifica indirizzo di fatturazione'],
        'Delete your invoice address' => ['Supprimer l\'adresse de facturation', 'Delete your invoice address', 'Eliminar dirección de facturación', 'Elimina indirizzo di fatturazione'],
        'Edit your shipping method' => ['Modifier le mode de livraison', 'Edit your shipping method', 'Editar método de envío', 'Modifica metodo di spedizione'],
        'Edit your cart' => ['Modifier le panier', 'Edit your cart', 'Editar tu carrito', 'Modifica il carrello'],
        'Apply voucher' => ['Appliquer le bon', 'Apply voucher', 'Aplicar cupón', 'Applica buono'],
        'Go to previous page' => ['Page précédente', 'Previous page', 'Página anterior', 'Pagina precedente'],
        'Go to next page' => ['Page suivante', 'Next page', 'Página siguiente', 'Pagina successiva'],
        'Show password' => ['Afficher le mot de passe', 'Show password', 'Mostrar contraseña', 'Mostra password'],
        'Hide password' => ['Masquer le mot de passe', 'Hide password', 'Ocultar contraseña', 'Nascondi password'],
        'Quick view %product_name%' => ['Aperçu rapide %product_name%', 'Quick view %product_name%', 'Vista rápida %product_name%', 'Vista rapida %product_name%'],
        'View product %product_name%' => ['Voir le produit %product_name%', 'View product %product_name%', 'Ver producto %product_name%', 'Vedi prodotto %product_name%'],
        'Send your message' => ['Envoyer votre demande', 'Send your message', 'Enviar tu mensaje', 'Invia il tuo messaggio'],
    ],
    // ── Shop.Theme.Catalog ────────────────────────────────────────────────
    'Shop.Theme.Catalog' => [
        'quick view' => ['aperçu rapide', 'quick view', 'vista rápida', 'vista rapida'],
        'Slide to product image %number%' => ['Aller à l\'image produit %number%', 'Slide to product image %number%', 'Ir a la imagen %number%', 'Vai all\'immagine %number%'],
        'Filtrer par marque' => ['Filter by brand', 'Filter by brand', 'Filtrar por marca', 'Filtra per marca'],
        'No products available at the moment' => ['Aucun produit disponible pour le moment', 'No products available at the moment', 'No hay productos disponibles por el momento', 'Nessun prodotto disponibile al momento'],
        'Products details' => ['Détails des produits', 'Products details', 'Detalles de los productos', 'Dettagli dei prodotti'],
        'Select product to return' => ['Sélectionner le produit à retourner', 'Select product to return', 'Seleccionar producto a devolver', 'Seleziona prodotto da restituire'],
        'Select all products' => ['Sélectionner tous les produits', 'Select all products', 'Seleccionar todos los productos', 'Seleziona tutti i prodotti'],
        'Order totals' => ['Totaux de la commande', 'Order totals', 'Totales del pedido', 'Totali dell\'ordine'],
        '%label%' => ['%label%', '%label%', '%label%', '%label%'],
    ],
    // ── Shop.Theme.Global ─────────────────────────────────────────────────
    'Shop.Theme.Global' => [
        'Change sort order' => ['Changer l\'ordre de tri', 'Change sort order', 'Cambiar orden', 'Cambia ordinamento'],
        'Previous image' => ['Image précédente', 'Previous image', 'Imagen anterior', 'Immagine precedente'],
        'Next image' => ['Image suivante', 'Next image', 'Imagen siguiente', 'Immagine successiva'],
        'Open zoomed product image gallery' => ['Ouvrir la galerie d\'images agrandies', 'Open zoomed product image gallery', 'Abrir galería de imágenes ampliadas', 'Apri galleria immagini ingrandite'],
        'Virtual product(s)' => ['Produit(s) virtuel(s)', 'Virtual product(s)', 'Producto(s) virtual(es)', 'Prodotto(i) virtuale(i)'],
        'No delivery service' => ['Pas de livraison', 'No delivery service', 'Sin servicio de entrega', 'Nessun servizio di consegna'],
        'Main navigation' => ['Navigation principale', 'Main navigation', 'Navegación principal', 'Navigazione principale'],
        'Skip to main content' => ['Aller au contenu principal', 'Skip to main content', 'Saltar al contenido principal', 'Vai al contenuto principale'],
        'Virtual product(s): No delivery service' => ['Produit(s) virtuel(s) : pas de livraison', 'Virtual product(s): No delivery service', 'Producto(s) virtual(es): sin entrega', 'Prodotto(i) virtuale(i): nessuna consegna'],
        'Toggle store information' => ['Afficher/masquer les informations boutique', 'Toggle store information', 'Mostrar/ocultar información de la tienda', 'Mostra/nascondi informazioni negozio'],
        'Toggle products links' => ['Afficher/masquer les liens produits', 'Toggle products links', 'Mostrar/ocultar enlaces de productos', 'Mostra/nascondi link prodotti'],
        'Toggle our company links' => ['Afficher/masquer les liens entreprise', 'Toggle our company links', 'Mostrar/ocultar enlaces de la empresa', 'Mostra/nascondi link azienda'],
        'Toggle your account links' => ['Afficher/masquer les liens compte', 'Toggle your account links', 'Mostrar/ocultar enlaces de cuenta', 'Mostra/nascondi link account'],
        'Subscribe to our newsletter' => ['Abonnez-vous à notre newsletter', 'Subscribe to our newsletter', 'Suscríbete a nuestro boletín', 'Iscriviti alla nostra newsletter'],
    ],
    // ── Shop.Theme.Checkout ───────────────────────────────────────────────
    'Shop.Theme.Checkout' => [
        'View my customization' => ['Voir ma personnalisation', 'View my customization', 'Ver mi personalización', 'Vedi personalizzazione'],
        'Gift(s)' => ['Cadeau(x)', 'Gift(s)', 'Regalo(s)', 'Regalo(i)'],
        'Delivery Option' => ['Mode de livraison', 'Delivery Option', 'Opción de entrega', 'Opzione di consegna'],
        'Delivery Information' => ['Informations de livraison', 'Delivery Information', 'Información de entrega', 'Informazioni di consegna'],
        'Products in cart' => ['Produits dans le panier', 'Products in cart', 'Productos en el carrito', 'Prodotti nel carrello'],
        'Place Order' => ['Commander', 'Place Order', 'Realizar pedido', 'Effettua l\'ordine'],
        'Added vouchers' => ['Bons ajoutés', 'Added vouchers', 'Cupones añadidos', 'Buoni aggiunti'],
        'Terms and conditions' => ['Conditions générales', 'Terms and conditions', 'Términos y condiciones', 'Termini e condizioni'],
    ],
    // ── Shop.Theme.Customeraccount ────────────────────────────────────────
    'Shop.Theme.Customeraccount' => [
        'My account (%firstname% %lastname%)' => ['Mon compte (%firstname% %lastname%)', 'My account (%firstname% %lastname%)', 'Mi cuenta (%firstname% %lastname%)', 'Il mio account (%firstname% %lastname%)'],
        'My account navigation sidebar' => ['Navigation du compte', 'My account navigation sidebar', 'Navegación de mi cuenta', 'Navigazione account'],
        'View order reference' => ['Voir la référence de commande', 'View order reference', 'Ver referencia del pedido', 'Vedi riferimento ordine'],
        'Returns details' => ['Détails des retours', 'Returns details', 'Detalles de las devoluciones', 'Dettagli dei resi'],
        'View return number' => ['Voir le numéro de retour', 'View return number', 'Ver número de devolución', 'Vedi numero reso'],
        'View returns details' => ['Voir le détail des retours', 'View returns details', 'Ver detalles de devoluciones', 'Vedi dettagli resi'],
        'Customer service messages' => ['Messages du service client', 'Customer service messages', 'Mensajes del servicio al cliente', 'Messaggi del servizio clienti'],
        'Shipment tracking details' => ['Détails du suivi d\'expédition', 'Shipment tracking details', 'Detalles del seguimiento del envío', 'Dettagli tracciamento spedizione'],
        'Order Reference: %reference% - placed on %date%' => ['Référence commande : %reference% - passée le %date%', 'Order Reference: %reference% - placed on %date%', 'Referencia del pedido: %reference% - realizado el %date%', 'Riferimento ordine: %reference% - effettuato il %date%'],
    ],
];

$locales = [
    'fr' => 'fr-FR',
    'en' => 'en-US',
    'es' => 'es-ES',
    'it' => 'it-IT',
];
$idxByIso = ['fr' => 0, 'en' => 1, 'es' => 2, 'it' => 3];

$added = 0;
foreach ($translations as $domain => $strings) {
    $baseName = str_replace('.', '', $domain); // Shop.Theme.Actions → ShopThemeActions
    foreach ($locales as $iso => $locale) {
        $idx = $idxByIso[$iso];
        $file = _PS_ROOT_DIR_ . '/translations/' . $locale . '/' . $baseName . '.' . $locale . '.xlf';
        if (!file_exists($file)) {
            echo "  ! fichier absent : {$file}\n";
            continue;
        }
        $xlf = file_get_contents($file);
        $newUnits = '';
        foreach ($strings as $src => $tr) {
            $target = $tr[$idx];
            if (strpos($xlf, '<source>' . htmlspecialchars($src, ENT_XML1) . '</source>') !== false) {
                continue; // déjà présent
            }
            $newUnits .= '    <trans-unit id="' . md5($src) . '" approved="yes"><source>' . htmlspecialchars($src, ENT_XML1) . '</source><target state="final">' . htmlspecialchars($target, ENT_XML1) . '</target><note>File: fullpowerexhaust/custom</note></trans-unit>';
        }
        if ($newUnits) {
            $xlf = str_replace('</body>', $newUnits . '</body>', $xlf);
            file_put_contents($file, $xlf);
            $added += substr_count($newUnits, '<trans-unit');
            echo "✔ {$baseName}.{$locale}.xlf : +" . substr_count($newUnits, '<trans-unit') . " traductions\n";
        }
    }
}

echo "\nTerminé : {$added} traductions ajoutées.\n";
