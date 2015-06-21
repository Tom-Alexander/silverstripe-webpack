<?php

namespace Webpack;

class WebpackTemplateProvider implements \TemplateGlobalProvider
{

    public static function get_template_global_variables()
    {
        Webpack::flush();
        return array('Webpack');
    }

    public static function Webpack()
    {
        return new Webpack();
    }

}