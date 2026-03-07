<?php

namespace App\Telegram\Menu;

use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;

readonly class StudentEventsMenu
{
    public const string ID = 'student_events';
    public const string LABEL_MARK_PARTICIPATION = '✍️ Отметить участие';
    public const string LABEL_VIEW_PARTICIPATION = '📋 Участия в мероприятиях';
    public const string LABEL_BACK = '⬅️ Назад';

    public static function make(): ReplyKeyboardMarkup
    {
        return ReplyKeyboardMarkup::make(resize_keyboard: true)
            ->addRow(KeyboardButton::make(self::LABEL_MARK_PARTICIPATION))
            ->addRow(KeyboardButton::make(self::LABEL_VIEW_PARTICIPATION))
            ->addRow(KeyboardButton::make(self::LABEL_BACK));
    }
}
