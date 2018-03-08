<?php
/**
 * Asset Uploader plugin for Craft CMS 3.x
 *
 * Front end asset upload tools for Craft CMS
 *
 * @link      http://fruitstudios.co.uk
 * @copyright Copyright (c) 2018 Fruit Studios
 */

namespace fruitstudiosassetuploader\assetuploader\variables;

use fruitstudiosassetuploader\assetuploader\AssetUploader;

use Craft;

/**
 * @author    Fruit Studios
 * @package   AssetUploader
 * @since     1.0.0
 */
class AssetUploaderVariable
{
    // Public Methods
    // =========================================================================

    /**
     * @param null $optional
     * @return string
     */
    public function exampleVariable($optional = null)
    {
        $result = "And away we go to the Twig template...";
        if ($optional) {
            $result = "I'm feeling optional today...";
        }
        return $result;
    }
}
