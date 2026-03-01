<?php

namespace App\Telegram\Conversation;

use App\Student\Dto\StudentDto;
use App\Student\StudentService;
use App\Telegram\Menu\MainMenu;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardRemove;

class EditStudentConversation extends Conversation
{
    protected ?int $id = null;
    protected ?string $name = null;

    private const string BUTTON_CONFIRM = '✅ Подтвердить';
    private const string BUTTON_DECLINE = '❌ Отмена';

    public function __construct(
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
        // 1. Удаляем клавиатуру
        $bot->sendMessage('...', reply_markup: ReplyKeyboardRemove::make(true))?->delete();

        // 2. Выводим список студентов
        $keyboard = InlineKeyboardMarkup::make();
        $students = $this->studentService->findEditable();
        foreach ($students as $student) {
            $keyboard->addRow(InlineKeyboardButton::make(text: $student->getName(), callback_data: $student->getId()));
        }

        $bot->sendMessage(
            '👤 Выберите студента, данные которого нужно изменить',
            reply_markup: $keyboard
        );

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

        $bot->setUserData('current_menu', MainMenu::ID);
        $this->end();
    }

    private function mainMenuKeyboard(): ReplyKeyboardMarkup
    {
        return MainMenu::make();
    }
}
