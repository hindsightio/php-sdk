<?php

namespace Hindsight\Monolog;

use GuzzleHttp\Client;
use Monolog\Handler\AbstractHandler;
use Monolog\Logger;

class HindsightMonologHandler extends AbstractHandler
{
    protected $apiToken;

    protected $logLevels = [
        Logger::DEBUG     => LOG_DEBUG,
        Logger::INFO      => LOG_INFO,
        Logger::NOTICE    => LOG_NOTICE,
        Logger::WARNING   => LOG_WARNING,
        Logger::ERROR     => LOG_ERR,
        Logger::CRITICAL  => LOG_CRIT,
        Logger::ALERT     => LOG_ALERT,
        Logger::EMERGENCY => LOG_EMERG,
    ];

    /**
     * HindsightMonologHandler constructor.
     *
     * @param string $apiToken
     * @param int $level
     * @param bool $bubble
     */
    public function __construct(string $apiToken, int $level = Logger::DEBUG, bool $bubble = true)
    {
        $this->apiToken = $apiToken;

        parent::__construct($level, $bubble);
    }

    /**
     * Handles a record.
     *
     * All records may be passed to this method, and the handler should discard
     * those that it does not want to handle.
     *
     * The return value of this function controls the bubbling process of the handler stack.
     * Unless the bubbling is interrupted (by returning true), the Logger class will keep on
     * calling further handlers in the stack with a given log record.
     *
     * @param  array $record The record to handle
     * @return Boolean true means that this handler handled the record, and that bubbling is not permitted.
     *                        false means the record was either not processed or that this handler allows bubbling.
     */
    public function handle(array $record)
    {
        $this->submitRecordsToHindsight([$record]);
    }

    /**
     * Handle a batch of records.
     *
     * @param array $records
     */
    public function handleBatch(array $records)
    {
        $this->submitRecordsToHindsight($records);
    }

    /**
     * Format a Monolog message for submission to Hindsight by merging extra into context.
     *
     * @param $record
     * @return array
     */
    protected function formatRecordForSubmission(array $record)
    {
        $record = $this->processRecord($record);

        $record['context'] = array_merge(
            $this->formatExtras($record['extra'] ?? []),
            $record['context'] ?? []
        );
        unset($record['extra']);

        if (! empty($record['context']['exception'])) {
            $record['context']['exception'] = $this->formatException($record['context']['exception']);
        }

        $record['timestamp'] = (int) $record['datetime']->format('Uv');
        unset($record['datetime']);

        $record['level'] = $this->logLevels[$record['level']];

        return $record;
    }

    /**
     * Format the extra data for submission.
     *
     * @param array $extra
     * @return array
     */
    protected function formatExtras(array $extra)
    {
        $formatted = array_map(function ($value, $key) {
            return ['_extra_'.$key => $value];
        }, $extra, array_keys($extra));

        return count($formatted) ? array_merge(
            ...$formatted
        ) : [];
    }

    /**
     * Normalizes an exception into a format suitable for sending to Hindsight.
     *
     * @param \Throwable $e
     * @param int $depth
     * @return array
     */
    protected function normalizeException(\Throwable $e, int $depth = 0)
    {
        $data = [
            'class' => get_class($e),
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile().':'.$e->getLine(),
        ];

        $trace = $e->getTrace();
        foreach ($trace as $frame) {
            if (isset($frame['file'])) {
                $data['trace'][] = $frame['file'].':'.$frame['line'];
            } else if (isset($frame['function']) && $frame['function'] === '{closure}') {
                $data['trace'][] = $frame['function'];
            } else {
                // We should again normalize the frames, because it might contain invalid items
                $data['trace'][] = $this->normalize($frame);
            }
        }

        if ($previous = $e->getPrevious()) {
            $data['previous'] = $this->normalizeException($previous, $depth + 1);
        }
        return $data;
    }

    /**
     * Normalizes given $data.
     *
     * @param mixed $data
     * @param int $depth
     * @return mixed
     */
    protected function normalize($data, int $depth = 0)
    {
        if (is_array($data) || $data instanceof \Traversable) {
            $normalized = [];
            $count = 1;

            foreach ($data as $key => $value) {
                if ($count++ >= 1000) {
                    $normalized['...'] = 'Over 1000 items, aborting normalization';
                    break;
                }

                $normalized[$key] = $this->normalize($value, $depth + 1);
            }

            return $normalized;
        }

        if ($data instanceof \Throwable) {
            return $this->normalizeException($data, $depth);
        }

        return $data;
    }

    /**
     * Submit the log messages to Hindsight.
     *
     * @param array $records
     */
    protected function submitRecordsToHindsight(array $records)
    {
        $httpClient = new Client([
            'base_uri' => 'https://logs.inhindsight.io',
        ]);

        $records = array_map([$this, 'formatRecordForSubmission'], $records);

        $httpClient->request('POST', '/', [
            'json' => [
                'messages' => $records,
            ],
            'headers' => ['Authorization' => "Bearer {$this->apiToken}"]
        ]);
    }

    /**
     * Processes a record.
     *
     * @param  array $record
     * @return array
     */
    protected function processRecord(array $record)
    {
        if ($this->processors) {
            foreach ($this->processors as $processor) {
                $record = call_user_func($processor, $record);
            }
        }

        return $record;
    }
}
