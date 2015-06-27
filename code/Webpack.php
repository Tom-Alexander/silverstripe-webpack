<?php

namespace Webpack;

use \Config;
use \Flushable;
use \ViewableData;

class Webpack extends ViewableData implements Flushable
{

    protected $chunks;

    public function __construct()
    {
        $this->chunks = $this->getChunksFromManifest($this->getManifestPath());
    }

    /**
     * Gets the path to the file which will require the
     * asset dependencies
     * @return string
     */
    protected static function getInjectionPath()
    {
        return sprintf(
            "%s/themes/%s/%s/%s",
            BASE_PATH,
            Config::inst()->get('SSViewer', 'theme'),
            Config::inst()->get('Webpack', 'source'),
            Config::inst()->get('Webpack', 'injectedName')
        );
    }

    /**
     * Gets the path to the webpack manifest file, relative
     * to the template directory
     * @return string
     */
    protected static function getManifestPath()
    {
        return sprintf(
            "%s/themes/%s/%s/%s",
            BASE_PATH,
            Config::inst()->get('SSViewer', 'theme'),
            Config::inst()->get('Webpack', 'build'),
            Config::inst()->get('Webpack', 'manifestName')
        );
    }

    /**
     * Resets the assets file injected from the templates
     * @return int
     */
    public static function flush()
    {
        return file_put_contents(
            self::getInjectionPath(),
            sprintf(
                "%s \n%s \n",
                '/** silverstripe-webpack asset injection **/',
                '"use strict";'
            )
        );
    }

    /**
     * Renders the style sheet chunks from the bundle
     * as link tags
     * @return \HTMLText
     */
    public function StyleSheet()
    {
        return $this->customise(array('Assets' => $this->Assets('css')))
            ->renderWith('WebpackAssets');
    }


    /**
     * Renders the javascript chunks from the bundle
     * as script tags
     * @return \HTMLText
     */
    public function Javascript()
    {
        return $this->customise(array('Assets' => $this->Assets('js')))
            ->renderWith('WebpackAssets');
    }

    /**
     * Returns an ArrayList of chunks from your webpack
     * manifest filtered by name
     * @param null $name
     * @return \ArrayList
     */
    public function Chunks($name = null)
    {
        if($name == null) return $this->chunks;
        return $this->chunks->filter(array('Name' => $name));
    }

    /**
     * Returns an ArrayList of Assets that exist in any chunk,
     * filtered by file extension
     * @param null $extension
     * @return \ArrayList
     */
    public function Assets($extension = null)
    {
        $assets = new \ArrayList();
        foreach($this->chunks as $chunk) {
            $assets->merge($chunk->Assets($extension));
        }

        return $assets;
    }

    /**
     * Injects the requested module into the template dependencies
     * file and returns resource at the resolved build path
     * @param $request
     * @return Resource
     */
    public function Asset($request)
    {
        $modulePath = sprintf("%s/%s/%s", BASE_PATH, $this->ThemeDir(), $request);
        file_put_contents(self::getInjectionPath(), "require(\"$modulePath\"); \n", FILE_APPEND);
        $asset = $this->getAssetFromUserRequest(self::getManifestPath(), $modulePath);
        return new Asset($asset);
    }

    /**
     * Bundles the asset chunks from the webpack manifest
     * into an ArrayList
     * @param $path
     * @return \ArrayList
     */
    protected function getChunksFromManifest($path)
    {
        $source = file_get_contents($path);
        $manifest = json_decode($source, true);
        $rawChunks = $manifest['assetsByChunkName'];
        $chunks = new \ArrayList();

        foreach ($rawChunks as $name => $rawChunk) {
            $chunk = new Chunk($name);

            if (is_array($rawChunk)) {
                foreach ($rawChunk as $asset) {
                    $chunk->addAsset(new Asset($asset));
                }
            } else {
                $chunk->addAsset(new Asset($rawChunk));
            }

            $chunks->add($chunk);
        }

        return $chunks;

    }

    /**
     * Retrieves the module path that is resolved
     * from the user request in the webpack manifest
     * @param $manifest
     * @param $userRequest
     * @return mixed
     */
    protected function getAssetFromUserRequest($manifest, $userRequest)
    {
        $source = file_get_contents($manifest);
        $data = json_decode($source, true);
        $modules = $data['modules'];

        foreach ($modules as $module) {
            if (isset($module['reasons']) && is_array($module['reasons'])) {
                if (isset($module['assets']) && count($module['assets'])) {
                    foreach ($module['reasons'] as $reason) {
                        if ($reason['userRequest'] == $userRequest) {
                            return $module['assets'][0];
                        }
                    }
                }
            }
        }

        return '';

    }

}