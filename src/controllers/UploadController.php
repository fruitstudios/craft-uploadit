<?php
namespace fruitstudios\uploadit\controllers;

use fruitstudios\uploadit\Uploadit;
use fruitstudios\uploadit\helpers\UploaditHelper;

use Craft;
use craft\web\Controller;
use craft\controllers\AssetsController;

class UploadController extends Controller
{
    // Protected Properties
    // =========================================================================

    protected $allowAnonymous = ['index', 'can-upload'];

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
            return $this->asErrorJson(Craft::t('uploadit', 'Could not get uploaded asset.'));
        }

        // Settings
        $request = Craft::$app->getRequest();
        $name = $request->getParam('name', false);
        $view = $request->getParam('view', 'image');
        $transform = $request->getParam('transform', '');
        $enableReorder = $request->getParam('enableReorder', false);
        $enableRemove = $request->getParam('enableRemove', false);

        // Preview
        $html = UploaditHelper::renderTemplate('uploadit/_macros/_preview', [
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

    public function actionCanUpload()
    {
        $this->requireAcceptsJson();

        $currentUser = Craft::$app->getUser()->getIdentity();
        if(!$currentUser)
        {
            return $this->asErrorJson(Craft::t('uploadit', 'Only logged in users can upload assets.'));
        }

        // TODO: this probably needs to happen else where, based on the specific volume permissions
        //
        // if (!Craft::$app->getUser()->checkPermission('deleteUsers'))
        // {
        //     return $this->asErrorJson(Craft::t('uploadit', 'You don\'t have permission to upload assets.'));
        // }

        return $this->asJson([
            'success' => true,
        ]);
    }

}
