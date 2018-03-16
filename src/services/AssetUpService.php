<?php
namespace fruitstudios\assetup\services;

use fruitstudios\assetup\AssetUp;
use fruitstudios\assetup\helpers\AssetUpHelper;

use Craft;
use craft\base\Component;

class AssetUpService extends Component
{
    // Public Methods
    // =========================================================================

    public function getFieldByHandleOrId($handleOrId)
    {
    	if(!$handleOrId)
    	{
    		return false;
    	}

    	if(is_numeric($handleOrId))
    	{
			$field = AssetUpHelper::getFieldById($handleOrId);
    	}
    	else
    	{
    		$field = AssetUpHelper::getFieldByHandle($handleOrId);
    	}
    	return $field;
    }

    public function getVolumeByHandleOrId($handleOrId)
    {
    	if(!$handleOrId)
    	{
    		return false;
    	}

    	if(is_numeric($handleOrId))
    	{
			$volume = Craft::$app->getVolumes()->getVolumeById($handleOrId);
    	}
    	else
    	{
    		$volume = Craft::$app->getVolumes()->getVolumeByHandle($handleOrId);
    	}
    	return $volume;
    }

    public function getFirstViewableVolume()
    {
    	$viewableVolumes = Craft::$app->getVolumes()->getViewableVolumes();
    	return $viewableVolumes[0] ?? false;
    }

}
