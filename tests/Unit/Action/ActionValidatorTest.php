<?php

declare(strict_types=1);

namespace webignition\BasilDataValidator\Tests\Unit\Action;

use webignition\BasilDataValidator\Action\ActionValidator;
use webignition\BasilDataValidator\ResultType;
use webignition\BasilDataValidator\ValueValidator;
use webignition\BasilModels\Action\ActionInterface;
use webignition\BasilModels\Action\InputAction;
use webignition\BasilModels\Action\InteractionAction;
use webignition\BasilParser\ActionParser;
use webignition\BasilValidationResult\InvalidResult;
use webignition\BasilValidationResult\InvalidResultInterface;
use webignition\BasilValidationResult\ValidResult;

class ActionValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ActionValidator
     */
    private $actionValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actionValidator = ActionValidator::create();
    }

    /**
     * @dataProvider validateIsValidDataProvider
     */
    public function testValidateIsValid(ActionInterface $action)
    {
        $this->assertEquals(new ValidResult($action), $this->actionValidator->validate($action));
    }

    public function validateIsValidDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'click element identifier' => [
                'action' => $actionParser->parse('click $".selector"'),
            ],
            'click descendant dom identifier' => [
                'action' => $actionParser->parse('click $"{{ $".parent" }} .child"'),
            ],
            'click single-character CSS selector element identifier' => [
                'action' => $actionParser->parse('click $"a"'),
            ],
            'submit element identifier' => [
                'action' => $actionParser->parse('submit $".selector"'),
            ],
            'wait-for element identifier' => [
                'action' => $actionParser->parse('wait-for $".selector"'),
            ],
            'wait-for descendant dom identifier' => [
                'action' => $actionParser->parse('wait-for $"{{ $".parent" }} .child"'),
            ],
            'wait literal value (unquoted)' => [
                'action' => $actionParser->parse('wait 1'),
            ],
            'wait literal value (quoted)' => [
                'action' => $actionParser->parse('wait "1"'),
            ],
            'wait element identifier value' => [
                'action' => $actionParser->parse('wait $".selector"'),
            ],
            'wait descendant dom identifier' => [
                'action' => $actionParser->parse('wait $"{{ $".parent" }} .child"'),
            ],
            'wait attribute identifier value' => [
                'action' => $actionParser->parse('wait $".selector".attribute'),
            ],
            'wait browser size value' => [
                'action' => $actionParser->parse('wait $browser.size'),
            ],
            'wait page title value' => [
                'action' => $actionParser->parse('wait $page.title'),
            ],
            'wait page url value' => [
                'action' => $actionParser->parse('wait $page.url'),
            ],
            'wait data parameter value' => [
                'action' => $actionParser->parse('wait $data.key'),
            ],
            'wait environment parameter value' => [
                'action' => $actionParser->parse('wait $env.KEY'),
            ],
            'set; literal value' => [
                'action' => $actionParser->parse('set $".selector" to "value"'),
            ],
            'set; element identifier value' => [
                'action' => $actionParser->parse('set $".selector" to $".value"'),
            ],
            'set; descendant dom identifier value' => [
                'action' => $actionParser->parse('set $".selector" to $"{{ $".parent" }} .child"'),
            ],
            'set; descendant dom identifier identifier and value' => [
                'action' => $actionParser->parse('set $"{{ $".parent" }} .child" to $"{{ $".parent" }} .child"'),
            ],
            'set; attribute identifier value' => [
                'action' => $actionParser->parse('set $".selector" to $".element".attribute'),
            ],
            'set; browser size value' => [
                'action' => $actionParser->parse('set $".selector" to $browser.size'),
            ],
            'set; page title value' => [
                'action' => $actionParser->parse('set $".selector" to $page.title'),
            ],
            'set; page url value' => [
                'action' => $actionParser->parse('set $".selector" to $page.url'),
            ],
            'set; data parameter value' => [
                'action' => $actionParser->parse('set $".selector" to $data.key'),
            ],
            'set; environment parameter value' => [
                'action' => $actionParser->parse('set $".selector" to $env.KEY'),
            ],
            'reload, no args' => [
                'action' => $actionParser->parse('reload'),
            ],
            'back, no args' => [
                'action' => $actionParser->parse('back'),
            ],
            'forward, no args' => [
                'action' => $actionParser->parse('forward'),
            ],
            'reload, with args' => [
                'action' => $actionParser->parse('reload arg1 arg2'),
            ],
            'back, with args' => [
                'action' => $actionParser->parse('back arg1 arg2'),
            ],
            'forward, with args' => [
                'action' => $actionParser->parse('forward arg1 arg2'),
            ],
        ];
    }

    /**
     * @dataProvider invalidInteractionActionDataProvider
     * @dataProvider invalidInputActionDataProvider
     * @dataProvider invalidWaitActionDataProvider
     * @dataProvider invalidActionTypeDataProvider
     */
    public function testValidateNotValid(ActionInterface $action, InvalidResultInterface $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->actionValidator->validate($action));
    }

    public function invalidInteractionActionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'interaction action: identifier invalid (element reference)' => [
                'action' => $actionParser->parse('click $elements.element_name'),
                'expectedResult' => new InvalidResult(
                    $actionParser->parse('click $elements.element_name'),
                    ResultType::ACTION,
                    ActionValidator::REASON_INVALID_IDENTIFIER
                ),
            ],
            'interaction action: identifier invalid (attribute reference)' => [
                'action' => $actionParser->parse('click $elements.element_name.attribute_name'),
                'expectedResult' => new InvalidResult(
                    $actionParser->parse('click $elements.element_name.attribute_name'),
                    ResultType::ACTION,
                    ActionValidator::REASON_INVALID_IDENTIFIER
                ),
            ],
            'interaction action: identifier invalid (attribute identifier)' => [
                'action' => $actionParser->parse('click $".selector".attribute_name'),
                'expectedResult' => new InvalidResult(
                    $actionParser->parse('click $".selector".attribute_name'),
                    ResultType::ACTION,
                    ActionValidator::REASON_INVALID_IDENTIFIER
                ),
            ],
            'interaction action: identifier invalid (page element reference)' => [
                'action' => $actionParser->parse('click $page_import_name.elements.element_name'),
                'expectedResult' => new InvalidResult(
                    $actionParser->parse('click $page_import_name.elements.element_name'),
                    ResultType::ACTION,
                    ActionValidator::REASON_INVALID_IDENTIFIER
                ),
            ],
            'interaction action: identifier invalid (browser property)' => [
                'action' => $actionParser->parse('click $browser.size'),
                'expectedResult' => new InvalidResult(
                    $actionParser->parse('click $browser.size'),
                    ResultType::ACTION,
                    ActionValidator::REASON_INVALID_IDENTIFIER
                ),
            ],
            'interaction action: identifier invalid (page property)' => [
                'action' => $actionParser->parse('click $page.url'),
                'expectedResult' => new InvalidResult(
                    $actionParser->parse('click $page.url'),
                    ResultType::ACTION,
                    ActionValidator::REASON_INVALID_IDENTIFIER
                ),
            ],
            'interaction action: identifier invalid (data parameter)' => [
                'action' => $actionParser->parse('click $data.key'),
                'expectedResult' => new InvalidResult(
                    $actionParser->parse('click $data.key'),
                    ResultType::ACTION,
                    ActionValidator::REASON_INVALID_IDENTIFIER
                ),
            ],
            'interaction action: identifier invalid (environment parameter)' => [
                'action' => $actionParser->parse('click $env.KEY'),
                'expectedResult' => new InvalidResult(
                    $actionParser->parse('click $env.KEY'),
                    ResultType::ACTION,
                    ActionValidator::REASON_INVALID_IDENTIFIER
                ),
            ],
            'interaction action: identifier invalid (quoted literal)' => [
                'action' => new InteractionAction('click "selector"', 'click', '"selector"', '"selector"'),
                'expectedResult' => new InvalidResult(
                    new InteractionAction('click "selector"', 'click', '"selector"', '"selector"'),
                    ResultType::ACTION,
                    ActionValidator::REASON_INVALID_IDENTIFIER
                ),
            ],
            'interaction action: identifier invalid (literal)' => [
                'action' => new InteractionAction('click selector', 'click', 'selector', 'selector'),
                'expectedResult' => new InvalidResult(
                    new InteractionAction('click selector', 'click', 'selector', 'selector'),
                    ResultType::ACTION,
                    ActionValidator::REASON_INVALID_IDENTIFIER
                ),
            ],
        ];
    }

    public function invalidInputActionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'input action: identifier invalid (element reference)' => [
                'action' => $actionParser->parse('set $elements.element_name to "value"'),
                'expectedResult' => new InvalidResult(
                    $actionParser->parse('set $elements.element_name to "value"'),
                    ResultType::ACTION,
                    ActionValidator::REASON_INVALID_IDENTIFIER
                ),
            ],
            'input action: identifier invalid (attribute reference)' => [
                'action' => $actionParser->parse('set $elements.element_name.attribute_name to "value"'),
                'expectedResult' => new InvalidResult(
                    $actionParser->parse('set $elements.element_name.attribute_name to "value"'),
                    ResultType::ACTION,
                    ActionValidator::REASON_INVALID_IDENTIFIER
                ),
            ],
            'input action: identifier invalid (attribute identifier)' => [
                'action' => $actionParser->parse('set $".selector".attribute_name to "value"'),
                'expectedResult' => new InvalidResult(
                    $actionParser->parse('set $".selector".attribute_name to "value"'),
                    ResultType::ACTION,
                    ActionValidator::REASON_INVALID_IDENTIFIER
                ),
            ],
            'input action: identifier invalid (page element reference)' => [
                'action' => $actionParser->parse('set $page_import_name.elements.element_name to "value"'),
                'expectedResult' => new InvalidResult(
                    $actionParser->parse('set $page_import_name.elements.element_name to "value"'),
                    ResultType::ACTION,
                    ActionValidator::REASON_INVALID_IDENTIFIER
                ),
            ],
            'input action: identifier invalid (browser property)' => [
                'action' => $actionParser->parse('set $browser.size to "value"'),
                'expectedResult' => new InvalidResult(
                    $actionParser->parse('set $browser.size to "value"'),
                    ResultType::ACTION,
                    ActionValidator::REASON_INVALID_IDENTIFIER
                ),
            ],
            'input action: identifier invalid (page property)' => [
                'action' => $actionParser->parse('set $page.url to "value"'),
                'expectedResult' => new InvalidResult(
                    $actionParser->parse('set $page.url to "value"'),
                    ResultType::ACTION,
                    ActionValidator::REASON_INVALID_IDENTIFIER
                ),
            ],
            'input action: identifier invalid (data parameter)' => [
                'action' => $actionParser->parse('set $data.key to "value"'),
                'expectedResult' => new InvalidResult(
                    $actionParser->parse('set $data.key to "value"'),
                    ResultType::ACTION,
                    ActionValidator::REASON_INVALID_IDENTIFIER
                ),
            ],
            'input action: identifier invalid (environment parameter)' => [
                'action' => $actionParser->parse('set $env.KEY to "value"'),
                'expectedResult' => new InvalidResult(
                    $actionParser->parse('set $env.KEY to "value"'),
                    ResultType::ACTION,
                    ActionValidator::REASON_INVALID_IDENTIFIER
                ),
            ],
            'input action: identifier invalid (quoted literal)' => [
                'action' => new InputAction(
                    'set "selector" to "value"',
                    '"selector" to "value"',
                    '"selector"',
                    '"value"'
                ),
                'expectedResult' => new InvalidResult(
                    new InputAction(
                        'set "selector" to "value"',
                        '"selector" to "value"',
                        '"selector"',
                        '"value"'
                    ),
                    ResultType::ACTION,
                    ActionValidator::REASON_INVALID_IDENTIFIER
                ),
            ],
            'input action: value invalid (unquoted value)' => [
                'action' => $actionParser->parse('set $".selector" to $page.address'),
                'expectedResult' => new InvalidResult(
                    $actionParser->parse('set $".selector" to $page.address'),
                    ResultType::ACTION,
                    ActionValidator::REASON_INVALID_VALUE,
                    new InvalidResult(
                        '$page.address',
                        ResultType::VALUE,
                        ValueValidator::REASON_PROPERTY_INVALID
                    )
                ),
            ],
        ];
    }

    public function invalidWaitActionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'wait action: value invalid (element reference)' => [
                'action' => $actionParser->parse('wait $elements.element_name'),
                'expectedResult' => new InvalidResult(
                    $actionParser->parse('wait $elements.element_name'),
                    ResultType::ACTION,
                    ActionValidator::REASON_INVALID_VALUE,
                    new InvalidResult(
                        '$elements.element_name',
                        ResultType::VALUE,
                        ValueValidator::REASON_INVALID
                    )
                ),
            ],
        ];
    }

    public function invalidActionTypeDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'invalid action type' => [
                'action' => $actionParser->parse('invalid'),
                'expectedResult' => new InvalidResult(
                    $actionParser->parse('invalid'),
                    ResultType::ACTION,
                    ActionValidator::REASON_INVALID_TYPE
                ),
            ],
        ];
    }
}
