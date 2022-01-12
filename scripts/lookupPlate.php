<?php
include_once('vendor/autoload.php');

use Medoo\Medoo;
use Balsama\BostonPlateLookup\Lookup;

$plateNumber = $argv[1];
if (!is_string($plateNumber)) {
    throw new Exception('You must provide a plate number as an argument to this script');
}

$database = new Medoo([
    'type' => 'sqlite',
    'database' => 'lookups.db'
]);

$existingRecord = $database->select('lookup', ['plate_number', 'fetched_timestamp'], [
    'plate_number' => $plateNumber,
    'ORDER' => ['fetched_timestamp' => 'DESC'],
    'LIMIT' => 1,
]);

if (!$existingRecord) {
    $lookup = new Lookup($plateNumber);
    $lookup->saveToDb();
}
else {
    $existingRecordTimestamp = reset($existingRecord)['fetched_timestamp'];
    if ((time() - $existingRecordTimestamp) > 86400) {
        $lookup = new Lookup($plateNumber);
        $lookup->saveToDb();
    }
}

$record = $database->select('lookup', '*', [
    'plate_number' => $plateNumber,
    'ORDER' => ['fetched_timestamp' => 'DESC'],
    'LIMIT' => 1,
]);
$record = reset($record);
$tickets = $database->select('tickets', '*', [
    'plate_number' => $plateNumber,
]);

$format = "Plate %s has a current balance of $%4.2f.\n";
print sprintf($format, $plateNumber, $record['balance']);
if ($tickets) {
    print "Tickets:\n";
    foreach ($tickets as $ticket) {
        /* @var \Balsama\BostonPlateLookup\Ticket $ticket */
        $format = "%s issued %s %s at %s.\n";
        print sprintf($format, $ticket['infraction'], $ticket['infraction_date'], $ticket['infraction_time'], $ticket['infraction_address']);
    }
}

$foo = 21;