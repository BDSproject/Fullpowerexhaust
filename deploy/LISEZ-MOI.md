# Mise en ligne des corrections — fullpowerexhaust.com

Toutes les corrections sont locales et poussées sur GitHub
(`BDSproject/Fullpowerexhaust`, branche `main`). Voici ce qu'il reste à faire
sur **O2switch**.

---

## 1. Fichiers à téléverser (FTP ou gestionnaire de fichiers cPanel)

### Indispensable — CSS compilé
| Fichier | Pourquoi |
|---|---|
| `themes/hummingbird/assets/css/custom.css` | Toutes les corrections d'affichage |
| `themes/hummingbird/assets/css/tailwind.css` | Correctif `.fpe-grid` (bande réassurance accueil) |

### Indispensable — templates
| Fichier | Pourquoi |
|---|---|
| `themes/hummingbird/templates/_partials/header.tpl` | Menu mobile, langues, recherche |
| `themes/hummingbird/templates/index.tpl` | Hero (doublon visuel supprimé) |
| `themes/hummingbird/templates/catalog/_partials/catalog-filterbar.tpl` | Barre de recherche catalogue |
| `themes/hummingbird/templates/catalog/_partials/miniatures/product.tpl` | Libellé de marque |
| `themes/hummingbird/templates/cms/page.tpl` | Sur-titre des pages légales |
| `themes/hummingbird/templates/catalog/_partials/product-flags.tpl` | Badge « Occasion » au lieu de « Neuf » |
| `themes/hummingbird/templates/catalog/_partials/product-details.tpl` | Suppression de la ligne « État » en double |
| `themes/hummingbird/modules/ps_shoppingcart/ps_shoppingcart.tpl` | Badge panier masqué à 0 |

### Indispensable — traductions
| Dossier | Contenu |
|---|---|
| `translations/fr-FR/` `en-US/` `es-ES/` `it-IT/` | Catalogues du thème complétés (159 chaînes) |
| `modules/*/translations/` | **368 fichiers** : sans eux, les chaînes des modules (newsletter, avis, alertes e-mail…) restent en anglais |

> Pour `modules/*/translations/`, téléversez l'arborescence complète.
> Elle peut aussi être régénérée sur le serveur avec
> `php tools/install_module_translations.php`.

### Indispensable — images
| Dossier | Contenu |
|---|---|
| `img/p/`, `img/c/`, `img/m/`, `img/su/`, `img/st/` | Vignettes régénérées (fond sombre) et visuels « aucune image » |
| `themes/hummingbird/assets/img/fullpower/` | Inchangé (à vérifier présent) |

> Ces vignettes peuvent aussi être régénérées sur le serveur :
> `php tools/regenerate_thumbnails.php && php tools/generate_placeholders.php`

---

## 2. Base de données

Dans **phpMyAdmin → base de la boutique → onglet SQL** : exécuter
`deploy/production-fixes.sql` (sauvegarder la base avant).

Il corrige :
- le badge produit : tous les produits passés en **« Occasion »** (et non plus le
  flag « nouveau produit » de PrestaShop), avec « Occasion » par défaut pour les
  futurs imports ;
- 7 chaînes de configuration restées en anglais (newsletter, RGPD, page de
  maintenance, rupture de stock, liste d'envies) ;
- les icônes des blocs de réassurance (URL `//modules/...` → `/modules/...`) ;
- le doublon du bloc « Informations » sur la page contact ;
- le sujet du formulaire de contact ;
- 24 titres de pages en français sur les versions ES/IT ;
- les noms des caractéristiques produit affichés en français sur les fiches ES/IT.

---

## 3. Vider les caches (obligatoire)

1. **Back-office** : *Paramètres avancés → Performances → Vider le cache*
   (bouton « Vider le cache » en haut à droite).
2. **Fichiers** : supprimer le contenu de
   - `var/cache/prod/`
   - `themes/hummingbird/assets/cache/` (fichiers `theme-*.css` et `bottom-*.js`)

Sans cette étape, PrestaShop continue de servir l'ancien CSS combiné.

---

## 4. Points de vigilance

- **Ne pas régénérer les miniatures depuis le back-office** : PrestaShop
  complèterait à nouveau les images en **blanc**. Si vous le faites, relancez
  `php tools/regenerate_thumbnails.php` puis `php tools/generate_placeholders.php`.
- Le fichier `.htaccess` est celui généré par PrestaShop pour le domaine
  `fullpowerexhaust.com` : ne pas le modifier à la main.
- `router.php` ne sert qu'au serveur de développement local ; il est inoffensif
  en production mais peut être supprimé du serveur.
- **Après chaque import eBay**, vérifiez le badge des nouvelles annonces : si le
  module les crée en « Neuf », relancez `php tools/set_products_used.php`
  (ou les deux `UPDATE` de la section 8 du script SQL).
