<?php
namespace App\Logging;

use Monolog\Handler\AbstractProcessingHandler;
use Google\Cloud\Logging\LoggingClient;

class GoogleCloudLoggingHandler extends AbstractProcessingHandler
{
    protected $logger;

    public function __construct($level = 'debug')
    {
        parent::__construct($level);

        if (app()->environment('production')) {
            $projectId = env('GOOGLE_APPLICATION_PROJECT_ID');
            $logging = new LoggingClient(['projectId' => $projectId]);
            $this->logger = $logging->logger(env('APP_NAME', 'gptworks'));
        }
    }

    protected function write(array $record): void
    {
        if ($this->logger) {
            $entry = $this->logger->entry($record['formatted']);
            $this->logger->write($entry);
        }
    }
}
