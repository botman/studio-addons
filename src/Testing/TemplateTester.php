<?php

namespace BotMan\Studio\Testing;

use BotMan\BotMan\Messages\Outgoing\Question;
use function GuzzleHttp\default_ca_bundle;
use PHPUnit\Framework\Assert as PHPUnit;

/**
 * Class QuestionTester.
 */
class TemplateTester
{

    protected $template;

    public function __construct($template)
    {
        $this->template = $template->toArray()['attachment'];
    }

    public function assertElementCount($count)
    {
        PHPUnit::assertCount($count, $this->template['payload']['elements']);

        return $this;
    }

    public function assertElement($index, $closure)
    {

    }

    public function assertHasElement(array $data, $closure = null) {
        $element_matches = false;

        foreach ($this->template['payload']['elements'] as $element) {
            if($element_matches = $this->checkElement($element, $data, $closure)){
                break;
            }
        }

        PHPUnit::assertTrue($element_matches, 'Failed asserting that template has given element');

        return $this;
    }

    public function assertHasNotElement(array $data, $closure = null) {
        $element_matches = false;

        foreach ($this->template['payload']['elements'] as $element) {
            if($element_matches = $this->checkElement($element, $data, $closure)){
                break;
            }
        }

        PHPUnit::assertFalse($element_matches, 'Failed asserting that template does not have given element');

        return $this;
    }

//    public function assertElement($index, array $data, $closure = null){
//        $element = $this->template['payload']['elements'][$index];
//        $element_matches = $this->checkElement($element, $data, $closure);
//
//        PHPUnit::assertTrue($element_matches, "Failed asserting that templates element with index {$index}");
//
//        return $this;
//    }

    public function __call($name, $arguments)
    {
        $kebab_name = kebab_case($name);

        if(starts_with($kebab_name, 'assert') && ends_with($kebab_name, 'element')){
            switch(explode('-', $kebab_name)[1]) {
                case 'first':
                    $index = 0;
                    break;
                case 'second':
                    $index = 1;
                    break;
                case 'third':
                    $index = 2;
                    break;
                case 'forth':
                    $index = 3;
                    break;
                case 'fifth':
                    $index = 4;
                    break;
                case 'sixth':
                    $index = 5;
                    break;
                case 'seventh':
                    $index = 6;
                    break;
                case 'eighth':
                    $index = 7;
                    break;
                case 'ninth':
                    $index = 8;
                    break;
                case 'last':
                    $index = count($this->template['payload']['elements']) - 1;
                    break;
                default:
                    $index = null;
            }
            if($index) {
                return call_user_func([$this, 'assertElement'], $index, $arguments[0], $arguments[1] ?? null);
            }
        }

        throw new \Exception("There is no method {$name}");
    }

    private function checkElement($actual, $data, $closure) {
        $attributes_matches = true;

        foreach ($data as $key => $value) {
            if(array_has($actual, $key) && array_get($actual, $key) == $value) {
                continue;
            }
            $attributes_matches = false;
            break;
        }

        $buttons_matches = true;

        if (is_callable($closure)) {
            $elementButtonTester = new ElementButtonTester($actual);
            call_user_func($closure, $elementButtonTester);
            $buttons_matches = $elementButtonTester->getMatch();
        }

        return ($attributes_matches && $buttons_matches) ? true : false;
    }
}