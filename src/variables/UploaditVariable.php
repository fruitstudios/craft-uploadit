<?php
namespace fruitstudios\uploadit\variables;

use fruitstudios\uploadit\models\Uploader; // DEPRICIATE

use fruitstudios\uploadit\Uploadit;
use fruitstudios\uploadit\assetbundles\uploadit\UploaditAssetBundle;
use fruitstudios\uploadit\base\UploaderInterface;
use fruitstudios\uploadit\models\VolumeUploader;
use fruitstudios\uploadit\models\FieldUploader;

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

    // Private Methods
    // =========================================================================

    public function _renderUploader($type, $attributes = [])
    {
        $uploader = false;
        switch ($type)
        {
            case VolumeUploader::class:
                $uploader = new VolumeUploader($attributes);
                break;

            case FieldUploader::class:
                $uploader = new FieldUploader($attributes);
                break;

        }

        if(!$uploader)
        {
            return TemplateHelper::raw('<p>Invalid Uploder!</p>');
        }

        return TemplateHelper::raw($uploader->render());
    }




    // public function uploader($attributes = [])
    // {
    //     // Uploader
    //     $uploader = new Uploader($attributes);
    //     // Craft::dd($uploader);
    //     // Return Uploader
    //     $html = $uploader->render();
    //     return TemplateHelper::raw($html);
    // }
}
