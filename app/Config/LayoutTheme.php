<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class LayoutTheme extends BaseConfig
{
	/**
	 * Layout global del sistema.
	 * Valores válidos: legacy, sgl
	 */
	public string $defaultVariant = 'sgl';
}