<?php

class ShopMenu
{
    const MENU_TYPE_HEADER = 'header_menu',
            MENU_TYPE_FOOTER = 'footer_menu';

    private $_menu = null;

    public function __construct($menu_srl)
    {
        if(!isset($menu_srl))
        {
            return null;
        }
        /**
         * @var menuAdminModel $menuModel
         */
        $menuModel = getAdminModel('menu');
        $shop_menu = $menuModel->getMenu($menu_srl);
        if(!file_exists($shop_menu->php_file))
        {
            $menuAdminController = getAdminController('menu');
            $menuAdminController->makeXmlFile($menu_srl);
        }

        $menu = NULL;
        @include($shop_menu->php_file); // Populates $menu with menu data
        $this->_menu = $menu;
        return $menu;
    }

    public function getHtml()
    {
        $menu_html = '<ul>';
        if($this->_menu)
        {
            foreach($this->_menu->list as $key1 => $val1)
            {
                // Open LI
                $menu_html .= '<li';
                if($val1['selected'])
                {
                    $menu_html .= ' class="active ';
                }
                $menu_html .= '>';

                // Link
                $menu_html .= '<a href="' . $val1['href']  .'"';
                if($val1['open_window'] == 'Y')
                {
                    $menu_html .= ' target="_blank"';
                }
                $menu_html .= '>';

                // Link text
                $menu_html .= $val1['link'];
                $menu_html .= '</a>';

                // Second level menu
                if($val1['list'])
                {
                    $menu_html .= '<ul>';
                    foreach($val1['list'] as $key2 => $val2)
                    {
                        // Open LI
                        $menu_html .= '<li';
                        if($val2['selected'])
                        {
                            $menu_html .= ' class="active ';
                        }
                        $menu_html .= '>';

                        // Link
                        $menu_html .= '<a href="' . $val2['href']  .'"';
                        if($val2['open_window'] == 'Y')
                        {
                            $menu_html .= ' target="_blank"';
                        }
                        $menu_html .= '>';

                        // Link text
                        $menu_html .= $val1['link'];
                        $menu_html .= '</a>';

                        $menu_html .= '</li>';
                    }

                    $menu_html .= '</ul>';
                }

                $menu_html .= '</li>';
            }
        }
        $menu_html .= '</ul>';
        return $menu_html;
    }
}