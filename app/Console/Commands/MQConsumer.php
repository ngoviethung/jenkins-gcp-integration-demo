<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Log;
use App\Services\RabbitMQService;

class MQConsumerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:consume {severity}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Consume the mq queue';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $severity = $this->argument('severity');
        if(!$severity){
            return;
        }
        $mqService = new RabbitMQService();
        $mqService->consume($severity);
    }
}
