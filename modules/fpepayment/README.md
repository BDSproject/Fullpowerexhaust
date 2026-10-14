# FPE Paiement (Stripe) — module de paiement allégé

Paiement par carte via la **page hébergée Stripe** : le numéro de carte est saisi
sur Stripe, jamais sur votre boutique. Aucun SDK JavaScript, aucune dépendance
Composer, ~400 lignes de code. Conforme PCI-DSS « par conception » (SAQ-A).

## Fonctionnement

```
Étape paiement           → « Payer par carte bancaire (Stripe) »
Passer la commande       → validation.php : commande créée en « attente »,
                           puis redirection vers la page de paiement Stripe
Stripe                   → le client paie (carte, Apple Pay, Google Pay…)
Retour                   → payment_return.php : confirme et redirige vers
                           la confirmation de commande
Webhook                  → webhook.php : confirme la commande même si le
                           client ferme l'onglet (checkout.session.completed)
```

## Mise en service (10–15 minutes)

1. **Installer** le dossier `modules/fpepayment/` puis installer le module dans
   le Module Manager.
2. **Stripe** → *Développeurs → Clés API* : copier la **clé secrète de test**
   (`sk_test_…`) dans le module (mode *Test*).
3. **Stripe** → *Développeurs → Webhooks → Ajouter un endpoint* :
   - URL : celle affichée dans la config du module (se termine par `/webhook`)
   - Événement : **`checkout.session.completed`**
   - Copier la « signing secret » (`whsec_…`) dans le module.
4. **Tester** une commande en mode *Test* avec la carte de test `4242 4242 4242 4242`
   (toute date future, tout CVC).
5. Passer en mode *Production* : saisir la clé `sk_live_…`, recréer un webhook
   (clé `whsec_…` de production), enregistrer.

## Limites volontaires

- Devise **EUR** uniquement (boutique mono-devise).
- Un seul article de ligne (le total TTC de la commande) sur la page Stripe.
- Livraison incluse dans le montant (pas de ligne séparée).

## Sécurité

- Signature des webhooks vérifiée (HMAC-SHA256, comparaison à temps constant).
- Aucune donnée carte ne transite par le serveur.
- Confirmation idempotente (le webhook et la page de retour peuvent se croiser).

## Stripe Connect — répartition plateforme / vendeur (marketplace)

Le module peut, en plus de traiter le paiement, **répartir les fonds** entre
deux comptes Stripe :

```
Client paie 100 €
      │
      ▼
Compte plateforme (Beassisted)
      ├──► commission (ex. 3 €)  →  reste sur Beassisted
      └──► solde (ex. 97 €)      →  transfert vers le compte connecté (FullPowerExhaust)
```

C'est un « destination charge » avec « application fee » :

1. Dans Stripe, activer **Stripe Connect** sur le compte plateforme (Beassisted).
2. Créer le **compte connecté** FullPowerExhaust via l'onboarding Connect
   (Stripe → Connect → Comptes → Créer), et noter son identifiant `acct_…`.
3. Dans le module : cocher **« Activer la répartition »**, coller le `acct_…`,
   et renseigner la **commission** (en % ou montant fixe).

À chaque paiement, Stripe prélève automatiquement la commission pour Beassisted
et reverse le solde à FullPowerExhaust — aucune comptabilité manuelle.
La commission est plafonnée pour toujours laisser au moins 1 centime au compte
connecté (contrainte Stripe).

### Note fiscale
Dans ce schéma, le compte plateforme est « merchant of record » : c'est
Beassisted qui encaisse et reverse. Validez le schéma de facturation
(refacturation ou commission) avec votre comptable.
