<?php
/**
 * Is Greater or Less than Validation Rule
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.validationrule.isgreaterorlessthan
 * @copyright   Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

namespace Fabrik\Plugins\Validationrule;

// No direct access
defined('_JEXEC') or die('Restricted access');

use Fabrik\Helpers\Worker;

/**
 * Is Greater or Less than Validation Rule
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.validationrule.isgreaterorlessthan
 * @since       3.0
 */
class Isgreaterorlessthan extends Validationrule
{
	/**
	 * Plugin name
	 *
	 * @var string
	 */
	protected $pluginName = 'isgreaterorlessthan';

	/**
	 * Validate the elements data against the rule
	 *
	 * @param   string  $data           To check
	 * @param   int     $repeatCounter  Repeat group counter
	 *
	 * @return  bool  true if validation passes, false if fails
	 */
	public function validate($data, $repeatCounter)
	{
		// Could be a drop-down with multi-values
		if (is_array($data))
		{
			$data = implode('', $data);
		}

		$params = $this->getParams();
		$elementModel = $this->elementModel;
		$formData = $elementModel->getFormModel()->formData;
		$cond = $params->get('isgreaterorlessthan-greaterthan');
		$compareValue = $params->get('compare_value', '');

		switch ($cond)
		{
			case '0':
				$cond = '<';
				$base = $data < $compareValue;
				break;
			case '1':
				$cond = '>';
				$base = $data > $compareValue;
				break;
			case '2':
				$cond = '<=';
				$base = $data <= $compareValue;
				break;
			case '3':
				$cond = '>=';
				$base = $data >= $compareValue;
				break;
			case '4':
			default:
				$cond = '==';
				$base = $data == $compareValue;
				break;
		}

		$otherElementModel = $this->getOtherElement();
		$compare = $compareValue === '' ? $otherElementModel->getValue($formData, $repeatCounter) : $compareValue;

		if ($this->allowEmpty() && ($data === '' || $compare === ''))
		{
			return true;
		}

		if ($compareValue === '')
		{
			$res = $elementModel->greaterOrLessThan($data, $cond, $compare);
		}
		else
		{
			return $base;
		}

		return $res;
	}

	/**
	 * Does the validation allow empty value?
	 * Default is false, can be overridden on per-validation basis (such as isnumeric)
	 *
	 * @return	bool
	 */
	protected function allowEmpty()
	{
		$params = $this->getParams();
		$allow_empty = $params->get('isgreaterorlessthan-allow_empty');

		return $allow_empty == '1';
	}

	/**
	 * Get the other element to compare this elements data against
	 *
	 * @return  object element model
	 */
	private function getOtherElement()
	{
		$params = $this->getParams();
		$otherField = $params->get('isgreaterorlessthan-comparewith');

		return Worker::getPluginManager()->getElementPlugin($otherField);
	}
}
