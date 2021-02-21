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

namespace Longman\TelegramBot\Commands\UserCommands;

use LitEmoji\LitEmoji;
use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\Commands\UserCommand;
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
use Longman\TelegramBot\Entities\UserProfilePhotos;
use Longman\TelegramBot\DB;
use Longman\TelegramBot\Entities\Chat;
use Longman\TelegramBot\Entities\PhotoSize;

/**
 * User "/help" command
 *
 * Command that lists all available commands and displays them in User and Admin sections.
 */
class ProfileCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'profile';

    /**
     * @var string
     */
    protected $description = 'Show Profile User';

    /**
     * @var string
     */
    protected $usage = '/profile';

    /**
     * @var string
     */
    protected $version = '0.1.0';

    /**
     * @var bool
     */
    protected $private_only = true;

    /**
     * @inheritdoc
     */
    public function execute(): ServerResponse
    {
        $message = $this->getMessage();

        $chat_id = $message->getChat()->getId();
        $command = $message->getCommand();
        $text    = trim($message->getText(true));

        $result = User::getUserData($chat_id);

        if (is_array($result)) {
            $result['id']       = $result['chat_id'];
            $result['username'] = $result['chat_username'];
            $chat               = new Chat($result);

            $user_id    = $result['id'];
            $created_at = $result['chat_created_at'];
            $updated_at = $result['chat_updated_at'];
            $old_id     = $result['old_id'];
        }


        Helpers::jsonDebug($result);

        $text = 'User ID: ' . $user_id . PHP_EOL;
        $text .= 'Name: ' . $chat->getFirstName() . ' ' . $chat->getLastName() . PHP_EOL;
        $username = $chat->getUsername();
        if ($username !== null && $username !== '') {
            $text .= 'Username: @' . $username . PHP_EOL;
        }
        $text .= 'Join : ' . $created_at . PHP_EOL;
        $text .= 'Last activity: ' . $updated_at . PHP_EOL;



        return Request::sendMessage([
            'text' => LitEmoji::encodeUnicode($text),
            'chat_id' => $chat_id,
            'parse_mode' => 'markdown',
            'reply_markup'             => new InlineKeyboard([
                ['text' => LitEmoji::encodeUnicode(':atm: Saldo'), 'callback_data' => 'command=profile&a=balance'],
                ['text' => LitEmoji::encodeUnicode(':radio_button: Edit'), 'callback_data' => 'command=profile&a=edit'],
            ], [
                ['text' => LitEmoji::encodeUnicode(':radio_button: Referral'), 'callback_data' => 'command=profile&a=refferal'],
            ]),
        ]);
    }
}
