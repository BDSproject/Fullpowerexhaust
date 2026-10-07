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

namespace LinkBay\Service;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Simulated eBay catalog used by the demo mode.
 *
 * Provides realistic listings, categories and item details so a prospect can
 * try the whole import/sync flow without an eBay account. Every entry follows
 * the exact shape produced by EbayApiClient, so the import and sync pipelines
 * run unchanged.
 */
final class DemoData
{
    /** Marketplace tag applied to every demo listing. */
    private const MARKETPLACE = 'EBAY_FR';

    /**
     * @return array<int, array<string, mixed>> the full demo catalog (normalized item shape)
     */
    public static function catalog(): array
    {
        return [
            self::item('3000001001', 'DEMO-1001', 'Lampe de table LED en bois', '101', 'Éclairage', 39.99, 25, 'lampe'),
            self::item('3000001002', 'DEMO-1002', 'Coussin décoratif velours côtelé 45x45', '103', 'Textile', 19.99, 60, 'coussin', [
                ['sku' => 'DEMO-1002-BC', 'quantity' => 20, 'price' => 19.99, 'attributes' => ['Couleur' => 'Bleu canard']],
                ['sku' => 'DEMO-1002-TE', 'quantity' => 22, 'price' => 19.99, 'attributes' => ['Couleur' => 'Terracotta']],
                ['sku' => 'DEMO-1002-GP', 'quantity' => 18, 'price' => 19.99, 'attributes' => ['Couleur' => 'Gris perle']],
            ]),
            self::item('3000001003', 'DEMO-1003', 'Vase en céramique artisanal', '102', 'Décoration', 29.50, 15, 'vase'),
            self::item('3000001004', 'DEMO-1004', 'Enceinte Bluetooth nomade waterproof', '201', 'Audio', 49.99, 40, 'enceinte', [
                ['sku' => 'DEMO-1004-NO', 'quantity' => 24, 'price' => 49.99, 'attributes' => ['Couleur' => 'Noir']],
                ['sku' => 'DEMO-1004-BL', 'quantity' => 16, 'price' => 49.99, 'attributes' => ['Couleur' => 'Blanc']],
            ]),
            self::item('3000001005', 'DEMO-1005', 'Câble USB-C tressé 2 m charge rapide', '202', 'Accessoires', 9.99, 200, 'cable'),
            self::item('3000001006', 'DEMO-1006', 'Support téléphone magnétique voiture', '202', 'Accessoires', 15.99, 80, 'support'),
            self::item('3000001007', 'DEMO-1007', 'Baskets blanches en cuir pleine fleur', '301', 'Chaussures', 59.99, 30, 'baskets', [
                ['sku' => 'DEMO-1007-39', 'quantity' => 6, 'price' => 59.99, 'attributes' => ['Pointure' => '39']],
                ['sku' => 'DEMO-1007-40', 'quantity' => 9, 'price' => 59.99, 'attributes' => ['Pointure' => '40']],
                ['sku' => 'DEMO-1007-41', 'quantity' => 10, 'price' => 59.99, 'attributes' => ['Pointure' => '41']],
                ['sku' => 'DEMO-1007-42', 'quantity' => 5, 'price' => 59.99, 'attributes' => ['Pointure' => '42']],
            ]),
            self::item('3000001008', 'DEMO-1008', 'Sac à main simili cuir grainé', '302', 'Sacs', 34.99, 22, 'sac'),
            self::item('3000001009', 'DEMO-1009', 'Tapis berbère de salon 160x230', '103', 'Textile', 89.99, 10, 'tapis'),
            self::item('3000001010', 'DEMO-1010', 'Casque audio sans fil à réduction de bruit', '201', 'Audio', 79.99, 18, 'casque', [
                ['sku' => 'DEMO-1010-NM', 'quantity' => 11, 'price' => 79.99, 'attributes' => ['Couleur' => 'Noir mat']],
                ['sku' => 'DEMO-1010-AR', 'quantity' => 7, 'price' => 79.99, 'attributes' => ['Couleur' => 'Argent']],
            ]),
        ];
    }

    /**
     * @return array<int|string, array<string, string|int>> category id => node (id, name, parent)
     */
    public static function tree(): array
    {
        return [
            '100' => ['id' => '100', 'name' => 'Maison & Jardin', 'parent' => -1],
            '101' => ['id' => '101', 'name' => 'Éclairage', 'parent' => 100],
            '102' => ['id' => '102', 'name' => 'Décoration', 'parent' => 100],
            '103' => ['id' => '103', 'name' => 'Textile', 'parent' => 100],
            '200' => ['id' => '200', 'name' => 'High-Tech', 'parent' => -1],
            '201' => ['id' => '201', 'name' => 'Audio', 'parent' => 200],
            '202' => ['id' => '202', 'name' => 'Accessoires', 'parent' => 200],
            '300' => ['id' => '300', 'name' => 'Mode', 'parent' => -1],
            '301' => ['id' => '301', 'name' => 'Chaussures', 'parent' => 300],
            '302' => ['id' => '302', 'name' => 'Sacs', 'parent' => 300],
        ];
    }

