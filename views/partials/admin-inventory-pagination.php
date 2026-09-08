<?php
declare(strict_types=1);

/** @var array{items: list<array<string,mixed>>, page: int, per_page: int, total: int, total_pages: int, from: int, to: int} $inventoryPagination */
$page = (int) $inventoryPagination['page'];
$per = (int) $inventoryPagination['per_page'];
$total = (int) $inventoryPagination['total'];
$totalPages = (int) $inventoryPagination['total_pages'];
$from = (int) $inventoryPagination['from'];
$to = (int) $inventoryPagination['to'];
$prevUrl = $page > 1 ? Catalog::inventoryUrl($page - 1, $per) : null;
$nextUrl = $page < $totalPages ? Catalog::inventoryUrl($page + 1, $per) : null;
$rangeLabel = $total === 0
    ? 'No products to show'
    : 'Showing ' . $from . '–' . $to . ' of ' . $total;
$navBtn = 'inline-flex items-center justify-center gap-1.5 min-h-[2.25rem] min-w-[5.75rem] px-3 py-2 rounded text-[10px] font-bold uppercase tracking-wider transition';
$navOn = $navBtn . ' bg-white border border-gray-300 text-gray-800 hover:border-[#4c1719] hover:text-[#4c1719]';
$navOff = $navBtn . ' bg-white/70 border border-gray-200 text-gray-400 cursor-not-allowed';
?>
<nav class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 px-4 py-3.5 bg-[#fbf9f5] border-t border-gray-200" aria-label="Inventory pagination">
    <p class="text-xs text-gray-600"><?= e($rangeLabel); ?></p>
    <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4">
        <form method="get" action="admin.php" class="flex items-center gap-2">
            <input type="hidden" name="tab" value="inventory">
            <label for="inventory-per-page" class="text-[10px] font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">Per page</label>
            <select
                id="inventory-per-page"
                name="per"
                class="px-2.5 py-2 bg-white border border-gray-300 rounded text-xs min-w-[4.5rem]"
                onchange="this.form.submit()"
            >
                <?php foreach (Catalog::PAGE_SIZES as $size): ?>
                    <option value="<?= (int) $size; ?>"<?= $per === (int) $size ? ' selected' : ''; ?>><?= (int) $size; ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="px-3 py-2 border border-gray-300 rounded text-[10px] font-bold uppercase tracking-wider text-gray-700 bg-white hover:bg-white hover:border-[#4c1719] hover:text-[#4c1719]">Apply</button>
        </form>
        <div class="flex items-center gap-2">
            <?php if ($prevUrl !== null): ?>
                <a href="<?= e($prevUrl); ?>" class="<?= e($navOn); ?>" aria-label="Previous page">
                    <i class="fa-solid fa-chevron-left text-[9px]" aria-hidden="true"></i>
                    Prev
                </a>
            <?php else: ?>
                <span class="<?= e($navOff); ?>" aria-disabled="true">
                    <i class="fa-solid fa-chevron-left text-[9px]" aria-hidden="true"></i>
                    Prev
                </span>
            <?php endif; ?>
            <p class="min-w-[5.5rem] text-center text-[11px] font-semibold text-[#4c1719] sm:hidden" aria-current="page">
                Page <?= $page; ?> of <?= $totalPages; ?>
            </p>
            <?php if ($totalPages <= 7): ?>
                <div class="hidden sm:flex items-center gap-1">
                    <?php for ($n = 1; $n <= $totalPages; $n++):
                        $isCurrent = $n === $page;
                        $pageClass = $isCurrent
                            ? 'inline-flex items-center justify-center w-9 h-9 rounded text-[11px] font-bold bg-[#4c1719] text-white'
                            : 'inline-flex items-center justify-center w-9 h-9 rounded text-[11px] font-semibold bg-white border border-gray-300 text-gray-700 hover:border-[#4c1719] hover:text-[#4c1719]';
                    ?>
                        <?php if ($isCurrent): ?>
                            <span class="<?= e($pageClass); ?>" aria-current="page"><?= $n; ?></span>
                        <?php else: ?>
                            <a href="<?= e(Catalog::inventoryUrl($n, $per)); ?>" class="<?= e($pageClass); ?>" aria-label="Page <?= $n; ?>"><?= $n; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
            <?php else: ?>
                <p class="hidden sm:block min-w-[5.5rem] text-center text-[11px] font-semibold text-[#4c1719]" aria-current="page">
                    Page <?= $page; ?> of <?= $totalPages; ?>
                </p>
            <?php endif; ?>
            <?php if ($nextUrl !== null): ?>
                <a href="<?= e($nextUrl); ?>" class="<?= e($navOn); ?>" aria-label="Next page">
                    Next
                    <i class="fa-solid fa-chevron-right text-[9px]" aria-hidden="true"></i>
                </a>
            <?php else: ?>
                <span class="<?= e($navOff); ?>" aria-disabled="true">
                    Next
                    <i class="fa-solid fa-chevron-right text-[9px]" aria-hidden="true"></i>
                </span>
            <?php endif; ?>
        </div>
    </div>
</nav>
