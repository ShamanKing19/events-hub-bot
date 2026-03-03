<?php

namespace App\Student\Dto;

use DateTime;

final readonly class EventDto
{
    public function __construct(
        public ?int      $id = null,
        public ?string   $name = null,
        public ?DateTime $startDate = null,
        public ?DateTime $finishDate = null
    ) {
    }
}
