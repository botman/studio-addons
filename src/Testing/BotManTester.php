<?php

namespace BotMan\Studio\Testing;

use BotMan\BotMan\BotMan;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Assert as PHPUnit;
use BotMan\BotMan\Drivers\Tests\FakeDriver;
use BotMan\BotMan\Messages\Attachments\File;
use BotMan\BotMan\Messages\Attachments\Audio;
use BotMan\BotMan\Messages\Attachments\Image;
use BotMan\BotMan\Messages\Attachments\Video;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Messages\Attachments\Location;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use BotMan\BotMan\Messages\Outgoing\OutgoingMessage;

/**
 * Class BotManTester.
 */
class BotManTester
{
    /** @var BotMan */
    private $bot;

    /** @var FakeDriver */
    private $driver;

    /** @var array */
    private $botMessages = [];

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
        return array_shift($this->botMessages);
    }

    /**
     * @return Question[]|string[]|Template[]
     */
    public function getMessages()
    {
        return $this->driver->getBotMessages();
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
     * @param array $user_info
     * @return $this
     */
    public function setUser($user_info)
    {
        $this->user_id = $user_info['id'] ?? $this->user_id;
        $this->driver->setUser($user_info);

        return $this;
    }

    /**
     * @param IncomingMessage $message
     * @return $this
     */
    public function receivesIncomingMessage($message)
    {
        $this->driver->messages = [$message];

        $this->driver->resetBotMessages();
        $this->listen();

        $this->botMessages = $this->getMessages();

        return $this;
    }

    /**
     * @param string $message
     * @return $this
     */
    public function receives($message)
    {
        return $this->receivesIncomingMessage(new IncomingMessage($message, $this->user_id, $this->channel));
    }

    /**
     * @param string $message
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
        $message->setLocation(new Location($latitude, $longitude, null));

        return $this->receivesIncomingMessage($message);
    }

    /**
     * @param array $urls
     * @return $this
     */
    public function receivesImages(array $urls = null)
    {
        if (is_null($urls)) {
            $images = [new Image('https://via.placeholder.com/350x150')];
        } else {
            $images = Collection::make($urls)->map(function ($url) {
                return new Image(($url));
            })->toArray();
        }
        $message = new IncomingMessage(Image::PATTERN, $this->user_id, $this->channel);
        $message->setImages($images);

        return $this->receivesIncomingMessage($message);
    }

    /**
     * @param array $urls
     * @return $this
     */
    public function receivesAudio(array $urls = null)
    {
        if (is_null($urls)) {
            $audio = [new Audio('https://www.youtube.com/watch?v=4zzSw-0IShE')];
        }
        if (is_array($urls)) {
            $audio = Collection::make($urls)->map(function ($url) {
                return new Audio(($url));
            })->toArray();
        }
        $message = new IncomingMessage(Audio::PATTERN, $this->user_id, $this->channel);
        $message->setAudio($audio);

        return $this->receivesIncomingMessage($message);
    }

    /**
     * @param array|null $urls
     * @return $this
     */
    public function receivesVideos(array $urls = null)
    {
        if (is_null($urls)) {
            $videos = [new Video('https://www.youtube.com/watch?v=4zzSw-0IShE')];
        } else {
            $videos = Collection::make($urls)->map(function ($url) {
                return new Video(($url));
            })->toArray();
        }
        $message = new IncomingMessage(Video::PATTERN, $this->user_id, $this->channel);
        $message->setVideos($videos);

        return $this->receivesIncomingMessage($message);
    }

    /**
     * @param array|null $urls
     * @return $this
     */
    public function receivesFiles(array $urls = null)
    {
        if (is_null($urls)) {
            $files = [new File('https://www.youtube.com/watch?v=4zzSw-0IShE')];
        } else {
            $files = Collection::make($urls)->map(function ($url) {
                return new File(($url));
            })->toArray();
        }
        $message = new IncomingMessage(File::PATTERN, $this->user_id, $this->channel);
        $message->setFiles($files);

        return $this->receivesIncomingMessage($message);
    }

    /**
     * @param $name
     * @param $payload
     * @return $this
     */
    public function receivesEvent($name, $payload = null)
    {
        $this->driver->setEventName($name);
        $this->driver->setEventPayload($payload);

        return $this->receivesIncomingMessage(new IncomingMessage('', $this->user_id, $this->channel));
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
     * @return $this
     */
    public function assertReplyNothing()
    {
        PHPUnit::assertNull($this->getReply());

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
     * @param string $template
     * @param bool $strict
     * @return $this
     */
    public function assertTemplate($template, $strict = false)
    {
        $message = $this->getReply();

        if ($strict) {
            PHPUnit::assertEquals($template, $message);
        } else {
            PHPUnit::assertInstanceOf($template, $message);
        }

        return $this;
    }

    /**
     * @param OutgoingMessage $message
     * @return $this
     */
    public function assertOutgoingMessage($message)
    {
        PHPUnit::assertSame($message, $this->getReply());

        return $this;
    }

    /**
     * @param int $times
     * @return $this
     */
    public function skipReply($times = 1)
    {
        foreach (range(1, $times) as $time) {
            $this->getReply();
        }

        return $this;
    }
}
