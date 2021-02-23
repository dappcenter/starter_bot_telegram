<?php

/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Longman\TelegramBot\Commands\AdminCommands;

use Longman\TelegramBot\Commands\AdminCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\Chat;
use Longman\TelegramBot\Entities\Entity;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use TelegramBot\SupportBot\Helpers;




class IbCommand extends AdminCommand
{
    /**
     * @var string
     */
    protected $name = 'ib';

    /**
     * @var string
     */
    protected $description = 'Login to Internet Banking BCA';

    /**
     * @var string
     */
    protected $usage = '/ib';

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
        $this->conversation = new Conversation($user_id, $chat_id, $this->getName());

        // Load any existing notes from this conversation
        $notes = &$this->conversation->notes;
        !is_array($notes) && $notes = [];

        // Load the current state of the conversation
        $state = $notes['state'] ?? 0;

        $result = Request::emptyResponse();


        $yes_no_keyboard = new Keyboard(
            [
                'keyboard' => [['Yes', 'No']],
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

                    $data['text'] = 'Untuk di ketahui Username dan Password IB Sudah ter encrypt hanya server yang akan membaca nya:';
                    $data['reply_markup'] = (new Keyboard(['Lanjut', 'Batalkan']))
                        ->setResizeKeyboard(true)
                        ->setOneTimeKeyboard(true)
                        ->setSelective(true);

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

                    $data['text'] = 'Silahkan Username IB:';
                    $data['reply_markup'] = Keyboard::remove(['selective' => true]);

                    $result = Request::sendMessage($data);
                    break;
                }

                $notes['username'] = $text;
                $text = '';

                // No break!
            case 2:
                if ($text === '') {
                    $notes['state'] = 2;
                    $this->conversation->update();

                    $data['reply_markup'] = Keyboard::remove(['selective' => true]);
                    $data['text'] = 'Input Kode PIN IB:';


                    $result = Request::sendMessage($data);
                    break;
                }

                $notes['pin'] = $text;
                $text = '';

                // No break!
            case 3:
                $this->conversation->update();



                $encrypt['username'] = $notes['username'];
                $encrypt['password'] = $notes['pin'];
                $encrypt['bank'] = 'BCA';

                $token = base64_encode(serialize($encrypt));

                $input = [
                    'username' => $encrypt['username'],
                    'pin' =>  $encrypt['password'],
                    'authToken' => $token,
                ];

                $fp = fopen(__DIR__ . '/temp/ibank_'  . $chat_id . '_session.json', 'w');

                fwrite($fp, json_encode($input, JSON_PRETTY_PRINT));

                fclose($fp);

                $out_text = '##### Anda Berhasil Login #####' . PHP_EOL;
                $out_text .= 'Hanya Admin Yang Bisa Menggunakan Command Ini' . PHP_EOL;



                $data['text'] = $out_text;


                $this->conversation->stop();

                $result = Request::sendMessage($data);
                break;
        }



        return $result;
    }
}
