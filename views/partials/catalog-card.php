<?php
declare(strict_types=1);

$stockUi = Catalog::stockDisplay((int) $product['stock']);
$badge = (string) ($product['badge'] ?? '');
$badgeClass = $badge !== '' ? Catalog::badgeClass($badge) : '';
$inStock = Catalog::inStock($product);
?>
            <div class="product-card" data-category="<?= e($product['category']); ?>">
                <div class="product-img-wrapper">
                    <?php if ($badgeClass !== ''): ?>
                    <span class="tag <?= e($badgeClass); ?>"><?= e(strtoupper($badge)); ?></span>
                    <?php endif; ?>
                    <img src="<?= e(kd_image_url((string) $product['image_path'])); ?>" alt="<?= e($product['name']); ?>" width="400" height="310">
                </div>
                <div class="product-info">
                    <span class="category"><?= e(Catalog::categoryLabel($product['category'])); ?></span>
                    <h4><?= e($product['name']); ?></h4>
                    <p class="desc"><?= e((string) ($product['description'] ?? '')); ?></p>
                    <span class="stock <?= e($stockUi['class']); ?>"><?= e($stockUi['text']); ?></span>
                    <div class="product-bottom">
                        <span class="price"><?= e(Catalog::formatPrice((int) $product['price_php'])); ?></span>
                        <?php if ($inStock): ?>
                        <a href="orderform.php?id=<?= (int) $product['id']; ?>" class="btn-primary add-to-cart">PLACE ORDER</a>
                        <?php else: ?>
                        <button class="btn-sold-out" disabled>SOLD OUT</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
