<?php

namespace App\Controller;

use SergiX44\Nutgram\Nutgram;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class BotController extends AbstractController
{
    #[Route('/api/v1/bot/webhook', name: 'app_bot')]
    public function webhook(Nutgram $bot): JsonResponse
    {
        $bot->onCommand('start', function (Nutgram $bot) {
            return $bot->sendMessage('Hello, world!');
        })->description('The start command!');

        $bot->run();

        return $this->json([]);
    }
}
