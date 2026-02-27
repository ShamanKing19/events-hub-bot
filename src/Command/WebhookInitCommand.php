<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsCommand(
    name: 'webhook:init',
    description: 'Команда для инициализации бота',
)]
class WebhookInitCommand extends Command
{
    public function __construct(
        #[Autowire(param: 'site_url')] private readonly string $baseUrl,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('url', null, InputOption::VALUE_REQUIRED, 'Базовая ссылка для установки вебхука');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $baseUrl = rtrim($input->getOption('url'), '/') ?: $this->baseUrl;

        return $this->getApplication()->doRun(new ArrayInput([
            'command' => 'nutgram:hook:set',
            'url' => $baseUrl . $this->urlGenerator->generate('bot_webhook')
        ]), $output);
    }
}
