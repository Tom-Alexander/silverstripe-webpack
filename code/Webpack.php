<?php

namespace Webpack;

class Webpack extends \ViewableData implements \Flushable
{

    protected $bundle;

    public function __construct()
    {
        $this->bundle = $this->bundleManifest($this->getManifestPath());
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
            \Config::inst()->get('SSViewer', 'theme'),
            \Config::inst()->get('Webpack', 'source'),
            \Config::inst()->get('Webpack', 'injectedName')
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
            \Config::inst()->get('SSViewer', 'theme'),
            \Config::inst()->get('Webpack', 'build'),
            \Config::inst()->get('Webpack', 'manifestName')
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
        return $this->customise(array(
                'Chunks' => $this->bundle->filter('Extension', 'css')
            ))->renderWith('WebpackStyleSheetBundle');
    }


    /**
     * Renders the javascript chunks from the bundle
     * as script tags
     * @return \HTMLText
     */
    public function Javascript()
    {
        return $this->customise(array(
                'Chunks' => $this->bundle->filter('Extension', 'js')
            ))->renderWith('WebpackJavascriptBundle');
    }

    /**
     * @return \ArrayList
     */
    public function Bundle()
    {
        return $this->bundle;
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
        return new Resource($asset);
    }

    /**
     * Bundles the asset chunks from the webpack manifest
     * into an ArrayList
     * @param $path
     * @return \ArrayList
     */
    protected function bundleManifest($path)
    {
        $source = file_get_contents($path);
        $manifest = json_decode($source, true);
        $assets = $manifest['assetsByChunkName'];
        $bundle = new \ArrayList();

        foreach ($assets as $name => $asset) {
            if (is_array($asset)) {
                foreach ($asset as $file) {
                    $bundle->add(new Resource($file));
                }
            } else {
                $bundle->add(new Resource($asset));
            }
        }

        return $bundle;

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