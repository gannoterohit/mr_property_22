<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'href' => null,
    'variant' => 'edit',
    'title' => null,
    'icon' => null,
    'type' => 'button',
    'form' => null,
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
    'variant' => 'edit',
    'title' => null,
    'icon' => null,
    'type' => 'button',
    'form' => null,
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
    $icons = [
        'view' => 'fa-eye',
        'edit' => 'fa-pen',
        'delete' => 'fa-trash-can',
        'remove' => 'fa-trash-can',
    ];
    $labels = [
        'view' => 'View',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'remove' => 'Remove',
    ];
    $isDanger = in_array($variant, ['delete', 'remove'], true);
    $iconClass = $icon ?: ($icons[$variant] ?? 'fa-circle');
    $label = $title ?: ($labels[$variant] ?? ucfirst($variant));
    $classes = $isDanger ? 'admin-action-icon admin-action-icon-danger' : 'admin-action-icon';
?>

<?php if($href): ?>
    <a href="<?php echo e($href); ?>" <?php if($target): ?> target="<?php echo e($target); ?>" <?php endif; ?> <?php echo e($attributes->merge(['class' => $classes, 'title' => $label, 'aria-label' => $label])); ?>>
        <i class="fas <?php echo e($iconClass); ?>"></i>
    </a>
<?php else: ?>
    <button type="<?php echo e($type); ?>" <?php if($form): ?> form="<?php echo e($form); ?>" <?php endif; ?> <?php echo e($attributes->merge(['class' => $classes, 'title' => $label, 'aria-label' => $label])); ?>>
        <i class="fas <?php echo e($iconClass); ?>"></i>
    </button>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\apnanest\resources\views/components/admin/action-icon.blade.php ENDPATH**/ ?>