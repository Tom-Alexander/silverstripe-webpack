<?php

namespace Webpack;

use \Config;
use \Director;
use \ArrayList;
use \ViewableData;

class Asset extends \ViewableData
{

    public $Path;
    public $Extension;

    public function __construct($path)
    {
        $this->Path = $path;
        $this->Extension = pathinfo($this->Path, PATHINFO_EXTENSION);
    }

    protected function getDevelopmentServer()
    {
        $server = Config::inst()->get('Webpack', 'development_server');
        return Director::isDev() && $server ? $server : false;
    }

    /**
     * Returns the content of the asset
     * @return string
     */
    public function Content()
    {
        if($server = $this->getDevelopmentServer()) {
            return file_get_contents($this->Link());
        }

        return file_get_contents(BASE_PATH . $this->Link());
    }

    /**
     * Returns the complied link to the asset
     * @return string
     */
    public function Link()
    {
        if($server = $this->getDevelopmentServer()) {
            return sprintf("%s/%s", $server, $this->Path);
        } else {
            $build = Config::inst()->get('Webpack', 'build');
            return sprintf("/%s/%s/%s", $this->ThemeDir(), $build, $this->Path);
        }
    }

    /**
     * Returns the markup representation of the asset
     * @return \HTMLText
     */
    public function Tag()
    {
        return $this->customise(array(
            'Assets' => ArrayList::create(array($this)),
            'Name' => 'Asset'
        ))->renderWith('WebpackAssets');
    }

    /**
     * Dependencies that should be required, but don't need
     * rendering should not render in the template
     * @return string
     */
    public function forTemplate()
    {
        return '';
    }
}