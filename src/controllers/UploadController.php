<?php
namespace fruitstudios\uploadit\controllers;

use fruitstudios\uploadit\Uploadit;
use fruitstudios\uploadit\helpers\UploaditHelper;

use Craft;
use craft\web\Controller;
use craft\web\UploadedFile;
use craft\controllers\AssetsController;
use craft\helpers\Assets;
use craft\helpers\FileHelper;
use craft\elements\Asset;

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
        if($response->data['error'] ?? false)
        {
            return $this->asErrorJson($response->data['error']);
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
        $enableReorder = (bool)$request->getParam('enableReorder', false);
        $enableRemove = (bool)$request->getParam('enableRemove', false);
        $elementId = $request->getParam('elementId', false);
        $saveOnUpload = (bool)$request->getParam('saveOnUpload', false);

        // Save element on upload
        if($saveOnUpload && $elementId)
        {
            $element = Craft::$app->getElements()->getElementById($elementId);
            if($element)
            {
                $fieldName = str_replace(['fields[', '[]', ']'], '', $name);

                $assetIds = $request->getParam('assetIds', '');
                $assetIds = $assetIds && $assetIds != '' ? explode(',', $assetIds) : [];
                $assetIds[] = $asset->id;

                $element->setFieldValues([
                    $fieldName => $assetIds
                ]);

                $elementSaved = Craft::$app->getElements()->saveElement($element);
                if(!$elementSaved)
                {
                    return $this->asErrorJson(Craft::t('uploadit', 'Could not save uploaded asset.'));
                }
            }
        }

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
            'image' => $asset->kind == 'image' ? ($asset->getUrl($transform) ?? $asset->getThumbUrl(800) ?? false) : false,
            'saved' => $elementSaved ?? false
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

        // $volumeId = '44';
        // $permission = 'viewVolume:'.$volumeId;
        // $permission = 'saveAssetInVolume:'.$volumeId;
        // $permission = 'createFoldersInVolume:'.$volumeId;
        // $permission = 'deleteFilesAndFoldersInVolume:'.$volumeId;

        // if (!Craft::$app->getUser()->checkPermission($permission))
        // {
        //     return $this->asErrorJson(Craft::t('uploadit', 'You don\'t have permission to upload assets here.'));
        // }

        return $this->asJson([
            'success' => true,
        ]);
    }

    public function actionUserPhoto()
    {
        $this->requireAcceptsJson();
        $this->requireLogin();

        $request = Craft::$app->getRequest();
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
