<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class TgBotController extends AbstractController
{
    #[Route('/api/v1/bot/webhook', name: 'api_v1_bot_webhook')]
    public function webhook(): JsonResponse
    {
        return $this->json([]);
    }
}
