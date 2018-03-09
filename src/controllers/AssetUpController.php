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
