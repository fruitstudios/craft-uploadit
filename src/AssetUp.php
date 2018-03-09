<?php
/**
 * Asset Up plugin for Craft CMS 3.x
 *
 * Front end asset upload tools for Craft CMS
 *
 * @link      http://fruitstudios.co.uk
 * @copyright Copyright (c) 2018 Fruit Studios
 */

namespace fruitstudios\assetup;

use fruitstudios\assetup\services\AssetUpService;
use fruitstudios\assetup\variables\AssetUpVariable;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\web\twig\variables\CraftVariable;
use craft\events\RegisterUrlRulesEvent;

use yii\base\Event;

/**
 * Class AssetUp
 *
 * @author    Fruit Studios
 * @package   AssetUp
 * @since     1.0.0
 *
 * @property  AssetUpServiceService $assetUploaderService
 */
class AssetUp extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var AssetUp
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        $this->setComponents([
            'service' => AssetUpService::class,
        ]);

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['siteActionTrigger1'] = 'assetup/default';
            }
        );

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('assetUp', AssetUpVariable::class);
            }
        );

        Craft::info(
            Craft::t(
                'assetup',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

}
