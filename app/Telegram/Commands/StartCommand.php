<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Commands\Command;

class StartCommand extends Command
{
    /**
     * The name of the command.
     *
     * @var string
     */
    protected string $name = 'start';  // ❌ Agar mavjud bo‘lmasa, qo‘shing

    /**
     * The command description.
     *
     * @var string
     */
    protected string  $description = "Botni ishga tushirish uchun /start buyrug'i";

    /**
     * Execute the command.
     */
    public function handle()
    {
        $this->replyWithMessage(['text' => "Assalomu alaykum! Botga xush kelibsiz."]);
    }
}
