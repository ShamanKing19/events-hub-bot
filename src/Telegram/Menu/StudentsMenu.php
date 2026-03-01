<?php

namespace App\Telegram\Menu;

use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;

class StudentsMenu
{
    public const string ID = 'students';
    public const string LABEL_ADD = '➕ Добавить';
    public const string LABEL_EDIT = '✏️ Редактировать';
    public const string LABEL_DELETE = '🗑 Удалить';
    public const string LABEL_LIST = '☰ Список';
    public const string LABEL_BACK = '⬅️ Назад';

    public static function make(): ReplyKeyboardMarkup
    {
        return ReplyKeyboardMarkup::make(resize_keyboard: true)
            ->addRow(KeyboardButton::make(self::LABEL_ADD), KeyboardButton::make(self::LABEL_EDIT))
            ->addRow(KeyboardButton::make(self::LABEL_DELETE), KeyboardButton::make(self::LABEL_LIST))
            ->addRow(KeyboardButton::make(self::LABEL_BACK));
    }
}
