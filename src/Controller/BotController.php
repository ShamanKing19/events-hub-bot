<?php

namespace App\Controller;

use App\Telegram\Conversation\AddStudentConversation;
use App\Telegram\Conversation\EditStudentConversation;
use App\Telegram\Menu\MainMenu;
use App\Telegram\Menu\StudentsMenu;
use Psr\Log\LoggerInterface;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class BotController extends AbstractController
{
    public function __construct(
        #[Target(name: 'monolog.logger.webhook')] private LoggerInterface $logger
    ) {
    }

    #[Route('/api/v1/bot/webhook', name: 'bot_webhook')]
    public function webhook(Nutgram $bot, Request $request): JsonResponse
    {
        $this->logger->info('webhook', $request->toArray());
        Conversation::refreshOnDeserialize();

        // ========================
        //         КОМАНДЫ
        // ========================

        $bot->onCommand('start', function (Nutgram $bot) {
            $this->sendMenu($bot, MainMenu::ID, 'Добро пожаловать! Выберите раздел:');
        })->description('Главное меню');

        $bot->onCommand('menu', function (Nutgram $bot) {
            $this->sendMenu($bot, MainMenu::ID, 'Главное меню:');
        })->description('Вернуться в главное меню');

        // ========================
        //       НАВИГАЦИЯ
        // ========================

        $bot->onText(MainMenu::BACK, function (Nutgram $bot) {
            $this->sendMenu($bot, MainMenu::ID, 'Главное меню:');
        });

        // ========================
        //       ГЛАВНОЕ МЕНЮ
        // ========================

        $bot->onText(MainMenu::STUDENTS, function (Nutgram $bot) {
            $this->sendMenu($bot, StudentsMenu::ID, '👤 Студенты:');
        });

        // ========================
        //   СТУДЕНТЫ — ДЕЙСТВИЯ
        // ========================

        $bot->onText(StudentsMenu::LABEL_ADD, function (Nutgram $bot) {
            $currentMenu = $bot->getUserData('current_menu', default: MainMenu::ID);
            if ($currentMenu !== StudentsMenu::ID) {
                return;
            }

            AddStudentConversation::begin($bot);
        });

        $bot->onText(StudentsMenu::LABEL_EDIT, function (Nutgram $bot) {
            $currentMenu = $bot->getUserData('current_menu', default: MainMenu::ID);
            if ($currentMenu !== StudentsMenu::ID) {
                return;
            }

            EditStudentConversation::begin($bot);
        });

        $bot->run();

        return $this->json([]);
    }

    private function sendMenu(Nutgram $bot, string $menu, string $text): void
    {
        $bot->setUserData('current_menu', $menu);
        $bot->sendMessage($text, reply_markup: $this->buildKeyboard($menu));
    }

    /**
     * Клавиатуры
     */
    private function buildKeyboard(string $menu): ReplyKeyboardMarkup
    {
        return match ($menu) {
            StudentsMenu::ID => StudentsMenu::make(),
            default => MainMenu::make(),
        };
    }
}
