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

    /**
     * Returns the content of the resource
     * @return string
     */
    public function Content()
    {
        if(Director::isDev() && $server = Config::inst()->get('Webpack', 'developmentServer')) {
            return file_get_contents($this->Link());
        }
        return file_get_contents(BASE_PATH . $this->Link());
    }

    /**
     * @return string
     */
    public function Link()
    {
        if(Director::isDev() && $server = Config::inst()->get('Webpack', 'developmentServer')) {
            return sprintf(
                "%s/%s",
                $server,
                $this->Path
            );
        } else {
            return sprintf(
                "/%s/%s/%s",
                $this->ThemeDir(),
                Config::inst()->get('Webpack', 'build'),
                $this->Path
            );
        }

    }

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