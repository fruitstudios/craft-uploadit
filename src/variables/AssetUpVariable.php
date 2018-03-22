<?php
namespace fruitstudios\assetup\variables;

use fruitstudios\assetup\AssetUp;
use fruitstudios\assetup\assetbundles\assetup\AssetUpAssetBundle;
use fruitstudios\assetup\models\Uploader;

use Craft;
use craft\web\View;
use craft\helpers\Template as TemplateHelper;
use craft\helpers\Json as JsonHelper;

class AssetUpVariable
{
    // Public Methods
    // =========================================================================

    public function uploader($attributes = [])
    {
        // Uploader
        $uploader = new Uploader($attributes);
        // Craft::dd($uploader);
        // Return Uploader
        $html = $uploader->render();
        return TemplateHelper::raw($html);
    }
}
