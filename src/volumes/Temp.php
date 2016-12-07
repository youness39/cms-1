<?php
namespace craft\volumes;

use Craft;
use craft\helpers\Url;

/**
 * The temporary volume class. Handles the implementation of a temporary volume
 * Craft.
 *
 * @author     Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @copyright  Copyright (c) 2014, Pixel & Tonic, Inc.
 * @license    http://craftcms.com/license Craft License Agreement
 * @see        http://craftcms.com
 * @package    craft.app.volumes
 * @since      3.0
 */
class Temp extends Local
{
    // Static
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['path'], 'required'];

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public static function displayName()
    {
        return Craft::t('app', 'Local Folder');
    }

    /**
     * @inheritdoc
     */
    public static function isLocal()
    {
        return true;
    }

    // Properties
    // =========================================================================

    /**
     * Path to the root of this sources local folder.
     *
     * @var string
     */
    public $path = "";

    // Public Methods
    // =========================================================================

    /**
     * Constructor
     */
    public function init()
    {
        parent::init();

        if (isset($this->path)) {
            $this->path = rtrim($this->path, '/');
        } else {
            $this->path = Craft::$app->getPath()->getAssetsTempVolumePath();
        }

        if (!isset($this->url)) {
            $this->url = rtrim(Url::getResourceUrl(), '/').'/tempassets/';
        }

        if (!isset($this->name)) {
            $this->name = Craft::t('app', 'Temporary source');
        }
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getRootPath()
    {
        return $this->path;
    }

    /**
     * @inheritdoc
     */
    public function getRootUrl()
    {
        return $this->url;
    }
}