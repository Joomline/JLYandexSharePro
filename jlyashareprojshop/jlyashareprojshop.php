<?php
/**
 * jlyasharepro
 *
 * @version 1.3.0
 * @author Arkadiy Sedelnikov
 * @copyright (C) 2014-2020 Arkadiy Sedelnikov, JoomLine (https://joomline.ru)
* @license GNU/GPL license: http://www.gnu.org/copyleft/gpl.html 
 **/

// no direct access
defined('_JEXEC') or die;
error_reporting(E_ERROR);
jimport('joomla.plugin.plugin');
jimport ('joomla.html.parameter');
require_once JPATH_ROOT . '/plugins/content/jlyasharepro/helper.php';

class plgJshoppingProductsJLYashareProJShop extends JPlugin {


	
	 public function onBeforeDisplayProductView(&$content){
		JPlugin::loadLanguage( 'plg_content_jlyasharepro' );
		$plugin = JPluginHelper::getPlugin('content', 'jlyasharepro');
		$plgParams = new JRegistry;
		$plgParams->loadString($plugin->params);
		$view = JRequest::getCmd('controller');

		$JShopShow= $plgParams->get('jshopcontent');
		$html='';
		IF ($JShopShow == 1 )
        {
			IF ($view=='product')
            {
                $helper = PlgJLYaShareProHelper::getInstance($plgParams);

                JFactory::getDocument()->addScript('//yandex.st/share/share.js');

                $image = $content->product->product_name_image;
				
				if(empty($image))
				{
					$image = $content->product->image;
				}
				
                if (!empty($image))
                {
                    $jshopConfig = JSFactory::getConfig();
                    $image = $jshopConfig->image_product_live_path . '/' . $image;
                }

                $lang = JFactory::getLanguage()->getTag();
                $name = 'name_'.$lang;
                $desc = 'description_'.$lang;
                $desc = $content->product->$desc;
                $desc = $helper->cleanText($desc);

                $helper->set('link', JURI::current());
                $helper->set('title', $content->product->$name);
                $helper->set('desc', $desc);
                $helper->set('image', $image);

                $html = $helper->ShowIN();
		    }
		
	    }

		switch ($plgParams->get('jshopposition', 2)) {
            case 1 :
                $content->_tmp_product_html_start = $html;
                break;
            case 3 :
                $content->_tmp_product_html_end = $html;
                break;
            default:
                $content->_tmp_product_html_after_buttons = $html;
                break;    
        }
		//return $view;

	} //end function
	
	
	
}//end class
