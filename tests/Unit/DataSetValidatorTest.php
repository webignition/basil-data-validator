<?php

declare(strict_types=1);

namespace webignition\BasilDataValidator\Tests\Unit;

use webignition\BasilDataValidator\DataSetValidator;
use webignition\BasilDataValidator\ResultType;
use webignition\BasilModels\DataParameter\DataParameter;
use webignition\BasilModels\DataParameter\DataParameterInterface;
use webignition\BasilModels\DataSet\DataSet;
use webignition\BasilModels\DataSet\DataSetInterface;
use webignition\BasilValidationResult\InvalidResult;
use webignition\BasilValidationResult\InvalidResultInterface;
use webignition\BasilValidationResult\ValidResult;

class DataSetValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DataSetValidator
     */
    private $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = DataSetValidator::create();
    }

    public function testValidateIsValid()
    {
        $dataSet = new DataSet('0', [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value1',
        ]);

        $expectedResult = new ValidResult($dataSet);

        $this->assertEquals($expectedResult, $this->validator->validate($dataSet, new DataParameter('$data.key1')));
        $this->assertEquals($expectedResult, $this->validator->validate($dataSet, new DataParameter('$data.key2')));
        $this->assertEquals($expectedResult, $this->validator->validate($dataSet, new DataParameter('$data.key3')));
    }

    /**
     * @dataProvider invalidDataSetDataProvider
     */
    public function testValidateNotValid(
        DataSetInterface $dataSet,
        DataParameterInterface $dataParameter,
        InvalidResultInterface $expectedResult
    ) {
        $this->assertEquals($expectedResult, $this->validator->validate($dataSet, $dataParameter));
    }

    public function invalidDataSetDataProvider(): array
    {
        return [
            'empty' => [
                'dataSet' => new DataSet('0', []),
                'dataParameter' => new DataParameter('$data.key'),
                'expectedResult' => (new InvalidResult(
                    new DataSet('0', []),
                    ResultType::DATASET,
                    DataSetValidator::REASON_DATASET_INCOMPLETE
                ))->withContext([
                    DataSetValidator::CONTEXT_DATA_PARAMETER_NAME => 'key',
                ]),
            ],
            'key not present' => [
                'dataSet' => new DataSet('0', [
                    'key1' => 'value1',
                    'key2' => 'value2',
                ]),
                'dataParameter' => new DataParameter('$data.key3'),
                'expectedResult' => (new InvalidResult(
                    new DataSet('0', [
                        'key1' => 'value1',
                        'key2' => 'value2',
                    ]),
                    ResultType::DATASET,
                    DataSetValidator::REASON_DATASET_INCOMPLETE
                ))->withContext([
                    DataSetValidator::CONTEXT_DATA_PARAMETER_NAME => 'key3',
                ]),
            ],
        ];
    }
}
