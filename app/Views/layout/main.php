<?php
if (!isset($layoutVariant)) {
	$layoutVariant = 'sgl';
	if (class_exists('\Config\LayoutTheme')) {
		$layoutConfig = new \Config\LayoutTheme();
		$layoutVariant = strtolower((string) ($layoutConfig->defaultVariant ?? 'sgl'));
	}
}

if (!in_array($layoutVariant, ['legacy', 'sgl'], true)) {
	$layoutVariant = 'sgl';
}
?>
<?= $this->include('layout/_main_shell', ['layoutVariant' => $layoutVariant]) ?>
