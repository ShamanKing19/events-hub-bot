<?php

namespace App\Telegram;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardRemove;

class BotService
{
    public function removeKeyboard(Nutgram $bot): void
    {
        $bot->sendMessage('...', reply_markup: ReplyKeyboardRemove::make(true))?->delete();
    }
}
