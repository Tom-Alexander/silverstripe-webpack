<?php

namespace Webpack;

use \ArrayList;
use \ViewableData;

class Chunk extends \ViewableData
{
    private $assets;
    public $Name;

    public function __construct($name = null)
    {
        $this->assets = new ArrayList();
        $this->Name = $name;
    }

    public function Name()
    {
        return $this->Name;
    }

    public function addAsset(Asset $asset)
    {
        $this->assets->push($asset);
    }

    public function Assets($extension = null)
    {
        if($extension == null) return $this->assets;
        return $this->assets->filter(array('Extension' => $extension));
    }

    public function forTemplate()
    {
        return $this->customise(array('Assets' => $this->assets, 'Name' => $this->Name))
            ->renderWith('WebpackAssets');
    }
}