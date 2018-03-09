<?php
namespace fruitstudios\assetup\controllers;

use fruitstudios\assetup\AssetUp;

use Craft;
use craft\web\Controller;

class AssetUpController extends Controller
{
    // Protected Properties
    // =========================================================================

    protected $allowAnonymous = ['index', 'upload-asset'];

    // Public Methods
    // =========================================================================

    public function actionUploadAsset()
    {
        $result = 'Welcome to the DefaultController actionDoSomething() method';

        return $result;
    }




}


    // private $_defaultAssetSourceId = 7;

    // public function actionSaveAsset()
    // {
    //     $this->requireAjaxRequest();

    //     // Get Asset
    //     $uploadedFile = $this->_getUploadedFile('asset');

    //     // Determine Source
    //     $assetSourceId = $this->_defaultAssetSourceId;
    //     $source = craft()->request->getPost('source', false);
    //     if($source)
    //     {
    //         $sourceId = craft()->findarace_asset->getSourceIdByHandle($source);
    //         if(!$sourceId)
    //         {
    //             $this->returnJson([
    //                 'success' => false,
    //                 'message' => 'Invalid Source'
    //             ]);
    //         }
    //         $assetSourceId = $sourceId;
    //     }

    //     // Determine Root Folder ID
    //     $sourceFolder = craft()->assets->findFolder([ 'sourceId' => $assetSourceId ]);
    //     if(!$sourceFolder) {
    //         $this->returnJson([
    //             'success' => false,
    //             'message' => 'Invalid Source Folder'
    //         ]);
    //     }
    //     $targetFolderId = $sourceFolder->id;


    //     // Got a Field ID
    //     $fieldId = craft()->request->getPost('fieldId', false);
    //     if($fieldId) {

    //         // Get Target Field
    //         $field = craft()->fields->getFieldById($fieldId);
    //         $field = $field ? craft()->fields->populateFieldType($field) : false;
    //         if(!($field instanceof AssetsFieldType))
    //         {
    //             $this->returnJson([
    //                 'success' => false,
    //                 'message' => 'Incompatible Field'
    //             ]);
    //         }

    //         // Get Related Element (Entry or Matrix Block)
    //         $elementId = craft()->request->getPost('elementId', false);
    //         $element = craft()->elements->getElementById($elementId);
    //         if(!$element)
    //         {
    //             $this->returnJson([
    //                 'success' => false,
    //                 'message' => 'Permission Error (Invalid Related Element)'
    //             ]);
    //         }
    //         $field->element = $element;

    //         $targetFolderId = $field->resolveSourcePath();
    //     }
    //     else
    //     {
    //         // Determine Location from Folder or Field/Element
    //         $folder = craft()->request->getPost('folder', false);
    //         if($folder)
    //         {
    //             $targetFolderId = craft()->findarace_asset->getUserFolderId($assetSourceId, $folder, true) ?? false;
    //             if(!$targetFolderId)
    //             {
    //                 $this->returnJson([
    //                     'success' => false,
    //                     'message' => 'Could Not Locate Folder'
    //                 ]);
    //             }
    //         }
    //     }

    //     try
    //     {
    //         $this->_checkUploadPermissions($targetFolderId);
    //     }
    //     catch (Exception $e)
    //     {
    //         $this->returnJson([
    //             'success' => false,
    //             'error' => $e->getMessage()
    //         ]);
    //     }

    //     // Save Asset
    //     try {

    //         // Move To Temp (Retain Data)
    //         $assetName = AssetsHelper::cleanAssetName($uploadedFile->getName());
    //         $assetPath = AssetsHelper::getTempFilePath(IOHelper::getExtension($assetName));
    //         move_uploaded_file($uploadedFile->getTempName(), $assetPath);

    //         // Insert
    //         $response = craft()->assets->insertFileByLocalPath(
    //             $assetPath,
    //             $assetName,
    //             $targetFolderId,
    //             AssetConflictResolution::KeepBoth
    //         );

    //         // Prevent sensitive information leak. Just in case.
    //         IOHelper::deleteFile($assetPath, true);
    //         $response->deleteDataItem('filePath');

    //     }
    //     catch (Exception $e)
    //     {
    //         $this->returnJson([
    //             'success' => false,
    //             'error' => $e->getMessage()
    //         ]);
    //     }

    //     if($response->isSuccess())
    //     {
    //         $fileId = $response->getDataItem('fileId');
    //         $asset = craft()->assets->getFileById($fileId);

    //         if($asset)
    //         {
    //             $url = $asset->kind == 'image' ? $asset->getUrl(craft()->request->getPost('transform', 'post')) : $asset->getUrl();
    //             $this->returnJson([
    //                 'success' => true,
    //                 'message' => 'Uploaded',
    //                 'asset' => [
    //                     'id' => $asset->id,
    //                     'url' => $url,
    //                     'title' => $asset->title,
    //                     'filename' => $asset->filename
    //                 ]
    //             ]);
    //         }
    //         else
    //         {
    //             $this->returnJson([
    //                 'success' => false,
    //                 'error' => 'Uploaded but unable to get asset'
    //             ]);
    //         }
    //     }
    //     else
    //     {
    //         $this->returnJson([
    //             'success' => false,
    //             'message' => 'Proccessing Failed',
    //             'errors' => $response->getErrors()
    //         ]);
    //     }
    // }

    // // Duplicated copied from: app/controllers/AssetsController.php
    // private function _checkUploadPermissions($folderId)
    // {
    //     $folder = craft()->assets->getFolderById($folderId);

    //     // if folder exists and the source ID is null, it's a temp source and we always allow uploads there.
    //     if (!(is_object($folder) && is_null($folder->sourceId)))
    //     {
    //         craft()->assets->checkPermissionByFolderIds($folderId, 'uploadToAssetSource');
    //     }
    // }

    // private function _getUploadedFile($name)
    // {
    //     // Check File
    //     $fileInfo = $_FILES[$name];
    //     if(!$fileInfo || $fileInfo['size'] > craft()->config->get('maxFrontEndUploadSize', 'findarace') )
    //     {
    //         $this->throwPermissionError($fileInfo ? 'This file is too big' : 'Upload failed');
    //     }

    //     // Check Upload
    //     $uploadedFile = UploadedFile::getInstanceByName($name);
    //     if(!$uploadedFile)
    //     {
    //         $this->throwPermissionError('Upload Failed');
    //     }

    //     return $uploadedFile;
    // }
