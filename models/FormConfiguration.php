<?php
// models/FormConfiguration.php

require_once __DIR__ . '/../config/database.php';

class FormConfiguration
{
    /** @var \MongoDB\Collection */
    private $collection;

    public function __construct()
    {
        // Get the MongoDB\Database instance and select the collection
        $db = Database::getInstance()->getDb();
        $this->collection = $db->form_configurations;  // adjust the collection name if yours differs
    }

    /**
     * Fetch the currently active form configuration
     *
     * @return array|null
     */
    public function getActive()
    {
        return $this->collection->findOne(['active' => true]);
    }

    /**
     * Fetch all saved form configurations
     *
     * @return array
     */
    public function getAll()
    {
        return $this->collection
            ->find()
            ->toArray();
    }

    /**
     * Save (insert or update) a form configuration document
     *
     * @param array $config
     * @return \MongoDB\InsertOneResult|\MongoDB\UpdateResult
     */
    public function save(array $config)
    {
        if (!empty($config['_id'])) {
            // Update existing
            $id = $config['_id'];
            unset($config['_id']);
            return $this->collection->updateOne(
                ['_id' => $id],
                ['$set' => $config]
            );
        } else {
            // Insert new
            return $this->collection->insertOne($config);
        }
    }
}
