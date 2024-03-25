<?php

namespace App\Services\YagaAPI\Model;

class Hall
{

    protected $id;

    protected $name;

    protected $venueId;

    protected $levels;


    public function __construct(array $data = null)
    {
        $this->id = isset($data['id']) ? $data['id'] : null;
        $this->name = isset($data['name']) ? $data['name'] : null;
        $this->venueId = isset($data['venueId']) ? $data['venueId'] : null;
        $this->levels = isset($data['levels']) ? $data['levels'] : null;
    }

}


