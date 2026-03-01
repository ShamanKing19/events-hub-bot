<?php

namespace App\Telegram;

use App\Telegram\Menu\MainMenu;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardRemove;

class BotService
{
    private const string CURRENT_MENU_KEY = 'current_menu';

    public function setCurrentMenu(Nutgram $bot, string $menu): void
    {
        $bot->setUserData(self::CURRENT_MENU_KEY, $menu);
    }

    public function getCurrentMenu(Nutgram $bot): string
    {
        return $bot->getUserData(self::CURRENT_MENU_KEY, default: MainMenu::ID);
    }

    public function removeKeyboard(Nutgram $bot): void
    {
        $bot->sendMessage('...', reply_markup: ReplyKeyboardRemove::make(true))?->delete();
    }
}
