<?php
namespace fruitstudios\uploadit\models;

use fruitstudios\uploadit\Uploadit;
use fruitstudios\uploadit\base\Uploader;

use Craft;
use craft\base\VolumeInterface;
use craft\models\VolumeFolder;

class VolumeUploader extends Uploader
{

    // Static
    // =========================================================================

    public static function type(): string
    {
        return self::TYPE_VOLUME;
    }

    // Public
    // =========================================================================

    public $volume;
    public $folder;

    // Public Methods
    // =========================================================================

    public function __construct(array $attributes = [])
    {
        parent::__construct();

        // Populate
        $this->setAttributes($attributes, false);

        // Defaults
        $this->enableReorder = false;
        $this->enableRemove = false;
    }

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['id'], 'required'];
        $rules[] = [['target'], 'required', 'message' => Craft::t('uploadit', 'A valid volume and optional folder / path must be set.')];
        return $rules;
    }

    public function setTarget(): bool
    {
        $folder = false;

        // Folder model supplied
        if($this->folder instanceof VolumeFolder)
        {
            return $this->_updateTarget($this->folder);
        }

        // Folder id supplied
        if(is_numeric($this->folder))
        {
            $folder = Craft::$app->getAssets()->getFolderById((int) $this->folder);

            // Folder is a duffer
            if(!$folder)
            {
                $this->addError('folder', Craft::t('uploadit', 'We cant locate any folder by the id supplied.'));
                return false;
            }

            // We have a folder
            return $this->_updateTarget($folder);
        }


        // Get supplied volume
        $volume = $this->volume instanceof VolumeInterface ? $this->volume : false;
        if(!$volume)
        {
            $volume = Uploadit::$plugin->service->getVolumeByHandleOrId($this->volume);
        }

        // Volume is a duffer
        if(!$volume)
        {
            $this->addError('volume', Craft::t('uploadit', 'We cant get a volume to work with.'));
            return false;

            // IDEA: Do we want to grab the first if nothing supplied
            // if(!$targetVolume)
            // {
            //     $targetVolume = Uploadit::$plugin->service->getFirstViewableVolume();
            // }
        }

        // Get volume top folder id
        $folderId = Craft::$app->getVolumes()->ensureTopFolder($volume);
        $folder = Craft::$app->getAssets()->getFolderById($folderId);
        if(!$folder)
        {
            $this->addError('folder', Craft::t('uploadit', 'We cant get or create the top folder for the volume you supplied.'));
            return false;
        }

        // Folder path supplied, lets ensure it exists on this volume
        if(is_string($this->folder))
        {
            // if the folder is a path does it exist
            $folderId = Craft::$app->getAssets()->ensureFolderByFullPathAndVolume($this->folder, $volume, false);
            if(!$folderId)
            {
                $this->addError('folder', Craft::t('uploadit', 'We cant find the folder path in the volume supplied.'));
                return false;
            }
            $folder = Craft::$app->getAssets()->getFolderById($folderId);
            if(!$folder)
            {
                $this->addError('folder', Craft::t('uploadit', 'We cant create the folder at the path you supplied.'));
                return false;
            }
        }

        return $this->_updateTarget($folder);
    }

    private function _updateTarget(VolumeFolder $folder)
    {
        // Set target and any uploader defaults
        $this->target = [
            'folderId' => $folder->id,
        ];

        return true;
    }
}
