<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'href' => null,
    'variant' => 'default',
    'icon' => null,
    'type' => 'button',
    'target' => null,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'href' => null,
    'variant' => 'default',
    'icon' => null,
    'type' => 'button',
    'target' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $variantClass = match ($variant) {
        'primary' => 'admin-btn-primary',
        'danger' => 'admin-btn-danger',
        default => '',
    };
?>

<?php if($href): ?>
    <a href="<?php echo e($href); ?>" <?php if($target): ?> target="<?php echo e($target); ?>" <?php endif; ?> <?php echo e($attributes->merge(['class' => trim("admin-btn {$variantClass}")])); ?>>
        <?php if($icon): ?><i class="fas <?php echo e($icon); ?>"></i><?php endif; ?>
        <?php echo e($slot); ?>

    </a>
<?php else: ?>
    <button type="<?php echo e($type); ?>" <?php echo e($attributes->merge(['class' => trim("admin-btn {$variantClass}")])); ?>>
        <?php if($icon): ?><i class="fas <?php echo e($icon); ?>"></i><?php endif; ?>
        <?php echo e($slot); ?>

    </button>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\apnanest\resources\views/components/admin/button.blade.php ENDPATH**/ ?>