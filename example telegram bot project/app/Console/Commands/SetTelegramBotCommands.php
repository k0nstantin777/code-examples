<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Helper\TableCell;
use Telegram\Bot\Api;
use Telegram\Bot\BotsManager;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Objects\BotCommand;

class SetTelegramBotCommands extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:commands {bot? : The bot name defined in the config file}
                {--all : To perform actions on all your bots.}
                {--setup : To declare your commands on Telegram servers. So they can call you.}
                {--remove : To remove your already declared commands on Telegram servers.}
                {--info : To get the information about your current commands on Telegram servers.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ease the Process of setting up and removing commands.';

    /** @var Api */
    protected $telegram;

    /** @var BotsManager */
    protected $botsManager;

    /** @var array Bot Config */
    protected $config = [];

    /**
     * WebhookCommand constructor.
     *
     * @param BotsManager $botsManager
     */
    public function __construct(BotsManager $botsManager)
    {
        parent::__construct();

        $this->botsManager = $botsManager;
    }

    /**
     * Execute the console command.
     *
     * @throws TelegramSDKException
     */
    public function handle()
    {
        $bot = $this->hasArgument('bot') ? $this->argument('bot') : null;
        $this->telegram = $this->botsManager->bot($bot);
        $this->config = $this->botsManager->getBotConfig($bot);

        if ($this->option('setup')) {
            $this->setupCommands();
        }

        if ($this->option('remove')) {
            $this->removeCommands();
        }

        if ($this->option('info')) {
            $this->getInfo();
        }
    }

    /**
     * Setup Commands.
     * @throws TelegramSDKException
     */
    protected function setupCommands()
    {
        $arrayCommands = [];
        foreach ($this->telegram->getCommands() as $command) {
            $arrayCommands[] = BotCommand::make([
                'command' => $command->getName(),
                'description' => $command->getDescription(),
            ]);
        }
        $response = $this->telegram->setMyCommands([
            'commands' => $arrayCommands,
        ]);

        if ($response) {
            $this->info('Success: Your commands has been set!');

            return;
        }

        $this->error('Your commands could not be set!');
    }

    /**
     * Remove Commands.
     * @throws TelegramSDKException
     */
    protected function removeCommands()
    {
        if ($this->confirm("Are you sure you want to remove the commands")) {
            $this->info('Removing Commands...');

            if ($this->telegram->removeCommands($this->telegram->getCommands())) {
                $this->info('Commands removed successfully!');

                return;
            }

            $this->error('Commands removal failed');
        }
    }

    /**
     * Get Commands Info.
     * @throws TelegramSDKException
     */
    protected function getInfo()
    {
        $this->alert('Commands Info');

        if ($this->hasArgument('bot') && ! $this->option('all')) {
            $response = $this->telegram->getMyCommands();
            $this->makeInfoResponse($response, $this->config['username']);

            return;
        }

        if ($this->option('all')) {
            $bots = $this->botsManager->getConfig('bots');
            collect($bots)->each(function ($bot, $key) {
                $response = $this->botsManager->bot($key)->getMyCommands();
                $this->makeInfoResponse($response, $bot['username']);
            });
        }
    }

    /**
     * Make WebhookInfo Response for console.
     *
     * @param array $response
     * @param string      $bot
     */
    protected function makeInfoResponse(array $response, string $bot)
    {
        $response = collect($response);
        $rows = $response->map(function ($value, $key) {
            $key = Str::title(str_replace('_', ' ', $key));
            $value = is_bool($value) ? $this->mapBool($value) : $value;

            return compact('key', 'value');
        })->toArray();

        $this->table([
            [new TableCell('Bot: '.$bot, ['colspan' => 2])],
            ['Key', 'Info'],
        ], $rows);
    }

    /**
     * Map Boolean Value to Yes/No.
     *
     * @param $value
     *
     * @return string
     */
    protected function mapBool($value)
    {
        return $value ? 'Yes' : 'No';
    }
}
