<?php
/**
 * Copyright since 2024 Beassisted
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/afl-3.0.php
 *
 * @author    Beassisted
 * @copyright Copyright (c) 2024-present Beassisted
 * @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

declare(strict_types=1);

namespace LinkBay\Hook;

use LinkBay\Config\Config;
use LinkBay\Entity\LinkBayLog;
use LinkBay\Repository\LinkBayLogRepository;
use LinkBay\Repository\LinkBayProductRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Centralizes the module hook implementations.
 */
final class HookHandler
{
    private \Module $module;
    private \Context $context;
    private LinkBayProductRepository $productLinkRepository;
    private LinkBayLogRepository $logRepository;

    public function __construct(\Module $module, \Context $context)
    {
        $this->module = $module;
        $this->context = $context;
        $this->productLinkRepository = new LinkBayProductRepository();
        $this->logRepository = new LinkBayLogRepository();
    }

    /**
     * Injects CSS/JS on LinkBay back office pages only.
     *
     * @param array<string, mixed> $params
     */
    public function displayBackOfficeHeader(array $params): string
    {
        $controllerName = (string) ($params['controller_name'] ?? \Tools::getValue('controller', ''));

        $linkbayControllers = ['AdminLinkBay', 'AdminLinkBayImport', 'AdminLinkBayDashboard'];
        if (!in_array($controllerName, $linkbayControllers, true)) {
            return '';
        }

        $controller = $this->context->controller;

        if ($controller instanceof \AdminController) {
            $controller->addCSS($this->module->getPathUri() . 'views/css/linkbay.css');
        }

        return '';
    }

    /**
     * Reacts to product updates.
     *
     * @param array<string, mixed> $params
     */
    public function onProductUpdate(array $params): void
    {
        $product = $params['object'] ?? null;

        if (!$product instanceof \Product || empty($product->id)) {
            return;
        }

        $rows = $this->productLinkRepository->findByProduct((int) $product->id);
        if (empty($rows)) {
            return;
        }

        $this->log(
            (int) $product->id,
            $rows[0]->getEbayItemId(),
            'product_update',
            LinkBayLog::STATUS_INFO,
            'PrestaShop product updated; stock will be pushed on next cron run.'
        );
    }

    /**
     * Reacts to product deletion by removing stale mapping rows.
     *
     * @param array<string, mixed> $params
     */
    public function onProductDelete(array $params): void
    {
        $product = $params['object'] ?? null;

        if (!$product instanceof \Product || empty($product->id)) {
            return;
        }

        $this->productLinkRepository->deleteByProduct((int) $product->id);
    }

    /**
     * Reacts to combination updates (stock changes on combinations).
     *
     * @param array<string, mixed> $params
     */
    public function onCombinationUpdate(array $params): void
    {
        $combination = $params['object'] ?? null;

        if (!$combination instanceof \Combination || empty($combination->id) || empty($combination->id_product)) {
            return;
        }

        $rows = $this->productLinkRepository->findByProduct((int) $combination->id_product);
        if (empty($rows)) {
            return;
        }

        $this->log(
            (int) $combination->id_product,
            $rows[0]->getEbayItemId(),
            'combination_update',
            LinkBayLog::STATUS_INFO,
            'PrestaShop combination updated; stock will be pushed on next cron run.'
        );
    }

    /**
     * Lazy cron: PrestaShop has no native scheduler, so the module piggybacks
     * on page views. When enough time has passed since the last run, a
     * fire-and-forget request is sent to the module's own cron URL so the sync
     * runs in a separate process and never slows down the visitor. The client
     * has nothing to configure.
     *
     * @param array<string, mixed> $params
     */
    public function onActionDispatcher(array $params): void
    {
        $frequency = max(1, (int) \Configuration::get(Config::SYNC_FREQUENCY));
        $lastRun = (int) \Configuration::get(Config::LAST_CRON_RUN);

        if ($lastRun > 0 && time() - $lastRun < $frequency * 60) {
            return;
        }

        \Configuration::updateValue(Config::LAST_CRON_RUN, (string) time());

        $token = (string) \Configuration::get(Config::CRON_TOKEN);
        if ($token === '') {
            return;
        }

        $this->fireBackgroundRequest($this->cronUrl($token));
    }

    /**
     * Builds the module's own cron URL from the current shop domain.
     */
    private function cronUrl(string $token): string
    {
        $base = \Tools::getShopDomainSsl(true) . __PS_BASE_URI__;

        return $base . 'index.php?fc=module&module=linkbay&controller=cron&token=' . urlencode($token) . '&type=all';
    }

    /**
     * Sends a non-blocking GET request so the cron runs in its own process.
     */
    private function fireBackgroundRequest(string $url): void
    {
        $parts = parse_url($url);
        if (!is_array($parts) || empty($parts['host'])) {
            return;
        }

        $scheme = (string) ($parts['scheme'] ?? 'http');
        $port = (int) ($parts['port'] ?? ($scheme === 'https' ? 443 : 80));
        $transport = $scheme === 'https' ? 'ssl://' : '';

        $fp = @fsockopen($transport . $parts['host'], $port, $errno, $errstr, 2);
        if ($fp === false) {
            return;
        }

        $path = ($parts['path'] ?? '/') . (isset($parts['query']) ? '?' . $parts['query'] : '');
        $host = $parts['host'] . ($port !== 80 && $port !== 443 ? ':' . $port : '');

        fwrite($fp, "GET $path HTTP/1.1\r\nHost: $host\r\nConnection: close\r\n\r\n");
        fclose($fp);
    }

    private function log(int $idProduct, string $ebayItemId, string $action, string $status, string $message): void
    {
        try {
            $log = new LinkBayLog();
            $log->setIdProduct($idProduct)
                ->setEbayItemId($ebayItemId)
                ->setAction($action)
                ->setStatus($status)
                ->setMessage($message);

            $this->logRepository->add($log);
        } catch (\Throwable $e) {
            // Never break the hook execution.
        }
    }
}
