<?php

namespace Syno\Storm\Services;

use MongoDB\Driver;

class Publishing
{
    private string $mongoDbUrl;
    private string $mongoDb;

    public function __construct(string $mongoDbUrl, string $mongoDb)
    {
        $this->mongoDbUrl = $mongoDbUrl;
        $this->mongoDb    = $mongoDb;
    }

    public function insert(string $collection, array $data)
    {
        $bulk = new Driver\BulkWrite(['ordered' => true]);
        $bulk->insert($data);

        $manager      = new Driver\Manager($this->mongoDbUrl);
        $writeConcern = new Driver\WriteConcern(1);

        try {
            $manager->executeBulkWrite($this->mongoDb . '.' . $collection, $bulk, $writeConcern);
        } catch (Driver\Exception\BulkWriteException $exception) {
            throw $exception;
        } catch (Driver\Exception\Exception $exception) {
            throw $exception;
        }
    }
}
