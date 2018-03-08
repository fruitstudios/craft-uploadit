<?php
/**
 * Asset Uploader plugin for Craft CMS 3.x
 *
 * Front end asset upload tools for Craft CMS
 *
 * @link      http://fruitstudios.co.uk
 * @copyright Copyright (c) 2018 Fruit Studios
 */

namespace fruitstudiosassetuploader\assetuploader\controllers;

use fruitstudiosassetuploader\assetuploader\AssetUploader;

use Craft;
use craft\web\Controller;

/**
 * @author    Fruit Studios
 * @package   AssetUploader
 * @since     1.0.0
 */
class DefaultController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = ['index', 'do-something'];

    // Public Methods
    // =========================================================================

    /**
     * @return mixed
     */
    public function actionIndex()
    {
        $result = 'Welcome to the DefaultController actionIndex() method';

        return $result;
    }

    /**
     * @return mixed
     */
    public function actionDoSomething()
    {
        $result = 'Welcome to the DefaultController actionDoSomething() method';

        return $result;
    }
}
