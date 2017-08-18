<?php

namespace AppBundle;

use Broadway\Serializer\Serializable;

class FakeEvent implements Serializable
{
    private $a;

    private $b;

    public function __construct($a, $b)
    {
        $this->a = $a;
        $this->b = $b;
    }

    public static function deserialize(array $data)
    {
        return new self();
    }

    public function serialize()
    {
        return [
            'a' => 'banana',
            'b' => 'gvnn'
        ];
    }
}