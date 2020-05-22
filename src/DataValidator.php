<?php

declare(strict_types=1);

namespace webignition\BasilDataValidator;

use webignition\BasilModels\DataParameter\DataParameterInterface;
use webignition\BasilModels\DataSet\DataSetCollectionInterface;
use webignition\BasilModels\DataSet\DataSetInterface;
use webignition\BasilValidationResult\InvalidResult;
use webignition\BasilValidationResult\InvalidResultInterface;
use webignition\BasilValidationResult\ResultInterface;
use webignition\BasilValidationResult\ValidResult;

class DataValidator
{
    public const REASON_DATASET_INVALID = 'data-dataset-invalid';
    public const REASON_DATA_EMPTY = 'data-empty';

    private DataSetValidator $dataSetValidator;

    public function __construct(DataSetValidator $dataSetValidator)
    {
        $this->dataSetValidator = $dataSetValidator;
    }

    public static function create(): DataValidator
    {
        return new DataValidator(
            DataSetValidator::create()
        );
    }

    public function validate(DataSetCollectionInterface $data, DataParameterInterface $dataParameter): ?ResultInterface
    {
        $localData = clone $data;
        $localData->rewind();

        if (0 === count($localData)) {
            return new InvalidResult(
                $data,
                ResultType::DATA,
                self::REASON_DATA_EMPTY
            );
        }

        foreach ($localData as $dataSet) {
            if ($dataSet instanceof DataSetInterface) {
                $dataSetValidationResult = $this->dataSetValidator->validate($dataSet, $dataParameter);

                if ($dataSetValidationResult instanceof InvalidResultInterface) {
                    return new InvalidResult(
                        $data,
                        ResultType::DATA,
                        self::REASON_DATASET_INVALID,
                        $dataSetValidationResult
                    );
                }
            }
        }

        return new ValidResult($data);
    }
}
