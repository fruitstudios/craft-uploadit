<?php
/**
 * Asset Uploader plugin for Craft CMS 3.x
 *
 * Front end asset upload tools for Craft CMS
 *
 * @link      http://fruitstudios.co.uk
 * @copyright Copyright (c) 2018 Fruit Studios
 */

namespace fruitstudiosassetuploader\assetuploader;

use fruitstudiosassetuploader\assetuploader\services\AssetUploaderService as AssetUploaderServiceService;
use fruitstudiosassetuploader\assetuploader\variables\AssetUploaderVariable;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\web\twig\variables\CraftVariable;
use craft\events\RegisterUrlRulesEvent;

use yii\base\Event;

/**
 * Class AssetUploader
 *
 * @author    Fruit Studios
 * @package   AssetUploader
 * @since     1.0.0
 *
 * @property  AssetUploaderServiceService $assetUploaderService
 */
class AssetUploader extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var AssetUploader
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

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['siteActionTrigger1'] = 'asset-uploader/default';
            }
        );

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['cpActionTrigger1'] = 'asset-uploader/default/do-something';
            }
        );

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('assetUploader', AssetUploaderVariable::class);
            }
        );

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                }
            }
        );

        Craft::info(
            Craft::t(
                'asset-uploader',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

}
