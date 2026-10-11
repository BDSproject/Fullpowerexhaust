-- ============================================================================
-- FULLPOWEREXHAUST — corrections à appliquer sur la base de PRODUCTION
-- (O2switch) — phpMyAdmin > base de la boutique > onglet SQL
-- ----------------------------------------------------------------------------
-- Ces corrections portent sur des DONNÉES (et non sur des fichiers) :
-- elles sont indispensables pour que les traductions et les visuels corrigés
-- apparaissent aussi en ligne.
--
-- ⚠️ Sauvegardez la base avant exécution (Export > Rapide > SQL).
-- ⚠️ Remplacez `ps_` par votre préfixe si différent.
-- Le script est IDEMPOTENT : vous pouvez le relancer sans risque.
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 1. Chaînes de configuration restées en anglais
-- ----------------------------------------------------------------------------
SET @k = (SELECT id_configuration FROM ps_configuration WHERE name = 'NW_CONDITIONS');
UPDATE ps_configuration_lang SET value = 'Vous pouvez vous désinscrire à tout moment. Vous trouverez pour cela nos informations de contact dans les conditions d''utilisation du site.'
  WHERE id_configuration = @k AND id_lang = 2;

SET @k = (SELECT id_configuration FROM ps_configuration WHERE name = 'CUSTPRIV_MSG_AUTH');
UPDATE ps_configuration_lang SET value = 'Les données personnelles que vous nous communiquez servent à répondre à vos demandes, traiter vos commandes ou vous donner accès à certaines informations. Vous disposez d''un droit de modification et de suppression de toutes vos données personnelles depuis la page « Mon compte ».'
  WHERE id_configuration = @k AND id_lang = 2;
UPDATE ps_configuration_lang SET value = 'Los datos personales que nos facilitas se utilizan para responder a tus consultas, procesar tus pedidos o darte acceso a información concreta. Puedes modificar y eliminar toda tu información personal desde la página «Mi cuenta».'
  WHERE id_configuration = @k AND id_lang = 3;
UPDATE ps_configuration_lang SET value = 'I dati personali che ci fornisci vengono utilizzati per rispondere alle tue richieste, elaborare i tuoi ordini o consentirti di accedere a informazioni specifiche. Puoi modificare ed eliminare tutti i tuoi dati personali dalla pagina «Il mio account».'
  WHERE id_configuration = @k AND id_lang = 4;

SET @k = (SELECT id_configuration FROM ps_configuration WHERE name = 'PS_CUSTOMER_SERVICE_SIGNATURE');
UPDATE ps_configuration_lang SET value = 'Cher client,\n\nCordialement,\nLe service client'            WHERE id_configuration = @k AND id_lang = 2;
UPDATE ps_configuration_lang SET value = 'Estimado cliente,\n\nUn saludo,\nAtención al cliente'         WHERE id_configuration = @k AND id_lang = 3;
UPDATE ps_configuration_lang SET value = 'Gentile cliente,\n\nCordiali saluti,\nServizio clienti'        WHERE id_configuration = @k AND id_lang = 4;

SET @k = (SELECT id_configuration FROM ps_configuration WHERE name = 'PS_MAINTENANCE_TEXT');
UPDATE ps_configuration_lang SET value = 'Nous mettons à jour notre boutique, nous serons de retour très vite.\nMerci de votre patience.' WHERE id_configuration = @k AND id_lang = 2;
UPDATE ps_configuration_lang SET value = 'Estamos actualizando nuestra tienda y volveremos muy pronto.\nGracias por tu paciencia.'       WHERE id_configuration = @k AND id_lang = 3;
UPDATE ps_configuration_lang SET value = 'Stiamo aggiornando il nostro negozio e torneremo molto presto.\nGrazie per la pazienza.'      WHERE id_configuration = @k AND id_lang = 4;

