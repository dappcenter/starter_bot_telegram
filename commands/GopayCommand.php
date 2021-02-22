<?php

/**
* This file is part of the PHP Telegram Support Bot.
*
* (c) PHP Telegram Bot Team (https://github.com/php-telegram-bot)
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

declare(strict_types = 1);

namespace Longman\TelegramBot\Commands\AdminCommands;

use LitEmoji\LitEmoji;
use Longman\TelegramBot\Commands\AdminCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\KeyboardButton;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Entities\Entity;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\DB;
use PDO;
use Longman\TelegramBot\Request;
use TelegramBot\SupportBot\Helpers;
use TelegramBot\SupportBot\GojekPay;


/**
* System "/start" command
*/
class GopayCommand extends AdminCommand
{
  /**
  * @var string
  */
  protected $name = 'gopay';

  /**
  * @var string
  */
  protected $description = 'Gopay Admin Command';

  /**
  * @var string
  */
  protected $version = '0.1.0';

  /**
  * @var string
  */
  protected $usage = '/gopay saldo,/gopay history,/gopay withdraw';

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

    $message = $this->getMessage() ?: $this->getCallbackQuery()->getMessage();
    $chat_id = $message->getChat()->getId();
    $text = trim($message->getText(true));
    $user_id = $message->getFrom()->getId();

    $greeting = Helpers::greeting();

    $sess = json_decode(file_get_contents(__DIR__ . "/../commands/temp/gojek_session.json"), true);

    $gopay = new GojekPay($sess['authToken']);

    if ($text === '') {
      $out_text = ":stop: Command Salah atau Tidak di Temukan ! \n\n";
      $helper = explode(",", $this->usage);
      $out_text .= "`{$helper[0]}` - Mengecek Saldo\n";
      $out_text .= "`{$helper[1]}` - Mengecek History\n";
      $out_text .= "`{$helper[2]}` - Melakukan Withdraw\n";
      $out_text .= "`/gopay login` - Melakukan sesi login\n";
      $out_text .= "`/gopay logout` - Melakukan sesi logout\n";

    }

    if ($text === 'saldo') {
      $getBalance = json_decode($gopay->getBalance(), true);

      $out_text = "Kamu Memiliki Saldo : {$getBalance['data']['balance']} {$getBalance['data']['currency']}\n\n";
    }

    if ($text === 'history') {
      $reply = new InlineKeyboard([
        ['text' => 'Cek Transaksi', 'url' => "https://api.reactmore.com/TGMorepay/public/ListTransaction.php?token=270796"]
      ]);

      $out_text = ":computer: Melakukan Cek Transaksi Mode Web";

      return Request::sendMessage([
        'text' => LitEmoji::encodeUnicode($out_text),
        'chat_id' => $chat_id,
        'reply_markup' => $reply,
        'parse_mode' => 'markdown',
      ]);


    }

    if ($text === 'withdraw') {
      $out_text = "##### Wait #### \n\n";
      return $this->getTelegram()->executeCommand('Withdrawovo');
    }

    if ($text === 'login') {

      $out_text = "##### Wait #### \n\n";

      return $this->getTelegram()->executeCommand('logingopay');

    }

    if ($text === 'logout') {

      $out_text = "##### Logout Successfuly #### \n\n";
      $out_text .= "Harap Menunggu 5-10mnt untuk membuat sesi login dengan nomor sama";


      return Request::sendMessage([
        'text' => LitEmoji::encodeUnicode($out_text),
        'chat_id' => $chat_id,
        'parse_mode' => 'markdown',
      ]);
    }


    return Request::sendMessage([
      'text' => LitEmoji::encodeUnicode($out_text),
      'chat_id' => $chat_id,
      'parse_mode' => 'markdown',
    ]);



  }

}