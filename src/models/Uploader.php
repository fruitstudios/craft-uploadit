<?php
namespace fruitstudios\assetup\models;

use fruitstudios\assetup\AssetUp;
use fruitstudios\assetup\helpers\AssetUpHelper;
use fruitstudios\assetup\assetbundles\assetup\AssetUpAssetBundle;

use Craft;
use craft\web\View;
use craft\base\Model;
use craft\helpers\Json as JsonHelper;

class Uploader extends Model
{
    // Private
    // =========================================================================

    private $_targetField;
    private $_targetFolder;

    // Public
    // =========================================================================

    public $id;
    public $name;
    public $assets;

    // Field (id | handle)
    public $field;

    // Volume (id | handle)
    public $volume;

    // Folder (id | path)
    public $folder;

    // Uploader Layout (grid | compact-grid | list)
    public $layout = 'grid';

    // Preview (file | background | img)
    public $preview = 'file';

    public $enableDropToUpload = true;
    public $enableReorder = true;
    public $enableRemove = true;

    public $selectText;
    public $dropText;

    public $transform;


    public $limit;
    public $maxSize;
    public $acceptedFileTypes;

    public $class;

    // Public Methods
    // =========================================================================

    public function __construct(array $attributes = null)
    {
        $this->id = uniqid('assetup');
        $this->selectText = Craft::t('assetup', 'Select files');
        $this->dropText = Craft::t('assetup', 'drop files here');

        if($attributes)
        {
            $this->setAttributes($attributes, false);
        }
    }

    public function getJavascriptVariables(bool $encode = true)
    {
        // Default
        $settings = [
            'id' => $this->id,
            'name' => $this->name,
            'layout' => $this->layout,
            'preview' => $this->preview,
            'enableDropToUpload' => $this->enableDropToUpload,
            'enableReorder' => $this->enableReorder,
            'enableRemove' => $this->enableRemove,
            'csrfTokenName' => Craft::$app->getConfig()->getGeneral()->csrfTokenName,
            'csrfTokenValue' => Craft::$app->getRequest()->getCsrfToken(),
        ];


        // Source
        $settings['fieldId'] = false;
        $settings['elementId'] = false;
        $settings['folderId'] = false;

        $field = $this->getTargetField();
        if($field)
        {
            // Set any addional settings based on the filed here
            $settings['fieldId'] = $field->id;
        }
        else
        {
            $settings['folderId'] = $this->getTargetFolder();
        }

        return $encode ? JsonHelper::encode($settings) : $settings;
    }

    public function getHtml()
    {
        $this->validate();
        return AssetUpHelper::renderTemplate('assetup/uploader', [
            'uploader' => $this
        ]);
    }

    public function getTargetField()
    {
        if(is_null($this->_targetField))
        {
            $this->_targetField = AssetUp::$plugin->service->getFieldByHandleOrId($this->field);
        }
        return $this->_targetField;
    }

    // getTargetFolder()
    // This can be made up of a volume + an optinal path/to/folder
    // If we dont have a source lets just grab the first asset source to upload to
    public function getTargetFolder()
    {
        if(is_null($this->_targetFolder))
        {
            $targetFolderId = false;

            // Folder ID
            if(is_numeric($this->folder))
            {
                $targetFolderId = Craft::$app->getAssets()->getFolderById($this->folder)->id ?? false;
            }

            // Volume
            if(!$targetFolderId)
            {
                // Get volume first
                if($this->volume)
                {
                    $targetVolume = AssetUp::$plugin->service->getVolumeByHandleOrId($this->volume);
                    if(!$targetVolume)
                    {
                        $targetVolume = AssetUp::$plugin->service->getFirstViewableVolume();
                        if(!$targetVolume)
                        {
                            // Handle Error: We cant get a volume to work with
                            //             : Should getFirstViewableVolume() throw an exception or here
                            $targetVolume = false;
                        }
                    }
                }

                // We have volume, lets try and get the folder
                if($targetVolume)
                {
                    // If there is a folder path lets check if the path exists
                    if(is_string($this->folder))
                    {

                        $targetFolderId = Craft::$app->getAssets()->ensureFolderByFullPathAndVolume($this->folder, $targetVolume, false);
                        if(!$targetFolderId)
                        {
                            // Handle Error: Specified path doesnt exist in the selected volume
                            //             : Should ensureFolderByFullPathAndVolume() throw an exception or here
                        }
                    }
                    else
                    {
                        // Get volume top folder id
                        $targetFolderId = Craft::$app->getVolumes()->ensureTopFolder($targetVolume);
                    }
                }
            }

            $this->_targetFolder = $targetFolderId;
        }
        return $this->_targetFolder;
    }

    public function render()
    {
        $view = Craft::$app->getView();
        $view->registerAssetBundle(AssetUpAssetBundle::class);
        $view->registerJs('new AssetUp('.$this->getJavascriptVariables().');', View::POS_END);
        return $this->getHtml();
    }

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['id'], 'required'];

        return $rules;
    }
}
