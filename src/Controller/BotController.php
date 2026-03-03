<?php

namespace App\Controller;

use App\Student\StudentService;
use App\Telegram\BotService;
use App\Telegram\Conversation\Event\AddEventConversation;
use App\Telegram\Conversation\Event\EditEventConversation;
use App\Telegram\Conversation\Event\RemoveEventConversation;
use App\Telegram\Conversation\Student\AddStudentConversation;
use App\Telegram\Conversation\Student\EditStudentConversation;
use App\Telegram\Conversation\Student\RemoveStudentConversation;
use App\Telegram\Menu\EventsMenu;
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
        #[Target(name: 'monolog.logger.webhook')]
        private LoggerInterface         $logger,
        private readonly BotService     $botService,
        private readonly StudentService $studentService
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
            $this->sendMenu($bot, MainMenu::ID, 'Добро пожаловать!');
        })->description('Главное меню');

        $bot->onCommand('menu', function (Nutgram $bot) {
            $this->sendMenu($bot, MainMenu::ID, 'Главное меню');
        })->description('Вернуться в главное меню');

        // ========================
        //       НАВИГАЦИЯ
        // ========================

        $bot->onText(MainMenu::BACK, function (Nutgram $bot) {
            $this->sendMenu($bot, MainMenu::ID, 'Главное меню');
        });

        // ========================
        //       ГЛАВНОЕ МЕНЮ
        // ========================

        $bot->onText(MainMenu::STUDENTS, function (Nutgram $bot) {
            $this->sendMenu($bot, StudentsMenu::ID, '👤 Студенты');
        });
        $bot->onText(MainMenu::EVENTS, function (Nutgram $bot) {
            $this->sendMenu($bot, EventsMenu::ID, '📅 Мероприятия');
        });

        // ========================
        //   СТУДЕНТЫ — ДЕЙСТВИЯ
        // ========================

        $bot->onText(StudentsMenu::LABEL_LIST, function (Nutgram $bot) {
            if ($this->botService->getCurrentMenu($bot) === StudentsMenu::ID) {
                $this->sendStudentList($bot);
            }
        });

        $bot->onText(StudentsMenu::LABEL_ADD, function (Nutgram $bot) {
            if ($this->botService->getCurrentMenu($bot) === StudentsMenu::ID) {
                AddStudentConversation::begin($bot);
            }
        });

        $bot->onText(StudentsMenu::LABEL_EDIT, function (Nutgram $bot) {
            if ($this->botService->getCurrentMenu($bot) === StudentsMenu::ID) {
                EditStudentConversation::begin($bot);
            }
        });

        $bot->onText(StudentsMenu::LABEL_DELETE, function (Nutgram $bot) {
            if ($this->botService->getCurrentMenu($bot) === StudentsMenu::ID) {
                RemoveStudentConversation::begin($bot);
            }
        });

        // ========================
        //   МЕРОПРИЯТИЯ — ДЕЙСТВИЯ
        // ========================

//        $bot->onText(EventsMenu::LABEL_LIST, function (Nutgram $bot) {
//            if ($this->botService->getCurrentMenu($bot) === EventsMenu::ID) {
//                $this->sendEventsList($bot);
//            }
//        });

        $bot->onText(EventsMenu::LABEL_ADD, function (Nutgram $bot) {
            if ($this->botService->getCurrentMenu($bot) === EventsMenu::ID) {
                AddEventConversation::begin($bot);
            }
        });

        $bot->onText(EventsMenu::LABEL_EDIT, function (Nutgram $bot) {
            if ($this->botService->getCurrentMenu($bot) === EventsMenu::ID) {
                EditEventConversation::begin($bot);
            }
        });

        $bot->onText(EventsMenu::LABEL_DELETE, function (Nutgram $bot) {
            if ($this->botService->getCurrentMenu($bot) === EventsMenu::ID) {
                RemoveEventConversation::begin($bot);
            }
        });

        $bot->run();

        return $this->json([]);
    }

    private function sendMenu(Nutgram $bot, string $menu, string $text): void
    {
        $this->botService->setCurrentMenu($bot, $menu);
        $bot->sendMessage($text, reply_markup: $this->buildKeyboard($menu));
    }

    /**
     * Клавиатуры
     */
    private function buildKeyboard(string $menu): ReplyKeyboardMarkup
    {
        return match ($menu) {
            StudentsMenu::ID => StudentsMenu::make(),
            EventsMenu::ID => EventsMenu::make(),
            default => MainMenu::make(),
        };
    }

    private function sendStudentList(Nutgram $bot, int $rowsPerMessage = 20): void
    {
        $students = $this->studentService->findForChoice();
        $number = 1;

        foreach (array_chunk($students, $rowsPerMessage) as $chunk) {
            $message = '';
            foreach ($chunk as $student) {
                $message .= $number++ . ". $student->name" . PHP_EOL;
            }
            $bot->sendMessage($message);
        }
    }
}
