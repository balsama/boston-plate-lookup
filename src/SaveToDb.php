<?php

namespace Balsama\BostonPlateLookup;

use Medoo\Medoo;

class SaveToDb
{
    private Medoo $database;
    private PlateInfo $record;
    private int $timestamp;

    public function __construct(PlateInfo $record)
    {
        $this->timestamp = time();
        $this->database = Helpers::initializeDatabase();

        $this->record = $record;

        $this->insertLookup();
        $this->insertTickets();
    }

    public function insertLookup()
    {
        if ($this->record->getIsFound()) {
            return $this->insertFoundRecord();
        }
        else {
            return $this->insertNotFoundRecord();
        }
    }

    public function insertTickets()
    {
        if ($tickets = $this->record->getTickets()) {
            /* @var Ticket[] $tickets */
            foreach ($tickets as $ticket) {
                $existingRecord = $this->database->select('tickets', 'ticket_number', [
                    'ticket_number' => $ticket->ticketNumber,
                ]);

                if ($existingRecord) {
                    continue;
                }

                $this->database->insert(
                    'tickets',
                    [
                        'ticket_number' => $ticket->ticketNumber,
                        'plate_number' => $ticket->plateNumber,
                        'infraction' => $ticket->reason,
                        'fine' => $ticket->amount,
                        'infraction_date' => $ticket->dateIssuedString,
                        'infraction_time' => $ticket->timeIssuedString,
                        'infraction_address' => $ticket->address,
                    ]
                );
            }
        }
    }

    public function insertFoundRecord(): ?\PDOStatement
    {
        return $this->database->insert(
            'lookup',
            [
                'plate_number' => $this->record->getPlateNumber(),
                'found' => $this->record->getIsFound(),
                'balance' => $this->record->getBalance(),
                'full_response' => $this->record->getFullResponse(),
                'fetched_timestamp' => $this->timestamp,
            ]
        );
    }
    public function insertNotFoundRecord(): ?\PDOStatement
    {
        return $this->database->insert(
            'lookup',
            [
                'plate_number' => $this->record->getPlateNumber(),
                'found' => 0,
                'fetched_timestamp' => $this->timestamp,
            ]
        );
    }

    public function createDb()
    {
        $this->database = new Medoo([
            'type' => 'sqlite',
            'database' => 'lookups.db'
        ]);
    }
    public function createDbTables()
    {
        $this->database->create('lookup', [
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

        $this->database->create('tickets', [
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
    }
}
