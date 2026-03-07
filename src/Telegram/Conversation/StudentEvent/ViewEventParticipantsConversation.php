<?php

namespace App\Telegram\Conversation\StudentEvent;

use App\Event\EventService;
use App\StudentEvent\StudentEventService;
use App\Telegram\BotService;
use App\Telegram\Menu\StudentEventsMenu;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;

class ViewEventParticipantsConversation extends Conversation
{
    protected ?int $eventId = null;

    public function __construct(
        private readonly BotService          $botService,
        private readonly EventService        $eventService,
        private readonly StudentEventService $studentEventService,
    ) {
    }

    protected function getSerializableAttributes(): array
    {
        return [
            'event_id' => $this->eventId,
        ];
    }

    public function start(Nutgram $bot): void
    {
        $this->botService->removeKeyboard($bot);

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

        $this->next('showParticipants');
    }

    public function showParticipants(Nutgram $bot): void
    {
        $this->eventId = (int)$bot->callbackQuery()?->data;

        $event = $this->eventId ? $this->eventService->find($this->eventId) : null;
        if (!$event) {
            $bot->sendMessage('❌ Мероприятие не найдено');
            $this->next(__FUNCTION__);
            return;
        }

        $participants = $this->studentEventService->findByEvent($this->eventId);
        if (empty($participants)) {
            $bot->sendMessage(
                "👥 Участники мероприятия: $event->name" . PHP_EOL . PHP_EOL .
                "Нет записей об участии студентов.",
                reply_markup: $this->menuKeyboard(),
            );
        } else {
            $message = "👥 Участники мероприятия: $event->name" . PHP_EOL;
            $message .= "📅 " . $event->startDate->format('d.m.Y') . PHP_EOL . PHP_EOL;

            foreach ($participants as $index => $participation) {
                $message .= ($index + 1) . ". {$participation->getStudent()->getName()}" . PHP_EOL;
                $message .= "🏆 Баллы: {$participation->getScore()}" . PHP_EOL . PHP_EOL;
            }

            $message .= "👤 Всего участников: " . count($participants);

            $bot->sendMessage(
                $message,
                reply_markup: $this->menuKeyboard(),
            );
        }

        $this->botService->setCurrentMenu($bot, StudentEventsMenu::ID);
        $this->end();
    }

    private function menuKeyboard(): ReplyKeyboardMarkup
    {
        return StudentEventsMenu::make();
    }
}
