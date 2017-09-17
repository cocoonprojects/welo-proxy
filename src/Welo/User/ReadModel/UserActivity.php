<?php

class UserActivity
{
    private $id;

    private $organizationId;

    private $userId;

    private $credits;

    private $occuredOn;

    private function __construct() {}

    public static function create($id, $organizationId, $userId, $credits, \DateTime $occuredOn)
    {
        $activity = new self;
        $activity->id = $id;
        $activity->organizationId = $organizationId;
        $activity->userId = $userId;
        $activity->credits = $credits;
        $activity->occuredOn = $occuredOn;

        return $activity;
    }
}