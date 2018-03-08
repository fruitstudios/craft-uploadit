<?php
/**
 * Asset Up plugin for Craft CMS 3.x
 *
 * Front end asset upload tools for Craft CMS
 *
 * @link      http://fruitstudios.co.uk
 * @copyright Copyright (c) 2018 Fruit Studios
 */

namespace fruitstudios\assetup\variables;

use fruitstudios\assetup\AssetUp;

use Craft;

/**
 * @author    Fruit Studios
 * @package   AssetUp
 * @since     1.0.0
 */
class AssetUpVariable
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
