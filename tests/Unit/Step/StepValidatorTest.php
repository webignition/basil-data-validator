<?php

declare(strict_types=1);

namespace webignition\BasilDataValidator\Tests\Unit\Step;

use webignition\BasilDataValidator\Action\ActionValidator;
use webignition\BasilDataValidator\ResultType;
use webignition\BasilDataValidator\Step\StepValidator;
use webignition\BasilModels\Step\StepInterface;
use webignition\BasilParser\ActionParser;
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
            'valid actions' => [
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

        $invalidActionStep = $stepParser->parse([
            'actions' => [
                'click $elements.element_name',
            ],
            'assertions' => [
                '$".selector" exists'
            ],
        ]);

        return [
            'interaction action: identifier invalid (element reference)' => [
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
        ];
    }
}
