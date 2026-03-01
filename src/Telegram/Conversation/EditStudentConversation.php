<?php

namespace App\Telegram\Conversation;

use App\Student\Dto\StudentDto;
use App\Student\StudentService;
use App\Telegram\BotService;
use App\Telegram\Menu\MainMenu;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;

class EditStudentConversation extends Conversation
{
    protected ?int $id = null;
    protected ?string $name = null;

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
            'id' => $this->id,
            'name' => $this->name,
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
                '👤 Выберите студента, данные которого нужно изменить',
                reply_markup: $keyboard
            );
        }

        $this->next('handleChosenStudent');
    }

    public function handleChosenStudent(Nutgram $bot): void
    {
        $id = null;
        if ($callback = $bot->callbackQuery()) {
            $id = $callback->data;
        }


        if (!is_numeric($id) || !$this->studentService->exists((int)$id)) {
            $bot->sendMessage('Выберите студента из списка');
            $this->next(__FUNCTION__);
            return;
        }

        $this->id = (int)$id;
        $bot->sendMessage('Введите новое ФИО студента:');
        $this->next('confirm');
    }

    public function confirm(Nutgram $bot): void
    {
        $this->name = $bot->message()->text;

        $bot->sendMessage(
            '✅ Подтвердите данные студента:' . PHP_EOL .
            "ФИО: $this->name",
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
        $text = $bot->message()->text;

        if ($text === self::BUTTON_CONFIRM) {
            $dto = new StudentDto(id: $this->id, name: $this->name);
            $this->studentService->update($dto);

            $bot->sendMessage(
                "✅ Данные успешно обновлены!",
                reply_markup: $this->mainMenuKeyboard(),
            );
        } else {
            $bot->sendMessage('❌ Изменение отменено.', reply_markup: $this->mainMenuKeyboard());
        }

        $this->botService->setCurrentMenu($bot, MainMenu::ID);
        $this->end();
    }

    private function mainMenuKeyboard(): ReplyKeyboardMarkup
    {
        return MainMenu::make();
    }
}
