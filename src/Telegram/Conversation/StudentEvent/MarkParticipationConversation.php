<?php

namespace App\Telegram\Conversation\StudentEvent;

use App\Event\EventService;
use App\Student\StudentService;
use App\StudentEvent\Dto\StudentEventDto;
use App\StudentEvent\StudentEventService;
use App\Telegram\BotService;
use App\Telegram\Menu\StudentEventsMenu;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardRemove;

class MarkParticipationConversation extends Conversation
{
    protected ?int $studentId = null;
    protected ?int $eventId = null;
    protected ?float $score = null;

    public function __construct(
        private readonly BotService $botService,
        private readonly StudentService $studentService,
        private readonly EventService $eventService,
        private readonly StudentEventService $studentEventService,
    ) {
    }

    protected function getSerializableAttributes(): array
    {
        return [
            'student_id' => $this->studentId,
            'event_id' => $this->eventId,
            'score' => $this->score,
        ];
    }

    public function start(Nutgram $bot): void
    {
        $this->botService->removeKeyboard($bot);

        foreach (array_chunk($this->studentService->findForChoice(), 100) as $chunk) {
            $keyboard = InlineKeyboardMarkup::make();
            foreach ($chunk as $student) {
                $keyboard->addRow(InlineKeyboardButton::make(text: $student->name, callback_data: $student->id));
            }

            $bot->sendMessage(
                '👤 Выберите студента',
                reply_markup: $keyboard,
            );
        }

        $this->next('selectEvent');
    }

    public function selectEvent(Nutgram $bot): void
    {
        $this->studentId = (int)$bot->callbackQuery()?->data;

        $student = $this->studentId ? $this->studentService->find($this->studentId) : null;
        if (!$student) {
            $bot->sendMessage('❌ Студент не найден');
            $this->next(__FUNCTION__);
            return;
        }

        foreach (array_chunk($this->eventService->findForChoice(), 100) as $chunk) {
            $keyboard = InlineKeyboardMarkup::make();
            foreach ($chunk as $event) {
                $keyboard->addRow(InlineKeyboardButton::make(text: $event->name, callback_data: $event->id));
            }

            $bot->sendMessage(
                '📅 Выберите мероприятие',
                reply_markup: $keyboard,
            );
        }

        $this->next('inputScore');
    }

    public function inputScore(Nutgram $bot): void
    {
        $this->eventId = (int)$bot->callbackQuery()?->data;

        $event = $this->eventId ? $this->eventService->find($this->eventId) : null;
        if (!$event) {
            $bot->sendMessage('❌ Мероприятие не найдено');
            $this->next(__FUNCTION__);
            return;
        }

        $bot->sendMessage(
            '🔢 Введите количество баллов',
            reply_markup: ReplyKeyboardRemove::make(true),
        );

        $this->next('save');
    }

    public function save(Nutgram $bot): void
    {
        $scoreText = $bot->message()?->text;

        if (!is_numeric($scoreText)) {
            $bot->sendMessage('❌ Пожалуйста, введите корректное число');
            return;
        }

        $this->score = (float)$scoreText;

        $student = $this->studentService->find($this->studentId);
        $event = $this->eventService->find($this->eventId);

        $dto = new StudentEventDto(
            studentId: $this->studentId,
            eventId: $this->eventId,
            score: $this->score
        );

        $this->studentEventService->create($dto);

        $bot->sendMessage(
            "✅ Участие отмечено!" . PHP_EOL . PHP_EOL .
            "Студент: $student->name" . PHP_EOL .
            "Мероприятие: $event->name" . PHP_EOL .
            "Баллы: $this->score",
            reply_markup: $this->menuKeyboard(),
        );

        $this->botService->setCurrentMenu($bot, StudentEventsMenu::ID);
        $this->end();
    }

    private function menuKeyboard(): ReplyKeyboardMarkup
    {
        return StudentEventsMenu::make();
    }
}
