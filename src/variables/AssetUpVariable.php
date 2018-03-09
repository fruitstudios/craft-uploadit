<?php
namespace fruitstudios\assetup\variables;

use fruitstudios\assetup\AssetUp;
use fruitstudios\assetup\assetbundles\assetup\AssetUpAssetBundle;

use Craft;
use craft\web\View;
use craft\helpers\Template as TemplateHelper;
use craft\helpers\Json as JsonHelper;

class AssetUpVariable
{
    // Public Methods
    // =========================================================================

    public function uploader($settings = [])
    {
        // Set Site Template Mode
        $currentTemplateMode = Craft::$app->getView()->getTemplateMode();
        Craft::$app->getView()->setTemplateMode(View::TEMPLATE_MODE_CP);

        // Register Assets
        Craft::$app->getView()->registerAssetBundle(AssetUpAssetBundle::class);

        // Set Uploader ID
        $settings['id'] = $settings['id'] ?? 'GENERATE-UID';

        // Javascript
        $js = 'new AssetUp('.JsonHelper::encode($settings).');';
        Craft::$app->getView()->registerJs($js, View::POS_END);

        // Uploader HTML
        $html = AssetUp::$plugin->service->getAssetUploaderHtml($settings);

        // Reset Template Mode
        Craft::$app->getView()->setTemplateMode($currentTemplateMode);

        // Return Uploader
        return TemplateHelper::raw($html);
    }
}
