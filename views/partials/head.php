<?php
declare(strict_types=1);

$pageTitle = $pageTitle ?? 'KDesigns Blooms & Styles';
$cssBundle = $cssBundle ?? 'app';
$extraHead = $extraHead ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <?php if ($cssBundle === 'storefront' || $cssBundle === 'both'): ?>
    <link href="https://fonts.googleapis.com/css2?family=Alex+Brush&family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600&display=swap" rel="stylesheet">
    <?php else: ?>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <?php endif; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
          integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw=="
          crossorigin="anonymous" referrerpolicy="no-referrer">
    <?php if ($cssBundle === 'storefront' || $cssBundle === 'both'): ?>
    <link rel="stylesheet" href="<?= e(kd_asset('assets/css/style.css')); ?>">
    <?php endif; ?>
    <?php if ($cssBundle === 'app' || $cssBundle === 'both'): ?>
    <link rel="stylesheet" href="<?= e(kd_asset('assets/css/app.css')); ?>">
    <?php endif; ?>
    <?= $extraHead; ?>
</head>
