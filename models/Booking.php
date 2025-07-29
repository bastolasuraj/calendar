<?php
// Enhanced Booking model with dynamic form support

class Booking {
    private $db;
    private $collection;
    private $formConfig;

    public function __construct() {
        $this->db = Database::getInstance()->getDb();
        $this->collection = $this->db->bookings;
        $this->formConfig = new FormConfiguration();
    }

    // Create a new booking with dynamic form data
    public function createBooking($formData, $bookingDateTime) {
        if (empty($formData) || empty($bookingDateTime) || !$this->isTimeSlotAvailable($bookingDateTime)) {
            return false;
        }

        // Validate form data against current form configuration
        $formValidation = new FormValidationService();
        $validationResult = $formValidation->validateSubmission($formData);

        if (!$validationResult['valid']) {
            return ['success' => false, 'errors' => $validationResult['errors']];
        }

        $accessCode = $this->generateAccessCode();

        // Extract email for naming (fallback to first text field)
        $email = $formData['email'] ?? $formData['Email'] ?? '';
        $name = '';

        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $name = explode('@', $email)[0];
        } else {
            // Use first non-email field as name
            foreach ($formData as $key => $value) {
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $name = $value;
                    break;
                }
            }
        }

        try {
            $result = $this->collection->insertOne([
                'form_data' => $formData,
                'name' => $name,
                'email' => $email,
                'booking_datetime' => new MongoDB\BSON\UTCDateTime(strtotime($bookingDateTime) * 1000),
                'access_code' => $accessCode,
                'status' => 'pending',
                'created_at' => new MongoDB\BSON\UTCDateTime(),
                'updated_at' => new MongoDB\BSON\UTCDateTime(),
                'form_version' => $this->formConfig->getCurrentFormVersion()
            ]);

            if ($result->getInsertedCount() > 0) {
                // Send confirmation email
                $emailService = new EmailService();
                $emailService->sendBookingConfirmation($formData, $accessCode, $bookingDateTime);

                return ['success' => true, 'access_code' => $accessCode];
            }

            return ['success' => false, 'errors' => ['general' => 'Failed to create booking']];

        } catch (Exception $e) {
            error_log('Booking creation error: ' . $e->getMessage());
            return ['success' => false, 'errors' => ['general' => 'Database error occurred']];
        }
    }

    // Update booking status and send notification
    public function updateBookingStatus($accessCode, $status) {
        if (!in_array($status, ['approved', 'rejected', 'pending', 'cancelled'])) {
            return false;
        }

        try {
            $booking = $this->getBookingByAccessCode($accessCode);
            if (!$booking) {
                return false;
            }

            $result = $this->collection->updateOne(
                ['access_code' => $accessCode],
                [
                    '$set' => [
                        'status' => $status,
                        'updated_at' => new MongoDB\BSON\UTCDateTime()
                    ]
                ]
            );

            if ($result->getModifiedCount() > 0) {
                // Send status update email
                $emailService = new EmailService();
                $emailService->sendStatusUpdate($booking, $status);
                return true;
            }

            return false;

        } catch (Exception $e) {
            error_log('Status update error: ' . $e->getMessage());
            return false;
        }
    }

    // Get public bookings with enhanced filtering
    public function getPublicBookings($limit = 50) {
        try {
            return $this->collection->find(
                ['status' => 'approved'],
                [
                    'projection' => ['name' => 1, 'booking_datetime' => 1, 'form_data' => 1],
                    'sort' => ['booking_datetime' => 1],
                    'limit' => $limit
                ]
            );
        } catch (Exception $e) {
            error_log('Error fetching public bookings: ' . $e->getMessage());
            return [];
        }
    }

    // Get all bookings with pagination and filtering
    public function getAllBookings($page = 1, $limit = 25, $filters = []) {
        try {
            $skip = ($page - 1) * $limit;
            $query = [];

            // Apply filters
            if (!empty($filters['status'])) {
                $query['status'] = $filters['status'];
            }

            if (!empty($filters['date_from'])) {
                $query['booking_datetime']['$gte'] = new MongoDB\BSON\UTCDateTime(strtotime($filters['date_from']) * 1000);
            }

            if (!empty($filters['date_to'])) {
                $query['booking_datetime']['$lte'] = new MongoDB\BSON\UTCDateTime(strtotime($filters['date_to']) * 1000);
            }

            if (!empty($filters['search'])) {
                $query['$or'] = [
                    ['name' => new MongoDB\BSON\Regex($filters['search'], 'i')],
                    ['email' => new MongoDB\BSON\Regex($filters['search'], 'i')],
                    ['access_code' => new MongoDB\BSON\Regex($filters['search'], 'i')]
                ];
            }

            $options = [
                'sort' => ['created_at' => -1],
                'skip' => $skip,
                'limit' => $limit
            ];

            $bookings = $this->collection->find($query, $options);
            $total = $this->collection->countDocuments($query);

            return [
                'bookings' => $bookings,
                'total' => $total,
                'page' => $page,
                'pages' => ceil($total / $limit)
            ];

        } catch (Exception $e) {
            error_log('Error fetching bookings: ' . $e->getMessage());
            return ['bookings' => [], 'total' => 0, 'page' => 1, 'pages' => 0];
        }
    }

    // Enhanced analytics for admin dashboard
    public function getBookingAnalytics($days = 30) {
        try {
            $startDate = new MongoDB\BSON\UTCDateTime((time() - ($days * 24 * 60 * 60)) * 1000);

            $pipeline = [
                ['$match' => ['created_at' => ['$gte' => $startDate]]],
                [
                    '$group' => [
                        '_id' => '$status',
                        'count' => ['$sum' => 1]
                    ]
                ]
            ];

            $statusCounts = $this->collection->aggregate($pipeline)->toArray();

            // Daily booking counts
            $dailyPipeline = [
                ['$match' => ['created_at' => ['$gte' => $startDate]]],
                [
                    '$group' => [
                        '_id' => [
                            'year' => ['$year' => '$created_at'],
                            'month' => ['$month' => '$created_at'],
                            'day' => ['$dayOfMonth' => '$created_at']
                        ],
                        'count' => ['$sum' => 1]
                    ]
                ],
                ['$sort' => ['_id' => 1]]
            ];

            $dailyCounts = $this->collection->aggregate($dailyPipeline)->toArray();

            return [
                'status_counts' => $statusCounts,
                'daily_counts' => $dailyCounts,
                'total_bookings' => $this->collection->countDocuments(['created_at' => ['$gte' => $startDate]])
            ];

        } catch (Exception $e) {
            error_log('Analytics error: ' . $e->getMessage());
            return ['status_counts' => [], 'daily_counts' => [], 'total_bookings' => 0];
        }
    }

    // Check holiday status with enhanced Canadian holidays
    private function isHoliday($date) {
        $year = $date->format('Y');
        $dateStr = $date->format('Y-m-d');

        // Fixed holidays
        $fixedHolidays = [
            "$year-01-01", // New Year's Day
            "$year-07-01", // Canada Day
            "$year-12-25", // Christmas Day
            "$year-12-26"  // Boxing Day
        ];

        // Good Friday dates (calculated)
        $goodFridays = ['2025-04-18', '2026-04-03', '2027-03-26', '2028-04-14', '2029-03-30'];

        if (in_array($dateStr, array_merge($fixedHolidays, $goodFridays))) {
            return true;
        }

        // Calculated holidays
        $holidays = [
            date("Y-m-d", strtotime("third monday of february $year")), // Family Day
            date('Y-m-d', strtotime("last monday", strtotime("$year-05-25"))), // Victoria Day
            date("Y-m-d", strtotime("first monday of september $year")), // Labour Day
            date("Y-m-d", strtotime("second monday of october $year")) // Thanksgiving
        ];

        // Handle Canada Day weekend adjustments
        $canadaDay = new DateTime("$year-07-01");
        if ($canadaDay->format('N') == 6) { // Saturday
            $holidays[] = "$year-07-03"; // Monday
        } elseif ($canadaDay->format('N') == 7) { // Sunday
            $holidays[] = "$year-07-02"; // Monday
        }

        return in_array($dateStr, $holidays);
    }

    public function isTimeSlotAvailable($dateTime) {
        try {
            $bookingDateTime = new MongoDB\BSON\UTCDateTime(strtotime($dateTime) * 1000);
            $count = $this->collection->countDocuments([
                'booking_datetime' => $bookingDateTime,
                'status' => ['$in' => ['approved', 'pending']]
            ]);
            return $count === 0;
        } catch (Exception $e) {
            error_log('Time slot check error: ' . $e->getMessage());
            return false;
        }
    }

    public function getAvailableSlotsForDate($dateStr) {
        try {
            $date = new DateTime($dateStr);
            $dayOfWeek = $date->format('N');

            // Check if weekend or holiday
            if ($dayOfWeek >= 6 || $this->isHoliday($date)) {
                return [];
            }

            // Get company settings for working hours
            $companySettings = new CompanySettings();
            $workingHours = $companySettings->getWorkingHours();

            $startTime = new DateTime($dateStr . ' ' . $workingHours['start']);
            $endTime = new DateTime($dateStr . ' ' . $workingHours['end']);
            $interval = new DateInterval('PT' . $workingHours['slot_duration'] . 'H');

            // Get booked slots for the day
            $dayStart = new MongoDB\BSON\UTCDateTime(strtotime($dateStr . ' 00:00:00') * 1000);
            $dayEnd = new MongoDB\BSON\UTCDateTime(strtotime($dateStr . ' 23:59:59') * 1000);

            $bookedSlots = $this->collection->find(
                [
                    'booking_datetime' => ['$gte' => $dayStart, '$lte' => $dayEnd],
                    'status' => ['$in' => ['approved', 'pending']]
                ],
                ['projection' => ['booking_datetime' => 1]]
            );

            $bookedTimes = [];
            foreach ($bookedSlots as $booking) {
                $bookedTimes[] = $booking['booking_datetime']->toDateTime()->format('H:i');
            }

            // Generate available slots
            $availableSlots = [];
            $period = new DatePeriod($startTime, $interval, $endTime);

            foreach ($period as $slot) {
                $slotTime = $slot->format('H:i');
                if (!in_array($slotTime, $bookedTimes)) {
                    $availableSlots[] = $slotTime;
                }
            }

            return $availableSlots;

        } catch (Exception $e) {
            error_log('Available slots error: ' . $e->getMessage());
            return [];
        }
    }

    private function generateAccessCode($length = 15) {
        do {
            $code = substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/62))), 1, $length);
            $exists = $this->collection->countDocuments(['access_code' => $code]) > 0;
        } while ($exists);

        return $code;
    }

    public function getBookingByAccessCode($accessCode) {
        try {
            return $this->collection->findOne(['access_code' => $accessCode]);
        } catch (Exception $e) {
            error_log('Get booking error: ' . $e->getMessage());
            return null;
        }
    }

    public function updateBooking($accessCode, $newDateTime, $newFormData = null) {
        if (empty($accessCode) || empty($newDateTime)) {
            return false;
        }

        if (!$this->isTimeSlotAvailable($newDateTime)) {
            return false;
        }

        try {
            $updateData = [
                'booking_datetime' => new MongoDB\BSON\UTCDateTime(strtotime($newDateTime) * 1000),
                'status' => 'pending', // Reset status on update
                'updated_at' => new MongoDB\BSON\UTCDateTime()
            ];

            // Update form data if provided
            if ($newFormData !== null) {
                $formValidation = new FormValidationService();
                $validationResult = $formValidation->validateSubmission($newFormData);

                if (!$validationResult['valid']) {
                    return ['success' => false, 'errors' => $validationResult['errors']];
                }

                $updateData['form_data'] = $newFormData;
            }

            $result = $this->collection->updateOne(
                ['access_code' => $accessCode],
                ['$set' => $updateData]
            );

            if ($result->getModifiedCount() > 0) {
                // Send update notification
                $booking = $this->getBookingByAccessCode($accessCode);
                $emailService = new EmailService();
                $emailService->sendBookingUpdate($booking);
                return true;
            }

            return false;

        } catch (Exception $e) {
            error_log('Booking update error: ' . $e->getMessage());
            return false;
        }
    }

    // Export bookings data
    public function exportBookings($format = 'csv', $filters = []) {
        try {
            $bookings = $this->getAllBookings(1, 10000, $filters);

            if ($format === 'csv') {
                return $this->exportToCSV($bookings['bookings']);
            } elseif ($format === 'json') {
                return $this->exportToJSON($bookings['bookings']);
            }

            return false;

        } catch (Exception $e) {
            error_log('Export error: ' . $e->getMessage());
            return false;
        }
    }

    private function exportToCSV($bookings) {
        $output = fopen('php://temp', 'r+');

        // Get form configuration for headers
        $formConfig = $this->formConfig->getActiveConfiguration();
        $headers = ['Access Code', 'Name', 'Email', 'Booking Date', 'Status', 'Created'];

        if ($formConfig) {
            foreach ($formConfig['fields'] as $field) {
                $headers[] = $field['label'];
            }
        }

        fputcsv($output, $headers);

        foreach ($bookings as $booking) {
            $row = [
                $booking['access_code'],
                $booking['name'] ?? '',
                $booking['email'] ?? '',
                $booking['booking_datetime']->toDateTime()->format('Y-m-d H:i:s'),
                ucfirst($booking['status']),
                $booking['created_at']->toDateTime()->format('Y-m-d H:i:s')
            ];

            // Add form data
            if (isset($booking['form_data']) && $formConfig) {
                foreach ($formConfig['fields'] as $field) {
                    $row[] = $booking['form_data'][$field['name']] ?? '';
                }
            }

            fputcsv($output, $row);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    private function exportToJSON($bookings) {
        $data = [];
        foreach ($bookings as $booking) {
            $data[] = [
                'access_code' => $booking['access_code'],
                'name' => $booking['name'] ?? '',
                'email' => $booking['email'] ?? '',
                'booking_datetime' => $booking['booking_datetime']->toDateTime()->format('c'),
                'status' => $booking['status'],
                'created_at' => $booking['created_at']->toDateTime()->format('c'),
                'form_data' => $booking['form_data'] ?? []
            ];
        }

        return json_encode($data, JSON_PRETTY_PRINT);
    }
}
?>
