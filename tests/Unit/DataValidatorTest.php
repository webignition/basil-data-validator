<?php

declare(strict_types=1);

namespace webignition\BasilDataValidator\Tests\Unit;

use webignition\BasilDataValidator\DataSetValidator;
use webignition\BasilDataValidator\DataValidator;
use webignition\BasilDataValidator\ResultType;
use webignition\BasilModels\DataParameter\DataParameter;
use webignition\BasilModels\DataParameter\DataParameterInterface;
use webignition\BasilModels\DataSet\DataSet;
use webignition\BasilModels\DataSet\DataSetCollection;
use webignition\BasilModels\DataSet\DataSetCollectionInterface;
use webignition\BasilValidationResult\InvalidResult;
use webignition\BasilValidationResult\InvalidResultInterface;
use webignition\BasilValidationResult\ValidResult;

class DataValidatorTest extends \PHPUnit\Framework\TestCase
{
    private DataValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = DataValidator::create();
    }

    public function testValidateIsValid(): void
    {
        $data = new DataSetCollection([
            '0' => [
                'key1' => 'value1',
                'key2' => 'value2',
            ],
            '1' => [
                'key1' => 'value1',
                'key2' => 'value2',
                'key3' => 'value3',
            ],
        ]);

        $expectedResult = new ValidResult($data);

        $this->assertEquals($expectedResult, $this->validator->validate($data, new DataParameter('$data.key1')));
        $this->assertEquals($expectedResult, $this->validator->validate($data, new DataParameter('$data.key2')));
    }

    /**
     * @dataProvider invalidDataSetDataProvider
     */
    public function testValidateNotValid(
        DataSetCollectionInterface $data,
        DataParameterInterface $dataParameter,
        InvalidResultInterface $expectedResult
    ): void {
        $this->assertEquals($expectedResult, $this->validator->validate($data, $dataParameter));
    }

    /**
     * @return array[]
     */
    public function invalidDataSetDataProvider(): array
    {
        return [
            'empty' => [
                'data' => new DataSetCollection([]),
                'dataParameter' => new DataParameter('$data.key'),
                'expectedResult' => new InvalidResult(
                    new DataSetCollection([]),
                    ResultType::DATA,
                    DataValidator::REASON_DATA_EMPTY
                ),
            ],
            'key not present' => [
                'data' => new DataSetCollection([
                    '0' => [
                        'key1' => 'value1',
                        'key2' => 'value2',
                    ],
                    '1' => [
                        'key2' => 'value2',
                    ],
                ]),
                'dataParameter' => new DataParameter('$data.key1'),
                'expectedResult' => new InvalidResult(
                    new DataSetCollection([
                        '0' => [
                            'key1' => 'value1',
                            'key2' => 'value2',
                        ],
                        '1' => [
                            'key2' => 'value2',
                        ],
                    ]),
                    ResultType::DATA,
                    DataValidator::REASON_DATASET_INVALID,
                    (new InvalidResult(
                        new DataSet('1', ['key2' => 'value2']),
                        ResultType::DATASET,
                        DataSetValidator::REASON_DATASET_INCOMPLETE
                    ))->withContext([
                        DataSetValidator::CONTEXT_DATA_PARAMETER_NAME => 'key1',
                    ])
                ),
            ],
        ];
    }
}
