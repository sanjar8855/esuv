<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Commands\Command;

class StartCommand extends Command
{
    protected $name = "start";
    protected $description = "Botni ishga tushirish komandasi";

    public function handle()
    {
        $this->reply("Salom! Botga xush kelibsiz.");
    }
}
