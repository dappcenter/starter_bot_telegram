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

    public static function handleCallbackQuery(CallbackQuery $callback_query, array $callback_data): ServerResponse
    {

        if ('balance' === $callback_data['a'] ?? null) {
            $message         = $callback_query->getMessage();
            $chat_id         = $message->getChat()->getId();
            $clicked_user_id = $callback_query->getFrom()->getId();

            // If the user is already activated, keep the initial activation date.
            $user = User::getUserData($clicked_user_id);

            if (is_array($user)) {
                $user['id']       = $user['chat_id'];
                $user['username'] = $user['chat_username'];
                $chat               = new Chat($user);

                $user_id    = $user['id'];
                $balance    = $user['balance'];
            }

            $text = 'User ID: ' . $user_id . PHP_EOL;
            $text .= 'Name: ' . $chat->getFirstName() . ' ' . $chat->getLastName() . PHP_EOL;
            $text .= 'Balance : ' . Helpers::Rupiah((int) $balance) . PHP_EOL;

            return Request::editMessageText([
                'text' => LitEmoji::encodeUnicode($text),
                'chat_id'      => $chat_id,
                'message_id'   => $message->getMessageId(),
                'parse_mode' => 'Markdown',
                'reply_markup'             => new InlineKeyboard([
                    ['text' => LitEmoji::encodeUnicode(':radio_button: Back'), 'callback_data' => 'command=profile&a=back'],
                ]),
            ]);
        }

        if ('refferal' === $callback_data['a'] ?? null) {
            $message         = $callback_query->getMessage();
            $chat_id         = $message->getChat()->getId();
            $clicked_user_id = $callback_query->getFrom()->getId();

            // If the user is already activated, keep the initial activation date.
            $user = User::getUserData($clicked_user_id);

            if (is_array($user)) {
                $user['id']       = $user['chat_id'];
                $user['username'] = $user['chat_username'];
                $chat               = new Chat($user);

                $user_id    = $user['id'];
                $balance    = $user['balance'];
            }

            $text = 'User ID: ' . $user_id . PHP_EOL;
            $text .= 'Name: ' . $chat->getFirstName() . ' ' . $chat->getLastName() . PHP_EOL;
            $text .= 'Refferal Code : `https://t.me/' . getenv('TG_BOT_USERNAME') . '?start=' . $user_id . '`' . PHP_EOL . PHP_EOL;
            $text .= 'Downline' . PHP_EOL;
            $text .= '===================================' .  PHP_EOL;
            $getRefferalin = User::getRefId($clicked_user_id);
            $i = 0;
            if (!empty($getRefferalin)) {
                foreach ($getRefferalin as $row) {
                    $i++;
                    $text .= $i . '. ' . $row['first_name'] . ' ' . $row['last_name'] .  PHP_EOL;
                }
            } else {
                $text .= 'Belum Memiliki Downline' .  PHP_EOL;
            }

            Helpers::jsonDebug($getRefferalin);

            return Request::editMessageText([
                'text' => LitEmoji::encodeUnicode($text),
                'chat_id'      => $chat_id,
                'message_id'   => $message->getMessageId(),
                'parse_mode' => 'Markdown',
                'reply_markup'             => new InlineKeyboard([
                    ['text' => LitEmoji::encodeUnicode(':radio_button: Back'), 'callback_data' => 'command=profile&a=back'],
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
            $wallet     = $result['wallet'];
            $wallet_account     = $result['wallet_account'];
        }




        $text = 'User ID: ' . $user_id . PHP_EOL;
        $text .= 'Name: ' . $chat->getFirstName() . ' ' . $chat->getLastName() . PHP_EOL;
        $username = $chat->getUsername();
        if ($username !== null && $username !== '') {
            $text .= 'Username: @' . $username . PHP_EOL;
        }

        if (!empty($wallet)) {
            $text .= 'Wallet Info : ' . $wallet . PHP_EOL;
            $text .= 'Wallet Account : ' . $wallet_account . PHP_EOL;
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
