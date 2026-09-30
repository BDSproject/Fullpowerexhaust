<?php
/**
 * FULLPOWEREXHAUST — crée/maj les pages légales du modèle Figma en pages CMS.
 * Usage : php -d memory_limit=768M tools/create_legal_pages.php
 */

if (PHP_SAPI !== 'cli') {
    die('CLI only');
}

require_once __DIR__ . '/../config/config.inc.php';

$idFr = (int) Language::getIdByIso('fr');

$pages = [
    // ─── CGV ────────────────────────────────────────────────────────────────
    [
        'title' => 'Conditions générales de vente',
        'desc'  => 'Conditions générales de vente de FullPowerExhaust',
        'slug'  => 'conditions-generales-de-vente',
        'html'  => '<p>Dernière mise à jour : 1er janvier 2025. Société FULLPOWEREXHAUST, e-commerce de pièces d\'échappement d\'occasion, immatriculée au RCS de Paris.</p>'
            . '<h2>Article 1 — Objet et champ d\'application</h2>'
            . '<p>Les présentes Conditions Générales de Vente (CGV) régissent l\'ensemble des ventes de pièces d\'échappement d\'occasion réalisées via le site fullpowerexhaust.com. Toute commande passée implique l\'acceptation pleine et entière des présentes CGV.</p>'
            . '<h2>Article 2 — Produits</h2>'
            . '<p>Tous les produits proposés sur le site sont des pièces d\'occasion ayant été contrôlées visuellement et fonctionnellement avant mise en vente. Les photos et descriptions sont indicatives. Les pièces sont vendues en l\'état, sans garantie constructeur. Un contrôle visuel est effectué par nos soins avant expédition.</p>'
            . '<p>FULLPOWEREXHAUST se réserve le droit de retirer tout produit jugé non conforme à la vente.</p>'
            . '<h2>Article 3 — Prix</h2>'
            . '<p>Les prix sont indiqués en euros TTC. Les frais de livraison sont calculés et affichés au moment de la validation du panier. FULLPOWEREXHAUST se réserve le droit de modifier ses prix à tout moment, sans préavis. Les commandes sont facturées au prix en vigueur au moment de la validation.</p>'
            . '<h2>Article 4 — Commandes et paiement</h2>'
            . '<p>La commande est définitive à réception du paiement intégral. Modes de paiement acceptés : carte bancaire (Visa, Mastercard), PayPal, virement bancaire. En cas de refus de paiement, la commande est automatiquement annulée.</p>'
            . '<h2>Article 5 — Livraison</h2>'
            . '<p>Les délais de livraison sont indicatifs. Expédition sous 3 à 5 jours ouvrés après réception du paiement. FULLPOWEREXHAUST n\'est pas responsable des retards imputables au transporteur. Tout colis endommagé doit être refusé à la livraison ou signalé par écrit au transporteur.</p>'
            . '<h2>Article 6 — Droit de rétractation</h2>'
            . '<p>Conformément à l\'article L221-18 du Code de la consommation, le client dispose d\'un délai de 14 jours calendaires à compter de la réception pour exercer son droit de rétractation, sans justification. Ce délai ne s\'applique pas aux pièces endommagées ou montées après réception.</p>'
            . '<p>Pour exercer ce droit, contactez-nous à retours@fullpowerexhaust.com. Les frais de retour sont à la charge du client. Voir notre politique de retour complète pour les conditions d\'éligibilité.</p>'
            . '<h2>Article 7 — Garantie légale</h2>'
            . '<p>Conformément aux articles L217-4 et suivants du Code de la consommation, les produits bénéficient de la garantie légale de conformité. Pour les défauts cachés, la garantie légale s\'applique selon l\'article 1641 du Code civil. Les pièces d\'occasion bénéficient d\'une garantie de 6 mois à compter de la livraison.</p>'
            . '<h2>Article 8 — Responsabilité</h2>'
            . '<p>FULLPOWEREXHAUST ne saurait être tenu responsable d\'une mauvaise utilisation des pièces, d\'une installation non conforme aux normes, ou de dommages indirects résultant de l\'utilisation des produits. Le montage des pièces doit être réalisé par un professionnel habilité.</p>'
            . '<h2>Article 9 — Litiges</h2>'
            . '<p>En cas de litige, les parties s\'efforceront de trouver une solution amiable. À défaut, les tribunaux compétents seront ceux du ressort du siège social de FULLPOWEREXHAUST. Le droit applicable est le droit français. Médiateur : FEVAD (www.fevad.com).</p>',
    ],
    // ─── CGU ────────────────────────────────────────────────────────────────
    [
        'title' => 'Conditions générales d\'utilisation',
        'desc'  => 'Conditions générales d\'utilisation du site',
        'slug'  => 'conditions-generales-d-utilisation',
        'html'  => '<p>Les présentes CGU définissent les conditions d\'accès et d\'utilisation du site fullpowerexhaust.com.</p>'
            . '<h2>Article 1 — Accès au site</h2>'
            . '<p>L\'accès au site est libre et gratuit. FULLPOWEREXHAUST se réserve le droit de suspendre ou d\'interrompre l\'accès au site pour maintenance ou force majeure, sans préavis ni indemnité.</p>'
            . '<h2>Article 2 — Création de compte</h2>'
            . '<p>La création d\'un compte est nécessaire pour passer commande. L\'utilisateur s\'engage à fournir des informations exactes et à maintenir ses identifiants confidentiels. Tout accès frauduleux engage la responsabilité de l\'utilisateur.</p>'
            . '<h2>Article 3 — Propriété intellectuelle</h2>'
            . '<p>L\'ensemble des éléments du site (textes, images, logos, structure) est la propriété exclusive de FULLPOWEREXHAUST. Toute reproduction ou utilisation sans autorisation écrite est strictement interdite et constitue une contrefaçon.</p>'
            . '<h2>Article 4 — Comportement utilisateur</h2>'
            . '<p>Il est interdit d\'utiliser le site à des fins illicites, de perturber son fonctionnement, ou de publier des contenus abusifs. FULLPOWEREXHAUST se réserve le droit de suspendre tout compte en violation de ces règles.</p>'
            . '<h2>Article 5 — Liens externes</h2>'
            . '<p>Le site peut contenir des liens vers des sites tiers. FULLPOWEREXHAUST n\'est pas responsable du contenu de ces sites et décline toute responsabilité quant à leur utilisation.</p>'
            . '<h2>Article 6 — Modification des CGU</h2>'
            . '<p>FULLPOWEREXHAUST se réserve le droit de modifier les présentes CGU à tout moment. Les utilisateurs seront informés par email ou notification sur le site. La poursuite de l\'utilisation du site après modification vaut acceptation des nouvelles CGU.</p>'
            . '<h2>Article 7 — Droit applicable</h2>'
            . '<p>Les présentes CGU sont soumises au droit français. Tout litige relève de la compétence des tribunaux français.</p>',
    ],
    // ─── Privacy ────────────────────────────────────────────────────────────
    [
        'title' => 'Politique de confidentialité',
        'desc'  => 'Politique de confidentialité de FullPowerExhaust',
        'slug'  => 'politique-de-confidentialite',
        'html'  => '<p>FULLPOWEREXHAUST s\'engage à protéger vos données personnelles conformément au RGPD (Règlement UE 2016/679) et à la loi Informatique et Libertés.</p>'
            . '<h2>1. Responsable du traitement</h2>'
            . '<p>FULLPOWEREXHAUST — contact@fullpowerexhaust.com. DPO : dpo@fullpowerexhaust.com.</p>'
            . '<h2>2. Données collectées</h2>'
            . '<p>Nous collectons les données suivantes : nom, prénom, adresse e-mail, adresse postale, numéro de téléphone (facultatif), données de navigation (cookies techniques), historique de commandes.</p>'
            . '<p>Nous ne collectons aucune donnée sensible. Le paiement est traité par des prestataires certifiés PCI-DSS — nous ne stockons aucune donnée bancaire.</p>'
            . '<h2>3. Finalités du traitement</h2>'
            . '<p>Vos données sont utilisées pour : traitement et suivi des commandes, gestion de votre compte client, communication transactionnelle (confirmation, expédition), obligations légales et comptables, amélioration de notre service.</p>'
            . '<p>Avec votre consentement explicite : envoi de newsletters et offres commerciales.</p>'
            . '<h2>4. Durée de conservation</h2>'
            . '<p>Données de compte : 3 ans après la dernière commande. Données de facturation : 10 ans (obligations légales). Données de navigation : 13 mois maximum.</p>'
            . '<h2>5. Partage des données</h2>'
            . '<p>Vos données ne sont pas vendues. Elles peuvent être transmises à : nos prestataires logistiques (livraison), notre prestataire de paiement, nos outils d\'analyse (anonymisés). Tous nos sous-traitants sont soumis à des engagements de confidentialité stricts.</p>'
            . '<h2>6. Vos droits</h2>'
            . '<p>Conformément au RGPD, vous disposez des droits suivants : accès, rectification, effacement, limitation, portabilité, opposition. Pour exercer ces droits, contactez-nous à dpo@fullpowerexhaust.com. Vous pouvez également introduire une réclamation auprès de la CNIL (cnil.fr).</p>',
    ],
    // ─── Cookies ────────────────────────────────────────────────────────────
    [
        'title' => 'Politique de cookies',
        'desc'  => 'Politique de cookies de FullPowerExhaust',
        'slug'  => 'politique-de-cookies',
        'html'  => '<p>En naviguant sur fullpowerexhaust.com, vous acceptez l\'utilisation de cookies conformément à la présente politique.</p>'
            . '<h2>Qu\'est-ce qu\'un cookie ?</h2>'
            . '<p>Un cookie est un petit fichier déposé sur votre terminal lors de la visite d\'un site web. Il permet de reconnaître votre navigateur lors de visites ultérieures et d\'améliorer votre expérience.</p>'
            . '<h2>Cookies essentiels — Toujours actifs</h2>'
            . '<p>Ces cookies sont strictement nécessaires au fonctionnement du site : gestion de session, panier, authentification, sécurité. Ils ne peuvent pas être désactivés.</p>'
            . '<ul><li><strong>Cookies fonctionnels</strong> : actifs en permanence.</li></ul>'
            . '<h2>Cookies analytiques — Optionnels</h2>'
            . '<p>Ces cookies nous permettent de mesurer l\'audience du site (pages vues, sources de trafic) via Google Analytics. Les données sont anonymisées.</p>'
            . '<ul><li><strong>Google Analytics</strong> : désactivable à tout moment.</li></ul>'
            . '<h2>Cookies marketing — Optionnels</h2>'
            . '<p>Ces cookies servent à personnaliser les publicités sur d\'autres sites et à mesurer l\'efficacité de nos campagnes (Meta Pixel, Google Ads).</p>'
            . '<ul><li><strong>Publicité ciblée</strong> : désactivable à tout moment.</li></ul>'
            . '<h2>Gestion de vos préférences</h2>'
            . '<p>Vous pouvez modifier vos préférences à tout moment via ce panneau ou via les paramètres de votre navigateur. La suppression des cookies peut affecter certaines fonctionnalités du site.</p>',
    ],
    // ─── Refund ─────────────────────────────────────────────────────────────
    [
        'title' => 'Politique de remboursement',
        'desc'  => 'Politique de remboursement de FullPowerExhaust',
        'slug'  => 'politique-de-remboursement',
        'html'  => '<p>En raison de la nature des pièces vendues (occasion), notre politique de remboursement est stricte. Lisez attentivement les conditions ci-dessous avant tout achat.</p>'
            . '<h2>1. Droit légal de rétractation (14 jours)</h2>'
            . '<p>Conformément à la loi, vous disposez de 14 jours calendaires à compter de la réception pour vous rétracter, sans motif. Ce droit est soumis aux conditions strictes suivantes :</p>'
            . '<ul>'
            . '<li>La pièce n\'a pas été montée ni essayée sur le véhicule.</li>'
            . '<li>La pièce est retournée dans son emballage d\'origine, en parfait état.</li>'
            . '<li>Aucune modification ou découpe n\'a été effectuée sur la pièce.</li>'
            . '<li>La demande est formulée par écrit dans les 14 jours (retours@fullpowerexhaust.com).</li>'
            . '</ul>'
            . '<h2>2. Remboursement pour défaut caché</h2>'
            . '<p>Si la pièce présente un défaut caché non visible lors de la réception et non mentionné dans la description (ex : fissure interne, corrosion masquée), un remboursement intégral est accordé sous conditions :</p>'
            . '<ul>'
            . '<li>Signalement sous 48h après réception (photos exigées).</li>'
            . '<li>Rapport d\'un garagiste certifié attestant le défaut (facultatif mais recommandé).</li>'
            . '<li>Retour de la pièce à nos frais après validation du dossier.</li>'
            . '</ul>'
            . '<h2>3. Cas exclus du remboursement</h2>'
            . '<p>Aucun remboursement ne sera accordé dans les cas suivants :</p>'
            . '<ul>'
            . '<li>Pièce montée, même partiellement.</li>'
            . '<li>Incompatibilité avec le véhicule non vérifiée avant l\'achat.</li>'
            . '<li>Dommages causés lors du montage ou de l\'utilisation.</li>'
            . '<li>Rouille ou usure supplémentaire constatée après réception.</li>'
            . '<li>Demande formulée après le délai de 14 jours.</li>'
            . '<li>Pièce retournée sans autorisation préalable de notre service client.</li>'
            . '</ul>'
            . '<h2>4. Modalités de remboursement</h2>'
            . '<p>Le remboursement est effectué sous 14 jours ouvrés après réception et validation de la pièce retournée, sur le moyen de paiement d\'origine. Les frais de livraison initiaux ne sont pas remboursés, sauf en cas de défaut caché avéré.</p>'
            . '<h2>5. Contact</h2>'
            . '<p>Pour toute demande : retours@fullpowerexhaust.com — Objet : REMBOURSEMENT + numéro de commande.</p>',
    ],
    // ─── Returns ────────────────────────────────────────────────────────────
    [
        'title' => 'Conditions de retour',
        'desc'  => 'Conditions de retour de FullPowerExhaust',
        'slug'  => 'conditions-de-retour',
        'html'  => '<h2>1. Procédure obligatoire</h2>'
            . '<p>Tout retour doit être préalablement autorisé par notre service client. Aucun colis non sollicité ne sera accepté. Pour initier un retour :</p>'
            . '<ul>'
            . '<li><strong>01</strong> — Envoyez un e-mail à retours@fullpowerexhaust.com avec votre numéro de commande et le motif du retour.</li>'
            . '<li><strong>02</strong> — Attendez notre confirmation et le numéro de retour (RMA) sous 48h ouvrées.</li>'
            . '<li><strong>03</strong> — Emballez soigneusement la pièce et apposez l\'étiquette de retour fournie.</li>'
            . '<li><strong>04</strong> — Déposez le colis chez le transporteur indiqué dans les 5 jours suivant l\'obtention du RMA.</li>'
            . '</ul>'
            . '<h2>2. État de la pièce retournée</h2>'
            . '<p>La pièce doit être retournée dans l\'état exact dans lequel elle a été reçue :</p>'
            . '<ul>'
            . '<li>Non montée, non modifiée, non découpée.</li>'
            . '<li>Propre — sans graisse supplémentaire ni produits chimiques.</li>'
            . '<li>Emballée de manière à éviter tout dommage lors du transport (mousse, carton renforcé).</li>'
            . '<li>Accompagnée d\'une copie de la facture et du numéro RMA.</li>'
            . '</ul>'
            . '<h2>3. Frais de retour</h2>'
            . '<p>Les frais de retour sont à la charge du client, sauf en cas de défaut caché avéré ou d\'erreur de notre part (mauvaise pièce expédiée). Dans ce cas, nous fournissons une étiquette prépayée.</p>'
            . '<p>Nous recommandons l\'envoi avec suivi et assurance pour les pièces de valeur supérieure à 100 €. FULLPOWEREXHAUST ne sera pas responsable des colis perdus ou endommagés lors du retour.</p>'
            . '<h2>4. Délai de traitement</h2>'
            . '<p>À réception de la pièce, notre équipe procède au contrôle sous 3 jours ouvrés. En cas de conformité, le remboursement ou l\'échange est traité selon notre politique de remboursement. Tout retour non conforme sera renvoyé à l\'expéditeur à ses frais.</p>'
            . '<h2>5. Échanges</h2>'
            . '<p>Les échanges sont possibles uniquement si la pièce demandée est disponible en stock. Dans le cas contraire, un remboursement sera effectué. Contactez-nous avant de retourner la pièce pour vérifier la disponibilité de l\'échange souhaité.</p>',
    ],
    // ─── Mentions légales ───────────────────────────────────────────────────
    [
        'title' => 'Mentions légales',
        'desc'  => 'Mentions légales de FullPowerExhaust',
        'slug'  => 'mentions-legales',
        'html'  => '<p>Conformément aux dispositions de la loi n° 2004-575 du 21 juin 2004 pour la confiance en l\'économie numérique, il est précisé aux utilisateurs du site fullpowerexhaust.com l\'identité des différents intervenants dans le cadre de sa réalisation et de son suivi.</p>'
            . '<h2>1. Éditeur du site</h2>'
            . '<p>Le site fullpowerexhaust.com est édité par la société FULLPOWEREXHAUST.</p>'
            . '<ul>'
            . '<li><strong>Forme juridique :</strong> SARL</li>'
            . '<li><strong>Capital social :</strong> 10 000 €</li>'
            . '<li><strong>Siège social :</strong> Paris, France</li>'
            . '<li><strong>RCS :</strong> Paris B XXX XXX XXX</li>'
            . '<li><strong>N° TVA intracommunautaire :</strong> FR XX XXX XXX XXX</li>'
            . '<li><strong>Email :</strong> contact@fullpowerexhaust.com</li>'
            . '<li><strong>Téléphone :</strong> +33 1 23 45 67 89</li>'
            . '</ul>'
            . '<h2>2. Directeur de la publication</h2>'
            . '<p>Le directeur de la publication du site fullpowerexhaust.com est le gérant de la société FULLPOWEREXHAUST.</p>'
            . '<h2>3. Hébergement</h2>'
            . '<p>Le site est hébergé par un prestataire d\'hébergement professionnel dont les coordonnées sont disponibles sur demande à l\'adresse contact@fullpowerexhaust.com.</p>'
            . '<h2>4. Propriété intellectuelle</h2>'
            . '<p>L\'ensemble du contenu du site fullpowerexhaust.com (textes, images, graphismes, logo, icônes, sons, logiciels…) est la propriété exclusive de FULLPOWEREXHAUST, à l\'exception des marques, logos ou contenus appartenant à d\'autres sociétés partenaires ou auteurs.</p>'
            . '<p>Toute reproduction, distribution, modification, adaptation, retransmission ou publication, même partielle, de ces différents éléments est strictement interdite sans l\'accord exprès par écrit de FULLPOWEREXHAUST. Cette représentation ou reproduction, par quelque procédé que ce soit, constitue une contrefaçon sanctionnée par les articles L.335-2 et suivants du Code de la propriété intellectuelle.</p>'
            . '<h2>5. Liens hypertextes</h2>'
            . '<p>Le site fullpowerexhaust.com peut contenir des liens vers d\'autres sites. FULLPOWEREXHAUST n\'a pas la possibilité de vérifier le contenu des sites ainsi visités, et n\'assumera en conséquence aucune responsabilité de ce fait.</p>'
            . '<h2>6. Données personnelles</h2>'
            . '<p>La collecte et le traitement des données à caractère personnel des utilisateurs sont régis par notre Politique de confidentialité disponible sur ce site. Conformément au Règlement Général sur la Protection des Données (RGPD), vous disposez d\'un droit d\'accès, de rectification, de suppression et d\'opposition aux données vous concernant. Pour exercer ces droits, contactez-nous à : contact@fullpowerexhaust.com.</p>'
            . '<h2>7. Droit applicable</h2>'
            . '<p>Les présentes mentions légales sont soumises au droit français. En cas de litige, les tribunaux français seront seuls compétents.</p>',
    ],
];

