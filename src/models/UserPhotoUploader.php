<?php
namespace fruitstudios\uploadit\models;

use fruitstudios\uploadit\Uploadit;
use fruitstudios\uploadit\assetbundles\uploadit\UploaditAssetBundle;
use fruitstudios\uploadit\base\Uploader;
use fruitstudios\uploadit\helpers\UploaditHelper;

use Craft;
use craft\base\ElementInterface;
use craft\base\FieldInterface;
use craft\web\View;


class UserPhotoUploader extends Uploader
{
    // Static
    // =========================================================================

    const DEFAULT_SIZE = 100;

    // Static
    // =========================================================================

    public static function type(): string
    {
        return self::TYPE_USER_PHOTO;
    }

    // Public
    // =========================================================================

    public $default;
    public $width;
    public $height;
    public $photo;
    public $round = false;
    public $imageClasses;


    // Public Methods
    // =========================================================================

    public function __construct(array $attributes = [])
    {
        parent::__construct();

        $this->enableRemove = true;
        $this->selectText = Craft::t('uploadit', 'Edit');

        // Populate
        $this->setAttributes($attributes, false);

        // Force
        $this->limit = 1;
        $this->allowedFileExtensions = UploaditHelper::getAllowedFileExtensionsByFieldKinds(['image']);
        $this->enableReorder = false;
        $this->setTarget();

    }

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['name'], 'required'];
        $rules[] = [['width', 'height'], 'number'];
        $rules[] = [['imageClasses'], 'string'];
        return $rules;
    }

    public function getJavascriptProperties(): array
    {
        $variables = parent::getJavascriptProperties();
        return $variables;
    }

    public function setTarget(): bool
    {
        $currentUser = Craft::$app->getUser()->getIdentity();
        if(!$currentUser)
        {
            return false;
        }

        $this->target = $currentUser->id;
        return true;
    }

    public function getDefaultWidth()
    {
        return self::DEFAULT_SIZE;
    }

    public function getDefaultHeight()
    {
        return self::DEFAULT_SIZE;
    }

    public function render()
    {
        $this->validate();

        $view = Craft::$app->getView();
        $view->registerAssetBundle(UploaditAssetBundle::class);
        $view->registerJs('new UploaditUserPhoto('.$this->getJavascriptVariables().');', View::POS_END);
        $view->registerCss($this->getCustomCss());

        return UploaditHelper::renderTemplate('uploadit/userPhoto', [
            'uploader' => $this
        ]);
    }
}
