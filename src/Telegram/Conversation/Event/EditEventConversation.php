<?php

namespace App\Telegram\Conversation\Event;

use App\Event\Dto\EventDto;
use App\Event\EventService;
use App\Telegram\BotService;
use App\Telegram\Menu\MainMenu;
use DateMalformedStringException;
use DateTime;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;

class EditEventConversation extends Conversation
{
    protected ?int $id = null;
    protected ?string $name = null;
    protected ?DateTime $startDate = null;
    protected ?DateTime $finishDate = null;

    private const string BUTTON_CONFIRM = '✅ Подтвердить';
    private const string BUTTON_DECLINE = '❌ Отмена';
    private const string TEMPLATE_DATE = '01.01.2026 00:00:00';

    public function __construct(
        private readonly BotService $botService,
        private readonly EventService $eventService
    ) {}

    protected function getSerializableAttributes(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'start_date' => $this->startDate,
            'finish_date' => $this->finishDate,
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
                '📅 Выберите мероприятие, данные которого нужно изменить',
                reply_markup: $keyboard
            );
        }

        $this->next('handleChosenEvent');
    }

    public function handleChosenEvent(Nutgram $bot): void
    {
        $id = null;
        if ($callback = $bot->callbackQuery()) {
            $id = $callback->data;
        }

        if (!is_numeric($id) || !$this->eventService->exists((int)$id)) {
            $bot->sendMessage('Выберите мероприятие из списка');
            $this->next(__FUNCTION__);
            return;
        }

        $this->id = (int)$id;
        $bot->sendMessage('Введите новое название мероприятия студента:');
        $this->next('setStartDate');
    }


    public function setStartDate(Nutgram $bot): void
    {
        $this->name = $bot->message()->text;
        $bot->sendMessage('Укажите новую дату начала мероприятия в формате 01.01.2026 00:00:00');
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

        $bot->sendMessage('Укажите новую дату окончания мероприятия в формате ' . self::TEMPLATE_DATE);
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
            '✅ Подтвердите изменение мероприятия:' . PHP_EOL
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
                id: $this->id,
                name: $this->name,
                startDate: $this->startDate,
                finishDate: $this->finishDate
            );
            $this->eventService->update($dto);

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
