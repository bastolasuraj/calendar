</div> <!-- /container -->

<footer class="footer mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <p class="text-muted mb-1">
                    &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($companyName); ?>
                </p>
                <p class="text-muted small mb-0">
                    Professional booking management system
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <?php if (!empty($branding['company_website'])): ?>
                    <a href="<?php echo htmlspecialchars($branding['company_website']); ?>"
                       class="text-decoration-none me-3" target="_blank">
                        <i class="bi bi-globe me-1"></i>Website
                    </a>
                <?php endif; ?>
                <?php if (!empty($companySettings->get('contact_email'))): ?>
                    <a href="mailto:<?php echo htmlspecialchars($companySettings->get('contact_email')); ?>"
                       class="text-decoration-none me-3">
                        <i class="bi bi-envelope me-1"></i>Contact
                    </a>
                <?php endif; ?>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?php echo BASE_PATH; ?>/admin" class="text-decoration-none">
                        <i class="bi bi-gear me-1"></i>Admin
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</footer>

<!-- Enhanced Calendar and Form CSS -->
<style>
    #calendar-container {
        border-radius: 12px;
        padding: 1.5rem;
        background: white;
        border: 2px solid #e9ecef;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }

    .calendar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f8f9fa;
    }

    .calendar-header button {
        border: none;
        background: var(--primary-color);
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        font-size: 1.2rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .calendar-header button:hover {
        background: var(--accent-color);
        transform: scale(1.1);
    }

    #calendar-month-year {
        font-size: 1.4rem;
        font-weight: 600;
        color: var(--primary-color);
    }

    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 8px;
    }

    .calendar-day, .calendar-weekday {
        text-align: center;
        padding: 0.75rem 0;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .calendar-weekday {
        font-weight: 600;
        color: var(--secondary-color);
        background: #f8f9fa;
        font-size: 0.9rem;
    }

    .calendar-day {
        cursor: pointer;
        position: relative;
        font-weight: 500;
    }

    .calendar-day:not(.disabled):not(.empty):hover {
        background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        color: white;
        transform: scale(1.05);
    }

    .calendar-day.selected {
        background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        color: white;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        transform: scale(1.05);
    }

    .calendar-day.disabled {
        color: #adb5bd;
        cursor: not-allowed;
        text-decoration: line-through;
        background: #f8f9fa;
    }

    .calendar-day.empty {
        cursor: default;
        opacity: 0;
    }

    .calendar-day.today {
        background: #fff3cd;
        border: 2px solid #ffc107;
        font-weight: 600;
    }

    /* Time slots styling */
    #time-slots {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 1rem;
    }

    #time-slots .btn {
        flex: 0 0 auto;
        min-width: 80px;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    #time-slots .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    }

    #time-slots .btn.active {
        background: linear-gradient(135deg, var(--accent-color), var(--primary-color));
        border-color: var(--accent-color);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.2);
    }

    /* Form enhancements */
    .form-floating {
        position: relative;
    }

    .form-floating > .form-control,
    .form-floating > .form-select {
        height: calc(3.5rem + 2px);
        line-height: 1.25;
    }

    .form-floating > label {
        padding: 1rem 0.75rem;
        color: var(--secondary-color);
    }

    .invalid-feedback {
        display: block;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.875rem;
        color: #dc3545;
    }

    .valid-feedback {
        display: block;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.875rem;
        color: #198754;
    }

    /* Animation classes */
    .fade-in {
        animation: fadeIn 0.5s ease-in;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .slide-up {
        animation: slideUp 0.3s ease-out;
    }

    @keyframes slideUp {
        from { transform: translateY(100%); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    /* Progress indicators */
    .progress-steps {
        display: flex;
        justify-content: space-between;
        margin-bottom: 2rem;
    }

    .progress-step {
        flex: 1;
        text-align: center;
        position: relative;
    }

    .progress-step::after {
        content: '';
        position: absolute;
        top: 15px;
        left: 50%;
        width: 100%;
        height: 2px;
        background: #e9ecef;
        z-index: 0;
    }

    .progress-step:last-child::after {
        display: none;
    }

    .progress-step.active::after {
        background: var(--primary-color);
    }

    .progress-step-circle {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #e9ecef;
        color: #6c757d;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        position: relative;
        z-index: 1;
        margin-bottom: 0.5rem;
    }

    .progress-step.active .progress-step-circle {
        background: var(--primary-color);
        color: white;
    }

    .progress-step.completed .progress-step-circle {
        background: var(--accent-color);
        color: white;
    }
</style>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<!-- Enhanced Calendar and Booking JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Enhanced calendar functionality
        const calendarContainer = document.getElementById('calendar-container');
        if (calendarContainer) {
            const timeSlotsWrapper = document.getElementById('time-slots-wrapper');
            const timeSlotsDiv = document.getElementById('time-slots');
            const noSlotsMessage = document.getElementById('no-slots-message');
            const hiddenDateTimeInput = document.getElementById('booking_datetime');
            const submitBtn = document.getElementById('submitBtn');
            const selectedDateDisplay = document.getElementById('selected-date-display');

            let currentDate = new Date();
            currentDate.setDate(1);
            let selectedDate = null;
            let selectedTime = null;

            // Enhanced holiday checker with more holidays
            function isHoliday(date) {
                const year = date.getFullYear();
                const month = date.getMonth();
                const day = date.getDate();
                const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

                // Fixed holidays
                const fixedHolidays = [
                    `${year}-01-01`, // New Year's Day
                    `${year}-07-01`, // Canada Day
                    `${year}-12-25`, // Christmas Day
                    `${year}-12-26`  // Boxing Day
                ];

                if (fixedHolidays.includes(dateStr)) return true;

                // Good Friday dates (pre-calculated)
                const goodFridays = ['2025-04-18', '2026-04-03', '2027-03-26', '2028-04-14', '2029-03-30'];
                if (goodFridays.includes(dateStr)) return true;

                // Victoria Day (Monday preceding May 25)
                if (month === 4) {
                    const vicDayTest = new Date(year, 4, 25);
                    const dayOfWeek = vicDayTest.getDay();
                    const prevMonday = 25 - (dayOfWeek === 0 ? 6 : dayOfWeek - 1);
                    if (day === prevMonday) return true;
                }

                // Family Day (Third Monday in February)
                if (month === 1) {
                    const firstDay = new Date(year, 1, 1).getDay();
                    const thirdMonday = 1 + (8 - firstDay) % 7 + 14;
                    if (day === thirdMonday) return true;
                }

                // Labour Day (First Monday in September)
                if (month === 8) {
                    const firstDay = new Date(year, 8, 1).getDay();
                    const firstMonday = 1 + (8 - firstDay) % 7;
                    if (day === firstMonday) return true;
                }

                // Thanksgiving (Second Monday in October)
                if (month === 9) {
                    const firstDay = new Date(year, 9, 1).getDay();
                    const secondMonday = 1 + (8 - firstDay) % 7 + 7;
                    if (day === secondMonday) return true;
                }

                // Statutory Canada Day adjustments
                if (month === 6) {
                    const dayOfWeek = new Date(year, 6, 1).getDay();
                    if ((dayOfWeek === 6 && day === 3) || (dayOfWeek === 0 && day === 2)) return true;
                }

                return false;
            }

            function generateCalendar(date) {
                calendarContainer.innerHTML = '';
                const month = date.getMonth();
                const year = date.getFullYear();
                const today = new Date();
                today.setHours(0,0,0,0);

                const daysInMonth = new Date(year, month + 1, 0).getDate();
                const startingDay = new Date(year, month, 1).getDay();

                // Create header
                const header = document.createElement('div');
                header.className = 'calendar-header';
                header.innerHTML = `
                <button type="button" id="prev-month" aria-label="Previous month">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <span id="calendar-month-year">${date.toLocaleString('default', { month: 'long' })} ${year}</span>
                <button type="button" id="next-month" aria-label="Next month">
                    <i class="bi bi-chevron-right"></i>
                </button>
            `;
                calendarContainer.appendChild(header);

                // Create grid
                const grid = document.createElement('div');
                grid.className = 'calendar-grid';

                // Weekday headers
                ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].forEach(day => {
                    const weekday = document.createElement('div');
                    weekday.className = 'calendar-weekday';
                    weekday.textContent = day;
                    grid.appendChild(weekday);
                });

                // Empty cells for days before month starts
                for (let i = 0; i < startingDay; i++) {
                    const emptyCell = document.createElement('div');
                    emptyCell.className = 'calendar-day empty';
                    grid.appendChild(emptyCell);
                }

                // Days of the month
                for (let i = 1; i <= daysInMonth; i++) {
                    const dayCell = document.createElement('div');
                    dayCell.className = 'calendar-day';
                    dayCell.textContent = i;
                    dayCell.dataset.date = `${year}-${String(month + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;

                    const cellDate = new Date(year, month, i);
                    const dayOfWeek = cellDate.getDay();

                    // Check if today
                    if (cellDate.toDateString() === today.toDateString()) {
                        dayCell.classList.add('today');
                    }

                    // Disable past days, weekends, and holidays
                    if (cellDate < today || dayOfWeek === 0 || dayOfWeek === 6 || isHoliday(cellDate)) {
                        dayCell.classList.add('disabled');
                    } else {
                        dayCell.addEventListener('click', () => handleDateClick(dayCell));
                    }

                    grid.appendChild(dayCell);
                }

                calendarContainer.appendChild(grid);

                // Event listeners for navigation
                document.getElementById('prev-month').addEventListener('click', () => {
                    currentDate.setMonth(currentDate.getMonth() - 1);
                    generateCalendar(currentDate);
                });

                document.getElementById('next-month').addEventListener('click', () => {
                    currentDate.setMonth(currentDate.getMonth() + 1);
                    generateCalendar(currentDate);
                });
            }

            function handleDateClick(dayCell) {
                if (dayCell.classList.contains('disabled')) return;

                // Remove previous selection
                document.querySelectorAll('.calendar-day.selected').forEach(day => {
                    day.classList.remove('selected');
                });

                // Select new date
                dayCell.classList.add('selected');
                selectedDate = dayCell.dataset.date;

                // Clear time selection
                selectedTime = null;
                hiddenDateTimeInput.value = '';
                submitBtn.disabled = true;

                // Update UI
                timeSlotsDiv.innerHTML = '<div class="text-center loading" style="padding: 2rem;"></div>';
                noSlotsMessage.style.display = 'none';
                timeSlotsWrapper.style.display = 'block';
                timeSlotsWrapper.classList.add('fade-in');

                // Format date for display
                const displayDate = new Date(selectedDate + 'T00:00:00');
                selectedDateDisplay.textContent = displayDate.toLocaleDateString('en-US', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });

                // Fetch available times
                fetch(`${BASE_PATH_JS}/book/getAvailableTimes/${selectedDate}`)
                    .then(response => response.json())
                    .then(data => {
                        timeSlotsDiv.innerHTML = '';

                        if (data.success && data.slots && data.slots.length > 0) {
                            data.slots.forEach(slot => {
                                const btn = document.createElement('button');
                                btn.type = 'button';
                                btn.className = 'btn btn-outline-primary';
                                btn.textContent = formatTime(slot);
                                btn.dataset.time = slot;
                                btn.addEventListener('click', () => handleTimeClick(btn));
                                timeSlotsDiv.appendChild(btn);
                            });
                        } else {
                            noSlotsMessage.style.display = 'block';
                            noSlotsMessage.textContent = 'No available time slots for this date.';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching time slots:', error);
                        timeSlotsDiv.innerHTML = '<div class="alert alert-warning">Error loading time slots. Please try again.</div>';
                    });
            }

            function handleTimeClick(timeBtn) {
                // Remove previous time selection
                document.querySelectorAll('#time-slots .btn.active').forEach(btn => {
                    btn.classList.remove('active', 'btn-primary');
                    btn.classList.add('btn-outline-primary');
                });

                // Select new time
                timeBtn.classList.remove('btn-outline-primary');
                timeBtn.classList.add('active', 'btn-primary');
                selectedTime = timeBtn.dataset.time;

                // Update hidden input and enable submit
                hiddenDateTimeInput.value = `${selectedDate} ${selectedTime}`;
                submitBtn.disabled = false;

                // Visual feedback
                timeBtn.classList.add('slide-up');
            }

            function formatTime(time24) {
                const [hours, minutes] = time24.split(':');
                const hour12 = hours % 12 || 12;
                const ampm = hours < 12 ? 'AM' : 'PM';
                return `${hour12}:${minutes} ${ampm}`;
            }

            // Initialize calendar
            generateCalendar(currentDate);
        }

        // Enhanced form validation
        const bookingForm = document.getElementById('bookingForm');
        if (bookingForm) {
            // Real-time validation
            const formFields = bookingForm.querySelectorAll('input, select, textarea');
            formFields.forEach(field => {
                field.addEventListener('blur', validateField);
                field.addEventListener('input', debounce(validateField, 300));
            });

            function validateField(event) {
                const field = event.target;
                const value = field.value.trim();

                clearFieldErrors(field);

                if (field.hasAttribute('required') && !value) {
                    showFieldError(field, 'This field is required.');
                    return false;
                }

                // Type-specific validation
                if (field.type === 'email' && value) {
                    if (!isValidEmail(value)) {
                        showFieldError(field, 'Please enter a valid email address.');
                        return false;
                    }
                }

                if (field.type === 'tel' && value) {
                    if (!isValidPhone(value)) {
                        showFieldError(field, 'Please enter a valid phone number.');
                        return false;
                    }
                }

                showFieldSuccess(field);
                return true;
            }

            function clearFieldErrors(field) {
                field.classList.remove('is-invalid', 'is-valid');
                const feedback = field.parentNode.querySelector('.invalid-feedback');
                if (feedback) feedback.remove();
            }

            function showFieldError(field, message) {
                field.classList.add('is-invalid');
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = message;
                field.parentNode.appendChild(feedback);
            }

            function showFieldSuccess(field) {
                field.classList.add('is-valid');
            }

            // Form submission with enhanced UX
            bookingForm.addEventListener('submit', function(event) {
                event.preventDefault();

                // Validate all fields
                let isValid = true;
                formFields.forEach(field => {
                    if (!validateField({target: field})) {
                        isValid = false;
                    }
                });

                // Check date/time selection
                if (!hiddenDateTimeInput.value) {
                    isValid = false;
                    showError('Please select a date and time for your booking.');
                }

                // Check terms acceptance if present
                const termsCheckbox = document.getElementById('accept_terms');
                if (termsCheckbox && !termsCheckbox.checked) {
                    isValid = false;
                    showError('Please accept the terms and conditions.');
                }

                if (isValid) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';

                    // Submit form
                    this.submit();
                } else {
                    // Scroll to first error
                    const firstError = bookingForm.querySelector('.is-invalid');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstError.focus();
                    }
                }
            });
        }

        // Utility functions
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        function isValidPhone(phone) {
            const phoneRegex = /^[\+]?[0-9\s\-\(\)\.]{10,20}$/;
            return phoneRegex.test(phone);
        }

        function showError(message) {
            // Remove existing error alerts
            document.querySelectorAll('.alert-danger.floating-alert').forEach(alert => alert.remove());

            const alert = document.createElement('div');
            alert.className = 'alert alert-danger floating-alert';
            alert.innerHTML = `
            <i class="bi bi-exclamation-triangle me-2"></i>
            ${message}
            <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
        `;

            document.querySelector('.container').insertBefore(alert, document.querySelector('.container').firstChild);
            alert.scrollIntoView({ behavior: 'smooth' });

            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (alert.parentNode) alert.remove();
            }, 5000);
        }

        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Initialize Flatpickr for other date inputs
        flatpickr(".datetime-picker", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            minDate: "today",
            time_24hr: true,
            minuteIncrement: 30
        });

        // Auto-hide alerts after 10 seconds
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                if (alert.querySelector('.btn-close')) {
                    alert.classList.add('fade');
                    setTimeout(() => alert.remove(), 300);
                }
            });
        }, 10000);
    });
</script>

</body>
</html>
