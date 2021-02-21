<?php

declare(strict_types=1);

namespace TelegramBot\SupportBot\Models;

use Longman\TelegramBot\DB;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\TelegramLog;
use Throwable;
use PDO;
use TelegramBot\SupportBot\Helpers;
use Envms\FluentPDO\Query as FluentPDO;
use Envms\FluentPDO\Literal;

class User
{


    public static function getAll()
    {
        $pdo = DB::getPdo();
        $fpdo = new FluentPDO($pdo);
        $query = $fpdo->from('user');
        $row = $query->fetchAll();

        return $row;
    }

    public static function getRefId($ids)
    {
        $pdo = DB::getPdo();
        $fpdo = new FluentPDO($pdo);
        $query = $fpdo->from('user')->where('refferal', $ids);
        $row = $query->fetchAll();

        return $row;
    }

    public static function getByIds($ids)
    {

        $pdo = DB::getPdo();
        $fpdo = new FluentPDO($pdo);
        $query = $fpdo->from('user', $ids);
        $row = $query->fetch();


        return $row;
    }


    public static function updateById($ids, array $params = array())
    {

        $pdo = DB::getPdo();
        $fpdo = new FluentPDO($pdo);
        $query = $fpdo->update('user')->set($params)->where('id', $ids);

        return $query->execute();
    }

    public static function increaseBalance($ids, $amount)
    {

        $pdo = DB::getPdo();
        $fpdo = new FluentPDO($pdo);
        $query = $fpdo->update('user')
            ->set([
                'saldo' => new Literal('saldo + ' . $amount)
            ])
            ->where('id', $ids);

        return $query->execute();
    }

    public static function decreaseBalance($ids, $amount)
    {

        $pdo = DB::getPdo();
        $fpdo = new FluentPDO($pdo);
        $query = $fpdo->update('user')
            ->set([
                'saldo' => new Literal('saldo - ' . $amount)
            ])
            ->where('id', $ids);

        return $query->execute();
    }

    public static function getUserData($id)
    {
        $user_id    = $id;
        $chat       = null;
        $created_at = null;
        $updated_at = null;
        $result     = null;

        if (is_numeric($id)) {
            $results = DB::selectChats([
                'groups'      => true,
                'supergroups' => true,
                'channels'    => true,
                'users'       => true,
                'chat_id'     => $user_id, //Specific chat_id to select
            ]);

            if (!empty($results)) {
                $result = reset($results);
            }
        } else {
            $results = DB::selectChats([
                'groups'      => true,
                'supergroups' => true,
                'channels'    => true,
                'users'       => true,
                'text'        => $id //Text to search in user/group name
            ]);

            if (is_array($results) && count($results) === 1) {
                $result = reset($results);
            }
        }


        return $result;
    }
}
