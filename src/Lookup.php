<?php

namespace Balsama\BostonPlateLookup;

class Lookup
{
    private PlateInfo $plateInfo;
    private LookupParameters $lookupParameters;
    private string $response;
    private bool $found = false;

    public function __construct($plateNumber, $plateType = 'PA', $yearDay = 1)
    {
        $this->plateInfo = new PlateInfo($plateNumber);
        $this->lookupParameters = new LookupParameters($plateNumber, $plateType, $yearDay);
        $this->process();
    }

    private function process()
    {
        if ($this->found) {
            return;
        }
        $this->response = $this->fetch();
        $this->found = ResponseParser::isFound($this->response);
        if ((!$this->found) && ($this->lookupParameters->yearDay < 366)) {
            $this->lookupParameters->incrementYearDay();
            return $this->process();
        }
        if ($this->found) {
            $balance = ResponseParser::getBalance($this->response);
            $this->plateInfo->setIsFound(true);
            $this->plateInfo->setBirthday($this->lookupParameters->getMonth(), $this->lookupParameters->getMonthDay());
            $this->plateInfo->setBalance($balance);
            $this->plateInfo->setFullResponse($this->response);
            if ($balance) {
                /* @var Ticket[] $tickets */
                $tickets = ResponseParser::getTickets($this->response);
                foreach ($tickets as $ticket) {
                    $ticket->plateNumber = $this->plateInfo->getPlateNumber();
                    $this->plateInfo->addTicket($ticket);
                }
            }
        }
    }

    private function fetch(): string
    {
        $request = RequestPlate::request($this->lookupParameters);
        $response = $request->getBody()->getContents();
        return $response;
    }

    public function getPlateInfo(): PlateInfo
    {
        return $this->plateInfo;
    }

    public function saveToDb(): SaveToDb
    {
        return new SaveToDb($this->plateInfo);
    }

}