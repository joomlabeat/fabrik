<?php
/**
 * Layout: Yes/No field list view
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.2
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Fabrik\Helpers\Html;
use Fabrik\Helpers\Worker;
use Fabrik\Helpers\Text;

$d = $displayData;
$data = $d->value;
$tmpl = $d->tmpl;
$format = $d->format;

$opts = array();
$properties = array();

if ($d->format == 'pdf') :
	$opts['forceImage'] = true;
	Html::addPath(COM_FABRIK_BASE . 'plugins/fabrik_element/yesno/images/', 'image', 'list', false);
endif;

if ($data == '1') :
	$icon = $format != 'pdf' ? 'checkmark.png' : '1.png';
	$properties['alt'] = Text::_('JYES');

	echo Html::image($icon, 'list', $tmpl, $properties, false, $opts);
else :
	$icon = $format != 'pdf' ? 'remove.png' : '0.png';
	$properties['alt'] = Text::_('JNO');

	echo Html::image($icon, 'list', $tmpl, $properties, false, $opts);
endif;
