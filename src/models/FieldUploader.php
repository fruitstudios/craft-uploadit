<?php
namespace fruitstudios\uploadit\models;

use fruitstudios\uploadit\Uploadit;
use fruitstudios\uploadit\base\Uploader;
use fruitstudios\uploadit\helpers\UploaditHelper;

use Craft;
use craft\base\ElementInterface;
use craft\base\FieldInterface;

class FieldUploader extends Uploader
{

    // Static
    // =========================================================================

    public static function type(): string
    {
        return self::TYPE_FIELD;
    }

    // Public
    // =========================================================================

    public $field;
    public $element;

    // Public Methods
    // =========================================================================

    public function __construct(array $attributes = [])
    {
        parent::__construct();

        // Populate
        $this->setAttributes($attributes, false);
    }

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['name'], 'required'];
        $rules[] = [['target'], 'required', 'message' => Craft::t('uploadit', 'A valid field and element must be set.')];
        return $rules;
    }

    public function getJavascriptProperties(): array
    {
        $variables = parent::getJavascriptProperties();
        $variables[] = 'name';
        return $variables;
    }

    public function setTarget(): bool
    {
        // Ok so an element is required then
        if(is_null($this->element))
        {
            $this->addError('field', Craft::t('uploadit', 'A valid element is required when a using a field as your asset target.'));
            return false;
        }

        // Element provided lets check it
        $element = $this->element instanceof ElementInterface ? $this->element : false;
        if(!$element)
        {
            $element = Craft::$app->getElements()->getElementById((int) $this->element);
        }

        // Element is a duffer
        if(!$element)
        {
            $this->addError('element', Craft::t('uploadit', 'Could not locate your element.'));
            return false;
        }

        // Got an element lets check the field
        $field = $this->field instanceof FieldInterface ? $this->field : false;
        if(!$field)
        {
            $field = Uploadit::$plugin->service->getAssetFieldByHandleOrId($this->field);
        }

        // Field is a duffer
        if(!$field)
        {
            $this->addError('field', Craft::t('uploadit', 'Could not locate your field.'));
            return false;
        }

        return $this->_updateTarget($field, $element);
    }

    private function _updateTarget(FieldInterface $field, ElementInterface $element)
    {
        // Set any uploader defaults based on the field
        $this->target = [
            'fieldId' => $field->id,
            'elementId' => $element->id
        ];
        $this->limit = $field->limit ? $field->limit : null;
        $this->allowedFileExtensions = UploaditHelper::getAllowedFileExtensionsByFieldKinds($field->allowedKinds);

        return true;
    }

}
