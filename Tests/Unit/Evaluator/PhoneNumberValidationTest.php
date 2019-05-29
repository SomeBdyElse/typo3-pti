<?php
namespace PrototypeIntegration\PrototypeIntegration\Tests\Unit\Evaluator;

use PHPUnit\Framework\TestCase;
use PrototypeIntegration\PrototypeIntegration\Evaluator\PhoneNumberValidation;

class PhoneNumberValidationTest extends TestCase
{
    /**
     * @var PhoneNumberValidation
     */
    protected $validator;

    public function setUp()
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
        $this->assertNotEmpty($this->validator->returnFieldJs());
    }

    /**
     *
     * @param $inputString
     * @param $expected
     * @test
     * @dataProvider telephoneIsProperlyEvaluatedDataProvider
     */
    public function telephoneIsProperlyEvaluated($inputString, $expected)
    {
        $isSet = false;
        $this->assertEquals($expected, $this->validator->evaluateFieldValue($inputString, '', $isSet));
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
