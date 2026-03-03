<?php

namespace App\Telegram\Conversation\Student;

use App\Event\EventService;
use App\Student\Dto\EventDto;
use App\Telegram\BotService;
use App\Telegram\Menu\MainMenu;
use DateMalformedStringException;
use DateTime;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardRemove;

class AddEventConversation extends Conversation
{
    protected ?string $name = null;
    protected ?DateTime $startDate = null;
    protected ?DateTime $finishDate = null;

    private const string BUTTON_CONFIRM = '✅ Подтвердить';
    private const string BUTTON_DECLINE = '❌ Отмена';
    private const string TEMPLATE_DATE = '01.01.2026 00:00:00';

    public function __construct(
        private readonly BotService   $botService,
        private readonly EventService $eventService
    ) {
    }

    protected function getSerializableAttributes(): array
    {
        return [
            'name' => $this->name,
            'start_date' => $this->startDate,
            'finish_date' => $this->finishDate,
        ];
    }

    public function start(Nutgram $bot): void
    {
        $this->botService->removeKeyboard($bot);
        $bot->sendMessage(
            '📅 Добавление мероприятия' . PHP_EOL . 'Введите название мероприятия',
            reply_markup: ReplyKeyboardRemove::make(true),
        );

        $this->next('setStartDate');
    }

    public function setStartDate(Nutgram $bot): void
    {
        $this->name = $bot->message()->text;
        $bot->sendMessage('Укажите дату начала мероприятия в формате 01.01.2026 00:00:00');
        $this->next('setFinishDate');
    }

    public function setFinishDate(Nutgram $bot): void
    {
        try {
            $this->startDate = new DateTime($bot->message()->text);
        } catch (DateMalformedStringException) {
            $bot->sendMessage('Некорректная дата. Укажите дату в формате ' . self::TEMPLATE_DATE);
            $this->next(__FUNCTION__);
            return;
        }

        $bot->sendMessage('Укажите дату окончания мероприятия в формате ' . self::TEMPLATE_DATE);
        $this->next('confirm');
    }

    public function confirm(Nutgram $bot): void
    {
        try {
            $this->finishDate = new DateTime($bot->message()->text);
        } catch (DateMalformedStringException) {
            $bot->sendMessage('Некорректная дата. Укажите дату в формате ' . self::TEMPLATE_DATE);
            $this->next(__FUNCTION__);
            return;
        }

        if ($this->finishDate < $this->startDate) {
            $bot->sendMessage('Дата окончания не может быть раньше даты начала');
            $this->next(__FUNCTION__);
            return;
        }

        $bot->sendMessage(
            '✅ Подтвердите добавление мероприятия:' . PHP_EOL
            . "Название: $this->name" . PHP_EOL
            . 'Дата начала: ' . $this->startDate->format('d.m.Y H:i:s') . PHP_EOL
            . 'Дата окончания: ' . $this->finishDate->format('d.m.Y H:i:s'),
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
            $dto = new EventDto(
                name: $this->name,
                startDate: $this->startDate,
                finishDate: $this->finishDate
            );
            $this->eventService->create($dto);

            $bot->sendMessage(
                "✅ Мероприятие «{$this->name}» успешно добавлено!",
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
