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

/**
 * Example K2 Plugin to render YouTube URLs entered in backend K2 forms to video players in the frontend.
 */

// Load the K2 Plugin API
JLoader::register('K2Plugin', JPATH_ADMINISTRATOR . '/components/com_k2/lib/k2plugin.php');

// Initiate class to hold plugin events
class plgK2Jlyasharepro extends K2Plugin
{

    // Some params
    var $pluginName = 'jlyasharepro';
    var $pluginNameHumanReadable = 'JoomLine Yandex Share K2 Plugin';
    private $enableShow;

    function __construct(&$subject, $params)
    {
        if(!$this->enableShow())
		{
            $this->enableShow = false;
			return;
		}
        parent::__construct($subject, $params);
        $plugin = JPluginHelper::getPlugin('content', 'jlyasharepro');
        $this->params = new JRegistry($plugin->params);
        $this->loadLanguage('plg_content_jlyasharepro');
        $this->enableShow = true;
    }

    function onK2BeforeDisplay(&$item, &$params, $limitstart){
        if($this->check('onK2BeforeDisplay')){
            return $this->loadLikes($item, $params, $limitstart);
        }
    }

    function onK2AfterDisplayTitle(&$item, &$params, $limitstart){
        if($this->check('onK2AfterDisplayTitle')){
            return $this->loadLikes($item, $params, $limitstart);
        }
    }

    function onK2BeforeDisplayContent(&$item, &$params, $limitstart){
        if($this->check('onK2BeforeDisplayContent')){
            return $this->loadLikes($item, $params, $limitstart);
        }
    }

    function onK2AfterDisplayContent(&$item, &$params, $limitstart){
        if($this->check('onK2AfterDisplayContent')){
            return $this->loadLikes($item, $params, $limitstart);
        }
    }

    function onK2AfterDisplay(&$item, &$params, $limitstart){
        if($this->check('onK2AfterDisplay')){
            return $this->loadLikes($item, $params, $limitstart);
        }
    }


    private function enableShow()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
        $view = $input->getString('view');
        $layout = $input->getString('layout', '');
        $task = $input->getString('task');

        if(!$app->isAdmin() && ($view == 'itemlist' || ($view == 'item' && ($layout == 'item' || $layout == ''))))
		{
            return true;
        }
        else
		{
            return false;
        }
    }

    private function check($trigger)
	{
        if($this->enableShow && $trigger == $this->params->get('k2trigger', ''))
		{
            return true;
        }
        return false;
    }

    private function loadLikes(&$article, &$params, $limitstart)
    {

        $k2categories = $this->params->get('k2categories', array());
        $k2categories = (is_array($k2categories)) ? $k2categories : array();
        $input = JFactory::getApplication()->input;
        $print = $input->getInt('print', 0);

        if(in_array($article->catid, $k2categories) || $print)
        {
            return true;
        }

        include_once JPATH_ROOT.'/plugins/content/jlyasharepro/helper.php';

        $url = $this->getUrl();
        $isCategory = ($input->getString('view', '') == 'itemlist') ? true : false;

        $plugin = JPluginHelper::getPlugin('content', 'jlyasharepro');
        $plgParams = new JRegistry($plugin->params);
        $helper = PlgJLYaShareProHelper::getInstance($plgParams);

        $conf = JFactory::getConfig();
        $enableSef = $conf->get('sef', 0);

        if($enableSef)
        {
            $link = $url.JRoute::_(K2HelperRoute::getItemRoute($article->id.':'.$article->alias, $article->catid.':'.urlencode($article->category->alias)));
        }
        else
        {
            $link = $url.'/'.K2HelperRoute::getItemRoute($article->id.':'.$article->alias, $article->catid.':'.urlencode($article->category->alias));
        }

        if($this->params->get('k2_images', 'fields') == 'fields' && !empty($article->imageLarge))
        {
            $image = JURI::root().$article->imageLarge;
        }
        else
        {
            $image = $helper->extractImageFromText($article->introtext, $article->fulltext);
        }

        $text = ($this->params->get('desc_source_k2', 'intro') == 'intro') ? $article->introtext : $article->fulltext;
        $text = $helper->cleanText($text);

        $helper->set('link', $link);
        $helper->set('title', $article->title);
        $helper->set('desc', $text);
        $helper->set('image', $image);
        $shares = $helper->ShowIN();

        if (!$isCategory)
        {
            JFactory::getDocument()->addScript('//yandex.st/share/share.js');
            return $shares;
        }
        else if($this->params->get('allow_in_category', 0))
        {
            JFactory::getDocument()->addScript('//yandex.st/share/share.js');
            return $shares;
        }
        else
        {
            return '';
        }
    }

    private function getUrl()
    {
        $url = 'http://' . $this->params->get('pathbase', '') . str_replace('www.', '', $_SERVER['HTTP_HOST']);

        if($this->params->get('punycode_convert',0))
        {
            $file = JPATH_ROOT.'/libraries/idna_convert/idna_convert.class.php';
            if(!JFile::exists($file))
            {
                return JText::_('PLG_JLLIKEPRO_PUNYCODDE_CONVERTOR_NOT_INSTALLED');
            }

            include_once $file;

            if($url)
            {
                if (class_exists('idna_convert'))
                {
                    $idn = new idna_convert;
                    $url = $idn->encode($url);
                }
            }
        }
        return $url;
    }
}
