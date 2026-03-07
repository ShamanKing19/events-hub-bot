<?php

namespace App\Telegram\Conversation\StudentEvent;

use App\Student\StudentService;
use App\StudentEvent\StudentEventService;
use App\Telegram\BotService;
use App\Telegram\Menu\StudentEventsMenu;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;

class ViewParticipationConversation extends Conversation
{
    protected ?int $studentId = null;

    public function __construct(
        private readonly BotService          $botService,
        private readonly StudentService      $studentService,
        private readonly StudentEventService $studentEventService,
    ) {
    }

    protected function getSerializableAttributes(): array
    {
        return [
            'student_id' => $this->studentId,
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

        $this->next('showParticipation');
    }

    public function showParticipation(Nutgram $bot): void
    {
        $this->studentId = (int)$bot->callbackQuery()?->data;
        $student = $this->studentId ? $this->studentService->find($this->studentId) : null;
        if (!$student) {
            $bot->sendMessage('❌ Студент не найден');
            $this->next(__FUNCTION__);
            return;
        }

        $participations = $this->studentEventService->findByStudent($this->studentId);
        if (empty($participations)) {
            $bot->sendMessage(
                "📋 Участия студента: $student->name" . PHP_EOL . PHP_EOL .
                "Нет записей об участии в мероприятиях.",
                reply_markup: $this->menuKeyboard(),
            );
        } else {
            $message = "📋 Участия студента: $student->name" . PHP_EOL . PHP_EOL;
            $totalScore = 0;
            foreach ($participations as $index => $participation) {
                $message .= ($index + 1) . ". {$participation->getEvent()->getName()}" . PHP_EOL;
                $message .= "📅 " . $participation->getEvent()->getStartDate()->format('d.m.Y') . PHP_EOL;
                $message .= "🏆 Баллов: {$participation->getScore()}" . PHP_EOL . PHP_EOL;
                $totalScore += $participation->getScore();
            }

            $message .= "💯 Всего баллов: $totalScore";

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
