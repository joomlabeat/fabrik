<?php
/**
 * Plugin element to render folder list
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.folder
 * @copyright   Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

namespace Fabrik\Plugins\Element;

// No direct access
defined('_JEXEC') or die('Restricted access');

use \JHtml;
use Fabrik\Helpers\Text;
use \JFolder;
use \stdClass;

/**
 * Plugin element to render folder list
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.folder
 * @since       3.0
 */

class Folder extends Element
{
	/**
	 * Draws the html form element
	 *
	 * @param   array  $data           to pre-populate element with
	 * @param   int    $repeatCounter  repeat group counter
	 *
	 * @return  string	elements html
	 */

	public function render($data, $repeatCounter = 0)
	{
		$name = $this->getHTMLName($repeatCounter);
		$id = $this->getHTMLId($repeatCounter);
		$params = $this->getParams();
		$selected = $this->getValue($data, $repeatCounter);
		$errorCSS = $this->elementError != '' ? " elementErrorHighlight" : '';
		$aRoValues = array();
		$path = JPATH_ROOT . '/' . $params->get('fbfolder_path');
		$opts = array();

		if ($params->get('folder_allownone', true))
		{
			$opts[] = JHtml::_('select.option', '', Text::_('NONE'));
		}

		if ($params->get('folder_listfolders', true))
		{
			$folders = JFolder::folders($path);

			foreach ($folders as $folder)
			{
				$opts[] = JHtml::_('select.option', $folder, $folder);

				if (is_array($selected) and in_array($folder, $selected))
				{
					$aRoValues[] = $folder;
				}
			}
		}

		if ($params->get('folder_listfiles', false))
		{
			$files = JFolder::files($path);

			foreach ($files as $file)
			{
				$opts[] = JHtml::_('select.option', $file, $file);

				if (is_array($selected) and in_array($file, $selected))
				{
					$aRoValues[] = $file;
				}
			}
		}

		if (!$this->isEditable())
		{
			return implode(', ', $aRoValues);
		}

		$layout = $this->getLayout('form');
		$displayData = new stdClass;
		$displayData->options = $opts;
		$displayData->name = $name;
		$displayData->selected = $selected;
		$displayData->id = $id;
		$displayData->errorCSS = $errorCSS;

		return $layout->render($displayData);
	}

	/**
	 * Returns javascript which creates an instance of the class defined in formJavascriptClass()
	 *
	 * @param   int  $repeatCounter  Repeat group counter
	 *
	 * @return  array
	 */

	public function elementJavascript($repeatCounter)
	{
		$id = $this->getHTMLId($repeatCounter);
		$params = $this->getParams();
		$element = $this->getElement();
		$path = JPATH_ROOT . '/' . $params->get('fbfbfolder_path');
		$folders = JFolder::folders($path);
		$opts = $this->getElementJSOptions($repeatCounter);
		$opts->defaultVal = $element->default;
		$opts->data = $folders;

		return array('FbFolder', $id, $opts);
	}
}
