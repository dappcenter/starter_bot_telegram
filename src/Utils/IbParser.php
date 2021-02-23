<?php

declare(strict_types=1);

namespace TelegramBot\SupportBot\Utils;

use TelegramBot\SupportBot\Utils\BCAParser;

class IbParser
{

    function __construct()
    {
        $this->conf['ip']       = $_SERVER['REMOTE_ADDR'];
        $this->conf['time']     = time();
        $this->conf['path']     = dirname(__FILE__);
    }




    function instantiate($bank)
    {

        $this->bank = new BCAParser($this->conf) or trigger_error('Undefined parser: ' .  E_USER_ERROR);
    }




    function getBalance($bank = null, $username = null, $password = null)
    {

        $this->instantiate($bank);
        $this->bank->login($username, $password);
        $balance = $this->bank->getBalance();
        $res = [];
        if (!$balance) :
            $res['result'] = false;
            $res['err'] = 'Gagal mengambil transaksi';
        else :
            $res['result'] = true;
            $res['saldo'] = number_format((int) $balance, 0, '', '');
        endif;

        $this->bank->logout();
        return json_encode($res);
    }




    function getTransactions($bank = null, $username = null, $password = null)
    {

        $this->instantiate($bank);
        $this->bank->login($username, $password);
        $transactions = $this->bank->getTransactions();

        $balance = $this->bank->getBalance($bank, $username, $password);
        if (!$transactions) :
            $res['result'] = true;
            $res['total_transaksi'] = 0;
            $res['saldo'] = number_format((int)  $balance, 0, '', '');
            $res['data'] = [];
        else :
            $res['result'] = true;
            $res['total_transaksi'] = count($transactions);
            $res['saldo'] = number_format((int)  $balance, 0, '', '');
            foreach ($transactions as $val) {
                $res['data'][] = [
                    'tgl' => $val[0],
                    'ket' => $val[1],
                    'tipe' => $val[2],
                    'total' => number_format((int)  $val[3], 0, '', ''),
                ];
            }

        endif;
        $this->bank->logout();
        return json_encode($res);
    }
}
