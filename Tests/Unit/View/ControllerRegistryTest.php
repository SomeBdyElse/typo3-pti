<?php

namespace PrototypeIntegration\PrototypeIntegration\Tests\Unit\View;

use PrototypeIntegration\PrototypeIntegration\View\ControllerRegistry;
use TYPO3\CMS\Core\Domain\RawRecord;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\Domain\Record\ComputedProperties;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class ControllerRegistryTest extends UnitTestCase
{
    protected array $tcaBackup = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->tcaBackup = $GLOBALS['TCA'] ?? [];
    }

    protected function tearDown(): void
    {
        $GLOBALS['TCA'] = $this->tcaBackup;
        parent::tearDown();
    }

    /**
     * @test
     */
    public function returnsControllerForTableAndType(): void
    {
        $registry = new ControllerRegistry();
        $registry->addController('tt_content', 'ce01_hero', ExampleContentController::class);

        self::assertSame(
            ExampleContentController::class,
            $registry->getControllerClassNameForTableAndType('tt_content', 'ce01_hero'),
        );
    }

    /**
     * @test
     */
    public function returnsControllerClassNameForRawRecordTypeField(): void
    {
        $GLOBALS['TCA']['tt_content']['ctrl']['type'] = 'CType';

        $registry = new ControllerRegistry();
        $registry->addController('tt_content', 'ce01_hero', ExampleContentController::class);

        self::assertSame(
            ExampleContentController::class,
            $registry->getControllerClassNameForRawRecord('tt_content', ['CType' => 'ce01_hero']),
        );
    }

    /**
     * @test
     */
    public function returnsNullWhenTableHasNoTypeField(): void
    {
        $registry = new ControllerRegistry();
        $registry->addController('tt_content', 'ce01_hero', ExampleContentController::class);

        self::assertNull($registry->getControllerClassNameForRawRecord('tt_content', ['CType' => 'ce01_hero']));
    }

    /**
     * @test
     */
    public function returnsControllerClassNameForRecord(): void
    {
        $registry = new ControllerRegistry();
        $registry->addController('tt_content', 'ce01_hero', ExampleContentController::class);

        self::assertSame(
            ExampleContentController::class,
            $registry->getControllerClassNameForRecord(
                new Record(
                    new RawRecord(
                        1,
                        2,
                        [],
                        new ComputedProperties(),
                        'tt_content.ce01_hero',
                    ),
                    [],
                ),
            ),
        );
    }
}

class ExampleContentController
{
}
