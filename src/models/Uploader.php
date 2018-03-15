<?php
namespace fruitstudios\assetup\models;

use fruitstudios\assetup\helpers\AssetUpHelper;
use fruitstudios\assetup\assetbundles\assetup\AssetUpAssetBundle;

use Craft;
use craft\web\View;
use craft\base\Model;
use craft\fields\Assets as AssetsField;
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

    public $source;
    public $folder;
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
            $this->_targetField = false;
            if($this->field)
            {
                $field = is_numeric($this->field) ? AssetUpHelper::getFieldById($this->field) : AssetUpHelper::getFieldByHandle($this->field);
                if($field && get_class($field) == AssetsField::class)
                {
                    $this->_targetField = $field;
                }
            }
        }
        return $this->_targetField;
    }

    public function getTargetFolder()
    {
        if(is_null($this->_targetFolder))
        {
            $this->_targetFolder = false;

            // This can be made up of a source + an optinal path/to/folder
            // AssetUpHelper::getTagetFolderId($source, $path = null, $create = true);

            // If we dont have a source lets just grab the first asset source to upload to

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
