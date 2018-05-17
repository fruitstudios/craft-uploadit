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

        // Is this a temporary upload?
        if(!$asset->getUrl())
        {
            // TODO: Could we grab the asset and try and run the transform as a public url
            //     : so it can be displayed on the front end.
        }

        // Settings
        $request = Craft::$app->getRequest();
        $name = $request->getParam('name', false);
        $view = $request->getParam('view', false);
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
            'image' => $asset->kind == 'image' ? ($asset->getUrl($transform) ?? $asset->getThumbUrl(800) ?? false) : false
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

    public function actionUserPhoto()
    {
        $this->requireAcceptsJson();
        $this->requireLogin();

        $transform = $request->getParam('transform', '');

        if (($file = UploadedFile::getInstanceByName('photo')) === null)
        {
            return $this->asErrorJson(Craft::t('uploadit', 'User photo is required.'));
        }
        try {
            if ($file->getHasError())
            {
                return $this->asErrorJson($file->error);
            }

            $users = Craft::$app->getUsers();
            $user = Craft::$app->getUser()->getIdentity();

            // Move to our own temp location
            $fileLocation = Assets::tempFilePath($file->getExtension());
            move_uploaded_file($file->tempName, $fileLocation);
            $users->saveUserPhoto($fileLocation, $user, $file->name);

            return $this->asJson([
                'success' => true,
                'photo' => $user->getPhoto()->getUrl($transform),
            ]);

        } catch (\Throwable $exception) {

            if (isset($fileLocation))
            {
                FileHelper::unlink($fileLocation);
            }
            Craft::error('There was an error uploading the photo: '.$exception->getMessage(), __METHOD__);
            return $this->asErrorJson(Craft::t('app', 'There was an error uploading your photo: {error}', [
                'error' => $exception->getMessage()
            ]));
        }
    }

    public function actionDeleteUserPhoto()
    {
        $this->requireAcceptsJson();
        $this->requireLogin();

        $user = Craft::$app->getUser()->getIdentity();
        if ($user->photoId) {
            Craft::$app->getElements()->deleteElementById($user->photoId, Asset::class);
        }
        $user->photoId = null;
        Craft::$app->getElements()->saveElement($user, false);
        return $this->asJson(['success' => true]);
    }

}
