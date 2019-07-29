<?php
namespace PrototypeIntegration\PrototypeIntegration\Twig;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Environment
 */
class Environment extends \Comwrap\Typo3\TwigForTypo3\Twig\Environment
{
    /**
     * @var array
     */
    protected $settingsPti;

    public function __construct()
    {
        parent::__construct();
        $this->settingsPti = \unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['twig_for_typo3']);
    }

    public function getLocalizationFile(): ?string
    {
        $path = isset($this->settingsPti['localization']) ? $this->settingsPti['localization'] : null;
        return $path;
    }

    public function getSpriteFile(): ?string
    {
        $path = isset($this->settingsPti['spritePath']) ? GeneralUtility::getFileAbsFileName($this->settingsPti['spritePath']) : null;
        return $path;
    }
}
