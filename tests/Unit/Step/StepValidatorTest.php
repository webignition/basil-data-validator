<?php

declare(strict_types=1);

namespace webignition\BasilDataValidator\Tests\Unit\Step;

use webignition\BasilDataValidator\Action\ActionValidator;
use webignition\BasilDataValidator\Assertion\AssertionValidator;
use webignition\BasilDataValidator\DataSetValidator;
use webignition\BasilDataValidator\DataValidator;
use webignition\BasilDataValidator\ResultType;
use webignition\BasilDataValidator\Step\StepValidator;
use webignition\BasilDataValidator\ValueValidator;
use webignition\BasilModels\DataSet\DataSet;
use webignition\BasilModels\DataSet\DataSetCollection;
use webignition\BasilModels\Step\StepInterface;
use webignition\BasilParser\ActionParser;
use webignition\BasilParser\AssertionParser;
use webignition\BasilParser\StepParser;
use webignition\BasilValidationResult\InvalidResult;
use webignition\BasilValidationResult\InvalidResultInterface;
use webignition\BasilValidationResult\ValidResult;

class StepValidatorTest extends \PHPUnit\Framework\TestCase
{
    private StepValidator $validator;

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
            'valid data sets' => [
                'step' => $stepParser->parse([
                    'actions' => [
                        'set $".input" to $data.input',
                    ],
                    'assertions' => [
                        '$".button" is $data.expected_button_text',
                        '$data.input is "value"',
                    ],
                    'data' => [
                        '0' => [
                            'input' => 'Sheep',
                            'expected_button_text' => 'Baa',
                        ],
                        '1' => [
                            'input' => 'Cow',
                            'expected_button_text' => 'Moo',
                        ],
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
            'assertions' => [
                '$elements.element_name exists'
            ],
        ]);

        $invalidActionDataStepNoData = $stepParser->parse([
            'actions' => [
                'set $".selector" to $data.key',
            ],
            'assertions' => [
                '$".selector" exists'
            ],
        ]);

        $invalidAssertionDataStepNoData = $stepParser->parse([
            'assertions' => [
                '$".selector" is $data.key'
            ],
        ]);

        $incompleteDataSet = [
            'key2' => 'key2value2',
        ];

        $incompleteData = [
            '0' => [
                'key1' => 'key1value1',
                'key2' => 'key2value1',
            ],
            '1' => $incompleteDataSet,
        ];

        $incompleteDataSetCollection = new DataSetCollection($incompleteData);

        $invalidDataResult = new InvalidResult(
            $incompleteDataSetCollection,
            ResultType::DATA,
            DataValidator::REASON_DATASET_INVALID,
            (new InvalidResult(
                new DataSet('1', $incompleteDataSet),
                ResultType::DATASET,
                DataSetValidator::REASON_DATASET_INCOMPLETE
            ))->withContext([
                DataSetValidator::CONTEXT_DATA_PARAMETER_NAME => 'key1',
            ])
        );

        $invalidActionDataStepDataParameterMissing = $stepParser->parse([
            'actions' => [
                'set $".selector1" to $data.key1',
                'set $".selector2" to $data.key2',
            ],
            'assertions' => [
                '$".selector" exists'
            ],
            'data' => $incompleteData,
        ]);

        $invalidAssertionDataStepDataParameterMissing1 = $stepParser->parse([
            'assertions' => [
                '$".selector1" is $data.key1',
                '$".selector2" is $data.key2',
            ],
            'data' => $incompleteData,
        ]);

        $invalidAssertionDataStepDataParameterMissing2 = $stepParser->parse([
            'assertions' => [
                '$data.key1 is "value1"',
                '$data.key2 is "value2',
            ],
            'data' => $incompleteData,
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
            'invalid step: action uses data parameter, step has no data' => [
                'step' => $invalidActionDataStepNoData,
                'expectedResult' => new InvalidResult(
                    $invalidActionDataStepNoData,
                    ResultType::STEP,
                    StepValidator::REASON_DATA_SET_EMPTY
                ),
            ],
            'invalid step: assertion uses data parameter, step has no data' => [
                'step' => $invalidAssertionDataStepNoData,
                'expectedResult' => new InvalidResult(
                    $invalidAssertionDataStepNoData,
                    ResultType::STEP,
                    StepValidator::REASON_DATA_SET_EMPTY
                ),
            ],
            'invalid step: action uses data parameter, key missing from step data' => [
                'step' => $invalidActionDataStepDataParameterMissing,
                'expectedResult' => (new InvalidResult(
                    $invalidActionDataStepDataParameterMissing,
                    ResultType::STEP,
                    StepValidator::REASON_DATA_INVALID,
                    $invalidDataResult
                ))->withContext([
                    StepValidator::CONTEXT_STATEMENT => $actionParser->parse('set $".selector1" to $data.key1'),
                ]),
            ],
            'invalid step: assertion uses data parameter, value key missing from step data' => [
                'step' => $invalidAssertionDataStepDataParameterMissing1,
                'expectedResult' => (new InvalidResult(
                    $invalidAssertionDataStepDataParameterMissing1,
                    ResultType::STEP,
                    StepValidator::REASON_DATA_INVALID,
                    $invalidDataResult
                ))->withContext([
                    StepValidator::CONTEXT_STATEMENT => $assertionParser->parse('$".selector1" is $data.key1'),
                ]),
            ],
            'invalid step: assertion uses data parameter, identifier key missing from step data' => [
                'step' => $invalidAssertionDataStepDataParameterMissing2,
                'expectedResult' => (new InvalidResult(
                    $invalidAssertionDataStepDataParameterMissing2,
                    ResultType::STEP,
                    StepValidator::REASON_DATA_INVALID,
                    $invalidDataResult
                ))->withContext([
                    StepValidator::CONTEXT_STATEMENT => $assertionParser->parse('$data.key1 is "value1"'),
                ]),
            ],
        ];
    }
}
