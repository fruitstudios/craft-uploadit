<?php

namespace fruitstudios\uploadit\helpers;

use Craft;
use craft\web\View;
use craft\db\Query;
use craft\fields\Assets as AssetsField;
use craft\helpers\Assets as AssetsHelper;

class UploaditHelper
{
    // Template
    // =========================================================================

    public static function renderTemplate(string $template, array $variables = [])
    {
        $view = Craft::$app->getView();
        $currentTemplateMode = $view->getTemplateMode();
        $view->setTemplateMode(View::TEMPLATE_MODE_CP);

        $html = $view->renderTemplate($template, $variables);

        $view->setTemplateMode($currentTemplateMode);
        return $html;
    }

    // Field Map
    // =========================================================================

    public static function getAllowedFileExtensionsByFieldKinds(array $kinds = null)
    {
        if(!$kinds)
        {
            return Craft::$app->getConfig()->getGeneral()->allowedFileExtensions;
        }


        $fileKinds = AssetsHelper::getFileKinds();

        $allowedFileExtensions = [];
        if($kinds)
        {
            foreach($kinds as $kind)
            {
                if(array_key_exists($kind, $fileKinds))
                {
                    $allowedFileExtensions = array_merge($allowedFileExtensions, $fileKinds[$kind]['extensions']);
                }
            }
        }

        $allowedFileExtensionsFromConfig = Craft::$app->getConfig()->getGeneral()->allowedFileExtensions;

        $vaidatedAllowedFileExtensions = [];
        foreach ($allowedFileExtensions as $allowedFileExtension)
        {
            if(in_array($allowedFileExtension, $allowedFileExtensionsFromConfig))
            {
                $vaidatedAllowedFileExtensions[] = $allowedFileExtension;
            }
        }
        return $vaidatedAllowedFileExtensions;
    }

    // Field Map
    // =========================================================================

    private static $_fieldsMapType = AssetsField::class;
    private static $_fieldsMap;

    public static function getFieldsMap()
    {
        self::_buildFieldsMap();
        return self::$_fieldsMap;
    }

    public static function getFieldByHandle(string $handle)
    {
        self::_buildFieldsMap();
        $fieldId = self::$_fieldsMap[$handle] ?? false;
        return $fieldId ? Craft::$app->getFields()->getFieldById($fieldId) : false;
    }

    public static function getFieldIdByHandle(string $handle)
    {
        self::_buildFieldsMap();
        return self::$_fieldsMap[$handle] ?? false;
    }

    private static function _buildFieldsMap()
    {
        if (self::$_fieldsMap === null) {

            $fields = (new Query())
                ->select(['id', 'handle', 'context'])
                ->where(['type' => self::$_fieldsMapType])
                ->from(['{{%fields}}'])
                ->all();

            $matrixFieldTypes =  (new Query())
                ->select(['matrixblocktypes.id', 'matrixblocktypes.handle', 'fields.handle as fieldHandle'])
                ->from(['{{%matrixblocktypes}} matrixblocktypes'])
                ->orderBy('matrixblocktypes.id')
                ->innerJoin('{{%fields}} fields', '[[fields.id]] = [[matrixblocktypes.fieldId]]')
                ->all();

            $matrixFieldsContext = [];
            foreach ($matrixFieldTypes as $matrixFieldType)
            {
                $matrixFieldsContext['matrixBlockType:'.$matrixFieldType['id']] = $matrixFieldType['fieldHandle'].':'.$matrixFieldType['handle'].':';
            }

            $fieldMap = [];
            foreach ($fields as $field)
            {
                if(array_key_exists($field['context'], $matrixFieldsContext))
                {
                    $handle = $matrixFieldsContext[$field['context']].$field['handle'];
                    $fieldMap[$handle] = $field['id'];
                }
                else
                {
                    $fieldMap[$field['handle']] = $field['id'];
                }
            }

            self::$_fieldsMap = $fieldMap;
        }
    }

}
