<?php

namespace supercrafter333\theSpawn\events\tpa;

use supercrafter333\theSpawn\events\theSpawnTpaEvent;
use supercrafter333\theSpawn\tpa\Tpa;

class TpaAnswerEvent extends theSpawnTpaEvent
{

    public function __construct(Tpa $tpa, private readonly bool $answer)
    {
        parent::__construct($tpa);
    }

    /**
     * @return bool
     */
    public function getAnswer(): bool
    {
        return $this->answer;
    }
}