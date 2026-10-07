# FullPowerExhaust — Guide de modification

Boutique PrestaShop 9 d'échappements d'occasion, avec un thème personnalisé
**Hummingbird** restylé pour coller au design Figma (dark theme, typographie
Outfit + Barlow Condensed).

Ce README explique **où se trouve votre texte**, **comment le modifier**, et
**tout ce qu'il faut savoir** pour faire les petites retouches vous-même.

---

## 1. Démarrage rapide

| Action | Commande / adresse |
|---|---|
| Lancer le site local | `./start-dev.sh` |
| Boutique (front) | http://localhost:8000/ |
| Administration (back) | http://localhost:8000/admin123/ |
| Identifiants admin | `admin@fullpowerexhaust.com` / `admin123` |

Le serveur utilise PHP intégré (`php -S` avec `router.php`), en mode dev
(`_PS_MODE_DEV_ = true`). Base MySQL locale : `fullpowerexhaust_dev`
(préfixe de tables `ps_`).

> Arrêt du serveur : `Ctrl+C` dans le terminal où tourne `start-dev.sh`.

---

## 2. Architecture : où tout se trouve

```
Fullpowerexhaust.com/
├── themes/hummingbird/            ← LE THÈME (votre zone de travail principale)
│   ├── templates/                 ← fichiers .tpl (Smarty) : le contenu HTML/texte
│   │   ├── _partials/             ← header, footer, copyright, breadcrumb…
│   │   ├── index.tpl              ← page d'accueil
│   │   ├── catalog/               ← listes produits + fiches
│   │   ├── cms/                   ← gabarit des pages CMS (légales)
│   │   ├── contact.tpl            ← page contact
│   │   └── layouts/               ← gabarits de page (html, colonnes)
│   ├── modules/                   ← surcharges des modules (connexion, panier…)
│   └── assets/css/                ← CSS compilés (NE PAS ÉDITER manuellement)
│       ├── custom.css             ← compilé depuis src/scss/
│       └── tailwind.css           ← compilé depuis tailwind/input.css
├── src/scss/                      ← VOS STYLES (à éditer) → compile en custom.css
│   ├── _variables.scss            ← couleurs / typos / rayons (tokens)
│   ├── _header.scss               ← style du header
│   ├── _footer.scss               ← style du footer
│   ├── _product-card.scss         ← style des fiches produit
│   ├── _responsive.scss           ← correctifs mobile/tablette
│   └── custom.scss                ← fichier d'entrée (assemble tout)
├── tailwind/input.css             ← configuration Tailwind (utilitaires)
├── tools/*.php                    ← scripts utilitaires (données démo, langues…)
├── router.php / start-dev.sh      ← serveur local
└── package.json                   ← commandes npm (compilation CSS)
```

> ⚠️ **Important** : le thème utilise **Smarty** (fichiers `.tpl`), pas Twig.
> Twig n'est pas utilisé dans les templates du front-office PrestaShop.

---

## 3. Où se trouve MON texte ?

### 3.1 Header (barre du haut)
Fichier : `themes/hummingbird/templates/_partials/header.tpl`

| Texte | Ligne / zone |
|---|---|
| Logo « FULL**POWER**EXHAUST » | bloc « Logo (gauche) » (`site-header__logo`) |
| Liens **Accueil / Catalogue / Contact** | bloc « Navigation » (`site-header__link`) |
| Bouton burger mobile (☰) | bloc « Actions » (`site-header__burger`) |

Pour changer le libellé d'un lien, éditez simplement le texte entre `>` et
`</a>`. Exemple :

```smarty
<a class="site-header__link ..." href="{$urls.pages.new_products}">Catalogue</a>
```

Changez `Catalogue` par ce que vous voulez (ex. `Boutique`).

### 3.2 « Connexion » et « Panier » (à droite du header)
Ces deux textes ne sont **pas dans le header.tpl**. Ils viennent des modules
PrestaShop, et ils sont **traduits** :

- **« Connexion »** → `themes/hummingbird/modules/ps_customersignin/ps_customersignin.tpl`
  (chaîne `{l s='Sign in' d='Shop.Theme.Actions'}`, traduite « Connexion » en français)
