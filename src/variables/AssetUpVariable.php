<?php
/**
 * Asset Up plugin for Craft CMS 3.x
 *
 * Front end asset upload tools for Craft CMS
 *
 * @link      http://fruitstudios.co.uk
 * @copyright Copyright (c) 2018 Fruit Studios
 */

namespace fruitstudios\assetup\variables;

use fruitstudios\assetup\AssetUp;
use fruitstudios\assetup\assetbundles\assetup\AssetUpAssetBundle;

use craft\helpers\Template as TemplateHelper;
use craft\helpers\Json as JsonHelper;

use Craft;

/**
 * @author    Fruit Studios
 * @package   AssetUp
 * @since     1.0.0
 */
class AssetUpVariable
{
    // Public Methods
    // =========================================================================

    public function assetUploader($settings = [])
    {
        // Register Assets
        Craft::$app->getView()->registerAssetBundle(AssetUpAssetBundle::class);

        // Get Uploader ID
        $settings['id'] = $settings['id'] ?? 'GENERATE-UID';

        // Javascript
        $jsVariables = JsonHelper::encode([
            'id' => $settings['id'],
        ]);
        Craft::$app->getView()->registerJs('new AssetUp.Uploader('.$jsVariables.');');

        // Uploader HTML
        $html = AssetUp::$plugin->service->getAssetUploaderHtml($settings);
        return TemplateHelper::raw($html);
    }
}
