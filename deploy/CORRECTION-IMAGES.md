# Images produit affichées en texte (alt) sur le front-office — cause et correction

## Symptôme
Les images importées par le module eBay sont **visibles dans le back-office**,
mais sur le front-office le navigateur affiche le **texte alternatif**
(« Ligne Akrapovic R1 2004-2006 ») à la place de la photo.

## Diagnostic (vérifié sur fullpowerexhaust.com)

| Test | Résultat | Conclusion |
|---|---|---|
| `https://fullpowerexhaust.com/img/p/6/1/9/619-product_main.jpg` | **200 image/jpeg** (139 Ko) | Le fichier **existe** : l'import a bien fonctionné |
| `https://fullpowerexhaust.com/619-product_main/akrapovic-r1-line-2004-2006.jpg` | **404** page HTML PrestaShop | La réécriture d'URL ne s'applique **pas** |
| `https://www.fullpowerexhaust.com/619-product_main/akrapovic-r1-line-2004-2006.jpg` | **200 image/jpeg** | …mais elle s'applique **sur le www** |
| `https://fullpowerexhaust.com/themes/…/custom.css` | 200 | Les deux domaines pointent bien vers le même dossier |

**Cause :** le `.htaccess` du serveur encadre les règles de réécriture d'images
par `RewriteCond %{HTTP_HOST} ^www.fullpowerexhaust.com$`. Or la boutique est
configurée **et visitée** en `fullpowerexhaust.com` (sans www) — c'est ce
qu'indiquent la balise canonique et le redirect 302 de www vers sans-www.

Conséquence : sur le domaine réel, `/619-product_main/….jpg` n'est jamais
réécrit vers `img/p/6/1/9/619-product_main.jpg`. La requête tombe dans
`index.php`, qui répond 404, et le navigateur affiche le texte `alt`.

Le même phénomène touchait **toutes** les images du site (accueil, catégories,
panier, produits importés) — pas seulement celles du module eBay.

## Correction

Le fichier `.htaccess` fourni (`deploy/htaccess-a-renommer.txt`) est **identique
à celui que génère PrestaShop**, mais **sans les conditions sur le nom d'hôte** :
les règles s'appliquent donc quel que soit le domaine (avec ou sans www,
domaine de préproduction, adresse IP…).

### Mise en œuvre
1. Renommer `deploy/htaccess-a-renommer.txt` en `.htaccess`
   (nom commençant par un point — activez l'affichage des fichiers cachés).
2. Le téléverser à la **racine** de la boutique, en remplaçant l'existant.
3. Vider le cache PrestaShop (*Paramètres avancés → Performances → Vider le cache*).

Aucune modification de la base ni des images n'est nécessaire.

### Vérification
```bash
curl -I "https://fullpowerexhaust.com/619-product_main/akrapovic-r1-line-2004-2006.jpg"
# Attendu : HTTP/2 200  content-type: image/jpeg
```

## Pourquoi le back-office fonctionnait

Le back-office s'affiche sur le domaine `www` (ou depuis une session
administrateur non redirigée) : les règles de réécriture y étaient actives, donc
les vignettes s'affichaient. Le front, lui, est servi sur le domaine sans www.

## Si le problème revient

PrestaShop régénère `.htaccess` lorsque vous enregistrez
*Paramètres avancés → SEO & URLs*. La régénération réintroduit des conditions
sur le domaine, calculées d'après l'URL de la boutique en base : elles seront
correctes tant que la boutique est bien configurée en `fullpowerexhaust.com`.
Si les images cassent à nouveau, rechargez le fichier fourni.
