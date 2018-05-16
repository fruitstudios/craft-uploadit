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
            // TODO: We need to grab the asset and try and run the transform as a public url
            //     : so it can be displayed on the front end.
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

    // public function actionUploadUserPhoto()
    // {
    //     $this->requireAcceptsJson();
    //     $this->requireLogin();
    //     $userId = Craft::$app->getRequest()->getRequiredBodyParam('userId');
    //     if ($userId !== Craft::$app->getUser()->getIdentity()->id) {
    //         $this->requirePermission('editUsers');
    //     }
    //     if (($file = UploadedFile::getInstanceByName('photo')) === null) {
    //         return null;
    //     }
    //     try {
    //         if ($file->getHasError()) {
    //             throw new UploadFailedException($file->error);
    //         }
    //         $users = Craft::$app->getUsers();
    //         $user = $users->getUserById($userId);
    //         // Move to our own temp location
    //         $fileLocation = Assets::tempFilePath($file->getExtension());
    //         move_uploaded_file($file->tempName, $fileLocation);
    //         $users->saveUserPhoto($fileLocation, $user, $file->name);
    //         $html = $this->getView()->renderTemplate('users/_photo', [
    //             'user' => $user
    //         ]);
    //         return $this->asJson([
    //             'html' => $html,
    //         ]);
    //     } catch (\Throwable $exception) {
    //         * @noinspection UnSafeIsSetOverArrayInspection - FP
    //         if (isset($fileLocation)) {
    //             FileHelper::unlink($fileLocation);
    //         }
    //         Craft::error('There was an error uploading the photo: '.$exception->getMessage(), __METHOD__);
    //         return $this->asErrorJson(Craft::t('app', 'There was an error uploading your photo: {error}', [
    //             'error' => $exception->getMessage()
    //         ]));
    //     }
    // }

    // public function actionDeleteUserPhoto(): Response
    // {
    //     $this->requireAcceptsJson();
    //     $this->requireLogin();
    //     $userId = Craft::$app->getRequest()->getRequiredBodyParam('userId');
    //     if ($userId != Craft::$app->getUser()->getIdentity()->id) {
    //         $this->requirePermission('editUsers');
    //     }
    //     $user = Craft::$app->getUsers()->getUserById($userId);
    //     if ($user->photoId) {
    //         Craft::$app->getElements()->deleteElementById($user->photoId, Asset::class);
    //     }
    //     $user->photoId = null;
    //     Craft::$app->getElements()->saveElement($user, false);
    //     $html = $this->getView()->renderTemplate('users/_photo', [
    //         'user' => $user
    //     ]);
    //     return $this->asJson(['html' => $html]);
    // }

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
