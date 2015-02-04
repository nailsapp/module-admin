<?php

/**
 * This class is used for building nav options in admin
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Helper
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin;

class Nav
{
	protected $label;
	protected $icon;
	protected $methods;

	// --------------------------------------------------------------------------

	public function __construct($label, $icon = 'fa-cog')
	{
		$this->label   = $label;
		$this->icon    = $icon;
		$this->methods = array();
	}

	// --------------------------------------------------------------------------

	public function getLabel()
	{
		return $this->label;
	}

	// --------------------------------------------------------------------------

	public function getIcon()
	{
		return $this->icon;
	}

	// --------------------------------------------------------------------------

	public function getMethods()
	{
		asort($this->methods);
		return $this->methods;
	}

	// --------------------------------------------------------------------------

	public function addMethod($label, $url = 'index')
	{
		$this->methods[$url] = $label;
		return $this;
	}

	// --------------------------------------------------------------------------

	public function removeMethod($url)
	{
		unset($this->methods[$url]);
		return $this;
	}
}