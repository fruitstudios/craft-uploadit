<?php

namespace fruitstudios\assetup\helpers;

use Craft;
use craft\web\View;
use craft\helpers\Template as TemplateHelper;

class AssetUpHelper
{

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
        // return TemplateHelper::raw($html);
    }


    // public static function getFieldMap()
    // {
    //     $fields = craft()->db->createCommand()
    //     ->select('f.id, f.handle, f.context')
    //     ->from('fields f')
    //     ->order('f.id')
    //     ->queryAll();

    //     $matrixFieldTypes = craft()->db->createCommand()
    //     ->select('mbt.id, mbt.handle, f.handle as fieldHandle')
    //     ->from('matrixblocktypes mbt')
    //     ->order('mbt.id')
    //     ->join('fields f', 'f.id = mbt.fieldId')
    //     ->queryAll();

    //     $matrixFieldsContext = [];
    //     foreach ($matrixFieldTypes as $matrixFieldType)
    //     {
    //         $matrixFieldsContext['matrixBlockType:'.$matrixFieldType['id']] = $matrixFieldType['fieldHandle'].':'.$matrixFieldType['handle'].':';
    //     }

    //     $fieldMap = [];
    //     foreach ($fields as $field)
    //     {
    //         if(array_key_exists($field['context'], $matrixFieldsContext))
    //         {
    //             $handle = $matrixFieldsContext[$field['context']].$field['handle'];
    //             $fieldMap[$handle] = $field['id'];
    //         }
    //         else
    //         {
    //             $fieldMap[$field['handle']] = $field['id'];
    //         }
    //     }

    //     return $fieldMap;
    // }

}
