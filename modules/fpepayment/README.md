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