SET @k = (SELECT id_configuration FROM ps_configuration WHERE name = 'PSGDPR_CREATION_FORM');
UPDATE ps_configuration_lang SET value = 'J''accepte les conditions générales et la politique de confidentialité' WHERE id_configuration = @k AND id_lang = 2;
UPDATE ps_configuration_lang SET value = 'Acepto las condiciones generales y la política de privacidad'           WHERE id_configuration = @k AND id_lang = 3;
UPDATE ps_configuration_lang SET value = 'Accetto i termini e le condizioni e l''informativa sulla privacy'        WHERE id_configuration = @k AND id_lang = 4;

SET @k = (SELECT id_configuration FROM ps_configuration WHERE name = 'PSGDPR_CUSTOMER_FORM');
UPDATE ps_configuration_lang SET value = 'J''accepte les conditions générales et la politique de confidentialité' WHERE id_configuration = @k AND id_lang = 2;
UPDATE ps_configuration_lang SET value = 'Acepto las condiciones generales y la política de privacidad'           WHERE id_configuration = @k AND id_lang = 3;
UPDATE ps_configuration_lang SET value = 'Accetto i termini e le condizioni e l''informativa sulla privacy'        WHERE id_configuration = @k AND id_lang = 4;

-- ----------------------------------------------------------------------------
-- 2. Blocs de réassurance : icônes en URL cassée (« //modules/... »)
--    → les icônes ne s'affichaient pas (produit, panier, tunnel de commande)
-- ----------------------------------------------------------------------------
UPDATE ps_psreassurance SET icon = REPLACE(icon, '//modules/', '/modules/') WHERE icon LIKE '//modules/%';

-- ----------------------------------------------------------------------------
-- 3. Page contact : le bloc « Informations » s'affichait DEUX FOIS
--    (module ps_contactinfo branché sur deux hooks)
-- ----------------------------------------------------------------------------
DELETE mh FROM ps_hook_module mh
  JOIN ps_hook h   ON h.id_hook   = mh.id_hook
  JOIN ps_module m ON m.id_module = mh.id_module
  WHERE h.name = 'displayContactContent' AND m.name = 'ps_contactinfo';

-- ----------------------------------------------------------------------------
-- 4. Sujet du formulaire de contact resté en anglais
-- ----------------------------------------------------------------------------
UPDATE ps_contact_lang SET name = 'Service client',
                           description = 'Pour toute question sur un produit ou une commande'
  WHERE id_contact = 2 AND id_lang = 2;

-- ----------------------------------------------------------------------------
-- 5. Vérification
-- ----------------------------------------------------------------------------
SELECT 'Réassurance — icônes corrigées' AS controle, COUNT(*) AS restant
  FROM ps_psreassurance WHERE icon LIKE '//modules/%'
UNION ALL
SELECT 'Contact — sujet FR', COUNT(*) FROM ps_contact_lang
  WHERE id_contact = 2 AND id_lang = 2 AND name = 'Service client'
UNION ALL
SELECT 'Hook en double supprimé', COUNT(*) FROM ps_hook_module mh
  JOIN ps_hook h ON h.id_hook = mh.id_hook JOIN ps_module m ON m.id_module = mh.id_module
  WHERE h.name = 'displayContactContent' AND m.name = 'ps_contactinfo';

-- ----------------------------------------------------------------------------
-- 6. Titres de pages (ps_meta_lang) restés en français sur les versions ES/IT
-- ----------------------------------------------------------------------------
UPDATE ps_meta_lang SET title='Más vendidos'              WHERE id_meta=2  AND id_lang=3;
UPDATE ps_meta_lang SET title='Contacta con nosotros'     WHERE id_meta=3  AND id_lang=3;
UPDATE ps_meta_lang SET title='Marcas'                    WHERE id_meta=5  AND id_lang=3;
UPDATE ps_meta_lang SET title='Novedades'                 WHERE id_meta=6  AND id_lang=3;
UPDATE ps_meta_lang SET title='¿Olvidaste tu contraseña?' WHERE id_meta=7  AND id_lang=3;
UPDATE ps_meta_lang SET title='Ofertas'                   WHERE id_meta=8  AND id_lang=3;
UPDATE ps_meta_lang SET title='Mapa del sitio'            WHERE id_meta=9  AND id_lang=3;
UPDATE ps_meta_lang SET title='Proveedores'               WHERE id_meta=10 AND id_lang=3;
UPDATE ps_meta_lang SET title='Dirección'                 WHERE id_meta=11 AND id_lang=3;
UPDATE ps_meta_lang SET title='Direcciones'               WHERE id_meta=12 AND id_lang=3;
UPDATE ps_meta_lang SET title='Iniciar sesión'            WHERE id_meta=13 AND id_lang=3;
UPDATE ps_meta_lang SET title='Registro'                  WHERE id_meta=14 AND id_lang=3;
UPDATE ps_meta_lang SET title='Carrito'                   WHERE id_meta=15 AND id_lang=3;
UPDATE ps_meta_lang SET title='Vales de descuento'        WHERE id_meta=16 AND id_lang IN (3,4);
UPDATE ps_meta_lang SET title='Historial de pedidos'      WHERE id_meta=17 AND id_lang=3;
UPDATE ps_meta_lang SET title='Información personal'      WHERE id_meta=18 AND id_lang=3;
UPDATE ps_meta_lang SET title='Mi cuenta'                 WHERE id_meta=19 AND id_lang=3;
UPDATE ps_meta_lang SET title='Seguimiento del pedido'    WHERE id_meta=20 AND id_lang=3;
UPDATE ps_meta_lang SET title='Facturas rectificativas'   WHERE id_meta=21 AND id_lang=3;
UPDATE ps_meta_lang SET title='Pedido'                    WHERE id_meta=22 AND id_lang=3;
UPDATE ps_meta_lang SET title='Buscar'                    WHERE id_meta=23 AND id_lang=3;
UPDATE ps_meta_lang SET title='Tiendas'                   WHERE id_meta=24 AND id_lang=3;
UPDATE ps_meta_lang SET title='Seguimiento de invitado'   WHERE id_meta=25 AND id_lang=3;
UPDATE ps_meta_lang SET title='Confirmación del pedido'   WHERE id_meta=26 AND id_lang=3;
UPDATE ps_meta_lang SET title='Cronologia ordini'         WHERE id_meta=17 AND id_lang=4;
UPDATE ps_meta_lang SET title='Informazioni personali'    WHERE id_meta=18 AND id_lang=4;
UPDATE ps_meta_lang SET title='Note di credito'           WHERE id_meta=21 AND id_lang=4;
UPDATE ps_meta_lang SET title='Tracciamento ospite'       WHERE id_meta=25 AND id_lang=4;
UPDATE ps_meta_lang SET title='Accedi'                    WHERE id_meta=13 AND id_lang=4;

-- ----------------------------------------------------------------------------
-- 7. Caractéristiques produit (fiche produit) : noms en français partout
-- ----------------------------------------------------------------------------
UPDATE ps_feature_lang SET name='Type of part'                 WHERE id_feature=3 AND id_lang=1;
UPDATE ps_feature_lang SET name='Tipo de pieza'                WHERE id_feature=3 AND id_lang=3;
UPDATE ps_feature_lang SET name='Tipo di ricambio'             WHERE id_feature=3 AND id_lang=4;
UPDATE ps_feature_lang SET name='Condition'                    WHERE id_feature=4 AND id_lang=1;
UPDATE ps_feature_lang SET name='Estado'                       WHERE id_feature=4 AND id_lang=3;
UPDATE ps_feature_lang SET name='Condizione'                   WHERE id_feature=4 AND id_lang=4;
UPDATE ps_feature_lang SET name='Exhaust line brand'           WHERE id_feature=5 AND id_lang=1;
UPDATE ps_feature_lang SET name='Marca de la línea de escape'  WHERE id_feature=5 AND id_lang=3;
UPDATE ps_feature_lang SET name='Marca della linea di scarico' WHERE id_feature=5 AND id_lang=4;

-- Vérification finale : aucune ligne ne doit rester
SELECT 'titres ES encore en français' AS controle, COUNT(*) AS restant
  FROM ps_meta_lang f JOIN ps_meta_lang s ON s.id_meta=f.id_meta AND s.id_lang=3
  WHERE f.id_lang=2 AND s.title=f.title AND f.title <> ''
UNION ALL
SELECT 'caractéristiques ES/IT en français', COUNT(*)
  FROM ps_feature_lang f JOIN ps_feature_lang s ON s.id_feature=f.id_feature AND s.id_lang IN (3,4)
  WHERE f.id_lang=2 AND s.name=f.name;

-- ----------------------------------------------------------------------------
-- 8. Badge produit : « Neuf » → « Occasion »
-- ----------------------------------------------------------------------------
-- Le badge « Neuf » affiché sur les fiches produit est le flag `new` de
-- PrestaShop (« produit ajouté récemment »), sans rapport avec l'état réel.
-- Le thème affiche maintenant l'ÉTAT du produit (`condition`) : on passe donc
-- tous les produits en « Occasion » et on active l'affichage de l'état.
--
-- ⚠️ Relancez ces deux UPDATE après chaque import eBay si le module remet
--    l'état des annonces à « Neuf » — ou utilisez :
--        php tools/set_products_used.php
UPDATE ps_product      SET `condition` = 'used', show_condition = 1;
UPDATE ps_product_shop SET `condition` = 'used', show_condition = 1;

-- Les nouveaux produits créés sans état explicite seront désormais « Occasion »
ALTER TABLE ps_product      MODIFY `condition` ENUM('new','used','refurbished') NOT NULL DEFAULT 'used';
ALTER TABLE ps_product_shop MODIFY `condition` ENUM('new','used','refurbished') NOT NULL DEFAULT 'used';
ALTER TABLE ps_product      MODIFY `show_condition` TINYINT(1) NOT NULL DEFAULT 1;
ALTER TABLE ps_product_shop MODIFY `show_condition` TINYINT(1) NOT NULL DEFAULT 1;

-- ----------------------------------------------------------------------------
-- 9. Libellés restés en anglais
-- ----------------------------------------------------------------------------
-- Badge « rupture de stock » affiché « Out-of-Stock » sur les pages françaises
UPDATE ps_configuration_lang SET value = 'Rupture de stock'
  WHERE id_configuration = (SELECT id_configuration FROM ps_configuration WHERE name = 'PS_LABEL_OOS_PRODUCTS_BOD') AND id_lang = 2;
UPDATE ps_configuration_lang SET value = 'Out of stock'
  WHERE id_configuration = (SELECT id_configuration FROM ps_configuration WHERE name = 'PS_LABEL_OOS_PRODUCTS_BOD') AND id_lang = 1;

-- Bouton du module liste d'envies
UPDATE ps_configuration_lang SET value = 'Create new list'
  WHERE id_configuration = (SELECT id_configuration FROM ps_configuration WHERE name = 'blockwishlist_CreateButtonLabel') AND id_lang = 1;
UPDATE ps_configuration_lang SET value = 'Créer une nouvelle liste'
  WHERE id_configuration = (SELECT id_configuration FROM ps_configuration WHERE name = 'blockwishlist_CreateButtonLabel') AND id_lang = 2;
UPDATE ps_configuration_lang SET value = 'Crear una nueva lista'
  WHERE id_configuration = (SELECT id_configuration FROM ps_configuration WHERE name = 'blockwishlist_CreateButtonLabel') AND id_lang = 3;
UPDATE ps_configuration_lang SET value = 'Crea una nuova lista'
  WHERE id_configuration = (SELECT id_configuration FROM ps_configuration WHERE name = 'blockwishlist_CreateButtonLabel') AND id_lang = 4;

-- Vérification : doit renvoyer 0
SELECT 'produits sans état « Occasion »' AS controle, COUNT(*) AS restant
  FROM ps_product WHERE `condition` <> 'used' OR show_condition = 0;
