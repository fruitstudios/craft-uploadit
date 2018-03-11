<?php
namespace fruitstudios\assetup\models;

use Craft;
use craft\base\Model;

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

    public $label;

    public $view = 'file';

    public $enableDropToUpload = true;
    public $enableReorder = true;
    public $enableRemove = true;

    public $source;
    public $folder;
    public $limit;
    public $maxSize;
    public $acceptedFileTypes;

    public $customClass;



    // Public Methods
    // =========================================================================

    public function __construct($settings)
    {





    }

    getCanUse() {
        // Return true only if we have enough data to use
        return true;
    }

    getJavascript() {

    }

    render() {
        return 'the html here';
    }




    // public function getUrl(): string
    // {
    //     return $this->getUser() ? $this->userPath.'-'.$this->getUser()->id.'-'.$this->getUser()->username : '';
    // }

    // public function getText(): string
    // {
    //     if($this->customText != '')
    //     {
    //         return $this->customText;
    //     }
    //     return $this->getUser()->fullName ?? $this->getUrl() ?? '';
    // }

    // public function getUser()
    // {
    //     if(is_null($this->_user))
    //     {
    //         $this->_user = Craft::$app->getUsers()->getUserById((int) $this->value);
    //     }
    //     return $this->_user;
    // }

}
