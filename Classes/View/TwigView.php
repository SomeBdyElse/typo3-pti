<?php
namespace PrototypeIntegration\PrototypeIntegration\View;

use PrototypeIntegration\PrototypeIntegration\Twig\Environment;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\Mvc\View\AbstractView;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class TwigView extends AbstractView
{
    /**
     * @var Environment
     */
    protected $twigEnvironment;

    /**
     * @var string
     */
    protected $template;

    public function __construct(string $template = '')
    {
        $this->template = $template;
        $this->twigEnvironment = GeneralUtility::makeInstance(Environment::class);
    }

    public function render(): string
    {
        if (empty($this->template)) {
            throw new \RuntimeException('Template file missing.', 1519205250412);
        }

        $variables = $this->getVariablesForTemplate();

        try {
            return $this->twigEnvironment->render($this->template, $variables);
        } catch (\Exception $exception) {
            throw new \RuntimeException('Twig view error: ' . $exception->getMessage(), 1519205228169, $exception);
        }
    }

    /**
     * Get the variables to be passed to the template.
     * This includes the variables set specific for this view
     * as well as the global variables passed to any twig
     * view.
     *
     * @return array
     */
    protected function getVariablesForTemplate(): array
    {
        $variables = array_merge(
            [
                'globals' => $this->getGlobalVariables(),
            ],
            $this->variables
        );
        return $variables;
    }

    /**
     * Array of global variables, that may be used in
     * any Twig template.
     *
     * @return array
     */
    protected function getGlobalVariables(): array
    {
        return [
            'spritePath' => $this->getSpriteUri(),
            'pageLanguage' => $this->getTypoScriptFrontendController()->lang,
            'pageLocale' => setlocale(LC_CTYPE, 0),
            'staticLabels' => $this->getStaticLabels()
        ];
    }

    /**
     * @return string
     */
    protected function getSpriteUri(): string
    {
        $spritePath = $this->twigEnvironment->getSpriteFile();
        $spriteUri = '';

        if (!is_null($spritePath)) {
            $spriteUri = PathUtility::getAbsoluteWebPath(
                GeneralUtility::createVersionNumberedFilename($spritePath)
            );
        }

        return $spriteUri;
    }

    /**
     * Get a series of static labels, required by the frontend as part of the global array
     * Retrieve the data from a locallang.xlf file
     *
     * @return array
     */
    protected function getStaticLabels(): array
    {
        $languageKey = $this->getTypoScriptFrontendController()->lang;
        $translationItems = $this->getTranslationItems($languageKey);
        $result = [];

        if (! $translationItems) {
            return $result;
        }

        // Overload default language with translation
        if ($languageKey !== 'default') {
            ArrayUtility::mergeRecursiveWithOverrule($translationItems['default'], $translationItems[$languageKey]);
        }
        $translationItems = $translationItems['default'];
        $translationItems['default'];

        foreach ($translationItems as $key => $locallangItem) {
            $value = $locallangItem[0]['target'] ?: $locallangItem[0]['source'];
            $result = ArrayUtility::setValueByPath($result, $key, $value, '.');
        }

        return $result;
    }

    /**
     * @param string $languageKey
     * @return array|bool
     */
    protected function getTranslationItems(string $languageKey)
    {
        $translationFile = $this->twigEnvironment->getLocalizationFile();
        $translationItems = false;

        if (!is_null($translationFile)) {
            /** @var $languageFactory \TYPO3\CMS\Core\Localization\LocalizationFactory */
            $languageFactory = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Localization\LocalizationFactory::class);
            $translationItems = $languageFactory->getParsedData($translationFile, $languageKey);
        }

        return $translationItems;
    }

    /**
     * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function getTypoScriptFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * @param string $template
     */
    public function setTemplate(string $template): void
    {
        $this->template = $template;
    }

    /**
     * @return array
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * @param array $variables
     */
    public function setVariables(array $variables): void
    {
        $this->variables = $variables;
    }
}
