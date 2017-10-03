<?php

namespace BotMan\Studio\Testing;

use BotMan\BotMan\BotMan;
use PHPUnit\Framework\Assert as PHPUnit;
use BotMan\BotMan\Drivers\Tests\FakeDriver;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use BotMan\BotMan\Messages\Outgoing\OutgoingMessage;
use BotMan\BotMan\Messages\Attachments\Audio;
use BotMan\BotMan\Messages\Attachments\File;
use BotMan\BotMan\Messages\Attachments\Image;
use BotMan\BotMan\Messages\Attachments\Location;
use BotMan\BotMan\Messages\Attachments\Video;

use Illuminate\Support\Collection;

/**
 * Class BotManTester.
 */
class BotManTester
{
    /** @var BotMan */
    private $bot;

    /** @var FakeDriver */
    private $driver;

    /** @var string */
    private $user_id = '1';

    /** @var string */
    private $channel = '#botman';

    /**
     * BotManTester constructor.
     *
     * @param BotMan $bot
     * @param FakeDriver $driver
     */
    public function __construct(BotMan $bot, FakeDriver $driver)
    {
        $this->bot = $bot;
        $this->driver = $driver;
    }

    protected function listen()
    {
        $this->bot->listen();
        $this->driver->isInteractiveMessageReply = false;
    }

    /**
     * @return OutgoingMessage
     */
    protected function getReply()
    {
        $message = $this->driver->getBotMessage();
        return $message;
    }

    /**
     * @param $driver
     * @return $this
     */
    public function setDriver($driver)
    {
        $this->driver->setName($driver::DRIVER_NAME);
        return $this;
    }

    /**
     * @param Array $user_info
     * @return $this
     */
    public function setUser($user_info)
    {
        $this->user_id = $user_info['id'] ?? $this->user_id;
        $this->driver->setUser($user_info);
        return $this;
    }

    /**
     * @param $message
     * @return $this
     */
    public function receives($message)
    {
        return $this->receivesRaw(new IncomingMessage($message, $this->user_id, $this->channel));
    }

    /**
     * @param IncomingMessage $message
     * @return $this
     */
    public function receivesRaw($message)
    {
        $this->driver->messages = [$message];

        $this->driver->resetBotMessages();
        $this->listen();

        return $this;
    }

    /**
     * @param $message
     * @return BotManTester
     */
    public function receivesInteractiveMessage($message)
    {
        $this->driver->isInteractiveMessageReply = true;

        return $this->receives($message);
    }

    /**
     * @param $latitude
     * @param $longitude
     * @return $this
     */
    public function receivesLocation($latitude = 24, $longitude = 57)
    {
        $message = new IncomingMessage(Location::PATTERN, $this->user_id, $this->channel);
        $message->setLocation( new Location($latitude, $longitude, null));

        return $this->receivesRaw($message);
    }

    /**
     * @param Array $urls
     * @return $this
     */
    public function receivesImages(Array $urls = null)
    {
        if(is_null($urls)){
            $images = [new Image('https://via.placeholder.com/350x150')]; // TODO : default image
        }
        if(is_array($urls)){
            $images = Collection::make($urls)->map(function($url){
                return new Image(($url));
            })->toArray();
        }
        $message = new IncomingMessage(Image::PATTERN, $this->user_id, $this->channel);
        $message->setImages($images);

        return $this->receivesRaw($message);
    }

    /**
     * @param Array $urls
     * @return $this
     */
    public function receivesAudio(Array $urls = null)
    {
        if(is_null($urls)){
            $audio = [new Audio('https://www.youtube.com/watch?v=4zzSw-0IShE')]; // TODO : default audio
        }
        if(is_array($urls)){
            $audio = Collection::make($urls)->map(function($url){
                return new Audio(($url));
            })->toArray();
        }
        $message = new IncomingMessage(Audio::PATTERN, $this->user_id, $this->channel);
        $message->setAudio($audio);

        return $this->receivesRaw($message);
    }

