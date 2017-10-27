<?php

namespace BotMan\Studio\Testing;

use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\Drivers\Facebook\Extensions\Element;
use PHPUnit\Framework\Assert as PHPUnit;

/**
 * Class QuestionTester.
 */
class ElementButtonTester
{

    protected $element;

    protected $match = true;

    public function __construct(array $element)
    {
        $this->element = $element;
    }

    public function assertButtonCount($count)
    {
        $this->setMatch($count == count($this->element['buttons']));

        return $this;
    }

    public function assertHasButton(array $data) {
        $button_matches = false;

        foreach ($this->element['buttons'] as $button) {
            if($button_matches=$this->checkButton($button, $data)) {
                break;
            }

        }
        $this->setMatch($button_matches);
    }

    public function assertHasNotButton(array $data) {
        $button_matches = false;

        foreach ($this->element['buttons'] as $button) {
            if($button_matches=$this->checkButton($button, $data)) {
                break;
            }

        }
        $this->setMatch(!$button_matches);
    }

    public function setMatch(bool $match) {
        if(!$match) {
            $this->match = $match;
        }
    }

    public function getMatch() {
        return $this->match;
    }

    private function checkButton($button, $data) {
        $attributes_matches = true;

        foreach ($data as $key => $value) {

            if(array_has($button, $key) && array_get($button, $key) == $value) {
                continue;
            }

            $attributes_matches = false;
            break;
        }

        return ($attributes_matches) ? true : false;
    }
}