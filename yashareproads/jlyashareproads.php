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
jimport('joomla.html.parameter');
require_once JPATH_ROOT . '/plugins/content/jlyasharepro/helper.php';

class plgAdsmanagercontentJLYashareProAds extends JPlugin
{
    public function ADSonContentAfterDisplay($content)
    {
        JPlugin::loadLanguage('plg_content_jlyasharepro');
        $plugin = & JPluginHelper::getPlugin('content', 'jlyasharepro');
        $plgParams = new JRegistry;
        $plgParams->loadString($plugin->params);
        $view = JRequest::getCmd('view');
        $ADSShow = $plgParams->get('adscontent', 0);

        if($ADSShow == 0 || $view != 'details')
        {
            return '';
        }

        $helper = PlgJLYaShareProHelper::getInstance($plgParams);

        JFactory::getDocument()->addScript('//yandex.st/share/share.js');

        $image = '';
        if(!empty($content->images[0]->thumbnail)){
            $image = JURI::base().'images/com_adsmanager/ads/'.$content->images[0]->thumbnail;
        }

        $desc = $helper->cleanText($content->ad_text);

        $helper->set('link', JURI::current());
        $helper->set('title', $content->ad_headline);
        $helper->set('desc', $desc);
        $helper->set('image', $image);

        $html = $helper->ShowIN();

        return $html;
    } //end function


}//end class
