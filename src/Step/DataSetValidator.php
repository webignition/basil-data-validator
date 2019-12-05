<?php

declare(strict_types=1);

namespace webignition\BasilDataValidator\Step;

use webignition\BasilDataValidator\ResultType;
use webignition\BasilModels\DataParameter\DataParameterInterface;
use webignition\BasilModels\DataSet\DataSetInterface;
use webignition\BasilValidationResult\InvalidResult;
use webignition\BasilValidationResult\ResultInterface;
use webignition\BasilValidationResult\ValidResult;

class DataSetValidator
{
    public const REASON_DATA_SET_INCOMPLETE = 'step-data-set-incomplete';
    public const CONTEXT_DATA_PARAMETER_NAME = 'data-parameter-name';
    public const CONTEXT_DATA_SET = 'data-set';

    public static function create(): DataSetValidator
    {
        return new DataSetValidator();
    }

    public function validate(DataSetInterface $dataSet, DataParameterInterface $dataParameter): ResultInterface
    {
        $property = $dataParameter->getProperty();

        if (false === $dataSet->hasParameterNames([$property])) {
            return (new InvalidResult($dataSet, ResultType::DATA_SET, self::REASON_DATA_SET_INCOMPLETE))
                ->withContext([
                    self::CONTEXT_DATA_SET => $dataSet,
                    self::CONTEXT_DATA_PARAMETER_NAME => $property,
                ]);
        }

        return new ValidResult($dataSet);
    }
}
