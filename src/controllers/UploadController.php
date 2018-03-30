<?php
namespace fruitstudios\assetup\controllers;

use fruitstudios\assetup\AssetUp;
use fruitstudios\assetup\helpers\AssetUpHelper;

use Craft;
use craft\web\Controller;
use craft\controllers\AssetsController;

class UploadController extends Controller
{
    // Protected Properties
    // =========================================================================

    protected $allowAnonymous = ['index'];

    // Public Methods
    // =========================================================================

    public function actionIndex()
    {
        // AssetsController - actionSaveAsset()
        $response = Craft::$app->runAction('assets/save-asset');

        // Response Errors
        if(!$response->getIsSuccessful())
        {
            $error = $response->data['error'] ?? 'Upload Error';
            return $this->asErrorJson($error);
        }

        // Asset
        $asset = Craft::$app->getAssets()->getAssetById($response->data['assetId']);
        if(!$asset)
        {
            return $this->asErrorJson(Craft::t('assetup', 'Could not get uploaded asset.'));
        }

        // Settings
        $request = Craft::$app->getRequest();
        $name = $request->getParam('name', false);
        $view = $request->getParam('view', 'image');
        $transform = $request->getParam('transform', '');
        $enableReorder = $request->getParam('enableReorder', false);
        $enableRemove = $request->getParam('enableRemove', false);

        // Preview
        $html = AssetUpHelper::renderTemplate('assetup/_macros/_preview', [
            'asset' => $asset,
            'name' => $name,
            'view' => $view,
            'transform' => $transform,
            'enableReorder' => $enableReorder,
            'enableRemove' => $enableRemove,
        ]);

        // Result
        return $this->asJson([
            'success' => true,
            'asset' => $response->data,
            'html' => $html,
            'image' => $asset->kind == 'image' ? $asset->getUrl($transform) : false
        ]);
    }

}
