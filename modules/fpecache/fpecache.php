<?php
/**
 * FullPowerExhaust — Cache
 *
 * Audit et optimisation de la gestion du cache pour PrestaShop 9.
 * Permet de :
 *   - auditer l'état du cache (Smarty, CCC, cache système, cache navigation) ;
 *   - purger tous les caches en un clic ;
 *   - appliquer les réglages de production recommandés ;
 *   - visualiser les modules qui pèsent sur le tunnel de commande.
 *
 * Aucun hook front : module purement administratif.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class FpeCache extends Module
{
    public function __construct()
    {
        $this->name = 'fpecache';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'FullPowerExhaust';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = 'FullPowerExhaust Cache';
        $this->description = 'Audit, purge et optimisation du cache (Smarty, CCC, navigation, cache système).';
        $this->ps_versions_compliancy = ['min' => '1.7.0.0', 'max' => '9.99.99'];
    }

    public function install()
    {
        return parent::install();
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    /**
     * Page de configuration (rendue dans le Module Manager).
     */
    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitFpePurge')) {
            $this->purgeAll();
            $output .= $this->displayConfirmation('Caches purgés : Smarty, Symfony, bundles CCC, média et navigation.');
        }

        if (Tools::isSubmit('submitFpeOptimize')) {
            $this->applyProductionSettings();
            $this->purgeAll();
            $output .= $this->displayConfirmation('Réglages de production appliqués et caches purgés.');
        }

        if (Tools::isSubmit('submitFpeCacheSystem')) {
            $backend = (string) Tools::getValue('fpe_caching_system', '');
            $enable = (bool) Tools::getValue('fpe_cache_enable');
            $result = $this->setSystemCache($enable, $backend);
            $output .= $result
                ? $this->displayConfirmation('Configuration du cache système mise à jour (app/config/parameters.php).')
                : $this->displayError('Impossible d\'écrire app/config/parameters.php. Vérifiez ses permissions.');
        }

        $this->context->smarty->assign([
            'fpe' => $this->getAudit(),
            'module_dir' => $this->_path,
        ]);

        return $output . $this->display(__FILE__, 'views/templates/admin/configure.tpl');
    }

    /**
     * Collecte l'ensemble des données d'audit.
     */
    private function getAudit(): array
    {
        $config = fn(string $key, $default = null) => Configuration::get($key, null, null, null, $default);

        // Cache système (défini dans app/config/parameters.php, pas en base)
        $system = $this->getSystemCacheState();

        $settings = [
            'smarty' => [
                'cache' => (int) $config('PS_SMARTY_CACHE', 0),
                'force_compile' => (int) $config('PS_SMARTY_FORCE_COMPILE', 0),
                'clear_cache' => (string) $config('PS_SMARTY_CLEAR_CACHE', 'never'),
                'caching_type' => (string) $config('PS_SMARTY_CACHING_TYPE', 'filesystem'),
            ],
            'ccc' => [
                'js' => (int) $config('PS_JS_THEME_CACHE', 0),
                'css' => (int) $config('PS_CSS_THEME_CACHE', 0),
            ],
            'layered' => (int) $config('PS_LAYERED_CACHE_ENABLED', 0),
            'cache_control' => (int) $config('PS_HTACCESS_CACHE_CONTROL', 0),
            'system' => $system,
        ];

        $recommendations = $this->buildRecommendations($settings, $system);

        $sizes = [
            'theme_cache' => $this->dirSize(_PS_ROOT_DIR_ . '/themes/hummingbird/assets/cache'),
            'var_cache' => $this->dirSize(_PS_ROOT_DIR_ . '/var/cache'),
            'smart_compile' => $this->dirSize(_PS_ROOT_DIR_ . '/var/cache/' . _PS_ENV_),
        ];

        $checkout = $this->getCheckoutModules();

        $volumes = [
            'products' => (int) Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'product'),
            'categories' => (int) Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'category'),
            'modules' => (int) Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'module'),
            'hooks' => (int) Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'hook'),
            'hook_module' => (int) Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'hook_module'),
        ];

        // Backends de cache disponibles
        $availableBackends = [];
        foreach (['CacheMemcached', 'CacheMemcache', 'CacheApc', 'CacheXcache'] as $class) {
            if (class_exists($class)) {
                $availableBackends[$class] = $this->backendDescription($class);
            }
        }

        return [
            'settings' => $settings,
            'recommendations' => $recommendations,
            'sizes' => $sizes,
            'checkout' => $checkout,
            'volumes' => $volumes,
            'backends' => $availableBackends,
            'php_version' => PHP_VERSION,
            'memory_limit' => ini_get('memory_limit'),
            'opcache' => extension_loaded('Zend OPcache'),
        ];
    }

    /**
     * Lit l'état du cache système dans app/config/parameters.php.
     */
    private function getSystemCacheState(): array
    {
        $path = _PS_ROOT_DIR_ . '/app/config/parameters.php';
        $state = [
            'enable' => false,
            'system' => '',
            'file_exists' => is_file($path),
            'writable' => is_writable($path),
        ];

        if (!is_file($path)) {
            return $state;
        }

        $config = require $path;
        $state['enable'] = !empty($config['parameters']['ps_cache_enable']);
        $state['system'] = (string) ($config['parameters']['ps_caching'] ?? '');

        return $state;
    }

    /**
     * Active/désactive le cache système en écrivant parameters.php
     * via le service officiel de PrestaShop.
     */
    private function setSystemCache(bool $enable, string $backend): bool
    {
        if ($backend === '' || !class_exists($backend)) {
            return false;
        }

        try {
            $phpParameters = new PrestaShop\PrestaShop\Adapter\Configuration\PhpParameters(
                _PS_ROOT_DIR_ . '/app/config/parameters.php'
            );
            $phpParameters->setProperty('parameters.ps_cache_enable', $enable);
            $phpParameters->setProperty('parameters.ps_caching', $backend);
            $saved = $phpParameters->saveConfiguration();
        } catch (Throwable $e) {
            return false;
        }

        if ($saved && function_exists('opcache_invalidate')) {
            @opcache_invalidate(_PS_ROOT_DIR_ . '/app/config/parameters.php');
        }

        return (bool) $saved;
    }

    /**
     * Applique les réglages de production recommandés.
     */
    private function applyProductionSettings(): void
    {
        Configuration::updateValue('PS_SMARTY_FORCE_COMPILE', 0);          // ne jamais recompiler
        Configuration::updateValue('PS_SMARTY_CLEAR_CACHE', 'never');      // ne pas purger à chaque page
        Configuration::updateValue('PS_SMARTY_CACHE', 1);
        Configuration::updateValue('PS_SMARTY_CACHING_TYPE', 'filesystem');
        Configuration::updateValue('PS_JS_THEME_CACHE', 1);
        Configuration::updateValue('PS_CSS_THEME_CACHE', 1);
        Configuration::updateValue('PS_LAYERED_CACHE_ENABLED', 1);
        Configuration::updateValue('PS_HTACCESS_CACHE_CONTROL', 1);
    }

    /**
     * Purge complète des caches.
     */
    private function purgeAll(): void
    {
        // Smarty (cache + templates compilés)
        if (method_exists('Tools', 'clearSmartyCache')) {
            Tools::clearSmartyCache();
        }

        // Symfony / container
        if (method_exists('Tools', 'clearSf2Cache')) {
            Tools::clearSf2Cache();
        }

        // Bundles CCC du thème (theme-*.css / bottom-*.js) + version cache-buster
        if (method_exists('Media', 'clearCache')) {
            Media::clearCache();
        }

        // Cache XML (liste déroulante, etc.)
        if (method_exists('Tools', 'clearXMLCache')) {
            Tools::clearXMLCache();
        }

        // var/cache (résiduel) + index de la navigation à facettes
        $this->deleteTree(_PS_ROOT_DIR_ . '/var/cache', ['index.php']);
        Hook::exec('actionClearCache');
    }

    /**
     * Modules branchés sur les hooks du tunnel de commande.
     */
    private function getCheckoutModules(): array
    {
        $hooks = [
            'paymentOptions' => 'Moyens de paiement',
            'displayPaymentTop' => 'Haut de l\'étape paiement',
            'displayPaymentByBinaries' => 'Bas de l\'étape paiement',
            'displayCheckoutBeforeConfirmation' => 'Avant confirmation',
            'actionFrontControllerSetMedia' => 'Assets JS/CSS (toutes pages)',
            'displayOrderConfirmation' => 'Confirmation de commande',
        ];

        $sql = '
            SELECT h.name AS hook, m.name AS module
            FROM ' . _DB_PREFIX_ . 'hook h
            JOIN ' . _DB_PREFIX_ . 'hook_module mh ON mh.id_hook = h.id_hook
            JOIN ' . _DB_PREFIX_ . 'module m ON m.id_module = mh.id_module
            WHERE h.name IN ("' . implode('","', array_keys($hooks)) . '")
            ORDER BY h.name, mh.position';

        $rows = Db::getInstance()->executeS($sql) ?: [];

        $out = [];
        foreach ($rows as $row) {
            $out[$row['hook']][] = $row['module'];
        }

        return [
            'labels' => $hooks,
            'modules' => $out,
        ];
    }

    private function buildRecommendations(array $s, array $system): array
    {
        $r = [];

        if ($s['smarty']['force_compile'] !== 0) {
            $r[] = ['level' => 'danger', 'text' => 'Smarty recompile les templates à chaque page (PS_SMARTY_FORCE_COMPILE ≠ 0). C\'est la cause n°1 de lenteur. Passez-le à 0.'];
        }
        if ($s['smarty']['clear_cache'] !== 'never') {
            $r[] = ['level' => 'danger', 'text' => 'Le cache Smarty est vidé à chaque page (PS_SMARTY_CLEAR_CACHE = "' . $s['smarty']['clear_cache'] . '"). Passez-le à "never".'];
        }
        if (!$s['ccc']['js'] || !$s['ccc']['css']) {
            $r[] = ['level' => 'warning', 'text' => 'La combinaison CSS/JS (CCC) est désactivée : chaque fichier est téléchargé séparément. Activez-la.'];
        }
        if (!$s['layered']) {
            $r[] = ['level' => 'warning', 'text' => 'Le cache de la navigation à facettes est désactivé. Activez-le (PS_LAYERED_CACHE_ENABLED).'];
        }
        if (!$s['cache_control']) {
            $r[] = ['level' => 'info', 'text' => 'Les en-têtes de cache HTTP (Cache-Control) sont désactivés. Activez PS_HTACCESS_CACHE_CONTROL.'];
        }
        if ($system['file_exists'] && !$system['enable']) {
            $r[] = ['level' => 'info', 'text' => 'Le cache système est désactivé. Sur un hébergement avec Memcached/Redis il accélère fortement les requêtes SQL. Sur O2switch (sans backend), laissez-le désactivé.'];
        }
        if (empty($r)) {
            $r[] = ['level' => 'success', 'text' => 'Réglages de cache conformes. Aucune action nécessaire.'];
        }

        return $r;
    }

    private function dirSize(string $dir): string
    {
        if (!is_dir($dir)) {
            return '—';
        }
        $size = 0;
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
        foreach ($it as $f) {
            if ($f->isFile()) {
                $size += $f->getSize();
            }
        }

        return $this->formatBytes($size);
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' Mo';
        }

        return round($bytes / 1024, 1) . ' Ko';
    }

    private function backendDescription(string $class): string
    {
        return match ($class) {
            'CacheMemcached' => 'Memcached (recommandé si disponible)',
            'CacheMemcache' => 'Memcache',
            'CacheApc' => 'APC/APCu',
            'CacheXcache' => 'XCache',
            default => $class,
        };
    }

    /**
     * Supprime récursivement le contenu d'un dossier en conservant certains fichiers.
     */
    private function deleteTree(string $dir, array $keep = []): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($it as $f) {
            if (in_array($f->getFilename(), $keep, true)) {
                continue;
            }
            $f->isDir() ? @rmdir($f->getPathname()) : @unlink($f->getPathname());
        }
    }
}
