<?php

namespace App\Models;

use Illuminate\Support\Str;

class Facility
{
    public string $applicant;
    public ?string $type;
    public ?string $locationDescription;
    public string $address;
    public string $permit;
    public string $status;
    public ?string $foodItems;
    public float $latitude;
    public float $longitude;
    public string $schedule;
    public ?\DateTime $approvalDate;
    public ?\DateTime $expirationDate;

    public function __construct(array $facility)
    {
        // There's some meta data before the standard data we need
        $dataToParse = array_slice($facility, 8, null, true);

        $this->applicant = $dataToParse[9];
        $this->type = $dataToParse[10];
        $this->locationDescription = $dataToParse[12];
        $this->address = $dataToParse[13];
        $this->permit = $dataToParse[17];
        $this->status = $dataToParse[18];
        $this->foodItems = $dataToParse[19] ?? '';
        $this->latitude = $dataToParse[22];
        $this->longitude = $dataToParse[23];
        $this->schedule = $dataToParse[24];
        $this->approvalDate = match ($dataToParse[27]) {
            null => null,
            default => new \DateTime($dataToParse[27]),
        };
        $this->expirationDate = match ($dataToParse[30]) {
            null => null,
            default => new \DateTime($dataToParse[30]),
        };
    }

    public function checkCriteria(array $criteria)
    {
        foreach ($criteria as $prop => $value) {
            if ($value && !Str::of($this->{$prop})->contains($value, true)) return false;
        }

        return true;
    }

    public final function getStatus(): string
    {
        $approvedOn = $this->approvalDate ? $this->approvalDate->format('Y-m-d') : '';
        $expiresOn = $this->expirationDate ? $this->expirationDate->format('Y-m-d') : '';
        $color = match (strtolower($this->status)) {
            'approved' => 'info',
            'requested' => 'comment',
            default => 'error'
        };

        return sprintf(
            '<%s>%s</%s>%s%s', 
            $color, 
            $this->status, 
            $color, 
            ($approvedOn ? ' (Approved On ' . $approvedOn . ')' : ''), 
            ($expiresOn ? ' (Expiration of ' . $expiresOn . ')' : '')
        );
    }

    public final function getAddress(): string
    {
        return $this->address . ($this->locationDescription ? ' (' . $this->locationDescription . ')' : '');
    }
}