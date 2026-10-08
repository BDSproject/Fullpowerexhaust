<?php
/**
 * FULLPOWEREXHAUST — crée/maj les pages légales (CGV, CGU, Mentions légales,
 * Politique de confidentialité, + politiques complémentaires) en pages CMS.
 *
 * Usage : php -d memory_limit=768M tools/create_legal_pages.php
 *
 * Les 4 pages du footer sont : CGV, CGU, Mentions légales, Politique de
 * confidentialité. Les champs entre [crochets] (SIREN, TVA, adresse…) sont à
 * compléter avec les informations réelles de la société.
 */

if (PHP_SAPI !== 'cli') {
    die('CLI only');
}

require_once __DIR__ . '/../config/config.inc.php';

$idFr = (int) Language::getIdByIso('fr');

$pages = [
    // ════════════════════════════════════════════════════════════════════
    // CGV — Conditions Générales de Vente
    // ════════════════════════════════════════════════════════════════════
    [
        'title' => 'Conditions générales de vente',
        'desc'  => 'Conditions générales de vente de FullPowerExhaust — vente en ligne de lignes d\'échappement d\'occasion.',
        'slug'  => 'conditions-generales-de-vente',
        'html'  => <<<'HTML'
<p>Dernière mise à jour : 30 septembre 2026.</p>
<p>Les présentes Conditions Générales de Vente (CGV) s'appliquent à toutes les commandes passées sur le site fullpowerexhaust.com, exploité par la société FULLPOWEREXHAUST, spécialisée dans la vente en ligne de lignes d'échappement et de pièces d'échappement d'occasion pour automobiles.</p>

<h2>Article 1 — Objet et champ d'application</h2>
<p>Les présentes CGV régissent les relations contractuelles entre FULLPOWEREXHAUST (ci-après « le Vendeur ») et toute personne physique majeure ou morale effectuant un achat sur le site fullpowerexhaust.com (ci-après « le Client »).</p>
<p>Toute commande passée sur le site implique l'acceptation pleine, entière et sans réserve des présentes CGV. Le Client reconnaît en avoir pris connaissance avant la validation de sa commande. Les CGV applicables sont celles en vigueur à la date de la commande.</p>

<h2>Article 2 — Identité du Vendeur</h2>
<p>FULLPOWEREXHAUST — vente en ligne de pièces d'échappement d'occasion.<br>Email : contact@power-exhaust.com<br>Site : fullpowerexhaust.com</p>
<p>Les coordonnées complètes (forme juridique, capital, siège social, SIREN, TVA) figurent dans les <a href="{url entity='cms' id=2}">Mentions légales</a>.</p>

<h2>Article 3 — Produits : pièces d'occasion</h2>
<p>Tous les produits proposés à la vente sont des <strong>pièces d'occasion</strong> (lignes d'échappement complètes, silencieux, catalyseurs, collecteurs, sorties d'échappement, etc.). Sauf mention expresse contraire, il ne s'agit pas de pièces neuves.</p>
<p>Chaque pièce fait l'objet, avant mise en vente, d'un contrôle visuel et fonctionnel destiné à vérifier : l'état général, la présence de fissures ou de perforations, l'état des fixations et des brides, et la conformité aux photographies publiées.</p>
<p>Les photographies et descriptions sont aussi fidèles que possible mais n'ont pas de valeur contractuelle. En raison de la nature des pièces d'occasion, de légères traces d'usage (rayures, ternissement, dépôts) peuvent subsister. Les pièces sont vendues <strong>en l'état</strong>, conformément à leur description.</p>
<p>Le Vendeur se réserve le droit de retirer de la vente tout produit jugé non conforme, ou de corriger toute erreur manifeste de description ou de prix.</p>

<h2>Article 4 — Compatibilité avec le véhicule</h2>
<p>Il appartient au Client de vérifier, avant toute commande, la compatibilité de la pièce avec son véhicule : marque, modèle, motorisation, année, référence constructeur (référence OEM) et référence du produit indiquée sur la fiche.</p>
<p>En cas de doute, le Client est invité à contacter le Vendeur avant l'achat via contact@power-exhaust.com, en communiquant la référence OEM de la pièce recherchée ou le numéro de châssis (VIN) de son véhicule.</p>
<p>Une erreur de compatibilité non signalée avant l'achat ne constitue pas un défaut du produit et ne donne pas droit à remboursement au titre de la garantie légale de conformité.</p>

<h2>Article 5 — Prix</h2>
<p>Les prix sont indiqués en euros, toutes taxes comprises (TTC), hors frais de livraison. Les frais de livraison sont calculés et affichés avant la validation définitive de la commande.</p>
<p>Le Vendeur se réserve le droit de modifier ses prix à tout moment ; les produits sont facturés sur la base du tarif en vigueur au moment de la validation de la commande.</p>

<h2>Article 6 — Commande</h2>
<p>La commande est réputée conclue lorsque le Client valide son panier, accepte les présentes CGV et procède au paiement. Un e-mail de confirmation récapitulant la commande est adressé au Client. Le Vendeur se réserve le droit de refuser ou d'annuler toute commande en cas de litige antérieur, de paiement irrégulier ou de suspicion de fraude.</p>

<h2>Article 7 — Paiement</h2>
<p>Le paiement s'effectue en ligne par les moyens proposés sur le site (carte bancaire, PayPal, et tout autre moyen indiqué au moment de la commande). Les transactions sont sécurisées par des prestataires certifiés ; le Vendeur ne conserve aucune donnée bancaire.</p>
<p>La commande n'est traitée qu'à réception du paiement intégral. En cas de refus de paiement, la commande est annulée de plein droit.</p>

<h2>Article 8 — Livraison et réception</h2>
<p>Les produits sont expédiés à l'adresse indiquée par le Client lors de la commande. Les délais de livraison sont indicatifs ; l'expédition intervient en principe sous 3 à 5 jours ouvrés après validation du paiement.</p>
<p>Le Client est tenu de vérifier l'état du colis à la réception. En cas d'avarie ou de colis endommagé, il doit émettre des réserves précises auprès du transporteur et en informer le Vendeur sous 48 heures, avec photographies à l'appui.</p>

<h2>Article 9 — Droit de rétractation</h2>
<p>Conformément aux articles L.221-18 et suivants du Code de la consommation, le Client dispose d'un délai de <strong>14 jours calendaires</strong> à compter de la réception du produit pour exercer son droit de rétractation, sans avoir à motiver sa décision.</p>
<p>Pour l'exercer, le Client doit notifier sa décision par écrit (e-mail à contact@power-exhaust.com) avant l'expiration du délai. La pièce doit être retournée <strong>non montée, non modifiée et dans son état d'origine</strong>, dans son emballage. Les frais de retour sont à la charge du Client, sauf disposition contraire des conditions de retour.</p>
<p>Le remboursement intervient dans un délai de 14 jours à compter de la récupération de la pièce ou de la preuve d'expédition, sur le moyen de paiement initial.</p>

<h2>Article 10 — Garanties légales</h2>
<p><strong>Garantie légale de conformité</strong> (articles L.217-3 à L.217-14 du Code de la consommation) : le Client bénéficie d'un délai de 2 ans à compter de la livraison pour agir. Pour les biens d'occasion, cette durée peut être réduite à <strong>12 mois</strong> par accord entre les parties. En cas de défaut de conformité, le Client peut obtenir la réparation, le remplacement ou, à défaut, la réduction du prix ou la résolution de la vente.</p>
<p><strong>Garantie des vices cachés</strong> (articles 1641 à 1649 du Code civil) : le Vendeur est tenu des vices cachés de la chose vendue qui la rendent impropre à l'usage auquel on la destine. L'action doit être intentée dans un délai de 2 ans à compter de la découverte du vice.</p>
<p>Ces garanties ne couvrent pas : l'usure normale de la pièce, les défauts visibles et mentionnés dans la description, les dommages résultant d'une mauvaise installation, d'une utilisation inadaptée ou du non-respect des préconisations du constructeur.</p>

<h2>Article 11 — Responsabilité et installation</h2>
<p>Le montage des pièces d'échappement doit être réalisé par un professionnel qualifié, dans le respect des normes en vigueur (notamment en matière d'émissions et de nuisances sonores). Le Vendeur décline toute responsabilité en cas d'installation non conforme, de modification de la pièce, ou d'usage du véhicule sur la voie publique en infraction avec la réglementation applicable.</p>
<p>La responsabilité du Vendeur est limitée au montant de la commande ; il ne saurait être tenu des dommages indirects ou immatériels.</p>

<h2>Article 12 — Réclamations et service après-vente</h2>
<p>Toute réclamation doit être adressée par e-mail à contact@power-exhaust.com en précisant le numéro de commande et en joignant des photographies. Le Vendeur s'engage à répondre dans un délai raisonnable.</p>

<h2>Article 13 — Médiation et litiges</h2>
<p>En cas de litige, le Client est invité à contacter préalablement le service client afin de rechercher une solution amiable. Conformément à l'article L.612-1 du Code de la consommation, le Client peut recourir gratuitement à un médiateur de la consommation en vue de la résolution amiable du litige.</p>
<p>À défaut d'accord amiable, le litige sera porté devant les juridictions françaises compétentes. Les présentes CGV sont soumises au droit français.</p>
HTML,
    ],

    // ════════════════════════════════════════════════════════════════════
    // CGU — Conditions Générales d'Utilisation
    // ════════════════════════════════════════════════════════════════════
    [
        'title' => 'Conditions générales d\'utilisation',
        'desc'  => 'Conditions générales d\'utilisation du site fullpowerexhaust.com.',
        'slug'  => 'conditions-generales-d-utilisation',
        'html'  => <<<'HTML'
<p>Dernière mise à jour : 30 septembre 2026.</p>
<p>Les présentes Conditions Générales d'Utilisation (CGU) définissent les conditions d'accès et d'utilisation du site fullpowerexhaust.com (ci-après « le Site »), édité par la société FULLPOWEREXHAUST.</p>

<h2>Article 1 — Objet</h2>
<p>Le Site est une boutique en ligne de vente de lignes d'échappement et de pièces d'échappement d'occasion. Les CGU s'appliquent à tout visiteur ou utilisateur (ci-après « l'Utilisateur ») du Site, indépendamment de tout achat. Les achats sont, quant à eux, régis par les <a href="{url entity='cms' id=3}">Conditions Générales de Vente</a>.</p>

<h2>Article 2 — Acceptation des CGU</h2>
<p>L'accès et l'utilisation du Site impliquent l'acceptation pleine et entière des présentes CGU. Si l'Utilisateur n'accepte pas ces conditions, il est invité à ne pas utiliser le Site.</p>

<h2>Article 3 — Accès au Site</h2>
<p>L'accès au Site est libre et gratuit. Le Vendeur se réserve le droit de suspendre, restreindre ou interrompre l'accès au Site, notamment pour des opérations de maintenance, de mise à jour ou en cas de force majeure, sans que sa responsabilité ne puisse être engagée.</p>

<h2>Article 4 — Compte client</h2>
<p>La création d'un compte client est nécessaire pour passer commande et suivre ses achats. L'Utilisateur s'engage à fournir des informations exactes, complètes et à jour, et à les maintenir à jour. Il est seul responsable de la confidentialité de ses identifiants et de toute activité effectuée depuis son compte. Tout accès ou usage frauduleux devra être immédiatement signalé à contact@power-exhaust.com.</p>

<h2>Article 5 — Propriété intellectuelle</h2>
<p>L'ensemble des éléments du Site (textes, photographies, illustrations, logos, marques, graphismes, structure, base de données, logiciels) est la propriété exclusive de FULLPOWEREXHAUST ou de ses partenaires, et est protégé par le droit de la propriété intellectuelle.</p>
<p>Toute reproduction, représentation, modification, adaptation, diffusion ou exploitation, totale ou partielle, sans autorisation écrite préalable, est strictement interdite et constitue une contrefaçon au sens des articles L.335-2 et suivants du Code de la propriété intellectuelle.</p>

<h2>Article 6 — Utilisation autorisée du Site</h2>
<p>L'Utilisateur s'engage à ne pas :</p>
<ul>
<li>utiliser le Site à des fins illicites ou frauduleuses ;</li>
<li>porter atteinte au fonctionnement du Site (intrusion, extraction de données, déni de service…) ;</li>
<li>contourner les mesures techniques de sécurité ;</li>
<li>publier des contenus illicites, diffamatoires, injurieux ou trompeurs.</li>
</ul>

<h2>Article 7 — Contenus publiés par l'Utilisateur</h2>
<p>Les éventuels avis ou commentaires publiés par l'Utilisateur doivent être sincères et conformes à la réglementation. Le Vendeur se réserve le droit de supprimer sans préavis tout contenu manifestement illicite ou inapproprié.</p>

<h2>Article 8 — Responsabilité</h2>
<p>Le Vendeur s'efforce d'assurer l'exactitude des informations publiées sur le Site mais ne saurait garantir l'absence d'erreurs ou d'omissions. Les informations sont fournies à titre indicatif et peuvent être modifiées sans préavis.</p>
<p>Le Vendeur décline toute responsabilité en cas de dommage résultant d'une impossibilité d'accès au Site, d'une interruption du service ou de l'utilisation des informations qu'il contient.</p>

<h2>Article 9 — Liens hypertextes</h2>
<p>Le Site peut contenir des liens vers des sites tiers. Le Vendeur n'exerce aucun contrôle sur ces sites et décline toute responsabilité quant à leur contenu, leur disponibilité ou leurs pratiques.</p>

<h2>Article 10 — Disponibilité et maintenance</h2>
<p>Le Vendeur se réserve le droit de faire évoluer, modifier ou suspendre le Site, à tout moment et sans préavis, notamment pour des raisons techniques ou de sécurité.</p>

<h2>Article 11 — Modification des CGU</h2>
<p>Le Vendeur se réserve le droit de modifier les présentes CGU à tout moment. Les CGU applicables sont celles en vigueur au moment de la navigation. La poursuite de l'utilisation du Site après modification vaut acceptation des nouvelles CGU.</p>

<h2>Article 12 — Droit applicable</h2>
<p>Les présentes CGU sont soumises au droit français. En cas de litige, les juridictions françaises seront seules compétentes.</p>
HTML,
    ],

    // ════════════════════════════════════════════════════════════════════
    // Politique de confidentialité
    // ════════════════════════════════════════════════════════════════════
    [
        'title' => 'Politique de confidentialité',
        'desc'  => 'Politique de confidentialité de FullPowerExhaust — protection des données personnelles (RGPD).',
        'slug'  => 'politique-de-confidentialite',
        'html'  => <<<'HTML'
<p>Dernière mise à jour : 30 septembre 2026.</p>
<p>La présente politique de confidentialité décrit la manière dont FULLPOWEREXHAUST collecte, utilise et protège vos données personnelles, conformément au Règlement (UE) 2016/679 dit « RGPD » et à la loi n° 78-17 du 6 janvier 1978 modifiée (« Informatique et Libertés »).</p>

<h2>1. Responsable du traitement</h2>
<p>Le responsable du traitement des données est la société FULLPOWEREXHAUST, joignable à l'adresse : contact@power-exhaust.com.</p>

<h2>2. Données collectées</h2>
<p>Dans le cadre de votre navigation et de vos commandes, nous sommes susceptibles de collecter :</p>
<ul>
<li><strong>Données d'identification</strong> : nom, prénom ;</li>
<li><strong>Données de contact</strong> : adresse e-mail, adresse postale, numéro de téléphone (facultatif) ;</li>
<li><strong>Données de commande</strong> : historique d'achats, factures ;</li>
<li><strong>Données de navigation</strong> : adresse IP, pages consultées, données techniques (via cookies).</li>
</ul>
<p>Aucune donnée sensible au sens du RGPD n'est collectée. Les données de paiement sont traitées par des prestataires certifiés PCI-DSS ; nous ne stockons jamais vos données bancaires.</p>

<h2>3. Finalités et bases légales</h2>
<ul>
<li><strong>Exécution du contrat</strong> : traitement et suivi des commandes, livraison, service après-vente, gestion du compte client ;</li>
<li><strong>Obligations légales</strong> : facturation, comptabilité (conservation 10 ans) ;</li>
<li><strong>Intérêt légitime</strong> : amélioration du site, sécurité, prévention de la fraude ;</li>
<li><strong>Consentement</strong> : envoi de newsletters et d'offres commerciales (uniquement si vous y avez consenti).</li>
</ul>

<h2>4. Durées de conservation</h2>
<ul>
<li>Données du compte client : 3 ans à compter de la dernière commande ;</li>
<li>Données de facturation : 10 ans (obligations comptables et fiscales) ;</li>
<li>Données de navigation et cookies : 13 mois maximum ;</li>
<li>Données de prospection : 3 ans à compter du dernier contact.</li>
</ul>

<h2>5. Destinataires des données</h2>
<p>Vos données ne sont jamais vendues. Elles peuvent être transmises aux seuls destinataires suivants, dans la limite de leurs attributions :</p>
<ul>
<li>prestataires de transport et de livraison ;</li>
<li>prestataires de paiement ;</li>
<li>prestataires techniques et d'hébergement ;</li>
<li>outils d'analyse d'audience (données anonymisées).</li>
</ul>
<p>Tous nos sous-traitants sont tenus à des obligations de confidentialité et de sécurité, et traitent les données exclusivement pour notre compte.</p>

<h2>6. Cookies</h2>
<p>Le Site utilise des cookies techniques nécessaires à son fonctionnement (session, panier, authentification), ainsi que, sous réserve de votre consentement, des cookies de mesure d'audience et de marketing. Pour plus d'informations, consultez notre <a href="{url entity='cms' id=8}">Politique de cookies</a>.</p>

<h2>7. Vos droits</h2>
<p>Conformément au RGPD, vous disposez des droits suivants sur vos données : droit d'accès, de rectification, d'effacement (« droit à l'oubli »), de limitation du traitement, d'opposition, et de portabilité.</p>
<p>Pour exercer ces droits, contactez-nous à contact@power-exhaust.com en justifiant de votre identité. Vous disposez également du droit d'introduire une réclamation auprès de la CNIL (www.cnil.fr).</p>

<h2>8. Sécurité des données</h2>
<p>Nous mettons en œuvre des mesures techniques et organisationnelles appropriées (chiffrement des échanges, accès restreints, hébergement sécurisé) afin de protéger vos données contre toute perte, altération, divulgation ou accès non autorisé.</p>

<h2>9. Transferts hors Union européenne</h2>
<p>Dans la mesure où certains prestataires seraient situés hors de l'Union européenne, des garanties appropriées (clauses contractuelles types) sont mises en place conformément au RGPD.</p>

<h2>10. Contact et réclamations</h2>
<p>Pour toute question relative à la protection de vos données, vous pouvez nous écrire à contact@power-exhaust.com.</p>

<h2>11. Modification de la politique</h2>
<p>La présente politique peut être mise à jour à tout moment afin de refléter l'évolution de nos pratiques ou de la réglementation. La version applicable est celle publiée sur le Site.</p>
HTML,
    ],

    // ════════════════════════════════════════════════════════════════════
    // Mentions légales
    // ════════════════════════════════════════════════════════════════════
    [
        'title' => 'Mentions légales',
        'desc'  => 'Mentions légales du site fullpowerexhaust.com.',
        'slug'  => 'mentions-legales',
        'html'  => <<<'HTML'
<p>Conformément aux dispositions de la loi n° 2004-575 du 21 juin 2004 pour la confiance dans l'économie numérique (LCEN), il est précisé aux utilisateurs du site fullpowerexhaust.com l'identité des différents intervenants dans le cadre de sa réalisation et de son suivi.</p>

<h2>1. Éditeur du site</h2>
<p>Le site fullpowerexhaust.com est édité par la société <strong>POWEREXHAUST</strong>.</p>
<ul>
<li><strong>Dénomination sociale :</strong> POWEREXHAUST</li>
<li><strong>Forme juridique :</strong> Société par actions simplifiée (SAS)</li>
<li><strong>Capital social :</strong> 10 000 €</li>
<li><strong>Siège social :</strong> 6 rue de la Brie, 77410 Claye-Souilly, France</li>
<li><strong>Immatriculation :</strong> SIREN 104 257 449 · SIRET 104 257 449 00017</li>
<li><strong>N° TVA intracommunautaire :</strong> FR 30 104 257 449</li>
<li><strong>Code APE :</strong> 4532Z — Commerce de détail d'équipements automobiles</li>
<li><strong>E-mail :</strong> contact@power-exhaust.com</li>
</ul>

<h2>2. Directeur de la publication</h2>
<p>Le directeur de la publication est <strong>Rodrigue BOISDUR-SALBRIS</strong>, Président de la société POWEREXHAUST, agissant et ayant les pouvoirs nécessaires en tant que Président.</p>

<h2>3. Hébergement</h2>
<p>Le site est hébergé par la société <strong>O2switch</strong>, SAS, dont le siège social est situé au 222-224 Boulevard Gustave Flaubert, 63000 Clermont-Ferrand, France. Téléphone : 04 44 44 60 40.</p>

<h2>4. Activité</h2>
<p>POWEREXHAUST exerce une activité de vente en ligne de lignes d'échappement et de pièces d'échappement d'occasion pour automobiles (code APE 4532Z — Commerce de détail d'équipements automobiles). Les ventes sont soumises aux <a href="{url entity='cms' id=3}">Conditions Générales de Vente</a>.</p>

<h2>5. Propriété intellectuelle</h2>
<p>L'ensemble du contenu du site (textes, photographies, illustrations, logos, marques, graphismes, structure, base de données) est la propriété exclusive de POWEREXHAUST, à l'exception des éléments appartenant à des tiers (marques automobiles citées à titre informatif, notamment).</p>
<p>Toute reproduction, représentation, modification ou exploitation, totale ou partielle, sans autorisation écrite préalable, est interdite et constitue une contrefaçon sanctionnée par les articles L.335-2 et suivants du Code de la propriété intellectuelle.</p>

<h2>6. Données personnelles</h2>
<p>La collecte et le traitement des données personnelles des utilisateurs sont régis par notre <a href="{url entity='cms' id=7}">Politique de confidentialité</a>. Conformément au RGPD, vous disposez d'un droit d'accès, de rectification, d'effacement et d'opposition sur les données vous concernant, exerçable à l'adresse contact@power-exhaust.com.</p>

<h2>7. Cookies</h2>
<p>Le site utilise des cookies dont les modalités sont décrites dans la <a href="{url entity='cms' id=8}">Politique de cookies</a>.</p>

<h2>8. Responsabilité</h2>
<p>Le contenu du site est fourni à titre informatif. Bien que les informations soient mises à jour avec soin, POWEREXHAUST ne saurait garantir l'exactitude, l'exhaustivité ou l'actualité des informations diffusées, ni être tenue responsable des dommages résultant de leur utilisation.</p>

<h2>9. Droit applicable</h2>
<p>Les présentes mentions légales sont soumises au droit français. En cas de litige, les juridictions françaises seront seules compétentes.</p>
HTML,
    ],

    // ════════════════════════════════════════════════════════════════════
    // Politique de cookies
    // ════════════════════════════════════════════════════════════════════
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

    // ════════════════════════════════════════════════════════════════════
    // Politique de remboursement
    // ════════════════════════════════════════════════════════════════════
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
            . '<li>La demande est formulée par écrit dans les 14 jours (contact@power-exhaust.com).</li>'
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
            . '<p>Pour toute demande : contact@power-exhaust.com — Objet : REMBOURSEMENT + numéro de commande.</p>',
    ],

    // ════════════════════════════════════════════════════════════════════
    // Conditions de retour
    // ════════════════════════════════════════════════════════════════════
    [
        'title' => 'Conditions de retour',
        'desc'  => 'Conditions de retour de FullPowerExhaust',
        'slug'  => 'conditions-de-retour',
        'html'  => '<h2>1. Procédure obligatoire</h2>'
            . '<p>Tout retour doit être préalablement autorisé par notre service client. Aucun colis non sollicité ne sera accepté. Pour initier un retour :</p>'
            . '<ul>'
            . '<li><strong>01</strong> — Envoyez un e-mail à contact@power-exhaust.com avec votre numéro de commande et le motif du retour.</li>'
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
