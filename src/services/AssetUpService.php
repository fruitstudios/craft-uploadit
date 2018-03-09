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



// public function getSourceIdByHandle($handle)
//     {
//         $query = craft()->db->createCommand()
//             ->select('id')
//             ->limit(1)
//             ->from('assetsources')
//             ->where([
//                 'handle' => $handle,
//             ])
//             ->queryRow();

//         return $query['id'] ?? false;
//     }
