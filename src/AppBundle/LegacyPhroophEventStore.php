<?php

namespace AppBundle;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventStore\EventStore;
use Broadway\EventStore\Exception\DuplicatePlayheadException;
use Doctrine\DBAL\Driver\Connection;

final class LegacyPhroophEventStore implements EventStore
{
    private $connection;

    private $tableName;

    public function __construct(Connection $connection, $tableName)
    {
        $this->connection = $connection;
        $this->tableName = $tableName;
    }

    /**
     * @param mixed $id
     *
     * @return DomainEventStream
     */
    public function load($id)
    {
        $query = "SELECT eventId, version, eventName, payload, occurredOn, aggregate_id, aggregate_type
                  FROM {$this->tableName}
                  WHERE aggregate_id = ?
                  AND version >= ?
                  ORDER BY version ASC";

        $statement = $this->connection->prepare($query);
        $statement->bindValue(1, $id);
        $statement->bindValue(2, 0);

        $statement->execute();

        $events = [];

        while ($row = $statement->fetch()) {
            $events[] = $this->deserializeEvent($row);
        }

        return $events;
    }

    /**
     * @param mixed $id
     * @param int $playhead
     */
    public function loadFromPlayhead($id, $playhead)
    {
        $query = "SELECT eventId, version, eventName, payload, occurredOn, aggregate_id, aggregate_type
                  FROM {$this->tableName}
                  WHERE aggregate_id = ?
                  AND version >= ?
                  ORDER BY version ASC";

        $statement = $this->connection->prepare($query);
        $statement->bindValue(1, $id);
        $statement->bindValue(2, $playhead);

        $statement->execute();

        $events = [];

        while ($row = $statement->fetch()) {
            $events[] = $this->deserializeEvent($row);
        }

        return $events;
    }

    private function deserializeEvent($row)
    {
        $payload = unserialize($row['payload']);

        $metadata = [
          'eventId' => $row['eventId'],
          'eventName' => $row['eventName'],
          'aggregateId' => $row['aggregate_id'],
          'aggreagateType' => $row['aggregate_type']
        ];

        return new DomainMessage(
            $row['aggregate_id'],
            (int) $row['version'],
            new Metadata($metadata),
            $payload,
            DateTime::fromString($row['occurredOn'])
        );
    }

    /**
     * @param mixed $id
     * @param DomainEventStream $eventStream
     *
     * @throws DuplicatePlayheadException
     */
    public function append($id, DomainEventStream $eventStream)
    {
        $id = (string) $id; // tries casting to string to catch error early

        $this->connection->beginTransaction();

        try {

            foreach ($eventStream as $domainMessage) {
                $this->insertMessage($domainMessage);
            }

            $this->connection->commit();
        } catch (UniqueConstraintViolationException $exception) {
            $this->connection->rollBack();

            throw new DuplicatePlayheadException($eventStream, $exception);
        } catch (DBALException $exception) {
            $this->connection->rollBack();

            throw DBALEventStoreException::create($exception);
        }
    }

    private function insertMessage(DomainMessage $domainMessage)
    {
        $metadata = $domainMessage->getMetadata()->serialize();

        $data = [
            eventId => (string) $domainMessage->getId(),
            version => $domainMessage->getPlayhead(),
            eventName => $metadata['eventName'],
            payload => serialize($domainMessage->getPayload()),
            occurredOn => $domainMessage->getRecordedOn()->toString(),
            aggregate_id => $metadata['aggregateId'],
            aggregate_type => $metadata['aggregateType']
        ];

        $this->connection->insert($this->tableName, $data);
    }
}