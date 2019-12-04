<?php

declare(strict_types=1);

namespace webignition\BasilDataValidator\Tests\Unit;

use webignition\BasilDataValidator\ResultType;
use webignition\BasilDataValidator\ValueValidator;
use webignition\BasilValidationResult\InvalidResult;
use webignition\BasilValidationResult\ValidResult;

class ValueValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ValueValidator
     */
    private $valueValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->valueValidator = ValueValidator::create();
    }

    /**
     * @dataProvider validateNotValidDataProvider
     */
    public function testValidateNotValid(string $value, string $expectedReason)
    {
        $expectedResult = new InvalidResult($value, ResultType::VALUE, $expectedReason);

        $this->assertEquals($expectedResult, $this->valueValidator->validate($value));
    }

    public function validateNotValidDataProvider(): array
    {
        return [
            'invalid page property' => [
                'value' => '$page.foo',
                'expectedReason' => ValueValidator::REASON_PROPERTY_INVALID,
            ],
            'invalid browser property name' => [
                'value' => '$browser.foo',
                'expectedReason' => ValueValidator::REASON_PROPERTY_INVALID,
            ],
            'unquoted' => [
                'value' => 'value',
                'expectedReason' => ValueValidator::REASON_INVALID,
            ],
            'misquoted' => [
                'value' => '"value',
                'expectedReason' => ValueValidator::REASON_INVALID,
            ],
        ];
    }

    /**
     * @dataProvider validateIsValidDataProvider
     */
    public function testValidateIsValid(string $value)
    {
        $expectedResult = new ValidResult($value);

        $this->assertEquals($expectedResult, $this->valueValidator->validate($value));
    }

    public function validateIsValidDataProvider(): array
    {
        return [
            'quoted literal' => [
                'value' => '"value"',
            ],
            'escaped quoted literal' => [
                'value' => '"va\"l\"ue"',
            ],
            'data parameter' => [
                'value' => '$data.value',
            ],
            'page property, url' => [
                'value' => '$page.url',
            ],
            'page property, title' => [
                'value' => '$page.title',
            ],
            'browser property, size' => [
                'value' => '$browser.size',
            ],
            'element dom identifier' => [
                'value' => '$".selector"',
            ],
            'attribute dom identifier' => [
                'value' => '$".selector".attribute_name',
            ],
            'environment parameter' => [
                'value' => '$env.KEY',
            ],
        ];
    }
}