- **« Panier »** → `themes/hummingbird/modules/ps_shoppingcart/ps_shoppingcart.tpl`
  (chaîne `{l s='Cart' d='Shop.Theme.Checkout'}`, masquée par le CSS — icône seule)

> Pour changer le mot **Connexion**, deux options :
> 1. Remplacer `{l s='Sign in' d='Shop.Theme.Actions'}` par le texte en dur
>    `Connexion` dans ce fichier ;
> 2. Ou passer par l'admin : **International → Traductions** (plus propre mais
>    plus long).

### 3.3 Footer (pied de page)
Fichiers :
- `themes/hummingbird/templates/_partials/footer.tpl`
- `themes/hummingbird/templates/_partials/copyright.tpl`

| Texte | Emplacement |
|---|---|
| Marque « FULLPOWEREXHAUST » | `footer.tpl` → `site-footer__brand` |
| Slogan « L'échappement performance occasion. » | `footer.tpl` → `site-footer__tagline` |
| Email / domaine | `footer.tpl` → `site-footer__contact` |
| Liens légaux CGV / CGU / Mentions / Confidentialité | `copyright.tpl` → `copyright__legal` |
| « © FULLPOWEREXHAUST » | `copyright.tpl` → `copyright__right` |

### 3.4 Page d'accueil
Fichier : `themes/hummingbird/templates/index.tpl`

| Texte | Bloc |
|---|---|
| Hero : « Lignes d'occasion · Toutes marques » | `home_hero` |
| Titre « Sonorité / Performance / Occasion » | `home_hero` (`<h1>`) |
| Boutons « Voir le catalogue » / « Mon compte » | `home_hero` |
| Bande réassurance (Performance, Sonorité unique, Qualité garantie, Expertise) | `home_features` |
| CTA « Vous ne trouvez pas votre marque ? » | `home_cta` |

### 3.5 Pages légales (CGV, CGU, Mentions, Confidentialité…)
Ces pages sont des **pages CMS** stockées **en base de données** (pas dans des
fichiers). Leur contenu se modifie :

- soit dans l'admin : **Design → Pages** ;
- soit en relançant le script `php tools/create_legal_pages.php`
  (⚠️ il régénère le contenu, cela écrasera vos modifications manuelles).

Identifiants (IDs) des pages CMS :

| ID | Page |
|---|---|
| 1 | Livraison |
| 2 | Mentions légales |
| 3 | Conditions générales de vente (CGV) |
| 4 | À propos |
| 5 | Paiement sécurisé |
| 6 | Conditions générales d'utilisation (CGU) |
| 7 | Politique de confidentialité |
| 8 | Politique de cookies |
| 9 | Politique de remboursement |
| 10 | Conditions de retour |

### 3.6 Fiche produit
Fichier : `themes/hummingbird/templates/catalog/_partials/miniatures/product.tpl`

C'est le gabarit de la « carte produit » (image, marque, titre, prix, bouton
Ajouter au panier). Le style associé est dans `src/scss/_product-card.scss`.

### 3.7 Page contact
Fichier : `themes/hummingbird/templates/contact.tpl`

La page contact affiche le module PrestaShop de contact (formulaire). Le contenu
du formulaire se gère dans l'admin → **Modules → Contact** (ou les réglages du
magasin).

---

## 4. Comment modifier du texte (procédure)

1. **Ouvrez le bon fichier** `.tpl` (voir tableau ci-dessus).
2. **Modifiez le texte** entre les balises HTML.
3. **Videz le cache** PrestaShop (obligatoire sinon rien ne change) :

```bash
rm -rf var/cache/dev/*
```

4. **Rafraîchissez** la page dans le navigateur (F5, ou Cmd+Shift+R pour vider
   le cache du navigateur).

> Les `.tpl` sont recompilés automatiquement après vidage du cache. En mode dev,
> vous pouvez aussi forcer en ajoutant `?nocache` à l'URL.

---

## 5. Comment modifier le style (couleurs, tailles, espacements)

### 5.1 Le principe
Le CSS final (`themes/hummingbird/assets/css/custom.css`) est **généré**
automatiquement depuis les fichiers SCSS de `src/scss/`.
👉 **Modifiez TOUJOURS les fichiers `.scss`, jamais `custom.css`.**

### 5.2 Compiler le CSS

```bash
npm run build:scss          # compile une fois (SCSS → custom.css)
npm run build:css           # compile Tailwind (input.css → tailwind.css)
```

