<?php
namespace fruitstudios\assetup\models;

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

    private $_assets;

    // Public
    // =========================================================================

    public $id;
    public $name;
    public $assets;

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
        return $encode ? JsonHelper::encode($settings) : $settings;
    }

    public function getHtml()
    {
        $this->validate();
        return AssetUpHelper::renderTemplate('assetup/uploader', [
            'uploader' => $this
        ]);
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
