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

    public function uploader($settings = [])
    {
        $view = Craft::$app->getView();

        // Set Site Template Mode
        $currentTemplateMode = $view->getTemplateMode();
        $view->setTemplateMode(View::TEMPLATE_MODE_CP);

        // Register Assets
        $view->registerAssetBundle(AssetUpAssetBundle::class);

        // Required Settings
        // TODO: This should probably use a model so you to handle all this logic
        //     : See models\Uploader (not ready yet)
        $settings['id'] = $settings['id'] ?? 'GENERATE-UID';
        // {% set id = (id is defined and id ? id : 'assetup'~random()) %}
        $settings['csrfTokenName'] = Craft::$app->getConfig()->getGeneral()->csrfTokenName;
        $settings['csrfTokenValue'] = Craft::$app->getRequest()->getCsrfToken();
        // $settings['assets'] = 'SHOULD BE REMOVED FOR JS';

        // Javascript
        $js = 'new AssetUp('.JsonHelper::encode($settings).');';
        $view->registerJs($js, View::POS_END);

        // Uploader HTML
        $html = AssetUp::$plugin->service->getAssetUploaderHtml($settings);

        // Reset Template Mode
        $view->setTemplateMode($currentTemplateMode);

        // Return Uploader
        return TemplateHelper::raw($html);
    }
}
