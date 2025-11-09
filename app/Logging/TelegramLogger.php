<?php

namespace App\Logging;

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use Illuminate\Support\Facades\Http;

class TelegramLogger
{
    public function __invoke(array $config)
    {
        return new Logger('telegram', [new TelegramHandler()]);
    }
}

class TelegramHandler extends AbstractProcessingHandler
{
    protected function write(LogRecord $record): void
    {
        try {
            // Elimina el dd(), registra en Telegram el contexto relevante de excepción si existe.
            if (isset($record->context['exception']) && $record->context['exception'] instanceof \Throwable) {
                $exception = $record->context['exception'];
                $contextString = "Exception: " . get_class($exception) . "\n"
                    . "Message: " . $exception->getMessage() . "\n"
                    . "File: " . $exception->getFile() . "\n"
                    . "Line: " . $exception->getLine() . "\n";
            } else {
                $contextString = json_encode($record->context, JSON_PRETTY_PRINT);
            }
            $message = "🚨 *Laravel Error Log*\n" .
                "Level: {$record->level->getName()}\n" .
                "Message: {$record->message}\n\n" .
                "Context: " . $contextString;

            Http::post("https://api.telegram.org/bot" . env('TELEGRAM_BOT_TOKEN') . "/sendMessage", [
                'chat_id' => env('TELEGRAM_CHAT_ID'),
                'text' => $message,
                'parse_mode' => 'Markdown',
            ]);
        } catch (\Exception $e) {
            // Evitamos que el log falle si Telegram no responde
        }
    }
}
