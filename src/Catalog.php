<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/db.php';

final class Catalog
{
    private const SELECT_COLUMNS = '
        id, name, slug, category, description, price_php, image_path, stock, badge, is_active
    ';

    /** @var list<string> */
    public const CATEGORIES = ['fresh', 'dried', 'bloombox', 'glassdome', 'others'];

    /** @var list<string> */
    public const BADGES = ['Bestseller', 'Limited', 'Luxury', 'New'];

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

    public const DEFAULT_PAGE_SIZE = 10;

    /** @var list<int> */
    public const PAGE_SIZES = [5, 10, 25, 50];

    public static function normalizePageSize(mixed $raw): int
    {
        $size = filter_var($raw, FILTER_VALIDATE_INT);
        if ($size === false || !in_array($size, self::PAGE_SIZES, true)) {
            return self::DEFAULT_PAGE_SIZE;
        }

        return $size;
    }

    /**
     * @param list<array<string,mixed>> $items
     * @return array{
     *   items: list<array<string,mixed>>,
     *   page: int,
     *   per_page: int,
     *   total: int,
     *   total_pages: int,
     *   from: int,
     *   to: int
     * }
     */
    public static function paginate(array $items, int $page, int $perPage): array
    {
        $perPage = self::normalizePageSize($perPage);
        $total = count($items);
        $totalPages = max(1, (int) ceil($total / $perPage));
        if ($page < 1) {
            $page = 1;
        }
        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $offset = ($page - 1) * $perPage;
        /** @var list<array<string,mixed>> $slice */
        $slice = array_values(array_slice($items, $offset, $perPage));

        return [
            'items'       => $slice,
            'page'        => $page,
            'per_page'    => $perPage,
            'total'       => $total,
            'total_pages' => $totalPages,
            'from'        => $total === 0 ? 0 : $offset + 1,
            'to'          => min($offset + count($slice), $total),
        ];
    }

    /** @param array<string, scalar> $extra */
    public static function inventoryUrl(int $page = 1, int $perPage = self::DEFAULT_PAGE_SIZE, array $extra = []): string
    {
        $perPage = self::normalizePageSize($perPage);
        $params = ['tab' => 'inventory'];
        if ($perPage !== self::DEFAULT_PAGE_SIZE) {
            $params['per'] = $perPage;
        }
        if ($page > 1) {
            $params['page'] = $page;
        }
        foreach ($extra as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $params[(string) $key] = $value;
        }

        return 'admin.php?' . http_build_query($params);
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

    public static function updateImage(int $id, string $imagePath): void
    {
        $path = str_replace('\\', '/', trim($imagePath));
        $path = ltrim($path, '/');
        if ($path === '' || str_contains($path, '..')) {
            throw new InvalidArgumentException('Invalid image path.');
        }
        if (!str_starts_with($path, 'images/') && !str_starts_with($path, 'uploads/')) {
            throw new InvalidArgumentException('Invalid image path.');
        }

        $stmt = db()->prepare(
            'UPDATE products
             SET image_path = :image_path
             WHERE id = :id'
        );
        $stmt->execute([
            ':image_path' => $path,
            ':id'         => $id,
        ]);

        if (self::findById($id) === null) {
            throw new RuntimeException('Product not found.');
        }
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

    public static function normalizeSlug(string $name): string
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
        $slug = trim($slug, '-');

        if ($slug === '') {
            $slug = 'product';
        }

        if (strlen($slug) > 180) {
            $slug = rtrim(substr($slug, 0, 180), '-');
        }

        return $slug;
    }

    public static function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $candidate = $base;
        $suffix = 2;

        while (self::slugTaken($candidate, $ignoreId)) {
            $suffixStr = '-' . (string) $suffix;
            $maxBaseLen = 180 - strlen($suffixStr);
            $truncated = rtrim(substr($base, 0, max(1, $maxBaseLen)), '-');
            $candidate = $truncated . $suffixStr;
            ++$suffix;
        }

        return $candidate;
    }

    /** @param array<string,mixed> $input */
    public static function create(array $input): int
    {
        $data = self::validateInput($input, true);

        $slug = self::uniqueSlug(self::normalizeSlug($data['name']));

        $stmt = db()->prepare(
            'INSERT INTO products (name, slug, category, description, price_php, image_path, stock, badge, is_active)
             VALUES (:name, :slug, :category, :description, :price_php, :image_path, :stock, :badge, :is_active)'
        );
        $stmt->execute([
            ':name'        => $data['name'],
            ':slug'        => $slug,
            ':category'    => $data['category'],
            ':description' => $data['description'],
            ':price_php'   => $data['price_php'],
            ':image_path'  => $data['image_path'],
            ':stock'       => $data['stock'],
            ':badge'       => $data['badge'],
            ':is_active'   => $data['is_active'],
        ]);

        return (int) db()->lastInsertId();
    }

