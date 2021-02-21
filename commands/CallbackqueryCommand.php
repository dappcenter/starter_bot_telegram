<?php

/**
 * This file is part of the PHP Telegram Support Bot.
 *
 * (c) PHP Telegram Bot Team (https://github.com/php-telegram-bot)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Commands\UserCommands\DonateCommand;
use Longman\TelegramBot\Commands\UserCommands\RulesCommand;
use Longman\TelegramBot\Commands\UserCommands\ProfileCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Request;
use TelegramBot\SupportBot\Helpers;

/**
 * Callback query command
 *
 * This command handles all callback queries sent via inline keyboard buttons.
 *
 * @see InlinekeyboardCommand.php
 */
class CallbackqueryCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'callbackquery';

    /**
     * @var string
     */
    protected $description = 'Reply to callback query';

    /**
     * @var string
     */
    protected $version = '0.1.0';

    /**
     * Command execute method
     *
     * @return ServerResponse
     */
    public function execute(): ServerResponse
    {
        $callback_query = $this->getCallbackQuery();
        parse_str($callback_query->getData(), $callback_data);

        if ('donate' === $callback_data['command']) {
            return DonateCommand::handleCallbackQuery($callback_query, $callback_data);
        }

        if ('profile' === $callback_data['command']) {
            if ('back' === $callback_data['a']) {
                $message = $this->getMessage() ?: $this->getCallbackQuery()->getMessage();
                $data = [
                    'chat_id' => $message->getChat()->getId(),
                    'message_id' => $message->getMessageId()
                ];

                $result = Request::deleteMessage($data);

                if ($result->isOk()) {
                    return $this->getTelegram()->executeCommand('profile');
                }
            } elseif ('edit' === $callback_data['a']) {
                return $callback_query->answer();
                // return $this->getTelegram()->executeCommand('editbank');
            } else {
                return ProfileCommand::handleCallbackQuery($callback_query, $callback_data);
            }
        }

        if ('rules' === $callback_data['command']) {
            return RulesCommand::handleCallbackQuery($callback_query, $callback_data);
        }

        return $callback_query->answer();
    }
}
