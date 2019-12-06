<?php

declare(strict_types=1);

namespace webignition\BasilDataValidator\Test;

use webignition\BasilDataValidator\ResultType;
use webignition\BasilModels\PageUrlReference\PageUrlReference;
use webignition\BasilModels\Test\ConfigurationInterface;
use webignition\BasilValidationResult\InvalidResult;
use webignition\BasilValidationResult\ResultInterface;
use webignition\BasilValidationResult\ValidResult;

class ConfigurationValidator
{
    public const REASON_BROWSER_EMPTY = 'test-configuration-browser-empty';
    public const REASON_URL_IS_PAGE_URL_REFERENCE = 'test-configuration-url-is-page-url-reference';

    public static function create(): ConfigurationValidator
    {
        return new ConfigurationValidator();
    }

    public function validate(ConfigurationInterface $configuration): ResultInterface
    {
        if ('' === trim($configuration->getBrowser())) {
            return new InvalidResult(
                $configuration,
                ResultType::TEST_CONFIGURATION,
                self::REASON_BROWSER_EMPTY
            );
        }

        $pageUrlReference = new PageUrlReference($configuration->getUrl());
        if ($pageUrlReference->isValid()) {
            return new InvalidResult(
                $configuration,
                ResultType::TEST_CONFIGURATION,
                self::REASON_URL_IS_PAGE_URL_REFERENCE
            );
        }
        return new ValidResult($configuration);
    }
}
