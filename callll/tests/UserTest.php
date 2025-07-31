<?php
// tests/UserTest.php

use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private $userModel;
    private $mockDatabase; // Mock of the Database singleton
    private $mockDb;       // Mock of MongoDB\Database
    private $mockCollection; // Mock of MongoDB\Collection for 'users'

    protected function setUp(): void
    {
        // 1. Create a mock for the Database singleton itself
        $this->mockDatabase = $this->createMock(Database::class);

        // 2. Create a mock for the MongoDB\Database object
        $this->mockDb = $this->createMock(MongoDB\Database::class);

        // 3. Configure the mock Database singleton to return our mock MongoDB\Database
        $this->mockDatabase->method('getDb')->willReturn($this->mockDb);

        // 4. Set the mock Database instance for testing
        Database::setInstanceForTesting($this->mockDatabase);

        // 5. Create a mock for the MongoDB\Collection specific to 'users'
        $this->mockCollection = $this->createMock(MongoDB\Collection::class);

        // 6. Configure the mock MongoDB\Database to return our mock 'users' collection
        $this->mockDb->method('selectCollection')
            ->with('users') // Expect 'users' collection to be selected
            ->willReturn($this->mockCollection);

        // 7. Configure the mock collection methods for UserTest
        $this->mockCollection->method('insertOne')->willReturn(
            $this->createStub(MongoDB\InsertOneResult::class)
                ->method('getInsertedCount')->willReturn(1)
                ->method('getInsertedId')->willReturn(new MongoDB\BSON\ObjectId())
                ->getMock()
        );
        $this->mockCollection->method('countDocuments')->willReturn(0); // Default: no users
        $this->mockCollection->method('findOne')->willReturn(null); // Default: no user found
        $this->mockCollection->method('updateOne')->willReturn(
            $this->createStub(MongoDB\UpdateResult::class)
                ->method('getModifiedCount')->willReturn(1)
                ->getMock()
        );
        $this->mockCollection->method('deleteOne')->willReturn(
            $this->createStub(MongoDB\DeleteResult::class)
                ->method('getDeletedCount')->willReturn(1)
                ->getMock()
        );
        $this->mockCollection->method('find')->willReturn(
            $this->createStub(MongoDB\Driver\Cursor::class)
                ->method('toArray')->willReturn([])
                ->getMock()
        );

        // Instantiate the User model (which will now use the mocked Database singleton)
        $this->userModel = new User();
    }

    protected function tearDown(): void
    {
        // Reset the Database instance after each test to avoid interference
        Database::setInstanceForTesting(null);
    }

    public function testRegisterUserSuccessfully()
    {
        // Configure mock for successful insertion and no existing user
        $this->mockCollection->expects($this->once())
            ->method('countDocuments')
            ->with(['email' => 'test@example.com'])
            ->willReturn(0); // No existing user

        $this->mockCollection->expects($this->once())
            ->method('insertOne'); // Expect insert operation

        $result = $this->userModel->register('test@example.com', 'password123', User::ROLE_USER, 'Test User');
        $this->assertTrue($result);
    }

    public function testRegisterUserWithExistingEmailFails()
    {
        // Configure mock to return 1 for existing user count
        $this->mockCollection->expects($this->once())
            ->method('countDocuments')
            ->with(['email' => 'existing@example.com'])
            ->willReturn(1); // User already exists

        $result = $this->userModel->register('existing@example.com', 'password123', User::ROLE_USER, 'Existing User');
        $this->assertFalse($result);
    }

    public function testRegisterUserWithInvalidEmailFails()
    {
        $result = $this->userModel->register('invalid-email', 'password123', User::ROLE_USER, 'Invalid User');
        $this->assertFalse($result);
    }

    public function testLoginUserSuccessfully()
    {
        // Mock a user document returned by findOne
        $mockUser = [
            '_id' => new MongoDB\BSON\ObjectId(),
            'email' => 'login@example.com',
            'password' => password_hash('correctpassword', PASSWORD_DEFAULT), // Hashed password
            'active' => true,
            'name' => 'Login User',
            'role' => User::ROLE_ADMIN,
            'permissions' => ['view_dashboard', 'manage_bookings']
        ];

        $this->mockCollection->expects($this->once())
            ->method('findOne')
            ->with(['email' => 'login@example.com', 'active' => true])
            ->willReturn($mockUser);

        $this->mockCollection->expects($this->once())
            ->method('updateOne'); // Expect update for last_login

        $loggedInUser = $this->userModel->login('login@example.com', 'correctpassword');
        $this->assertIsArray($loggedInUser);
        $this->assertEquals('login@example.com', $loggedInUser['email']);
    }

    public function testLoginUserWithIncorrectPasswordFails()
    {
        $mockUser = [
            '_id' => new MongoDB\BSON\ObjectId(),
            'email' => 'login@example.com',
            'password' => password_hash('correctpassword', PASSWORD_DEFAULT),
            'active' => true,
            'name' => 'Login User',
            'role' => User::ROLE_USER,
            'permissions' => []
        ];

        $this->mockCollection->expects($this->once())
            ->method('findOne')
            ->willReturn($mockUser);

        $loggedInUser = $this->userModel->login('login@example.com', 'wrongpassword');
        $this->assertFalse($loggedInUser);
    }

    public function testHasPermissionReturnsTrueForGrantedPermission()
    {
        $userId = new MongoDB\BSON\ObjectId();
        $mockUser = [
            '_id' => $userId,
            'email' => 'admin@example.com',
            'active' => true,
            'permissions' => new MongoDB\Model\BSONArray(['manage_users', 'view_dashboard']), // Mock BSONArray
        ];

        $this->mockCollection->expects($this->once())
            ->method('findOne')
            ->with(['_id' => $userId])
            ->willReturn($mockUser);

        $this->assertTrue($this->userModel->hasPermission((string)$userId, 'manage_users'));
    }

    public function testHasPermissionReturnsFalseForDeniedPermission()
    {
        $userId = new MongoDB\BSON\ObjectId();
        $mockUser = [
            '_id' => $userId,
            'email' => 'user@example.com',
            'active' => true,
            'permissions' => new MongoDB\Model\BSONArray(['view_dashboard']),
        ];

        $this->mockCollection->expects($this->once())
            ->method('findOne')
            ->with(['_id' => $userId])
            ->willReturn($mockUser);

        $this->assertFalse($this->userModel->hasPermission((string)$userId, 'manage_users'));
    }

    public function testDeleteUserSuccessfully()
    {
        $userIdToDelete = new MongoDB\BSON\ObjectId();
        $mockUser = [
            '_id' => $userIdToDelete,
            'email' => 'user_to_delete@example.com',
            'role' => User::ROLE_USER,
            'active' => true
        ];

        // Mock getting the user to be deleted
        $this->mockCollection->expects($this->once())
            ->method('findOne')
            ->with(['_id' => $userIdToDelete])
            ->willReturn($mockUser);

        // Mock count of super admins
        $this->mockCollection->expects($this->once())
            ->method('countDocuments')
            ->with(['role' => User::ROLE_SUPER_ADMIN])
            ->willReturn(10); // More than one super admin

        $this->mockCollection->expects($this->once())
            ->method('deleteOne')
            ->with(['_id' => $userIdToDelete]);

        $result = $this->userModel->deleteUser((string)$userIdToDelete);
        $this->assertTrue($result);
    }

    public function testCannotDeleteLastSuperAdmin()
    {
        $superAdminId = new MongoDB\BSON\ObjectId();
        $mockSuperAdmin = [
            '_id' => $superAdminId,
            'email' => 'last_admin@example.com',
            'role' => User::ROLE_SUPER_ADMIN,
            'active' => true
        ];

        // Mock getting the user to be deleted
        $this->mockCollection->expects($this->once())
            ->method('findOne')
            ->with(['_id' => $superAdminId])
            ->willReturn($mockSuperAdmin);

        // Mock count of super admins to be 1
        $this->mockCollection->expects($this->once())
            ->method('countDocuments')
            ->with(['role' => User::ROLE_SUPER_ADMIN])
            ->willReturn(1); // Only one super admin

        // Ensure deleteOne is NOT called
        $this->mockCollection->expects($this->never())
            ->method('deleteOne');

        $result = $this->userModel->deleteUser((string)$superAdminId);
        $this->assertFalse($result);
    }
}