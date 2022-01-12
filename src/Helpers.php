<?php

namespace Balsama\BostonPlateLookup;

use Medoo\Medoo;

class Helpers
{
    public static function initializeDatabase(): Medoo
    {
        $database = new Medoo([
            'type' => 'sqlite',
            'database' => 'lookups.db'
        ]);

        $database->create('lookup', [
            'id' => [
                'INTEGER',
                'PRIMARY KEY'
            ],
            'plate_number' => ['TEXT'],
            'found' => ['INTEGER'],
            'balance' => ['FLOAT'],
            'full_response' => ['TEXT'],
            'fetched_timestamp' => ['INTEGER'],
        ]);

        $database->create('tickets', [
            'id' => [
                'INTEGER',
                'PRIMARY KEY',
            ],
            'ticket_number' => ['TEXT', 'UNIQUE'],
            'plate_number' => ['TEXT'],
            'infraction' => ['TEXT'],
            'fine' => ['FLOAT'],
            'infraction_date' => ['TEXT'],
            'infraction_time' => ['TEXT'],
            'infraction_address' => ['TEXT'],
        ]);

        return $database;
    }
}