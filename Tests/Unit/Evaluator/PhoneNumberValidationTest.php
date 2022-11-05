<?php

namespace PrototypeIntegration\PrototypeIntegration\Tests\Unit\Evaluator;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use PrototypeIntegration\PrototypeIntegration\Evaluator\PhoneNumberValidation;

class PhoneNumberValidationTest extends UnitTestCase
{
    /**
     * @var PhoneNumberValidation
     */
    protected $validator;

    public function setUp(): void
    {
        parent::setUp();
        $this->validator = $this->getMockBuilder(PhoneNumberValidation::class)
            ->setMethods(['addFlashMessage', 'translate'])
            ->getMock();
    }

    /**
     * @test
     */
    public function jsEvaluationIsCalled()
    {
        self::assertNotEmpty($this->validator->returnFieldJs());
    }

    /**
     * @param $inputString
     * @param $expected
     * @test
     * @dataProvider telephoneIsProperlyEvaluatedDataProvider
     */
    public function telephoneIsProperlyEvaluated($inputString, $expected)
    {
        $isSet = false;
        self::assertEquals($expected, $this->validator->evaluateFieldValue($inputString, '', $isSet));
    }

    public function telephoneIsProperlyEvaluatedDataProvider(): array
    {
        return [
            'empty string' => ['', ''],
            'with preceding plus' => ['+43 699 12 54 12 1', '+43 699 12 54 12 1'],
            'with preceding 00' => ['0043 699 12 54 12 1', '+43 699 12 54 12 1'],
            'national format return same' => ['0699 12 54 12 1', '0699 12 54 12 1'],
            'with non numeric characters' => ['+43 699 12 54 12 1 -D:4', '+43 699 12 54 12 1 4']
        ];
    }
}
