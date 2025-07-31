<?php
// tests/bootstrap.php

// Load Composer's autoloader
	require_once __DIR__ . '/../vendor/autoload.php';

// Define a constant for the base path if your application uses it
// If your app uses __DIR__ directly for paths, you might not need BASE_PATH
	if (!defined('BASE_PATH')) {
		define('BASE_PATH', '/cal'); // Adjust this based on your web server setup
	}

// Mock the Database class to prevent actual database connections during tests
// This is a basic mock. For more complex scenarios, you might use a mocking framework
// or a dedicated in-memory database like SQLite for integration tests.

// First, ensure the original Database class exists so we can mock it
	require_once __DIR__ . '/../config/database.php';

// Use a test-specific MongoDB URI or an in-memory database if possible.
// For this example, we'll mock the `getDb()` method to return a mock MongoDB\Database instance.

// IMPORTANT: This basic mock assumes you call Database::getInstance()->getDb()
// inside your models/services. It prevents a real connection.
// For more advanced mocking, consider using Mockery or PHPUnit's
// createMock/createStub for each dependency.

// A simple approach for mocking the Database singleton:
//	class MockDbClient extends MongoDB\Client {
//		public function __construct($uri) {
//			// Do nothing, prevent real connection
//		}
//		public function selectDatabase(string $databaseName, array $options = []): MongoDB\Database {
//			// Return a mock database instance
//			return (new class($this, $databaseName) extends MongoDB\Database {
//				public function __construct($client, $databaseName) {} // Prevent parent constructor call
//
//				public function selectCollection($name, array $options = []): MongoDB\Collection {
//					// Return a mock collection instance
//					return (new class($this, $name) extends MongoDB\Collection {
//						public function __construct($database, $collectionName) {} // Prevent parent constructor call
//
//						public function find($filter = [], array $options = []): MongoDB\Driver\Cursor {
//							// Return an empty mock cursor or a predefined one for specific tests
//							return (new class() extends MongoDB\Driver\Cursor {
//								public function __construct() {}
//								public function toArray(): array { return []; }
//								public function current() {}
//								public function next() {}
//								public function key() {}
//								public function valid(): bool { return false; }
//								public function rewind() {}
//							});
//						}
//						// CORRECTED: Match the original method signature
//						public function findOne($filter = [], array $options = []): ?array { return null; }
//						public function insertOne($document, array $options = []): MongoDB\InsertOneResult {
//							return (new class() extends MongoDB\InsertOneResult {
//								public function getInsertedCount(): int { return 1; }
//								public function getInsertedId() { return new MongoDB\BSON\ObjectId(); }
//							});
//						}
//						public function updateOne($query, $update, array $options = []): MongoDB\UpdateResult {
//							return (new class() extends MongoDB\UpdateResult {
//								public function getModifiedCount(): int { return 1; }
//								public function getUpsertedCount(): int { return 0; }
//								public function getMatchedCount(): int { return 1; }
//							});
//						}
//						public function updateMany($query, $update, array $options = []): MongoDB\UpdateResult {
//							return (new class() extends MongoDB\UpdateResult {
//								public function getModifiedCount(): int { return 1; }
//								public function getUpsertedCount(): int { return 0; }
//								public function getMatchedCount(): int { return 1; }
//							});
//						}
//						public function deleteOne($query, array $options = []): MongoDB\DeleteResult {
//							return (new class() extends MongoDB\DeleteResult {
//								public function getDeletedCount(): int { return 1; }
//							});
//						}
//						public function deleteMany($query, array $options = []): MongoDB\DeleteResult {
//							return (new class() extends MongoDB\DeleteResult {
//								public function getDeletedCount(): int { return 1; }
//							});
//						}
//						public function countDocuments($filter = [], array $options = []): int { return 0; }
//						public function aggregate(array $pipeline, array $options = []): Traversable {
//							return new ArrayIterator([]); // Return an empty iterator
//						}
//					});
//				}
//			});
//		}
//	}


// Override the real MongoDB\Client in the Database class
// This is a tricky part as Database uses `new MongoDB\Client()`.
// For simpler mocking, we'll use a trick or advise on using a mocking framework
// for internal dependencies or directly mocking the Database::getInstance() static method.

// For simplicity for a quick test, we'll directly modify the behavior of
// Database::getInstance()->getDb() by creating a mock instance that is returned.

// Temporarily replace the singleton instance
//	$mockDb = (new class extends Database {
//		private $mockClient;
//		private $mockDbInstance;
//		public function __construct() {
//			// Create specific mocks for collections that will be used in tests
//			$this->mockClient = new MockDbClient('mongodb://localhost');
//			$this->mockDbInstance = $this->mockClient->selectDatabase('test_db');
//
//			// Set specific mocks for collections
//			$this->mockDbInstance->users = $this->mockDbInstance->selectCollection('users');
//			$this->mockDbInstance->bookings = $this->mockDbInstance->selectCollection('bookings');
//			$this->mockDbInstance->company_settings = $this->mockDbInstance->selectCollection('company_settings');
//			$this->mockDbInstance->form_configurations = $this->mockDbInstance->selectCollection('form_configurations');
//			$this->mockDbInstance->email_templates = $this->mockDbInstance->selectCollection('email_templates');
//		}
//
//		public static function getInstance(): Database {
//			static $instance = null;
//			if ($instance === null) {
//				$instance = new self(); // Use self to create mock instance
//			}
//			return $instance;
//		}
//
//		public function getDb(): MongoDB\Database {
//			return $this->mockDbInstance;
//		}
//	});

// Replace the real Database singleton with our mock. This is usually done carefully.
// In real PHPUnit, you'd inject dependencies or use mocking frameworks.
// For this demonstration, we are effectively re-defining the static instance.
// This assumes `Database::getInstance()` is called *after* this bootstrap runs.

// Set up global mocks or includes if necessary
	require_once __DIR__ . '/../models/User.php';
	require_once __DIR__ . '/../models/Booking.php';
	require_once __DIR__ . '/../models/FormConfiguration.php';
	require_once __DIR__ . '/../models/CompanySettings.php';
	require_once __DIR__ . '/../models/EmailTemplate.php';
	require_once __DIR__ . '/../services/EmailService.php';
	require_once __DIR__ . '/../services/FormValidationService.php';
	require_once __DIR__ . '/../services/FormRenderer.php';
	require_once __DIR__ . '/../config/email.php'; // Required by EmailService