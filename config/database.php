<?php
// config/database.php

require_once __DIR__ . '/../vendor/autoload.php';  // Adjust path to Composer’s autoload as needed

class Database
{
    private static $instance = null;
    private $client;
    private $db;

    private function __construct()
    {
        // Use your Atlas connection string directly
        $mongoUri = 'mongodb+srv://bastolasuraj:oxZwEAA62hWAnL4C'
            . '@cluster0.iyow4fd.mongodb.net'
            . '/?retryWrites=true&w=majority&appName=Cluster0';

        try {
            $this->client = new MongoDB\Client($mongoUri);
            // Select the database you’re using
            $this->db = $this->client->selectDatabase('booking_cms');

            // Ensure your indexes exist
            $this->db->bookings->createIndex(['access_code'    => 1], ['unique' => true]);
            $this->db->bookings->createIndex(['booking_datetime'=> 1]);
            // …add any other indexes you need here…

        } catch (\Exception $e) {
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Get the singleton instance of Database
     *
     * @return Database
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    /**
     * Get the MongoDB\Database object
     *
     * @return MongoDB\Database
     */
    public function getDb(): MongoDB\Database
    {
        return $this->db;
    }
}
