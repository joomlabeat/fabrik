<?php
/**
 * Display a json loaded window with a repeatable set of sub fields
 *
 * @package     Joomla
 * @subpackage  Form
 * @copyright   Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Fabrik\Helpers\Html;
use Fabrik\Helpers\Worker;
use Fabrik\Helpers\Text;

jimport('joomla.form.formfield');

/**
 * Display a json loaded window with a repeatable set of sub fields
 *
 * @package     Joomla
 * @subpackage  Form
 * @since       1.6
 */

class JFormFieldFabrikModalrepeat extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'FabrikModalrepeat';

	/**
	 * Method to get the field input markup.
	 *
	 * @since	1.6
	 *
	 * @return	string	The field input markup.
	 */

	protected function getInput()
	{
		// Initialize variables.
		$app = JFactory::getApplication();
		$document = JFactory::getDocument();
		JHTML::stylesheet('administrator/components/com_fabrik/views/fabrikadmin.css');
		$subForm = new JForm($this->name, array('control' => 'jform'));
		$xml = $this->element->children()->asXML();
		$subForm->load($xml);

		if (!isset($this->form->repeatCounter))
		{
			$this->form->repeatCounter = 0;
		}
		
		// Needed for repeating modals in gmaps viz
		$subForm->repeatCounter = (int) $this->form->repeatCounter;

		/**
		 * f3 hack
		 */

		$input = $app->input;
		$view = $input->get('view', 'list');

		switch ($view)
		{
			case 'item':
				$view = 'list';
				$id = (int) $this->form->getValue('request.listid');
				break;
			case 'module':
				$view = 'list';
				$id = (int) $this->form->getValue('params.list_id');
				break;
			default:
				$id = $input->getInt('id');
				break;
		}

		if ($view === 'element')
		{
			$pluginManager = Worker::getPluginManager();
			$feModel = $pluginManager->getPluginFromId($id);
		}
		else
		{
			$feModel = JModelLegacy::getInstance($view, 'FabrikFEModel');
			$feModel->setId($id);
		}

		$subForm->model = $feModel;

		// Hack for order by elements which we now want to store as ids
		$v = json_decode($this->value);

		if (isset($v->order_by))
		{
			$formModel = $feModel->getFormModel();

			foreach ($v->order_by as &$orderBy)
			{
				$elementModel = $formModel->getElement($orderBy, true);
				$orderBy = $elementModel ? $elementModel->getId() : $orderBy;
			}
		}

		$this->value = json_encode($v);

		/*
		 * end
		 */
		$children = $this->element->children();

		// $$$ rob 19/07/2012 not sure y but this fires a strict standard warning deep in JForm, suppress error for now
		@$subForm->setFields($children);

		$str = array();
		$modalId = 'attrib-' . $this->id . '_modal';

		// As JForm will render child fieldsets we have to hide it via CSS
		$fieldSetId = str_replace('jform_params_', '', $modalId);
		$css = '#' . $fieldSetId . ' { display: none; }';
		$document->addStyleDeclaration($css);

		$path = 'templates/' . $app->getTemplate() . '/images/menu/';

		$str[] = '<div id="' . $modalId . '" style="display:none">';
		$str[] = '<table class="adminlist ' . $this->element['class'] . ' table table-striped">';
		$str[] = '<thead><tr class="row0">';
		$names = array();
		$attributes = $this->element->attributes();

		foreach ($subForm->getFieldset($attributes->name . '_modal') as $field)
		{
			$names[] = (string) $field->element->attributes()->name;
			$str[] = '<th>' . strip_tags($field->getLabel($field->name));
			$str[] = '<br /><small style="font-weight:normal">' . Text::_($field->description) . '</small>';
			$str[] = '</th>';
		}

		$str[] = '<th><a href="#" class="add btn button btn-success"><i class="icon-plus"></i> </a></th>';
		$str[] = '</tr></thead>';
		$str[] = '<tbody><tr>';

		foreach ($subForm->getFieldset($attributes->name . '_modal') as $field)
		{
			$str[] = '<td>' . $field->getInput() . '</td>';
		}

		$str[] = '<td>';
		$str[] = '<div class="btn-group"><a class="add btn button btn-success"><i class="icon-plus"></i> </a>';
		$str[] = '<a class="remove btn button btn-danger"><i class="icon-minus"></i> </a></div>';
		$str[] = '</td>';
		$str[] = '</tr></tbody>';
		$str[] = '</table>';
		$str[] = '</div>';
		$form = implode("\n", $str);
		static $modalRepeat;

		if (!isset($modalRepeat))
		{
			$modalRepeat = array();
		}

		if (!array_key_exists($modalId, $modalRepeat))
		{
			$modalRepeat[$modalId] = array();
		}

		if (!array_key_exists($this->form->repeatCounter, $modalRepeat[$modalId]))
		{
			// If loaded as js template then we don't want to repeat this again. (fabrik)
			$names = json_encode($names);
			$pane = str_replace('jform_params_', '', $modalId) . '-options';

			$modalRepeat[$modalId][$this->form->repeatCounter] = true;
			$opts = new stdClass;
			$opts = json_encode($opts);
			$script = str_replace('-', '', $modalId) . " = new FabrikModalRepeat('$modalId', $names, '$this->id', $opts);";
			$option = $input->get('option');

			if ($option === 'com_fabrik')
			{
				Html::script('administrator/components/com_fabrik/models/fields/fabrikmodalrepeat.js', $script);
			}
			else
			{
				// Wont work when rendering in admin module page
				// @TODO test this now that the list and form pages are loading plugins via ajax (18/08/2012)
				Html::script('administrator/components/com_fabrik/models/fields/fabrikmodalrepeat.js', $script);
			}
		}

		if (is_array($this->value))
		{
			$this->value = array_shift($this->value);
		}

		$value = htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8');

		$icon = $this->element['icon'] ? '<i class="icon-' . $this->element['icon'] . '"></i> ' : '';
		$icon .= Text::_('JLIB_FORM_BUTTON_SELECT');
		$str[] = '<button class="btn" id="' . $modalId . '_button" data-modal="' . $modalId . '">' . $icon . '</button>';
		$str[] = '<input type="hidden" name="' . $this->name . '" id="' . $this->id . '" value="' . $value . '" />';

		Html::framework();
		Html::iniRequireJS();

		return implode("\n", $str);
	}
}
