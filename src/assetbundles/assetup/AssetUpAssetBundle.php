<?php
namespace fruitstudios\assetup\assetbundles\assetup;

use Craft;

use yii\web\AssetBundle;

class AssetUpAssetBundle extends AssetBundle
{
    // Public Methods
    // =========================================================================

    public function init()
    {
        $this->sourcePath = "@fruitstudios/assetup/assetbundles/assetup/build";

        $this->depends = [];

        $this->js = [
            'js/AssetUp.js',
        ];

        $this->css = [
            'css/styles.css',
        ];

        parent::init();
    }
}
