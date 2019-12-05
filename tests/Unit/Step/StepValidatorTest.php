<?php

declare(strict_types=1);

namespace webignition\BasilDataValidator\Tests\Unit\Step;

use webignition\BasilDataValidator\Action\ActionValidator;
use webignition\BasilDataValidator\Assertion\AssertionValidator;
use webignition\BasilDataValidator\ResultType;
use webignition\BasilDataValidator\Step\StepValidator;
use webignition\BasilDataValidator\ValueValidator;
use webignition\BasilModels\Step\StepInterface;
use webignition\BasilParser\ActionParser;
use webignition\BasilParser\AssertionParser;
use webignition\BasilParser\StepParser;
use webignition\BasilValidationResult\InvalidResult;
use webignition\BasilValidationResult\InvalidResultInterface;
use webignition\BasilValidationResult\ValidResult;

class StepValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var StepValidator
     */
    private $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = StepValidator::create();
    }

    /**
     * @dataProvider validateIsValidDataProvider
     */
    public function testValidateIsValid(StepInterface $step)
    {
        $this->assertEquals(new ValidResult($step), $this->validator->validate($step));
    }

    public function validateIsValidDataProvider(): array
    {
        $stepParser = StepParser::create();

        return [
            'valid actions, valid assertions' => [
                'step' => $stepParser->parse([
                    'actions' => [
                        'click $".selector"',
                    ],
                    'assertions' => [
                        '$".selector" exists',
                    ],
                ]),
            ],
        ];
    }

    /**
     * @dataProvider invalidStepDataProvider
     */
    public function testValidateNotValid(StepInterface $step, InvalidResultInterface $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->validator->validate($step));
    }

    public function invalidStepDataProvider(): array
    {
        $stepParser = StepParser::create();
        $actionParser = ActionParser::create();
        $assertionParser = AssertionParser::create();

        $invalidActionStep = $stepParser->parse([
            'actions' => [
                'click $elements.element_name',
            ],
            'assertions' => [
                '$".selector" exists'
            ],
        ]);

        $invalidAssertionStep = $stepParser->parse([
            'actions' => [
                'click $".selector"',
            ],
            'assertions' => [
                '$elements.element_name exists'
            ],
        ]);

        return [
            'invalid step: no assertions' => [
                'step' => $stepParser->parse([]),
                'expectedResult' => new InvalidResult(
                    $stepParser->parse([]),
                    ResultType::STEP,
                    StepValidator::REASON_NO_ASSERTIONS
                ),
            ],
            'invalid step: invalid action' => [
                'step' => $invalidActionStep,
                'expectedResult' => new InvalidResult(
                    $invalidActionStep,
                    ResultType::STEP,
                    StepValidator::REASON_INVALID_ACTION,
                    new InvalidResult(
                        $actionParser->parse('click $elements.element_name'),
                        ResultType::ACTION,
                        ActionValidator::REASON_INVALID_IDENTIFIER
                    )
                ),
            ],
            'invalid step: invalid assertion' => [
                'step' => $invalidAssertionStep,
                'expectedResult' => new InvalidResult(
                    $invalidAssertionStep,
                    ResultType::STEP,
                    StepValidator::REASON_INVALID_ASSERTION,
                    new InvalidResult(
                        $assertionParser->parse('$elements.element_name exists'),
                        ResultType::ASSERTION,
                        AssertionValidator::REASON_INVALID_IDENTIFIER,
                        new InvalidResult(
                            '$elements.element_name',
                            ResultType::VALUE,
                            ValueValidator::REASON_INVALID
                        )
                    )
                ),
            ],
        ];
    }
}
