<?php

namespace App\Telegram\Menu;

use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;

readonly class MainMenu
{
    public const string ID = 'main';
    public const string STUDENTS = '👤 Студенты';
    public const string EVENTS = '📅 Мероприятия';
    public const string SCORES = '🏆 Баллы и топ';
    public const string BACK = '⬅️ Назад';

    public static function make(): ReplyKeyboardMarkup
    {
        return ReplyKeyboardMarkup::make(resize_keyboard: true)
            ->addRow(
                KeyboardButton::make(self::STUDENTS),
                KeyboardButton::make(self::EVENTS),
            )
            ->addRow(KeyboardButton::make(self::SCORES));
    }
}
