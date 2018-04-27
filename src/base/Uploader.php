<?php
namespace fruitstudios\uploadit\base;

use fruitstudios\uploadit\Uploadit;
use fruitstudios\uploadit\base\UploaderInterface;
use fruitstudios\uploadit\helpers\UploaditHelper;
use fruitstudios\uploadit\assetbundles\uploadit\UploaditAssetBundle;

use Craft;
use craft\web\View;
use craft\base\Model;
use craft\helpers\Json as JsonHelper;
use craft\helpers\Assets as AssetsHelper;

abstract class Uploader extends Model implements UploaderInterface
{

    // Constants
    // =========================================================================

    const TYPE_VOLUME = 'volume';
    const TYPE_FIELD = 'field';

    // Private
    // =========================================================================

    public static function type(): string
    {
        return null;
    }

    // Private
    // =========================================================================

    private $_defaultAllowedfileKinds;
    private $_defaultMaxUploadFileSize;
    private $_defaultJavascriptVariables;

    // Public
    // =========================================================================

    // ID
    public $id;

    // Name
    public $name;

    // Assets
    public $assets;

    // Target
    public $target;

    // Settings
    public $enableDropToUpload = true;
    public $enableReorder = true;
    public $enableRemove = true;

    // CSS, Layout & Preview
    public $layout = 'grid';
    public $preview = 'file';
    public $customClass;
    public $themeColour = '#000000';

    // Language
    public $selectText;
    public $dropText;

    // Asset
    public $transform = '';
    public $limit;
    public $maxSize;
    public $acceptedFileTypes;


    // Public Methods
    // =========================================================================

    public function __construct()
    {
        // Defualts
        $this->_defaultJavascriptVariables = [
            'csrfTokenName' => Craft::$app->getConfig()->getGeneral()->csrfTokenName,
            'csrfTokenValue' => Craft::$app->getRequest()->getCsrfToken(),
        ];
        $this->_defaultAllowedfileKinds = AssetsHelper::getFileKinds();
        $this->_defaultMaxUploadFileSize = Craft::$app->getConfig()->getGeneral()->maxUploadFileSize;

        $this->id = uniqid('uploadit');
        $this->selectText = Craft::t('uploadit', 'Select files');
        $this->dropText = Craft::t('uploadit', 'drop files here');
        $this->maxSize = $this->_defaultMaxUploadFileSize;
        $this->acceptedFileTypes = $this->_defaultAllowedfileKinds;
    }

    public function render()
    {
        $this->validate();

        $view = Craft::$app->getView();
        $view->registerAssetBundle(UploaditAssetBundle::class);
        $view->registerJs('new Uploadit('.$this->_getJavascriptVariables().');', View::POS_END);
        $view->registerCss($this->_getCustomCss());

        return UploaditHelper::renderTemplate('uploadit/uploader', [
            'uploader' => $this
        ]);
    }

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['id'], 'required'];
        $rules[] = [['maxSize'], 'integer', 'max' => $this->_defaultMaxUploadFileSize, 'message' => Craft::t('uploadit', 'Max file cant be greater than the global setting maxUploadFileSize')];
        return $rules;
    }

    public function beforeValidate()
    {
        $this->setTarget();
    }

    public function getJavascriptProperties(): array
    {
        return [
            'id',
            'type',
            'target',
            'layout',
            'preview',
            'limit',
            'maxSize',
            'transform',
            'acceptedFileTypes',
            'enableDropToUpload',
            'enableReorder',
            'enableRemove'
        ];
    }

    public function setTarget(): bool
    {
        return null;
    }



    // Private Methods
    // =========================================================================

    private function _getJavascriptVariables(bool $encode = true)
    {
        $settings = $this->_defaultJavascriptVariables;
        foreach ($this->getJavascriptProperties() as $property)
        {
            $settings[$property] = $this->$property ?? null;
        }

        return $encode ? JsonHelper::encode($settings) : $settings;
    }

    private function _getCustomCss()
    {
      $css = '
        .uploadit--isLoading:after { border-color: '.$this->themeColour.'; }
        .uploadit--label { background-color: '.$this->themeColour.'; }
        .uploadit--btn { color: '.$this->themeColour.'; }
      ';

      return $css;
    }
}
