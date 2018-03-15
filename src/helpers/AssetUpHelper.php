<?php

namespace fruitstudios\assetup\helpers;

use Craft;
use craft\web\View;
use craft\db\Query;

class AssetUpHelper
{
    // Private
    // =========================================================================

    private static $_fieldsMap;

    // Public Methods
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


    public static function getFieldByHandle(string $handle)
    {
        self::_buildFieldsMap();
        $fieldId = self::$_fieldsMap[$handle] ?? false;
        return $fieldId ? self::getFieldById($fieldId) : false;
    }

    public static function getFieldIdByHandle(string $handle)
    {
        self::_buildFieldsMap();
        return self::$_fieldsMap[$handle] ?? false;
    }

    public static function getFieldById(int $id)
    {
        return Craft::$app->getFields()->getFieldById($id);
    }

    public static function getFieldsMap()
    {
        self::_buildFieldsMap();
        return self::$_fieldsMap;
    }

    // Private Methods
    // =========================================================================

    private static function _buildFieldsMap()
    {
        if (self::$_fieldsMap === null) {

            $fields = (new Query())
                ->select(['id', 'handle', 'context'])
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
