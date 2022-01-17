<?php

declare(strict_types=1);

namespace webignition\BasilDataValidator\Tests\Unit\Assertion;

use webignition\BasilDataValidator\Assertion\AssertionContentValidator;
use webignition\BasilDataValidator\ResultType;
use webignition\BasilDataValidator\Tests\DataProvider\ValueDataProviderTrait;
use webignition\BasilValidationResult\InvalidResult;
use webignition\BasilValidationResult\ValidResult;

class AssertionContentValidatorTest extends \PHPUnit\Framework\TestCase
{
    use ValueDataProviderTrait;

    private AssertionContentValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = AssertionContentValidator::create();
    }

    /**
     * @dataProvider invalidValueDataProvider
     */
    public function testValidateNotValid(string $value, string $expectedReason): void
    {
        $expectedResult = new InvalidResult($value, ResultType::VALUE, $expectedReason);

        $this->assertEquals($expectedResult, $this->validator->validate($value));
    }

    /**
     * @dataProvider validValueDataProvider
     * @dataProvider validAssertionValueDataProvider
     */
    public function testValidateIsValid(string $value): void
    {
        $expectedResult = new ValidResult($value);

        $this->assertEquals($expectedResult, $this->validator->validate($value));
    }

    /**
     * @return array<mixed>
     */
    public function validAssertionValueDataProvider(): array
    {
        return [
            'descendant element dom identifier' => [
                'value' => '$"parent" >> $".selector"',
            ],
            'descendant attribute dom identifier' => [
                'value' => '$"parent" >> $".selector".attribute_name',
            ],
        ];
    }
}
