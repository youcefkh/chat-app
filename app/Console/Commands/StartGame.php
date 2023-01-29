<?php

namespace App\Console\Commands;

use App\Events\RemainingTime;
use App\Events\WinnerNumber;
use Illuminate\Console\Command;

class StartGame extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start executing the game';

    private $time = 15;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        while(true) {
            broadcast(new RemainingTime($this->time . 's'));
            $this->time--;
            sleep(1);

            if ($this->time === 0) {
                broadcast(new RemainingTime($this->time . 's'));

                broadcast(new WinnerNumber(mt_rand(1,12)));

                sleep(10);
                $this->time = 15;
            }
        }
        //return Command::SUCCESS;
    }
}
