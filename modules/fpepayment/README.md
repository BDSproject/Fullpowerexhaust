# FPE Paiement (Stripe Connect) — plateforme Beassisted

Module de paiement **distribué** pour PrestaShop, branché sur la plateforme
Stripe Connect de **Beassisted**. Connexion en un clic, aucune clé à saisir,
back-office minimal.

## Architecture (2 composants)

```
┌─ beassisted-pay/  (backend, hébergé par Beassisted) ──────────────┐
│  détient la clé secrète de la plateforme                         │
│  prélève la commission, reverse le solde au compte connecté       │
└───────────────────────────────────────────────────────────────────┘
        ▲  API (clé marchand)          │  onboarding OAuth
        │                              ▼
┌─ modules/fpepayment/ (chez chaque marchand) ──────────────────────┐
│  bouton « Se connecter à Stripe », aucune clé secrète stockée     │
└───────────────────────────────────────────────────────────────────┘
```

Flux d'un paiement :

```
Client paie 100 €
    │
    ▼
Backend Beassisted → crée la session Stripe
    │  application_fee (commission Beassisted)
    │  transfer → compte connecté du marchand (solde)
    ▼
Stripe héberge le paiement → webhook → backend → notification signée → module
    → commande confirmée
```

## Sécurité

- La clé secrète de plateforme **ne quitte jamais** le backend Beassisted.
- Chaque marchand a une **clé API dédiée** (`merchant_key`), générée à l'onboarding.
- Les notifications backend → module sont signées en HMAC-SHA256.
- Le webhook Stripe est vérifié sur le backend (signature `whsec_`).

## Installation côté Beassisted (une seule fois)

1. Créer un compte Stripe **plateforme** et activer **Stripe Connect** (Standard).
2. Copier `beassisted-pay/config.example.php` → `config.local.php` et renseigner :
   - `stripe_secret_key` (clé secrète de la plateforme)
   - `connect_client_id` (`ca_…`, Dashboard → Paramètres → Connect)
   - `backend_url` (URL publique de `beassisted-pay/`)
   - `webhook_secret` (`whsec_…`)
   - `commission_type` / `commission_value` (la marge de Beassisted)
3. Héberger `beassisted-pay/` sur un serveur PHP (7.4+) accessible en HTTPS,
   dossier `data/` en écriture.
4. Dans Stripe → Webhooks : créer un endpoint vers `{backend_url}/webhook`
   (événement `checkout.session.completed`), et copier la `whsec_…`.

## Installation côté marchand (très simple)

1. Installer `modules/fpepayment/`.
2. Configurer → renseigner l'**URL du backend** Beassisted → Enregistrer.
3. Cliquer **« Se connecter à Stripe »** → l'onboarding Stripe s'ouvre →
   le marchand renseigne son compte/RIB → retour automatique.
4. C'est prêt : l'option « Payer par carte bancaire » apparaît à l'étape paiement.

## Endpoints du backend

| Endpoint | Rôle |
|---|---|
| `GET /onboarding/start?return_url=…` | Lance l'onboarding Stripe |
| `GET /onboarding/callback` | Échange le code → `acct_` → clé marchand |
| `POST /create-session` | Crée la session (commission + transfert) |
| `GET /retrieve-session` | Statut d'une session |
| `POST /register-webhook` | Le module déclare son URL de notification |
| `POST /webhook` | Reçoit les événements Stripe |

## Limites

- Devise **EUR** uniquement.
- Un seul article de ligne (le total TTC) sur la page Stripe.
- Le backend stocke les marchands dans `data/merchants.json` (suffisant pour
  un démarrage ; passer à une vraie base SQL avant la mise en production à
  grande échelle).

## Note fiscale

Dans ce schéma (« destination charge »), la plateforme est *merchant of record* :
c'est Beassisted qui encaisse puis reverse. Valider le schéma de facturation
avec un comptable.
