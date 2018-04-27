<?php
namespace fruitstudios\uploadit\assetbundles\uploadit;

use Craft;

use yii\web\AssetBundle;
use craft\web\assets\cp\CpAsset;


class UploaditAssetBundle extends AssetBundle
{
    // Public Methods
    // =========================================================================

    public function init()
    {
        $this->sourcePath = "@fruitstudios/uploadit/assetbundles/uploadit/build";

        $this->depends = [];

        $this->js = [
            'js/vendor/polyfill.js',
            'js/vendor/ready.js',
            'js/vendor/extend.js',
            'js/vendor/Sortable.js',
            'js/Uploadit.js',
        ];

        $this->css = [
            'css/styles.css',
        ];

        parent::init();
    }
}
