<?php
/**
 * jlyasharepro
 *
 * @version 1.3.0
 * @author Arkadiy Sedelnikov
 * @copyright (C) 2014-2020 Arkadiy Sedelnikov, JoomLine (https://joomline.ru)
* @license GNU/GPL license: http://www.gnu.org/copyleft/gpl.html 
 **/
jimport('joomla.plugin.plugin');

class PlgJLYaShareProHelper
{
    var $params = null;

    protected static $instance = null;

    private
    $link,
    $title,
    $desc,
    $image,
    $quickServices,
    $theme,
    $lang,
    $type;

    public function ShowIn()
    {
        $scriptPage = <<<HTML
		    <div class="jlYaSharesContayner">
		        <div
				    class="yashare-auto-init"
				    data-yashareLink="{$this->link}"
				    data-yashareTitle="{$this->title}"
				    data-yashareDescription="{$this->desc}"
				    data-yashareImage="{$this->image}"
				    data-yashareQuickServices="{$this->quickServices}"
				    data-yashareTheme="{$this->theme}"
				    data-yashareType="{$this->type}"
				    data-yashareL10n="{$this->lang}"
				    ></div>
			</div>
HTML;
        return $scriptPage;
    }

    function __construct($params = null)
    {
        $this->params = $params;
        $this->lang = $this->getLang();
        $providers = $this->params->get('providers', array());
        $this->quickServices = !empty($providers) ? implode(',', $providers) : 'yaru,vkontakte,facebook,twitter,odnoklassniki,moimir,gplus';
        $this->theme = $this->params->get('theme', 'counter');
        $this->type = $this->params->get('type', 'small');
    }

    public function cleanText($text)
    {
        $desc = strip_tags($text);
        $words = explode(' ', $desc);
        $words = array_slice($words, 0, 20);
        $words = JString::trim(implode(' ', $words));
        $desc = JString::str_ireplace('"', '\'', $words);
        return $desc;
    }
	
	public function extractImageFromText( $introtext, $fulltext = '' )
    {
        jimport('joomla.filesystem.file');

        $regex = '#<\s*img [^\>]*src\s*=\s*(["\'])(.*?)\1#im';

        preg_match ($regex, $introtext, $matches);

        if(!count($matches))
        {
            preg_match ($regex, $fulltext, $matches);
        }

        $images = (count($matches)) ? $matches : array();

        $image = '';

        if (count($images))
        {
            $image = $images[2];
        }

        if (!preg_match("#^http|^https|^ftp#i", $image))
        {
            $image = JFile::exists( JPATH_SITE . DS . $image ) ? $image : '';
            $image = JURI::root().$image;
        }

        return $image;
    }
	
    /**
    az - азербайджанский;
    be - белорусский;
    en - английский;
    hy - армянский;
    ka - грузинский;
    kk - казахский;
    ro - румынский;
    ru - русский;
    tr - турецкий;
    tt - татарский;
    uk - украинский.
     */
    private function getLang()
    {
        $langs = array( 'az', 'be', 'en', 'hy', 'ka', 'kk', 'ro', 'ru', 'tr', 'tt', 'uk' );
        $lang = substr(JFactory::getLanguage()->getTag(), 0, 2);
        $lang = (in_array($lang, $langs)) ? $lang : 'ru';
        return $lang;
    }

    public static function getInstance($params = null, $folder = 'content', $plugin = 'jlyasharepro')
    {
        if (self::$instance === null) {
            if (!$params) {
                $params = self::getPluginParams($folder, $plugin);
            }
            self::$instance = new PlgJLYaShareProHelper($params);
        }

        return self::$instance;
    }

    private static function getPluginParams($folder = 'content', $name = 'jlyasharepro')
    {
        $plugin = JPluginHelper::getPlugin($folder, $name);

        if (!$plugin)
        {
            throw new RuntimeException(JText::_('JLLIKEPRO_PLUGIN_NOT_FOUND'));
        }

        $params = new JRegistry($plugin->params);

        return $params;
    }

    public function set($var, $val)
    {
        $this->$var = $val;
    }
}