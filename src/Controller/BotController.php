<?php

namespace App\Controller;

use App\Event\Dto\EventDto;
use App\Event\EventService;
use App\Student\StudentService;
use App\StudentEvent\StudentEventService;
use App\Telegram\BotService;
use App\Telegram\Conversation\Event\AddEventConversation;
use App\Telegram\Conversation\Event\EditEventConversation;
use App\Telegram\Conversation\Event\RemoveEventConversation;
use App\Telegram\Conversation\Student\AddStudentConversation;
use App\Telegram\Conversation\Student\EditStudentConversation;
use App\Telegram\Conversation\Student\RemoveStudentConversation;
use App\Telegram\Conversation\StudentEvent\MarkParticipationConversation;
use App\Telegram\Conversation\StudentEvent\ViewEventParticipantsConversation;
use App\Telegram\Conversation\StudentEvent\ViewParticipationConversation;
use App\Telegram\Menu\EventsMenu;
use App\Telegram\Menu\MainMenu;
use App\Telegram\Menu\StudentEventsMenu;
use App\Telegram\Menu\StudentsMenu;
use App\User\Dto\CreateUserDto;
use App\User\Exception\UserAlreadyExistsException;
use App\User\UserService;
use Psr\Log\LoggerInterface;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BotController extends AbstractController
{
    public function __construct(
        #[Autowire(param: 'kernel.debug')]
        private readonly bool $isDebug,
        #[Target(name: 'monolog.logger.webhook')]
        private readonly LoggerInterface $logger,
        private readonly BotService $botService,
        private readonly StudentService $studentService,
        private readonly EventService $eventService,
        private readonly StudentEventService $studentEventService,
        private readonly UserService $userService
    ) {}

    #[Route('/test', name: 'test')]
    public function test(): Response
    {
        return new Response();
    }

    #[Route('/api/v1/bot/webhook', name: 'bot_webhook')]
    public function webhook(Nutgram $bot, Request $request): JsonResponse
    {
        $webhook = $request->toArray();
        $this->logger->info('webhook', $webhook);
        $chatId = $this->findChatId($webhook);
        if ($chatId === null) {
            return $this->json(null);
        }

        if (!$this->userService->canUseBot($chatId)) {
            if ($this->userService->doesAnyUserExists()) {
                $this->logger->notice('Неавторизованный пользователь', $webhook);
                return $this->json(null);
            }

            try {
                // Первого пользователя регистрируем автоматически
                $this->userService->create(new CreateUserDto(chatId: $chatId, username: $this->findUsername($webhook)));
            } catch (UserAlreadyExistsException $e) {
                $this->logger->error($e->getMessage(), $webhook);
                return $this->json(null);
            }
        }

        Conversation::refreshOnDeserialize();
        $this->registerHandlers($bot);

        try {
            $bot->run();
        } catch (\Throwable $e) {
            if (!$this->isDebug) {
                $bot->sendMessage('Что-то пошло не так. Попробуйте повторить действие позже.');
                return $this->json([]);
            }

            $bot->sendMessage($e::class . ' ' . $e->getMessage());
            foreach ($e->getTrace() as $trace) {
                foreach (str_split(json_encode($trace) ?: 'Не удалось превратить trace в json', 4096) as $part) {
                    $bot->sendMessage($part);
                }
            }

            return $this->json([]);
        }

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
            StudentEventsMenu::ID => StudentEventsMenu::make(),
            default => MainMenu::make(),
        };
    }

    private function sendEventList(Nutgram $bot, int $rowsPerMessage = 20): void
    {
        $events = $this->eventService->findForChoice();
        $number = 1;

        foreach (array_chunk($events, $rowsPerMessage) as $chunk) {
            $message = '';
            /** @var EventDto $event */
            foreach ($chunk as $event) {
                $message .= $number++ . ". $event->name (с " . $event->startDate->format('d.m.Y H:i:s') . ' по ' . $event->finishDate->format('d.m.Y H:i:s') . ')' . PHP_EOL;
            }
            $bot->sendMessage($message);
        }
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

    private function sendTopStudents(Nutgram $bot): void
    {
        $topStudents = $this->studentEventService->getTopStudents(20);

        if (empty($topStudents)) {
            $bot->sendMessage('🏆 Топ студентов' . PHP_EOL . PHP_EOL . 'Нет данных об участии студентов.');
            return;
        }

        $message = '🏆 Топ студентов по баллам' . PHP_EOL . PHP_EOL;
        $medals = ['🥇', '🥈', '🥉'];
        foreach ($topStudents as $index => $item) {
            if ($medal = $medals[$index] ?? null) {
                $message .= $medal . ' ';
            }

            $position = $index + 1;
            $message .= " $position. {$item->student->name} — {$item->score} баллов" . PHP_EOL;
        }

        $bot->sendMessage($message);
    }

    private function registerHandlers(Nutgram $bot): void
    {
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
        $bot->onText(MainMenu::SCORES, function (Nutgram $bot) {
            $this->sendMenu($bot, StudentEventsMenu::ID, '✍️ Мероприятия');
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

        $bot->onText(EventsMenu::LABEL_LIST, function (Nutgram $bot) {
            if ($this->botService->getCurrentMenu($bot) === EventsMenu::ID) {
                $this->sendEventList($bot);
            }
        });

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

        // ========================
        //   УЧАСТИЕ В МЕРОПРИЯТИЯХ
        // ========================

        $bot->onText(StudentEventsMenu::LABEL_MARK_PARTICIPATION, function (Nutgram $bot) {
            if ($this->botService->getCurrentMenu($bot) === StudentEventsMenu::ID) {
                MarkParticipationConversation::begin($bot);
            }
        });

        $bot->onText(StudentEventsMenu::LABEL_VIEW_PARTICIPATION, function (Nutgram $bot) {
            if ($this->botService->getCurrentMenu($bot) === StudentEventsMenu::ID) {
                ViewParticipationConversation::begin($bot);
            }
        });

        $bot->onText(StudentEventsMenu::LABEL_VIEW_EVENT_PARTICIPANTS, function (Nutgram $bot) {
            if ($this->botService->getCurrentMenu($bot) === StudentEventsMenu::ID) {
                ViewEventParticipantsConversation::begin($bot);
            }
        });

        $bot->onText(StudentEventsMenu::LABEL_TOP_STUDENTS, function (Nutgram $bot) {
            if ($this->botService->getCurrentMenu($bot) === StudentEventsMenu::ID) {
                $this->sendTopStudents($bot);
            }
        });
    }

    private function findChatId(array $webhook): ?int
    {
        if (isset($webhook['message'])) {
            return isset($webhook['message']['from']['id']) ? (int)$webhook['message']['from']['id'] : null;
        }

        if (isset($webhook['callback_query'])) {
            return isset($webhook['callback_query']['from']['id']) ? (int)$webhook['callback_query']['from']['id'] : null;
        }

        return null;
    }

    private function findUsername(array $webhook): ?string
    {
        if (isset($webhook['message'])) {
            return $webhook['message']['from']['username'] ?? null;
        }

        if (isset($webhook['callback_query'])) {
            return $webhook['callback_query']['from']['username'] ?? null;
        }

        return null;
    }
}
