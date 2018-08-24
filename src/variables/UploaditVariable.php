<?php
namespace fruitstudios\uploadit\variables;

use fruitstudios\uploadit\models\Uploader; // DEPRICIATE

use fruitstudios\uploadit\Uploadit;
use fruitstudios\uploadit\assetbundles\uploadit\UploaditAssetBundle;
use fruitstudios\uploadit\base\UploaderInterface;
use fruitstudios\uploadit\models\VolumeUploader;
use fruitstudios\uploadit\models\FieldUploader;
use fruitstudios\uploadit\models\UserPhotoUploader;

use Craft;
use craft\web\View;
use craft\helpers\Template as TemplateHelper;
use craft\helpers\Json as JsonHelper;

class UploaditVariable
{
    // Public Methods
    // =========================================================================

    public function volumeUploader($attributes = [])
    {
        return $this->_renderUploader(VolumeUploader::class, $attributes);
    }

    public function fieldUploader($attributes = [])
    {
        return $this->_renderUploader(FieldUploader::class, $attributes);
    }

    public function userPhotoUploader($attributes = [])
    {
        return $this->_renderUploader(UserPhotoUploader::class, $attributes);
    }

    // Private Methods
    // =========================================================================

    public function _renderUploader($type, $attributes = [])
    {
        try{
            $uploader = new $type($attributes);
        } catch(\Throwable $exception) {
            $uploader = false;
        }

        if(!$uploader)
        {
            return TemplateHelper::raw('<p>Invalid Uploder!</p>');
        }

        return TemplateHelper::raw($uploader->render());
    }

}
