<?php
/**
 * Fabrik Timeline Viz HTML View
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.visualization.timeline
 * @copyright   Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

namespace Fabrik\Plugin\FabrikVisualization\Timeline\View\Timeline;

// No direct access
defined('_JEXEC') or die('Restricted access');

use Fabrik\Helpers\Html;
use Fabrik\Plugin\FabrikVisualization\Timeline\Model\TimelineModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseView;

/**
 * Fabrik Timeline Viz HTML View
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.visualization.timeline
 * @since       4.0
 */
class HtmlView extends BaseView
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string $tpl The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 *
	 * @since 4.0
	 */
	public function display($tpl = null)
	{
		$app   = Factory::getApplication();
		$input = $app->input;
		$srcs  = Html::framework();

		$usersConfig = ComponentHelper::getParams('com_fabrik');
		/** @var TimelineModel $model */
		$model       = $this->getModel();
		$id          = $input->getInt('id', $usersConfig->get('visualizationid', $input->getInt('visualizationid', 0)));
		$model->setId($id);
		$row = $model->getVisualization();

		if (!$model->canView())
		{
			echo Text::_('JERROR_ALERTNOAUTHOR');

			return false;
		}

		$js                   = $model->render();
		$this->containerId    = $this->get('ContainerId');
		$this->row            = $row;
		$this->showFilters    = $input->getInt('showfilters', 1) === 1 ? 1 : 0;
		$this->filters        = $model->getFilters();
		$this->advancedSearch = $model->getAdvancedSearchLink();
		$this->filterFormURL  = $model->getFilterFormURL();
		$params               = $model->getParams();
		$this->params         = $params;
		$this->width          = $params->get('timeline_width', '700');
		$this->height         = $params->get('timeline_height', '300');
		$tpl                  = $params->get('timeline_layout', 'bootstrap');
		$tmplpath             = '/plugins/fabrik_visualization/timeline/tmpl/timeline/' . $tpl;
		$this->_setPath('template', JPATH_ROOT . $tmplpath);

		HTMLHelper::stylesheet('media/com_fabrik/css/list.css');

		Html::stylesheetFromPath($tmplpath . '/template.css');
		$srcs['FbListFilter']   = 'media/com_fabrik/js/listfilter.js';
		$srcs['Timeline']       = 'plugins/fabrik_visualization/timeline/timeline.js';
		$srcs['AdvancedSearch'] = 'media/com_fabrik/js/advanced-search.js';

		$js .= $model->getFilterJs();
		Html::iniRequireJs($model->getShim());
		Html::script($srcs, $js);

		Text::script('COM_FABRIK_ADVANCED_SEARCH');
		Text::script('COM_FABRIK_LOADING');
		$opts             = array('alt' => 'calendar', 'class' => 'calendarbutton', 'id' => 'timelineDatePicker_cal_img');
		$img              = Html::image('calendar', 'form', @$this->tmpl, $opts);
		$this->datePicker = '<input type="text" name="timelineDatePicker" id="timelineDatePicker" value="" />' . $img;

		// Check and add a general fabrik custom css file overrides template css and generic table css
		Html::stylesheetFromPath('media/com_fabrik/css/custom.css');

		// Check and add a specific biz  template css file overrides template css generic table css and generic custom css
		Html::stylesheetFromPath('plugins/fabrik_visualization/timeline/views/timeline/tmpl/' . $tpl . '/custom.css');

		return parent::display();
	}
}