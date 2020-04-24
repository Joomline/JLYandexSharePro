<?php Header("Content-Type: application/x-javascript; charset=UTF-8"); ?>
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

jimport('joomla.plugin.plugin');

require_once JPATH_ROOT.'/plugins/content/jlyasharepro/helper.php';

class plgContentJlyasharepro extends JPlugin
{
    public function onContentPrepare($context, &$article, &$params, $page = 0)
    {

        $allowContext = array(
            'com_content.article',
            'easyblog.blog',
            'com_virtuemart.productdetails'
        );

        $allow_in_category = $this->params->get('allow_in_category', 0);

        if($allow_in_category)
        {
            $allowContext[] = 'com_content.category';
        }


        if(!in_array($context, $allowContext)){
            return true;
        }

        if (strpos($article->text, '{jlyasharepro-off}') !== false) {
            $article->text = str_replace("{jlyasharepro-off}", "", $article->text);
            return true;
        }

        $autoAdd = $this->params->get('autoAdd',0);
        $sharePos = (int)$this->params->get('shares_position', 1);
        $option = JRequest::getCmd('option');
        $helper = PlgJLYaShareProHelper::getInstance($this->params);

        if (strpos($article->text, '{jlyasharepro}') === false && !$autoAdd)
        {
            return true;
        }

        if (!isset($article->catid))
        {
            $article->catid = '';
        }

        JFactory::getDocument()->addScript('//yastatic.net/share2/share.js');

        $url = 'http://' . $this->params->get('pathbase', '') . str_replace('www.', '', $_SERVER['HTTP_HOST']);

        $print = JRequest::getCmd('print');

        switch ($option) {
            case 'com_content':

                if(!$article->id){
                    //если категория, то завершаем
                    return true;
                }

                include_once JPATH_ROOT.'/components/com_content/helpers/route.php';

                if ($context == 'com_content.article')
                {
                    If (!$print)
                    {
                        $cat = $this->params->get('categories', array());
                        $exceptcat = is_array($cat) ? $cat : array($cat);
                        if (!in_array($article->catid, $exceptcat))
                        {
                            $view = JRequest::getCmd('view');
                            if ($view == 'article')
                            {
                                if ($autoAdd == 1 || strpos($article->text, '{jlyasharepro}') == true)
                                {
                                    $images = json_decode($article->images);

                                    if(!empty($images->image_intro))
                                    {
                                        $image = $url . '/' . stripslashes($images->image_intro);
                                    }
                                    else if(!empty($images->image_fulltext))
                                    {
                                        $image = $url . '/' . stripslashes($images->image_fulltext);
                                    }
                                    else
                                    {
                                        $image = '';
                                    }

                                    $desc = $helper->cleanText($article->text);

                                    $helper->set('link', $url . JRoute::_(ContentHelperRoute::getArticleRoute($article->slug, $article->catid)));
                                    $helper->set('title', $article->title);
                                    $helper->set('desc', $desc);
                                    $helper->set('image', $image);

                                    $shares = $helper->ShowIN();

                                    switch($sharePos){
                                        case 0:
                                            $article->text = $shares . str_replace("{jlyasharepro}", "", $article->text);
                                            break;
                                        default:
                                            $article->text = str_replace("{jlyasharepro}", "", $article->text) . $shares;
                                            break;
                                    }
                                }
                            }
                        }
                        else
                        {
                            $article->text = str_replace("{jlyasharepro}", "", $article->text);
                        }
                    }
                }
                else if ($context == 'com_content.category')
                {
                    If (!$print)
                    {
                        $cat = $this->params->get('categories', array());
                        $exceptcat = is_array($cat) ? $cat : array($cat);
                        if (!in_array($article->catid, $exceptcat))
                        {
                            if ($autoAdd == 1 || strpos($article->text, '{jlyasharepro}') == true)
                            {
                                $images = json_decode($article->images);

                                if(!empty($images->image_intro))
                                {
                                    $image = $url . '/' . stripslashes($images->image_intro);
                                }
                                else if(!empty($images->image_fulltext))
                                {
                                    $image = $url . '/' . stripslashes($images->image_fulltext);
                                }
                                else
                                {
                                    $image = '';
                                }

                                $desc = $helper->cleanText($article->text);

                                $helper->set('link', $url . JRoute::_(ContentHelperRoute::getArticleRoute($article->slug, $article->catid)));
                                $helper->set('title', $article->title);
                                $helper->set('desc', $desc);
                                $helper->set('image', $image);
                                $shares = $helper->ShowIN();

                                $article->text = str_replace("{jlyasharepro}", "", $article->text) . $shares;
                            }
                        }
                        else
                        {
                            $article->text = str_replace("{jlyasharepro}", "", $article->text);
                        }
                    }
                }
                break;
            case 'com_virtuemart':
                if ($context == 'com_virtuemart.productdetails') {
                    $VirtueShow = $this->params->get('virtcontent', 1);
                    if ($VirtueShow == 1)
                    {
                        $autoAddvm = $this->params->get('autoAddvm', 0);
                        if ($autoAddvm == 1 || strpos($article->text, '{jlyasharepro}') !== false)
                        {
                            $db = JFactory::getDbo();
                            $q = $db->getQuery(true);
                            $q->select('vm.file_url')
                                ->from('#__virtuemart_medias as vm')
                                ->innerJoin('#__virtuemart_product_medias as vpm ON vpm.virtuemart_media_id = vm.virtuemart_media_id')
                                ->where('vpm.virtuemart_product_id = '.(int)$article->virtuemart_product_id)
                                ->where('vm.file_is_downloadable = 0')
                                ->where('vm.file_is_forSale = 0')
                                ->where('vm.published = 1')
                            ;
                            $db->setQuery($q,0,1);
                            $image = $db->loadResult();

                            if(!empty($image))
                            {
                                $image = $url . '/' . $image;
                            }
                            else
                            {
                                $image = '';
                            }

                            $desc = $helper->cleanText($article->text);

                            $helper->set('link', JURI::current());
                            $helper->set('title', $article->product_name);
                            $helper->set('desc', $desc);
                            $helper->set('image', $image);

                            $shares = $helper->ShowIN();

                            switch($sharePos){
                                case 0:
                                    $article->text = $shares . str_replace("{jlyasharepro}", "", $article->text);
                                    break;
                                default:
                                    $article->text = str_replace("{jlyasharepro}", "", $article->text) . $shares;
                                    break;
                            }
                        }
                    }
                }
                break;
            case 'com_easyblog':
                if (($context == 'easyblog.blog') && ($this->params->get('easyblogshow', 0) == 1))
                {
                    if ($autoAdd == 1 || strpos($article->text, '{jlyasharepro}') == true)
                    {

                        $shares = $helper->ShowIN($article->id);
                        switch($sharePos){
                            case 0:
                                $article->text = $shares . str_replace("{jlyasharepro}", "", $article->text);
                                break;
                            default:
                                $article->text = str_replace("{jlyasharepro}", "", $article->text) . $shares;
                                break;
                        }
                    }
                }
                break;
            default:
                break;
        }
    }
}