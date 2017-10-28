<?php

namespace BotMan\Studio\Testing;

use PHPUnit\Framework\Assert as PHPUnit;
use BotMan\BotMan\Messages\Outgoing\Question;

/**
 * Class QuestionTester.
 */
class QuestionTester
{
    protected $question;

    public function __construct(Question $question)
    {
        $this->question = $question->toArray();
    }

    public function assertText($text)
    {
        PHPUnit::assertSame($text, $this->question['text']);

        return $this;
    }

    public function assertTextIsNot($text)
    {
        PHPUnit::assertNotSame($text, $this->question['text']);

        return $this;
    }

    public function assertTextIn(array $haystack)
    {
        PHPUnit::assertTrue(in_array($this->question['text'], $haystack));

        return $this;
    }

    public function assertTextNotIn(array $haystack)
    {
        PHPUnit::assertFalse(in_array($this->question['text'], $haystack));

        return $this;
    }

    public function assertFallback($fallback)
    {
        PHPUnit::assertSame($fallback, $this->question['fallback']);

        return $this;
    }

    public function assertFallbackIsNot($fallback)
    {
        PHPUnit::assertNotSame($fallback, $this->question['fallback']);

        return $this;
    }

    public function assertCallbackId($callback)
    {
        PHPUnit::assertSame($callback, $this->question['callback_id']);

        return $this;
    }

    public function assertCallbackIdIsNot($callback)
    {
        PHPUnit::assertNotSame($callback, $this->question['callback_id']);

        return $this;
    }

    public function assertButtonCount($count)
    {
        PHPUnit::assertCount($count, $this->question['actions']);

        return $this;
    }

    public function assertButton($index, $closure)
    {
        $button = $this->question['actions'][$index];
        call_user_func($closure, new ButtonTester($button));

        return $this;
    }

    public function assertFirstButton($closure)
    {
        return $this->assertButton(0, $closure);
    }

    public function assertLastButton($closure)
    {
        $last_index = count($this->question['actions']) - 1;

        return $this->assertButton($last_index, $closure);
    }
}