<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/db.php';

final class Catalog
{
    private const SELECT_COLUMNS = '
        id, name, slug, category, description, price_php, image_path, stock, badge, is_active
    ';

    /** @return list<array<string,mixed>> */
    public static function allActive(): array
    {
        $stmt = db()->query(
            'SELECT ' . self::SELECT_COLUMNS . '
             FROM products
             WHERE is_active = 1
             ORDER BY id ASC'
        );

        $rows = $stmt->fetchAll();
        $products = [];

        foreach ($rows as $row) {
            $products[] = self::mapRow($row);
        }

        return $products;
    }

    /** @return list<array<string,mixed>> */
    public static function all(): array
    {
        $stmt = db()->query(
            'SELECT ' . self::SELECT_COLUMNS . '
             FROM products
             ORDER BY id ASC'
        );

        $rows = $stmt->fetchAll();
        $products = [];

        foreach ($rows as $row) {
            $products[] = self::mapRow($row);
        }

        return $products;
    }

    public static function findById(int $id): ?array
    {
        $stmt = db()->prepare(
            'SELECT ' . self::SELECT_COLUMNS . '
             FROM products
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);

        $row = $stmt->fetch();
        if ($row === false) {
            return null;
        }

        return self::mapRow($row);
    }

    public static function findBySlug(string $slug): ?array
    {
        $stmt = db()->prepare(
            'SELECT ' . self::SELECT_COLUMNS . '
             FROM products
             WHERE slug = :slug
             LIMIT 1'
        );
        $stmt->execute([':slug' => $slug]);

        $row = $stmt->fetch();
        if ($row === false) {
            return null;
        }

        return self::mapRow($row);
    }

    /** Lookup by exact name (legacy URLs). */
    public static function findByName(string $name): ?array
    {
        $stmt = db()->prepare(
            'SELECT ' . self::SELECT_COLUMNS . '
             FROM products
             WHERE name = :name
             LIMIT 1'
        );
        $stmt->execute([':name' => $name]);

        $row = $stmt->fetch();
        if ($row === false) {
            return null;
        }

        return self::mapRow($row);
    }

    public static function inStock(array $product): bool
    {
        return (int) ($product['stock'] ?? 0) >= 1
            && (int) ($product['is_active'] ?? 0) === 1;
    }

    public static function fromRequest(): ?array
    {
        if (array_key_exists('id', $_GET)) {
            $id = filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($id === false) {
                return null;
            }

            return self::findById((int) $id);
        }

        if (array_key_exists('slug', $_GET)) {
            $slug = trim((string) $_GET['slug']);
            if ($slug === '' || strlen($slug) > 180) {
                return null;
            }

            return self::findBySlug($slug);
        }

        if (array_key_exists('product', $_GET)) {
            $name = trim((string) $_GET['product']);
            if ($name === '' || strlen($name) > 160) {
                return null;
            }

            return self::findByName($name);
        }

        return null;
    }

    public static function updateStock(int $id, int $qty): void
    {
        if ($qty < 0 || $qty > 99999) {
            throw new InvalidArgumentException('Stock quantity must be between 0 and 99999.');
        }

        $stmt = db()->prepare(
            'UPDATE products
             SET stock = :qty
             WHERE id = :id'
        );
        $stmt->execute([
            ':qty' => $qty,
            ':id'  => $id,
        ]);

        if (self::findById($id) === null) {
            throw new RuntimeException('Product not found.');
        }
    }

    public static function categoryLabel(string $category): string
    {
        return match ($category) {
            'fresh'     => 'FRESH BOUQUET',
            'dried'     => 'DRIED BOUQUET',
            'bloombox'  => 'BLOOM BOX',
            'glassdome' => 'GLASS DOME',
            'others'    => 'OTHERS',
            default     => strtoupper($category),
        };
    }

    public static function badgeClass(string $badge): string
    {
        return match ($badge) {
            'Bestseller' => 'tag-bestseller',
            'Limited'    => 'tag-limited',
            'Luxury'     => 'tag-luxury',
            'New'        => 'tag-new',
            default      => '',
        };
    }

    /** @return array{text: string, class: 'in-stock'|'low-stock'|'out-of-stock'} */
    public static function stockDisplay(int $stock): array
    {
        if ($stock === 0) {
            return [
                'text'  => 'Out of Stock',
                'class' => 'out-of-stock',
            ];
        }

        if ($stock <= 3) {
            return [
                'text'  => 'Only ' . $stock . ' left',
                'class' => 'low-stock',
            ];
        }

        return [
            'text'  => $stock . ' in stock',
            'class' => 'in-stock',
        ];
    }

    public static function formatPrice(int $pricePhp): string
    {
        return '₱' . number_format($pricePhp);
    }

    /** @param array<string,mixed> $row */
    private static function mapRow(array $row): array
    {
        return [
            'id'          => (int) $row['id'],
            'name'        => (string) $row['name'],
            'slug'        => (string) $row['slug'],
            'category'    => (string) $row['category'],
            'description' => $row['description'] !== null ? (string) $row['description'] : null,
            'price_php'   => (int) $row['price_php'],
            'image_path'  => (string) $row['image_path'],
            'stock'       => (int) $row['stock'],
            'badge'       => $row['badge'] !== null ? (string) $row['badge'] : null,
            'is_active'   => (int) $row['is_active'],
        ];
    }
}
