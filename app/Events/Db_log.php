<?php

namespace App\Events;

use Config\App;
use Config\Database;

class Db_log
{
    private App $config;

    public function db_log_queries(): void
    {
        $this->config = config('App');

        if ($this->config->db_log_enabled) {
            $filepath = WRITEPATH . 'logs/Query-log-' . date('Y-m-d') . '.log';
            $handle   = fopen($filepath, 'a+b');
            $message  = $this->generate_message();

            if ($message !== '') {
                fwrite($handle, $message . "\n\n");
            }

            // Close the file
            fclose($handle);
        }
    }

    private function generate_message(): string
    {
        $db        = Database::connect();
        $lastQuery = $db->getLastQuery();

        if ($lastQuery === null) {
            return '';
        }

        $affectedRows  = $db->affectedRows();
        $executionTime = $this->convert_time($lastQuery->getDuration());

        $message = '*** Query: ' . date('Y-m-d H:i:s T') . ' *******************'
            . "\n" . $lastQuery->getQuery()
            . "\n Affected rows: {$affectedRows}"
            . "\n Execution Time: " . $executionTime['time'] . ' ' . $executionTime['unit'];

        $longQuery = ($executionTime['unit'] === 's') && ($executionTime['time'] > 0.5);
        if ($longQuery) {
            $message .= ' [LONG RUNNING QUERY]';
        }

        return $this->config->db_log_only_long && ! $longQuery ? '' : $message;
    }

    private function convert_time(float $time): array
    {
        $unit = 's';

        if ($time <= 0.1 && $time > 0.0001) {
            $time *= 1000;
            $unit = 'ms';
        } elseif ($time <= 0.0001) {
            $time *= 1000000;
            $unit = 'µs';
        }

        return ['time' => $time, 'unit' => $unit];
    }
}
