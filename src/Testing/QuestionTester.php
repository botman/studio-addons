<?php

namespace BotMan\Studio\Testing;

use BotMan\BotMan\Messages\Outgoing\Question;
use PHPUnit\Framework\Assert as PHPUnit;

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
    }

    public function assertTextIsNot($text)
    {
        PHPUnit::assertNotSame($text, $this->question['text']);
    }

    public function assertFallback($fallback)
    {
        PHPUnit::assertSame($fallback, $this->question['fallback']);
    }

    public function assertFallbackIsNot($fallback)
    {
        PHPUnit::assertNotSame($fallback, $this->question['fallback']);
    }

    public function assertCallbackId($callback)
    {
        PHPUnit::assertSame($callback, $this->question['callback_id']);
    }

    public function assertCallbackIdIsNot($callback)
    {
        PHPUnit::assertNotSame($callback, $this->question['callback_id']);
    }

    public function assertButtonCount($count)
    {
        PHPUnit::assertCount($count, $this->question['actions']);
    }

    public function assertButton($index, $closure)
    {
        $button = $this->question['actions'][$index];
        call_user_func($closure, new ButtonTester($button));
    }

    public function assertHasButton($text)
    {
        PHPUnit::assertTrue(in_array($text, $this->pluck('text')));
    }

    public function assertHasNotButton($text)
    {
        PHPUnit::assertFalse(in_array($text, $this->pluck('text')));
    }

    public function assertHasValue($value)
    {
        PHPUnit::assertTrue(in_array($value, $this->pluck('value')));
    }

    public function assertHasNotValue($value)
    {
        PHPUnit::assertFalse(in_array($value, $this->pluck('value')));
    }

    public function assertButtons(array $buttons)
    {
        sort($buttons);

        PHPUnit::assertEquals(array_sort($this->pluck('text')), $buttons);
    }


    private function pluck ($type) {
        return collect($this->question['actions'])
            ->pluck($type)
            ->toArray();
    }
}