    /** @param array<string,mixed> $input */
    public static function update(int $id, array $input): void
    {
        $existing = self::findById($id);
        if ($existing === null) {
            throw new RuntimeException('Product not found.');
        }

        $data = self::validateInput($input, false);
        if (!array_key_exists('image_path', $input) || $data['image_path'] === null) {
            $data['image_path'] = (string) $existing['image_path'];
        }

        $slug = self::uniqueSlug(self::normalizeSlug($data['name']), $id);

        $stmt = db()->prepare(
            'UPDATE products
             SET name = :name,
                 slug = :slug,
                 category = :category,
                 description = :description,
                 price_php = :price_php,
                 image_path = :image_path,
                 stock = :stock,
                 badge = :badge,
                 is_active = :is_active
             WHERE id = :id'
        );
        $stmt->execute([
            ':name'        => $data['name'],
            ':slug'        => $slug,
            ':category'    => $data['category'],
            ':description' => $data['description'],
            ':price_php'   => $data['price_php'],
            ':image_path'  => $data['image_path'],
            ':stock'       => $data['stock'],
            ':badge'       => $data['badge'],
            ':is_active'   => $data['is_active'],
            ':id'          => $id,
        ]);
    }

    public static function setActive(int $id, bool $active): void
    {
        $stmt = db()->prepare(
            'UPDATE products SET is_active = :is_active WHERE id = :id'
        );
        $stmt->execute([
            ':is_active' => $active ? 1 : 0,
            ':id'        => $id,
        ]);

        if (self::findById($id) === null) {
            throw new RuntimeException('Product not found.');
        }
    }

    public static function hasOrderItems(int $id): bool
    {
        $stmt = db()->prepare(
            'SELECT COUNT(*) FROM order_items WHERE product_id = :id'
        );
        $stmt->execute([':id' => $id]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public static function deleteIfUnused(int $id): void
    {
        if (self::hasOrderItems($id)) {
            throw new RuntimeException('This product is on past orders. Hide it instead.');
        }

        $stmt = db()->prepare('DELETE FROM products WHERE id = :id');
        $stmt->execute([':id' => $id]);

        if ($stmt->rowCount() === 0) {
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

    private static function slugTaken(string $slug, ?int $ignoreId = null): bool
    {
        if ($ignoreId !== null) {
            $stmt = db()->prepare(
                'SELECT id FROM products WHERE slug = :slug AND id != :ignore_id LIMIT 1'
            );
            $stmt->execute([':slug' => $slug, ':ignore_id' => $ignoreId]);
        } else {
            $stmt = db()->prepare('SELECT id FROM products WHERE slug = :slug LIMIT 1');
            $stmt->execute([':slug' => $slug]);
        }

        return $stmt->fetch() !== false;
    }

    private static function validateImagePath(string $imagePath): string
    {
        $path = str_replace('\\', '/', trim($imagePath));
        $path = ltrim($path, '/');
        if ($path === '' || str_contains($path, '..')) {
            throw new InvalidArgumentException('Invalid image path.');
        }
        if (!str_starts_with($path, 'images/') && !str_starts_with($path, 'uploads/')) {
            throw new InvalidArgumentException('Invalid image path.');
        }

        return $path;
    }

    /**
     * @param array<string,mixed> $input
     * @return array{name: string, category: string, description: ?string, price_php: int, stock: int, badge: ?string, is_active: int, image_path: ?string}
     */
    private static function validateInput(array $input, bool $requireImage): array
    {
        $name = trim((string) ($input['name'] ?? ''));
        if ($name === '' || strlen($name) > 160) {
            throw new InvalidArgumentException('Product name must be 1–160 characters.');
        }

        $category = (string) ($input['category'] ?? '');
        if (!in_array($category, self::CATEGORIES, true)) {
            throw new InvalidArgumentException('Invalid product category.');
        }

        $descriptionRaw = $input['description'] ?? null;
        $description = null;
        if ($descriptionRaw !== null && $descriptionRaw !== '') {
            $description = trim((string) $descriptionRaw);
            if (strlen($description) > 2000) {
                throw new InvalidArgumentException('Description must be at most 2000 characters.');
            }
        }

        $pricePhp = filter_var($input['price_php'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 999999]]);
        if ($pricePhp === false) {
            throw new InvalidArgumentException('Price must be between 1 and 999999.');
        }

        $stock = filter_var($input['stock'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 99999]]);
        if ($stock === false) {
            throw new InvalidArgumentException('Stock must be between 0 and 99999.');
        }

        $badgeRaw = $input['badge'] ?? null;
        $badge = null;
        if ($badgeRaw !== null && $badgeRaw !== '') {
            $badge = (string) $badgeRaw;
            if (!in_array($badge, self::BADGES, true)) {
                throw new InvalidArgumentException('Invalid badge.');
            }
        }

        $isActiveRaw = $input['is_active'] ?? 1;
        $isActive = filter_var($isActiveRaw, FILTER_VALIDATE_INT);
        if ($isActive !== 0 && $isActive !== 1) {
            throw new InvalidArgumentException('Active flag must be 0 or 1.');
        }

        $imagePath = null;
        if (array_key_exists('image_path', $input)) {
            $rawPath = trim((string) $input['image_path']);
            if ($rawPath !== '') {
                $imagePath = self::validateImagePath($rawPath);
            }
        }

        if ($requireImage && ($imagePath === null || $imagePath === '')) {
            throw new InvalidArgumentException('Image is required.');
        }

        return [
            'name'        => $name,
            'category'    => $category,
            'description' => $description,
            'price_php'   => (int) $pricePhp,
            'stock'       => (int) $stock,
            'badge'       => $badge,
            'is_active'   => (int) $isActive,
            'image_path'  => $imagePath,
        ];
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
