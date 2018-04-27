<?php
namespace fruitstudios\uploadit\services;

use fruitstudios\uploadit\Uploadit;
use fruitstudios\uploadit\helpers\UploaditHelper;

use Craft;
use craft\base\Component;

class UploaditService extends Component
{
    // Public Methods
    // =========================================================================

    public function getAssetFieldByHandleOrId($handleOrId)
    {
    	if(!$handleOrId)
    	{
    		return false;
    	}

    	if(is_numeric($handleOrId))
    	{
			$field = UploaditHelper::getFieldById($handleOrId);
    	}
    	else
    	{
    		$field = UploaditHelper::getFieldByHandle($handleOrId);
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
