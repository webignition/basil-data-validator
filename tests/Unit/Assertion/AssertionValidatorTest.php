<?php

declare(strict_types=1);

namespace webignition\BasilDataValidator\Tests\Unit\Assertion;

use webignition\BasilDataValidator\Assertion\AssertionValidator;
use webignition\BasilDataValidator\ResultType;
use webignition\BasilDataValidator\ValueValidator;
use webignition\BasilModels\Assertion\Assertion;
use webignition\BasilModels\Assertion\AssertionInterface;
use webignition\BasilParser\AssertionParser;
use webignition\BasilValidationResult\InvalidResult;
use webignition\BasilValidationResult\InvalidResultInterface;
use webignition\BasilValidationResult\ValidResult;

class AssertionValidatorTest extends \PHPUnit\Framework\TestCase
{
    private AssertionValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = AssertionValidator::create();
    }

    /**
     * @dataProvider invalidAssertionDataProvider
     */
    public function testValidateNotValid(AssertionInterface $assertion, InvalidResultInterface $expectedResult): void
    {
        $this->assertEquals($expectedResult, $this->validator->validate($assertion));
    }

    /**
     * @return array<mixed>
     */
    public function invalidAssertionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'invalid identifier' => [
                'assertion' => $assertionParser->parse('$elements.element_name is "value"'),
                'expectedResult' => new InvalidResult(
                    $assertionParser->parse('$elements.element_name is "value"'),
                    ResultType::ASSERTION,
                    AssertionValidator::REASON_INVALID_IDENTIFIER,
                    new InvalidResult(
                        '$elements.element_name',
                        ResultType::VALUE,
                        ValueValidator::REASON_INVALID
                    )
                ),
            ],
            'invalid operator' => [
                'assertion' => new Assertion('$".button" glows', '$".button"', 'glows'),
                'expectedResult' => (new InvalidResult(
                    new Assertion('$".button" glows', '$".button"', 'glows'),
                    ResultType::ASSERTION,
                    AssertionValidator::REASON_INVALID_OPERATOR
                ))->withContext([
                    AssertionValidator::CONTEXT_OPERATOR => 'glows',
                ]),
            ],
            'invalid value' => [
                'assertion' => $assertionParser->parse('$".selector" is $elements.element_name'),
                'expectedResult' => new InvalidResult(
                    $assertionParser->parse('$".selector" is $elements.element_name'),
                    ResultType::ASSERTION,
                    AssertionValidator::REASON_INVALID_VALUE,
                    new InvalidResult(
                        '$elements.element_name',
                        ResultType::VALUE,
                        ValueValidator::REASON_INVALID
                    )
                ),
            ],
        ];
    }

    /**
     * @dataProvider validAssertionIdentifierDataProvider
     * @dataProvider validAssertionOperatorDataProvider
     * @dataProvider validAssertionValueDataProvider
     */
    public function testValidateIsValid(AssertionInterface $assertion): void
    {
        $expectedResult = new ValidResult($assertion);

        $this->assertEquals($expectedResult, $this->validator->validate($assertion));
    }

    /**
     * @return array<mixed>
     */
    public function validAssertionIdentifierDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'identifier: element identifier' => [
                'value' => $assertionParser->parse('$".selector" is "value"'),
            ],
            'identifier: descendant element identifier' => [
                'value' => $assertionParser->parse('$".parent" >> $".child" is "value"'),
            ],
            'identifier: attribute identifier' => [
                'value' => $assertionParser->parse('$".selector".attribute_name is "value"'),
            ],
            'identifier: quoted literal' => [
                'value' => $assertionParser->parse('"value" is "value"'),
            ],
            'identifier: browser property' => [
                'value' => $assertionParser->parse('$browser.size is "value"'),
            ],
            'identifier: page property' => [
                'value' => $assertionParser->parse('$page.title is "value"'),
            ],
            'identifier: data parameter' => [
                'value' => $assertionParser->parse('$data.key is "value"'),
            ],
            'identifier: environment parameter' => [
                'value' => $assertionParser->parse('$env.KEY is "value"'),
            ],
        ];
    }

    /**
     * @return array<mixed>
     */
    public function validAssertionOperatorDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'operator: is' => [
                'value' => $assertionParser->parse('$".selector" is "value"'),
            ],
            'operator: is-not' => [
                'value' => $assertionParser->parse('$".selector" is-not "value"'),
            ],
            'operator: exists' => [
                'value' => $assertionParser->parse('$".selector" exists'),
            ],
            'operator: not-exists' => [
                'value' => $assertionParser->parse('$".selector" not-exists'),
            ],
            'operator: includes' => [
                'value' => $assertionParser->parse('$".selector" includes "value"'),
            ],
            'operator: excludes' => [
                'value' => $assertionParser->parse('$".selector" excludes "value"'),
            ],
            'operator: matches' => [
                'value' => $assertionParser->parse('$".selector" matches "value"'),
            ],
        ];
    }

    /**
     * @return array<mixed>
     */
    public function validAssertionValueDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'value: element identifier' => [
                'value' => $assertionParser->parse('"value" is $".selector"'),
            ],
            'value: descendant element identifier' => [
                'value' => $assertionParser->parse('"value" is $".parent" >> $".child"'),
            ],
            'value: attribute identifier' => [
                'value' => $assertionParser->parse('"value" is $".selector".attribute_name'),
            ],
            'value: quoted literal' => [
                'value' => $assertionParser->parse('"value" is "value"'),
            ],
            'value: browser property' => [
                'value' => $assertionParser->parse('"value" is $browser.size'),
            ],
            'value: page property' => [
                'value' => $assertionParser->parse('"value" is $page.title'),
            ],
            'value: data parameter' => [
                'value' => $assertionParser->parse('"value" is $data.key'),
            ],
            'value: environment parameter' => [
                'value' => $assertionParser->parse('"value" is $env.KEY'),
            ],
        ];
    }
}