$position = 1;

foreach ($pages as $page) {
    $idCms = (int) Db::getInstance()->getValue(
        'SELECT id_cms FROM ' . _DB_PREFIX_ . 'cms_lang WHERE link_rewrite = "' . pSQL($page['slug']) . '" AND id_lang = ' . $idFr
    );

    if ($idCms) {
        // Mise à jour de la page existante
        Db::getInstance()->update('cms_lang', [
            'meta_title' => pSQL($page['title']),
            'head_seo_title' => pSQL($page['title']),
            'meta_description' => pSQL($page['desc']),
            'content' => pSQL($page['html'], true),
        ], 'id_cms = ' . $idCms . ' AND id_lang = ' . $idFr);
        echo "✔ MAJ CMS #{$idCms} — {$page['title']}\n";
    } else {
        // Création de la page
        $cms = new CMS();
        $cms->id_cms_category = 1;
        $cms->position = $position;
        $cms->active = 1;
        $cms->indexation = 1;
        $cms->meta_title = [$idFr => $page['title']];
        $cms->head_seo_title = [$idFr => $page['title']];
        $cms->meta_description = [$idFr => $page['desc']];
        $cms->link_rewrite = [$idFr => $page['slug']];
        $cms->content = [$idFr => $page['html']];
        if ($cms->add()) {
            echo "✔ Créé CMS #{$cms->id} — {$page['title']}\n";
        } else {
            echo "✖ Échec création — {$page['title']}\n";
        }
    }
    $position++;
}

echo "Terminé.\n";
