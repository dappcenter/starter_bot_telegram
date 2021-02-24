<?php

/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\Chat;
use Longman\TelegramBot\Entities\Entity;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use TelegramBot\SupportBot\Helpers;
use TelegramBot\SupportBot\Models\User;




class EditprofileCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'editprofile';

    /**
     * @var string
     */
    protected $description = 'Edit Profile Command';

    /**
     * @var string
     */
    protected $usage = '/editprofile';

    /**
     * @var string
     */
    protected $version = '0.3.0';

    /**
     * @var bool
     */
    protected $need_mysql = true;

    /**
     * Conversation Object
     *
     * @var Conversation
     */
    protected $conversation;

    /**
     * Command execute method
     *
     * @return ServerResponse|mixed
     * @throws TelegramException
     */
    public function execute(): ServerResponse
    {
        $message = $this->getMessage() ?: $this->getCallbackQuery()->getMessage();

        $chat = $message->getChat();
        $user = $message->getFrom();
        $text = trim($message->getText(true));
        $chat_id = $chat->getId();
        $user_id = $user->getId();

        // Preparing response
        $data = [
            'chat_id' => $chat_id,
            // Remove any keyboard by default
            'reply_markup' => Keyboard::remove(['selective' => true]),
        ];

        if ($chat->isGroupChat() || $chat->isSuperGroup()) {
            // Force reply is applied by default so it can work with privacy on
            $data['reply_markup'] = Keyboard::forceReply(['selective' => true]);
        }

        // Conversation start
        $this->conversation = new Conversation($chat_id, $chat_id, $this->getName());

        // Load any existing notes from this conversation
        $notes = &$this->conversation->notes;
        !is_array($notes) && $notes = [];

        // Load the current state of the conversation
        $state = $notes['state'] ?? 0;

        $result = Request::emptyResponse();


        $yes_no_keyboard = new Keyboard(
            [
                'keyboard' => [['Lanjut', 'Batalkan']],
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
                'selective' => true,
            ]
        );

        switch ($state) {
            case 0:
                if ($text === '' || !in_array($text, ['Lanjut'], true)) {
                    $notes['state'] = 0;
                    $this->conversation->update();

                    $data['text'] = 'Bot Akan Menyimpan Data Wallet anda anda bisa mengubahnya lagi nanti silahkan tekan lanjut:';
                    $data['reply_markup'] =  $yes_no_keyboard;

                    if ($text === 'Batalkan') {
                        $text = 'Di Batalkan';
                        $data = [
                            'reply_markup' => Keyboard::remove(['selective' => true]),
                        ];
                        $this->conversation->stop();
                        return $this->replyToChat($text, $data);
                    }

                    $result = Request::sendMessage($data);
                    break;
                }

                $notes['stepone'] = $text;
                $text = '';

                // No break!
            case 1:
                if ($text === '') {
                    $notes['state'] = 1;
                    $this->conversation->update();

                    $data['text'] = 'Input Nama Wallet anda ex : BCA, MANDIRI, OVO, GOPAY dll :';
                    $data['reply_markup'] = Keyboard::remove(['selective' => true]);

                    $result = Request::sendMessage($data);
                    break;
                }

                $notes['wallet'] = $text;
                $text = '';

                // No break!
            case 2:
                if ($text === '') {
                    $notes['state'] = 2;
                    $this->conversation->update();

                    $data['reply_markup'] = Keyboard::remove(['selective' => true]);
                    $data['text'] = 'Masukan Nomor wallet anda:';


                    $result = Request::sendMessage($data);
                    break;
                }

                $notes['wallet_account'] = $text;
                $text = '';

                // No break!
            case 3:
                $this->conversation->update();


                $out_text = 'Data Berhasil direkam terimakasih' . PHP_EOL;

                $out_text .= 'Wallet : ' . $notes['wallet'] . PHP_EOL;
                $out_text .= 'Wallet Account : ' . $notes['wallet_account'] . PHP_EOL;

                User::updateById(
                    $this->getMessage()->getChat()->getId(),
                    [
                        'wallet' => $notes['wallet'],
                        'wallet_account' => $notes['wallet_account']
                    ]
                );



                $data['text'] = $out_text;


                $this->conversation->stop();


                $result = Request::sendMessage($data);

                if ($result->isOK()) {
                    $this->getTelegram()->executeCommand('profile');
                }
                break;
        }



        return $result;
    }
}
