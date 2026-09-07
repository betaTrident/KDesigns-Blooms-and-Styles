<?php
declare(strict_types=1);

$tab = $tab ?? 'overview';
$pendingCount = (int) ($pendingCount ?? 0);
$lowStockCount = (int) ($lowStockCount ?? 0);
?>
    <aside class="w-64 bg-[#361012] text-white flex flex-col justify-between flex-shrink-0">
        <div>
            <div class="px-6 py-6 border-b border-white/10">
                <p class="text-[9px] tracking-[0.2em] font-semibold text-white/50 uppercase mb-1">ADMIN PANEL</p>
                <h1 class="font-serif text-lg font-bold text-white">KDesigns</h1>
                <p class="text-[11px] text-white/70 italic">Blooms &amp; Styles</p>
            </div>

            <nav class="p-4 space-y-1 text-xs">
                <a href="admin.php?tab=overview" class="flex items-center justify-between px-4 py-3 rounded transition <?= $tab === 'overview' ? 'bg-[#4c1719] font-bold text-white' : 'text-white/80 hover:bg-white/5'; ?>">
                    <span class="flex items-center gap-3"><i class="fa-solid fa-chart-pie text-sm"></i> Overview</span>
                </a>
                <a href="admin.php?tab=orders" class="flex items-center justify-between px-4 py-3 rounded transition <?= $tab === 'orders' ? 'bg-[#4c1719] font-bold text-white' : 'text-white/80 hover:bg-white/5'; ?>">
                    <span class="flex items-center gap-3"><i class="fa-regular fa-file-lines text-sm"></i> Orders</span>
                    <span class="px-2 py-0.5 bg-[#4c1719] border border-white/20 text-white rounded-full text-[10px]"><?= $pendingCount; ?></span>
                </a>
                <a href="admin.php?tab=inventory" class="flex items-center justify-between px-4 py-3 rounded transition <?= $tab === 'inventory' ? 'bg-[#4c1719] font-bold text-white' : 'text-white/80 hover:bg-white/5'; ?>">
                    <span class="flex items-center gap-3"><i class="fa-solid fa-box-archive text-sm"></i> Inventory</span>
                    <span class="px-2 py-0.5 bg-[#4c1719] border border-white/20 text-white rounded-full text-[10px]"><?= $lowStockCount; ?></span>
                </a>
                <a href="admin.php?tab=buyers" class="flex items-center justify-between px-4 py-3 rounded transition <?= $tab === 'buyers' ? 'bg-[#4c1719] font-bold text-white' : 'text-white/80 hover:bg-white/5'; ?>">
                    <span class="flex items-center gap-3"><i class="fa-solid fa-users text-sm"></i> Buyers History</span>
                </a>
            </nav>
        </div>

        <div class="p-4 border-t border-white/10">
            <a href="/" class="flex items-center justify-center gap-2 py-3 px-4 bg-white/5 hover:bg-white/10 rounded text-xs text-white/90 border border-white/10 transition uppercase tracking-wider font-semibold">
                ← Back to Site
            </a>
        </div>
    </aside>
