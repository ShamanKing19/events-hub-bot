<?php

namespace App\Telegram\Conversation\Student;

use App\Student\Dto\StudentDto;
use App\Student\StudentService;
use App\Telegram\BotService;
use App\Telegram\Menu\MainMenu;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardRemove;

class AddStudentConversation extends Conversation
{
    protected ?string $name = null;

    private const string BUTTON_CONFIRM = '✅ Подтвердить';
    private const string BUTTON_DECLINE = '❌ Отмена';

    public function __construct(
        private readonly BotService     $botService,
        private readonly StudentService $studentService
    ) {
    }

    protected function getSerializableAttributes(): array
    {
        return [
            'name' => $this->name,
        ];
    }

    public function start(Nutgram $bot): void
    {
        $this->botService->removeKeyboard($bot);
        $bot->sendMessage(
            '👤 Добавление студента' . PHP_EOL . 'Введите ФИО студента',
            reply_markup: ReplyKeyboardRemove::make(true),
        );

        $this->next('confirm');
    }

    public function confirm(Nutgram $bot): void
    {
        $this->name = $bot->message()->text;

        $bot->sendMessage(
            '✅ Подтвердите добавление студента:' . PHP_EOL .
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
            $dto = new StudentDto(name: $this->name);
            $this->studentService->create($dto);

            $bot->sendMessage(
                "✅ Студент «{$this->name}» успешно добавлен!",
                reply_markup: $this->mainMenuKeyboard(),
            );
        } else {
            $bot->sendMessage('❌ Добавление отменено.', reply_markup: $this->mainMenuKeyboard());
        }

        $this->botService->setCurrentMenu($bot, MainMenu::ID);
        $this->end();
    }

    private function mainMenuKeyboard(): ReplyKeyboardMarkup
    {
        return MainMenu::make();
    }
}
