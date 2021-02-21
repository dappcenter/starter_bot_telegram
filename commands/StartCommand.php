<?php

/**
 * This file is part of the PHP Telegram Support Bot.
 *
 * (c) PHP Telegram Bot Team (https://github.com/php-telegram-bot)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use LitEmoji\LitEmoji;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\KeyboardButton;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Entities\Entity;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Request;
use TelegramBot\SupportBot\Helpers;
use TelegramBot\SupportBot\Models\User;

/**
 * System "/start" command
 */
class StartCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'start';

    /**
     * @var string
     */
    protected $description = 'Show the PHP Telegram Support Bot start';

    /**
     * @var string
     */
    protected $version = '0.1.0';

    /**
     * @var string
     */
    protected $usage = '/start';

    /**
     * @var bool
     */
    protected $private_only = true;

    /**
     * @inheritdoc
     * @throws TelegramException
     */
    public function execute(): ServerResponse
    {
        if ('activate' === $this->getMessage()->getText(true)) {
            return $this->getTelegram()->executeCommand('activate');
        }

        if ('rules' === $this->getMessage()->getText(true)) {
            return $this->getTelegram()->executeCommand('rules');
        }

        return $this->privateExecute();
    }

    public function privateExecute(): ServerResponse
    {
        $message     = $this->getMessage();
        $chat_id = $message->getChat()->getId();
        $command_str = trim($message->getText(true));

        $greeting = Helpers::greeting();
        $username_bot = getenv('TG_BOTNAME');
        $out_text = "{$greeting} {$message->getChat()->tryMention(true)} ! \n\n";
        $out_text .= "Selamat Datang di {$username_bot}";

        $reply_markup = (new Keyboard(['Top up', 'Transaksi', 'Profile'],  ['More', 'Bantuan', 'Withdraw']))
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->setSelective(true);

        return Request::sendMessage([
            'text' => LitEmoji::encodeUnicode($out_text),
            'chat_id' => $chat_id,
            'parse_mode' => 'markdown',
            'reply_markup' => $reply_markup
        ]);
    }
}
