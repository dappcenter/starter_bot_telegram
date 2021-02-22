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

/**
 * User "/help" command
 *
 * Command that lists all available commands and displays them in User and Admin sections.
 */
class HelpCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'help';

    /**
     * @var string
     */
    protected $description = 'Show bot commands help';

    /**
     * @var string
     */
    protected $usage = '/help or /help <command>';

    /**
     * @var string
     */
    protected $version = '0.1.0';

    /**
     * @var bool
     */
    protected $private_only = true;

    public static $menus = [
        '1' => 'command=help&a=1',
        '2' => 'command=help&a=2',
        '3' => 'command=help&a=3',
        '4' => 'command=help&a=4',
        '5' => 'command=help&a=5',
    ];

    public static function handleCallbackQuery(CallbackQuery $callback_query, array $callback_data): ServerResponse
    {

        if ('1' === $callback_data['a'] ?? null) {
            $message         = $callback_query->getMessage();
            $chat_id         = $message->getChat()->getId();
            $clicked_user_id = $callback_query->getFrom()->getId();

            $text = 'https://bantuan.com/' . PHP_EOL;


            return Request::editMessageText([
                'text' => LitEmoji::encodeUnicode($text),
                'chat_id'      => $chat_id,
                'message_id'   => $message->getMessageId(),
                'parse_mode' => 'Markdown',
                'reply_markup'             => new InlineKeyboard([
                    ['text' => LitEmoji::encodeUnicode(':radio_button: Back'), 'callback_data' => 'command=help&a=back'],
                ]),
            ]);
        }

        if ('2' === $callback_data['a'] ?? null) {
            $message         = $callback_query->getMessage();
            $chat_id         = $message->getChat()->getId();
            $clicked_user_id = $callback_query->getFrom()->getId();

            $text = 'https://bantuan.com/' . PHP_EOL;


            return Request::editMessageText([
                'text' => LitEmoji::encodeUnicode($text),
                'chat_id'      => $chat_id,
                'message_id'   => $message->getMessageId(),
                'parse_mode' => 'Markdown',
                'reply_markup'             => new InlineKeyboard([
                    ['text' => LitEmoji::encodeUnicode(':radio_button: Back'), 'callback_data' => 'command=help&a=back'],
                ]),
            ]);
        }

        if ('3' === $callback_data['a'] ?? null) {
            $message         = $callback_query->getMessage();
            $chat_id         = $message->getChat()->getId();
            $clicked_user_id = $callback_query->getFrom()->getId();

            $text = 'https://bantuan.com/' . PHP_EOL;


            return Request::editMessageText([
                'text' => LitEmoji::encodeUnicode($text),
                'chat_id'      => $chat_id,
                'message_id'   => $message->getMessageId(),
                'parse_mode' => 'Markdown',
                'reply_markup'             => new InlineKeyboard([
                    ['text' => LitEmoji::encodeUnicode(':radio_button: Back'), 'callback_data' => 'command=help&a=back'],
                ]),
            ]);
        }

        if ('4' === $callback_data['a'] ?? null) {
            $message         = $callback_query->getMessage();
            $chat_id         = $message->getChat()->getId();
            $clicked_user_id = $callback_query->getFrom()->getId();

            $text = 'https://bantuan.com/' . PHP_EOL;


            return Request::editMessageText([
                'text' => LitEmoji::encodeUnicode($text),
                'chat_id'      => $chat_id,
                'message_id'   => $message->getMessageId(),
                'parse_mode' => 'Markdown',
                'reply_markup'             => new InlineKeyboard([
                    ['text' => LitEmoji::encodeUnicode(':radio_button: Back'), 'callback_data' => 'command=help&a=back'],
                ]),
            ]);
        }

        if ('5' === $callback_data['a'] ?? null) {
            $message         = $callback_query->getMessage();
            $chat_id         = $message->getChat()->getId();
            $clicked_user_id = $callback_query->getFrom()->getId();

            $text = 'https://bantuan.com/' . PHP_EOL;


            return Request::editMessageText([
                'text' => LitEmoji::encodeUnicode($text),
                'chat_id'      => $chat_id,
                'message_id'   => $message->getMessageId(),
                'parse_mode' => 'Markdown',
                'reply_markup'             => new InlineKeyboard([
                    ['text' => LitEmoji::encodeUnicode(':radio_button: Back'), 'callback_data' => 'command=help&a=back'],
                ]),
            ]);
        }




        return $callback_query->answer([
            'text' => 'Awesome',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function execute(): ServerResponse
    {
        $message = $this->getMessage() ?: $this->getCallbackQuery()->getMessage();

        $chat_id = $message->getChat()->getId();
        $text = trim($message->getText(true));



        $out_text = "*FAQ* \n\n";
        $out_text .= "1. Tentang Bot :robot: \n";
        $out_text .= "2. Cara Topup :atm: \n";
        $out_text .= "3. Cara Withdraw :bank: \n";
        $out_text .= "4. Cara Transaksi :moneybag: \n";
        $out_text .= "5. Tanya Admin :cop: \n";

        $keyboard_buttons = [];
        foreach (self::$menus as $key => $value) {
            $keyboard_buttons[] = new InlineKeyboardButton([
                'text' => $key,
                'callback_data' => $value,
            ]);
        }
        $keyboard_rows = array_chunk($keyboard_buttons, 2);
        $reply_markup = new InlineKeyboard(...$keyboard_rows);


        return Request::sendMessage([
            'text' => LitEmoji::encodeUnicode($out_text),
            'chat_id' => $chat_id,
            'parse_mode' => 'markdown',
            'reply_markup' =>  $reply_markup
        ]);
    }
}
