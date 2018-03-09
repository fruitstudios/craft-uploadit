<?php
namespace fruitstudios\assetup\services;

use fruitstudios\assetup\AssetUp;

use Craft;
use craft\base\Component;

class AssetUpService extends Component
{
    // Public Methods
    // =========================================================================

    public function getAssetUploaderHtml($settings = [])
    {
        return Craft::$app->getView()->renderTemplate(
            'assetup/uploader', $settings
        );
    }
}
