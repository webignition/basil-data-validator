<?php

declare(strict_types=1);

namespace webignition\BasilDataValidator;

use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\Page\PageInterface;
use webignition\BasilValidationResult\InvalidResult;
use webignition\BasilValidationResult\ResultInterface;
use webignition\BasilValidationResult\ValidResult;

class PageValidator
{
    public const REASON_URL_EMPTY = 'page-url-empty';
    public const REASON_IDENTIFIER_INVALID = 'page-invalid-identifier';
    public const CONTEXT_NAME = 'name';
    public const CONTEXT_IDENTIFIER = 'identifier';

    private $identifierTypeAnalyser;

    public function __construct(IdentifierTypeAnalyser $identifierTypeAnalyser)
    {
        $this->identifierTypeAnalyser = $identifierTypeAnalyser;
    }

    public static function create(): PageValidator
    {
        return new PageValidator(
            new IdentifierTypeAnalyser()
        );
    }

    public function validate(PageInterface $page): ResultInterface
    {
        $url = trim($page->getUrl());
        if ('' === $url) {
            return new InvalidResult(
                $page,
                ResultType::PAGE,
                self::REASON_URL_EMPTY
            );
        }

        $identifiers = $page->getIdentifiers();
        foreach ($identifiers as $name => $identifier) {
            if (
                !$this->identifierTypeAnalyser->isElementIdentifier($identifier) &&
                !$this->identifierTypeAnalyser->isDescendantDomIdentifier($identifier)
            ) {
                return (new InvalidResult(
                    $page,
                    ResultType::PAGE,
                    self::REASON_IDENTIFIER_INVALID
                ))->withContext([
                    self::CONTEXT_NAME => $name,
                    self::CONTEXT_IDENTIFIER => $identifier,
                ]);
            }
        }

        return new ValidResult($page);
    }
}