Pour recompiler automatiquement à chaque sauvegarde (pratique) :

```bash
npm run watch:scss          # surveille src/scss/
npm run watch:css           # surveille tailwind/
```

Puis, après compilation : `rm -rf var/cache/dev/*` et rafraîchissez.

### 5.3 Les « tokens » de design (couleurs & typo)
Tout est centralisé dans `src/scss/_variables.scss`, sous forme de variables CSS :

| Variable | Valeur | Rôle |
|---|---|---|
| `--fpe-bg` | `#0d0d0d` | fond principal |
| `--fpe-fg` | `#f0f0f0` | texte principal |
| `--fpe-card` | `#141414` | fond des cartes |
| `--fpe-muted` | `#1f1f1f` | fond secondaire |
| `--fpe-muted-fg` | `#888888` | texte atténué (gris) |
| `--fpe-border` | `rgba(255,255,255,.09)` | bordures |
| `--fpe-primary` | `#c8152a` | **rouge** (accent, boutons) |
| `--fpe-primary-hover` | `#a0101f` | rouge au survol |
| `--fpe-radius` | `0.375rem` | arrondi des coins |
| `--fpe-font-sans` | Outfit | police de texte |
| `--fpe-font-display` | Barlow Condensed | police des titres / logo |

Exemple : pour changer le rouge de la marque, modifiez `--fpe-primary` dans
`_variables.scss`, recompilez, videz le cache.

### 5.4 Où sont les styles par zone

| Zone | Fichier SCSS |
|---|---|
| Header | `src/scss/_header.scss` |
| Footer | `src/scss/_footer.scss` |
| Boutons | `src/scss/_buttons.scss` |
| Formulaires | `src/scss/_forms.scss` |
| Fiches produit | `src/scss/_product-card.scss` |
| Page catalogue / filtres | `src/scss/_catalog.scss` |
| Petits composants (alertes, dropdowns…) | `src/scss/_components.scss` |
| Mobile / tablette | `src/scss/_responsive.scss` |

---

## 6. Les pièges à éviter

1. **N'éditez pas `custom.css` ni `tailwind.css`** : ils sont régénérés à chaque
   compilation et vos changements seraient perdus.
2. **Videz toujours le cache** après une modification de `.tpl` :
   `rm -rf var/cache/dev/*`.
3. **Smarty, pas Twig** : les templates sont en `.tpl` (syntaxe `{...}`), pas en
   Twig `{{ ... }}`.
4. **Les textes de modules** (« Connexion », « Panier ») sont des *traductions*,
   pas du texte en dur dans le header.
5. **Le tailwind est chargé sans preflight** (pour cohabiter avec Bootstrap) :
   utilisez les classes Tailwind dans les `.tpl` existants, mais le CSS de
   structure vient surtout du SCSS.
6. **`body { padding-top: 4rem }`** compense le header fixe : si vous changez la
   hauteur du header (`height: 4rem`), ajustez aussi ce padding (dans
   `_header.scss` et `_responsive.scss`).

---

## 7. Git

Le projet est relié au dépôt privé GitHub.

```bash
git remote -v                 # → https://github.com/BDSproject/Fullpowerexhaust.git
git status                    # voir les fichiers modifiés
git add -A
git commit -m "Mon changement"
git push                      # envoyer sur GitHub (branche main)
```

---

## 8. Scripts utilitaires (`tools/`)

À lancer depuis la racine avec `php tools/<script>.php` (utile pour les données
de démo, pas pour les retouches de texte courantes) :

- `install_french.php` — installer la langue française
- `create_demo_categories.php` / `create_demo_products.php` / `create_demo_manufacturers.php` — données de démo
- `add_demo_images.php` — images de démo
- `create_legal_pages.php` — générer les pages légales CMS
- `translate_to_french.php` / `translate_content.php` / `add_missing_fr.php` — traductions FR

---

## 9. Récap des commandes utiles

```bash
./start-dev.sh                 # lancer le site (localhost:8000)
npm run build:scss             # compiler le SCSS
npm run build:css              # compiler Tailwind
npm run watch:scss             # compiler le SCSS en continu
rm -rf var/cache/dev/*         # vider le cache PrestaShop
git status && git push         # voir / envoyer les changements
```