    /**
     * @return array<int, array<string, bool|int|string>> category entries, same shape as EbayApiClient::getCategories()
     */
    public static function categories(int $categoryParent): array
    {
        $result = [];
        $hasChildren = [];

        foreach (self::tree() as $node) {
            $parent = (int) $node['parent'];
            if ($parent >= 0) {
                $hasChildren[$parent] = true;
            }
        }

        foreach (self::tree() as $node) {
            if ((int) $node['parent'] !== $categoryParent) {
                continue;
            }

            $result[] = [
                'CategoryID' => (string) $node['id'],
                'CategoryName' => (string) $node['name'],
                'LeafCategory' => !isset($hasChildren[(int) $node['id']]),
                'CategoryParentID' => $categoryParent <= 0 ? 0 : $categoryParent,
            ];
        }

        return $result;
    }

    /**
     * @return array<int, array<string, mixed>> listings of a category (including its subcategories)
     */
    public static function listings(?string $categoryId = null): array
    {
        $categoryId = $categoryId === null || $categoryId === '' || $categoryId === 'all' ? null : $categoryId;

        $result = [];
        foreach (self::catalog() as $item) {
            if ($categoryId !== null && !self::isInCategory((string) $item['category_id'], $categoryId)) {
                continue;
            }
            $result[] = $item;
        }

        return $result;
    }

    /**
     * @return array<string, mixed> the full normalized item detail, or an empty array
     */
    public static function itemDetail(string $itemId): array
    {
        $itemId = (string) preg_replace('/^v1\|/', '', $itemId);

        foreach (self::catalog() as $item) {
            if ((string) $item['legacy_item_id'] === $itemId || (string) $item['item_id'] === $itemId) {
                return $item;
            }
        }

        throw new \RuntimeException('Listing "' . $itemId . '" was not found in the demo catalog.');
    }

    /**
     * @return array<string, mixed> category info, same shape as EbayApiClient::getCategoryInfo()
     */
    public static function categoryInfo(string $categoryId): array
    {
        $node = self::tree()[$categoryId] ?? null;

        return [
            'Category' => [
                'CategoryID' => $categoryId,
                'CategoryParentID' => $node !== null ? (int) $node['parent'] : 0,
                'CategoryName' => $node !== null ? (string) $node['name'] : '',
            ],
        ];
    }

    /**
     * Builds one normalized item.
     *
     * @param array<int, array<string, mixed>> $variations
     *
     * @return array<string, mixed>
     */
    private static function item(
        string $legacyId,
        string $sku,
        string $title,
        string $categoryId,
        string $categoryName,
        float $price,
        int $quantity,
        string $seed,
        array $variations = [],
    ): array {
        $image = 'https://picsum.photos/seed/linkbay' . $seed . '/800/800';

        return [
            'item_id' => 'v1|' . $legacyId . '|0',
            'legacy_item_id' => $legacyId,
            'marketplace_id' => self::MARKETPLACE,
            'sku' => $sku,
            'title' => $title,
            'description' => self::description($title),
            'images' => [$image],
            'quantity' => $quantity,
            'price' => $price,
            'currency' => 'EUR',
            'category_id' => $categoryId,
            'category_name' => $categoryName,
            'variations' => $variations,
        ];
    }

    private static function description(string $title): string
    {
        return '<p>' . $title . ' — article de démonstration LinkBay.</p>'
            . '<p>Excellente qualité, expédié sous 24 h ouvrées. Cet article fictif sert à illustrer '
            . 'l\'import, la synchronisation de stock et les prix dynamiques du module.</p>'
            . '<p><strong>Caractéristiques :</strong> matériaux durables, finitions soignées, garanti.</p>';
    }

    private static function isInCategory(string $itemCategoryId, string $targetCategoryId): bool
    {
        $node = self::tree()[$itemCategoryId] ?? null;
        if ($node === null) {
            return false;
        }

        $current = $itemCategoryId;
        while ($current !== '' && isset(self::tree()[$current])) {
            if ($current === $targetCategoryId) {
                return true;
            }
            $parent = (int) self::tree()[$current]['parent'];
            if ($parent < 0) {
                break;
            }
            $current = (string) $parent;
        }

        return false;
    }
}
