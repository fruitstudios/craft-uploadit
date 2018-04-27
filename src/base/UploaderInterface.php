<?php
namespace fruitstudios\uploadit\base;

use Craft;
use craft\base\ComponentInterface;

interface UploaderInterface
{
    // Static
    // =========================================================================

    public static function type(): string;

    // Public Methods
    // =========================================================================

    public function getJavascriptProperties(): array;
    public function setTarget(): bool;
}
