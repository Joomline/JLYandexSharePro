<?php
/**
 * jlyasharepro
 *
 * @version 1.3.0
 * @author Arkadiy Sedelnikov
 * @copyright (C) 2014-2020 Arkadiy Sedelnikov, JoomLine (https://joomline.ru)
* @license GNU/GPL license: http://www.gnu.org/copyleft/gpl.html 
 **/
defined('_JEXEC') or die ;

require_once JPATH_ROOT.'/plugins/content/jlyasharepro/helper.php';

class ElementJlyashareproZooEl extends Element implements iSubmittable {

	public function hasValue($params = array())
    {
		return (bool) $this->get('value', $this->config->get('default', 1));
	}

	public function render($params = array())
    {
		if (!$this->get('value', $this->config->get('default', 1)))
        {
            return '';
        }

        $plugin = JPluginHelper::getPlugin('content', 'jlyasharepro');
        $plgParams = new JRegistry($plugin->params);
        $helper = PlgJLYaShareProHelper::getInstance($plgParams);

        $intro = $text = $image = '';

        $field = $this->config->get('intro_field', '');
        if($field != '')
        {
            $element = $this->_item->getElement($field);
            $intro = $element->get('value');
            if(empty($intro))
            {
                $intro = $element->data();
                $intro = $intro[0]["value"];
            }
        }

        $field = $this->config->get('text_field', '');
        if($field != '')
        {
            $element = $this->_item->getElement($field);
            $text = $element->get('value');
            if(empty($text))
            {
                $text = $element->data();
                $text = $text[0]["value"];
            }
        }

        if($this->config->get('img_source', 'field') == 'field')
        {
            $field = $this->config->get('img_field', '');
            if(!empty($field)){
                $image = $this->_item->getElement($field)->get('file');
                $image = (!empty($image)) ? JURI::root() . $image : '';
            }
        }

        $desc = (!empty($intro)) ? $intro : $text;
        $desc = $helper->cleanText($desc);

        $item_route = JRoute::_($this->app->route->item($this->_item, false), true, -1);

        JFactory::getDocument()->addScript('//yandex.st/share/share.js');

        $helper->set('link', $item_route);
        $helper->set('title', $this->_item->name);
        $helper->set('desc', $desc);
        $helper->set('image', $image);
        $shares = $helper->ShowIN();
		return $shares;
	}

	public function edit()
    {
		return $this->app->html->_('select.booleanlist', $this->getControlName('value'), '', $this->get('value', $this->config->get('default', 1)));
	}

	public function renderSubmission($params = array())
    {
        return $this->edit();
	}

	public function validateSubmission($value, $params)
    {
		return array('value' => (bool) $value->get('value'));
	}
}