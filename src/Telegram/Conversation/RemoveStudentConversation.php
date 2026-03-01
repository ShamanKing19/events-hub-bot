<?php

namespace App\Telegram\Conversation;

use App\Student\StudentService;
use App\Telegram\BotService;
use App\Telegram\Menu\MainMenu;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;

class RemoveStudentConversation extends Conversation
{
    protected ?int $id = null;

    private const string BUTTON_CONFIRM = '✅ Подтвердить';
    private const string BUTTON_DECLINE = '❌ Отмена';

    public function __construct(
        private readonly BotService $botService,
        private readonly StudentService $studentService,
    ) {
    }

    protected function getSerializableAttributes(): array
    {
        return [
            'id' => $this->id
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
                '👤 Выберите студента, которого необходимо удалить',
                reply_markup: $keyboard
            );
        }

        $this->next('confirm');
    }

    public function confirm(Nutgram $bot): void
    {
        $id = $bot->callbackQuery()->data;
        if (!is_numeric($id) || !$this->studentService->exists((int)$id)) {
            $bot->sendMessage('Выберите студента из списка');
            $this->next(__METHOD__);
            return;
        }

        $this->id = (int)$id;
        $student = $this->studentService->find($this->id);
        if ($student === null) {
            $bot->sendMessage('Студент не найден. Выберите студента из списка.');
            $this->start($bot);
            return;
        }

        $bot->sendMessage(
            "Вы собираетесь удалить студента \"$student->name\"",
            reply_markup: ReplyKeyboardMarkup::make(resize_keyboard: true)
                ->addRow(
                    KeyboardButton::make(self::BUTTON_CONFIRM),
                    KeyboardButton::make(self::BUTTON_DECLINE),
                ),
        );

        $this->next('save');
    }

    public function save(Nutgram $bot): void
    {
        if ($bot->message()->text === self::BUTTON_CONFIRM) {
            $this->studentService->remove($this->id);

            $bot->sendMessage(
                "✅ Студент удалён!",
                reply_markup: $this->mainMenuKeyboard(),
            );
        } else {
            $bot->sendMessage('❌ Удаление отменено.', reply_markup: $this->mainMenuKeyboard());
        }

        $this->botService->setCurrentMenu($bot, MainMenu::ID);
        $this->end();
    }

    private function mainMenuKeyboard(): ReplyKeyboardMarkup
    {
        return MainMenu::make();
    }
}
