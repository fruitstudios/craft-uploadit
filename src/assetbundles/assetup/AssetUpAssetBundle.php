<?php
namespace fruitstudios\assetup\assetbundles\assetup;

use Craft;

use yii\web\AssetBundle;
use craft\web\assets\cp\CpAsset;


class AssetUpAssetBundle extends AssetBundle
{
    // Public Methods
    // =========================================================================

    public function init()
    {
        $this->sourcePath = "@fruitstudios/assetup/assetbundles/assetup/build";

        $this->depends = [];

        $this->js = [
            'js/vendor/polyfill.js',
            'js/vendor/ready.js',
            'js/vendor/Sortable.js',
            'js/vendor/extend.js',
            // 'js/vendor/atomic.js',
            // 'js/vendor/fetch.js',
            'js/AssetUp.js',
        ];

        $this->css = [
            'css/styles.css',
        ];

        parent::init();
    }
}
