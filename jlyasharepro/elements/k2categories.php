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
defined('_JEXEC') or die ;

jimport('joomla.form.formfield');

class JFormFieldk2Categories extends JFormField
{

    public $type = 'k2categories';

    protected function getInput()
    {
        if(!is_file(JPATH_ADMINISTRATOR.'/components/com_k2/k2.php'))
        {
            return '';
        }

        $value = empty($this->value) ? array() : $this->value;

        $db = JFactory::getDBO();
        $query = 'SELECT m.* FROM #__k2_categories m WHERE trash = 0 ORDER BY parent, ordering';
        $db->setQuery($query);
        $mitems = $db->loadObjectList();
        $children = array();
        if ($mitems)
        {
            foreach ($mitems as $v)
            {
                $v->title = $v->name;
                $v->parent_id = $v->parent;
                $pt = $v->parent;
                $list = @$children[$pt] ? $children[$pt] : array();
                array_push($list, $v);
                $children[$pt] = $list;
            }
        }
        $list = JHTML::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0);
        $mitems = array();

        foreach ($list as $item)
        {
            $item->treename = JString::str_ireplace('&#160;', '- ', $item->treename);
            $mitems[] = JHTML::_('select.option', $item->id, '   '.$item->treename);
        }

        $output = JHTML::_('select.genericlist', $mitems, $this->name, 'class="inputbox" multiple="multiple" size="10"', 'value', 'text', $value);
        return $output;
    }
}