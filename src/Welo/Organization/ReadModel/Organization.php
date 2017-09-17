<?php

namespace Welo\Organization\ReadModel;

class Organization
{
    private $id;

    private $name;

    private $settings = [];

    private $createdAt;

    public function getName()
    {
        return $this->name;
    }

    public function getSettings($key = null)
    {
        if ($key === null) {

            return $this->settings;
        }

        if (array_key_exists($key, $this->settings)){

            return $this->settings[$key];
        }

        return null;
    }

}