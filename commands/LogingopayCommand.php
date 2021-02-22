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
use TelegramBot\SupportBot\GojekPay;



class LogingopayCommand extends AdminCommand
{
  /**
  * @var string
  */
  protected $name = 'logingopay';

  /**
  * @var string
  */
  protected $description = 'Melakukan Login Ke Gopay';

  /**
  * @var string
  */
  protected $usage = '/logingopay';

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


    $gopay = new GojekPay();

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

          $data['text'] = 'Melakukan Login Kembali akan Menyebabkan Session Tertimpa Tekan Lanjut Jika Belum Melakukan Login:';
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
          if ($text === '' || !is_numeric($text)) {
            $notes['state'] = 1;
            $this->conversation->update();

            $data['text'] = 'Silahkan Input Nomor Telp Gojek:';
            $data['reply_markup'] = Keyboard::remove(['selective' => true]);

            $result = Request::sendMessage($data);
            break;
          }

          $notes['phone_number'] = $text;

          $refid = $gopay->loginRequest($text);
          $refid = json_decode($refid, true);
          $notes['refid'] = $refid['data']['otp_token'];
          $text = '';

          // No break!
          case 2:
            if ($text === '') {
              $notes['state'] = 2;
              $this->conversation->update();

              $data['reply_markup'] = Keyboard::remove(['selective' => true]);

              $data['text'] = 'Inputkan Kode OTP yang dikirim Lewat SMS:';


              $result = Request::sendMessage($data);
              break;
            }

            $notes['otp'] = $text;
            $tokenUpdate = $gopay->getAuthToken($notes['refid'], $text);
            $tokenUpdate = json_decode($tokenUpdate, true);
            $notes['tokenAuth'] = $tokenUpdate['access_token'];
            $text = '';
            Helpers::DebugMyCommand($tokenUpdate);

            // No break!
            case 3:
              if ($text === '') {
                $notes['state'] = 3;
                $this->conversation->update();

                $data['reply_markup'] = Keyboard::remove(['selective' => true]);

                $data['text'] = 'Inputkan Pin Gojek Kamu:';


                $result = Request::sendMessage($data);
                break;
              }

              $notes['pin'] = $text;


              // No break!
              case 6:
                $this->conversation->update();

                $input_data = [
                  'phone_number' => $notes['phone_number'],
                  'otp' => $notes['otp'],
                  'pin' => $notes['pin'],
                  'authToken' => $notes['tokenAuth'],
                ];


                $fp = fopen(__DIR__ . '/temp/gojek_session.json', 'w');

                fwrite($fp, json_encode($input_data, JSON_PRETTY_PRINT));

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