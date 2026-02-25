<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use SergiX44\Nutgram\Nutgram;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class BotController extends AbstractController
{
    public function __construct(#[Target(name: 'monolog.logger.webhook')] private LoggerInterface $logger)
    {
    }

    #[Route('/api/v1/bot/webhook', name: 'app_bot')]
    public function webhook(Nutgram $bot, Request $request): JsonResponse
    {
        $this->logger->info('webhook', $request->toArray());

        $bot->onCommand('start', function (Nutgram $bot) {
            return $bot->sendMessage('Hello, world!');
        })->description('The start command!');

        $bot->run();

        return $this->json([]);
    }
}