    /**
     * @param $message
     * @return $this
     */
    public function receivesVideos(Array $urls = null)
    {
        if(is_null($urls)){
            $videos = [new Video('https://www.youtube.com/watch?v=4zzSw-0IShE')]; // TODO : default video
        }
        if(is_array($urls)){
            $videos = Collection::make($urls)->map(function($url){
                return new Video(($url));
            })->toArray();
        }
        $message = new IncomingMessage(Video::PATTERN, $this->user_id, $this->channel);
        $message->setVideos($videos);

        return $this->receivesRaw($message);
    }

    /**
     * @param $message
     * @return $this
     */
    public function receivesFiles(Array $urls = null)
    {
        if(is_null($urls)){
            $files = [new File('https://www.youtube.com/watch?v=4zzSw-0IShE')]; // TODO : default video
        }
        if(is_array($urls)){
            $files = Collection::make($urls)->map(function($url){
                return new File(($url));
            })->toArray();
        }
        $message = new IncomingMessage(File::PATTERN, $this->user_id, $this->channel);
        $message->setFiles($files);

        return $this->receivesRaw($message);
    }

    /**
     * @param $name
     * @param $payload
     * @return $this
     */
    public function receivesEvent($name, $payload = null) {
        $this->driver->setEventName($name);
        $this->driver->setEventPayload($payload);
        //$this->driver->hasMatchingEvent();

        return $this->receives(new IncomingMessage('', $this->user_id, $this->channel));
    }

    /**
     * @param $message
     * @return $this
     */
    public function assertReply($message)
    {
        $reply = $this->getReply();
        if ($reply instanceof OutgoingMessage) {
            PHPUnit::assertSame($message, $reply->getText());
        } else {
            PHPUnit::assertEquals($message, $reply);
        }

        return $this;
    }

    /**
     * Assert that there are specific multiple replies.
     *
     * @param array $expectedMessages
     * @return $this
     */
    public function assertReplies($expectedMessages)
    {
        $actualMessages = $this->getMessages();

        foreach ($actualMessages as $key => $actualMessage) {
            if ($actualMessage instanceof OutgoingMessage) {
                PHPUnit::assertSame($expectedMessages[$key], $actualMessage->getText());
            } else {
                PHPUnit::assertEquals($expectedMessages[$key], $actualMessage);
            }
        }

        return $this;
    }

    /**
     * @param $text
     * @return $this
     */
    public function assertReplyIsNot($text)
    {
        PHPUnit::assertNotSame($this->getReply()->getText(), $text);

        return $this;
    }

    /**
     * @param array $haystack
     * @return $this
     */
    public function assertReplyIn(array $haystack)
    {
        PHPUnit::assertTrue(in_array($this->getReply()->getText(), $haystack));

        return $this;
    }

    /**
     * @param array $haystack
     * @return $this
     */
    public function assertReplyNotIn(array $haystack)
    {
        PHPUnit::assertFalse(in_array($this->getReply()->getText(), $haystack));

        return $this;
    }

    /**
     * @param null $text
     * @return $this
     */
    public function assertQuestion($text = null)
    {
        /** @var Question $question */
        $question = $this->getReply();
        PHPUnit::assertInstanceOf(Question::class, $question);

        if (! is_null($text)) {
            PHPUnit::assertSame($text, $question->getText());
        }

        return $this;
    }

    /**
     * @return Question[]|\string[]
     */
    public function getMessages()
    {
        return $this->driver->getBotMessages();
    }

    /**
     * @param string $template
     * @return $this
     */
    public function assertTemplate(string $template)
    {
        PHPUnit::assertInstanceOf($template, $this->getReply());
        return $this;
    }

    /**
     * @param array $payload
     * @return $this
     */
    public function assertPayload(array $payload)
    {
        $messages = $this->getMessages();

        /** @var Question $question */
        $message = array_pop($messages);
        PHPUnit::assertEquals($message->toArray(), $payload);

        return $this;
    }
}
