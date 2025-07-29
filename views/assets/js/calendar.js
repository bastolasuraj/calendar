/**
 * Enhanced Calendar Implementation
 * Handles date selection and time slot booking
 */

class BookingCalendar {
    constructor(options = {}) {
        this.options = {
            container: options.container || document.getElementById('calendar-container'),
            timeSlotsWrapper: options.timeSlotsWrapper || document.getElementById('time-slots-wrapper'),
            timeSlotsDiv: options.timeSlotsDiv || document.getElementById('time-slots'),
            noSlotsMessage: options.noSlotsMessage || document.getElementById('no-slots-message'),
            hiddenDateTimeInput: options.hiddenDateTimeInput || document.getElementById('booking_datetime'),
            submitBtn: options.submitBtn || document.getElementById('submitBtn'),
            selectedDateDisplay: options.selectedDateDisplay || document.getElementById('selected-date-display'),
            prefix: options.prefix || '',
            ...options
        };

        this.currentDate = new Date();
        this.currentDate.setDate(1);
        this.selectedDate = null;
        this.selectedTime = null;
        this.availableSlots = [];

        this.init();
    }

    init() {
        if (!this.options.container) {
            console.warn('Calendar container not found');
            return;
        }

        this.generateCalendar(this.currentDate);
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Time slot selection
        if (this.options.timeSlotsDiv) {
            this.options.timeSlotsDiv.addEventListener('click', (e) => {
                if (e.target.tagName === 'BUTTON') {
                    this.selectTimeSlot(e.target);
                }
            });
        }
    }

    generateCalendar(date) {
        if (!this.options.container) return;

        this.options.container.innerHTML = '';

        const month = date.getMonth();
        const year = date.getFullYear();
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const startingDay = new Date(year, month, 1).getDay();

        // Create header
        const header = document.createElement('div');
        header.className = 'calendar-header';
        header.innerHTML = `
            <button type="button" id="${this.options.prefix}prev-month" class="calendar-nav-btn">
                <i class="fas fa-chevron-left"></i>
            </button>
            <span id="${this.options.prefix}calendar-month-year" class="calendar-title">
                ${date.toLocaleString('default', { month: 'long' })} ${year}
            </span>
            <button type="button" id="${this.options.prefix}next-month" class="calendar-nav-btn">
                <i class="fas fa-chevron-right"></i>
            </button>
        `;
        this.options.container.appendChild(header);

        // Create grid
        const grid = document.createElement('div');
        grid.className = 'calendar-grid';

        // Weekday headers
        const weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        weekdays.forEach(day => {
            const weekday = document.createElement('div');
            weekday.className = 'calendar-weekday';
            weekday.textContent = day;
            grid.appendChild(weekday);
        });

        // Empty cells for days before month starts
        for (let i = 0; i < startingDay; i++) {
            const emptyDay = document.createElement('div');
            emptyDay.className = 'calendar-day empty';
            grid.appendChild(emptyDay);
        }

        // Days of the month
        for (let i = 1; i <= daysInMonth; i++) {
            const dayCell = document.createElement('div');
            dayCell.className = 'calendar-day';
            dayCell.textContent = i;
            dayCell.dataset.date = `${year}-${String(month + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;

            const cellDate = new Date(year, month, i);
            const dayOfWeek = cellDate.getDay();

            // Check if day should be disabled
            if (cellDate < today || dayOfWeek === 0 || dayOfWeek === 6 || this.isHoliday(cellDate)) {
                dayCell.classList.add('disabled');
            }

            // Highlight today
            if (cellDate.getTime() === today.getTime()) {
                dayCell.classList.add('today');
            }

            grid.appendChild(dayCell);
        }

        this.options.container.appendChild(grid);

        // Setup navigation
        document.getElementById(`${this.options.prefix}prev-month`)?.addEventListener('click', () => {
            this.currentDate.setMonth(this.currentDate.getMonth() - 1);
            this.generateCalendar(this.currentDate);
        });

        document.getElementById(`${this.options.prefix}next-month`)?.addEventListener('click', () => {
            this.currentDate.setMonth(this.currentDate.getMonth() + 1);
            this.generateCalendar(this.currentDate);
        });

        // Day selection
        grid.addEventListener('click', (e) => this.handleDateClick(e));
    }

    handleDateClick(e) {
        const target = e.target;
        if (!target.classList.contains('calendar-day') ||
            target.classList.contains('disabled') ||
            target.classList.contains('empty')) {
            return;
        }

        // Update selected state
        this.options.container.querySelectorAll('.calendar-day').forEach(day => {
            day.classList.remove('selected');
        });
        target.classList.add('selected');

        const dateStr = target.dataset.date;
        this.selectedDate = dateStr;
        this.selectedTime = null;

        // Clear previous selection
        if (this.options.hiddenDateTimeInput) {
            this.options.hiddenDateTimeInput.value = '';
        }
        if (this.options.submitBtn) {
            this.options.submitBtn.disabled = true;
        }

        // Update display
        if (this.options.selectedDateDisplay) {
            const displayDate = new Date(dateStr + 'T00:00:00');
            this.options.selectedDateDisplay.textContent = displayDate.toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }

        // Load time slots
        this.loadTimeSlots(dateStr);
    }

    async loadTimeSlots(dateStr) {
        if (!this.options.timeSlotsDiv) return;

        // Show loading state
        this.options.timeSlotsDiv.innerHTML = '<div class="text-center"><div class="spinner-border spinner-border-sm" role="status"></div></div>';

        if (this.options.noSlotsMessage) {
            this.options.noSlotsMessage.style.display = 'none';
        }

        if (this.options.timeSlotsWrapper) {
            this.options.timeSlotsWrapper.style.display = 'block';
        }

        try {
            const response = await fetch(`/book/getAvailableTimes/${dateStr}`);
            const slots = await response.json();

            if (slots.error) {
                throw new Error(slots.error);
            }

            this.availableSlots = slots;
            this.renderTimeSlots(slots);
        } catch (error) {
            console.error('Failed to load time slots:', error);
            this.options.timeSlotsDiv.innerHTML = '';

            if (this.options.noSlotsMessage) {
                this.options.noSlotsMessage.textContent = 'Failed to load available time slots.';
                this.options.noSlotsMessage.style.display = 'block';
            }
        }
    }

    renderTimeSlots(slots) {
        if (!this.options.timeSlotsDiv) return;

        if (slots.length === 0) {
            this.options.timeSlotsDiv.innerHTML = '';
            if (this.options.noSlotsMessage) {
                this.options.noSlotsMessage.textContent = 'No available time slots for this date.';
                this.options.noSlotsMessage.style.display = 'block';
            }
            return;
        }

        if (this.options.noSlotsMessage) {
            this.options.noSlotsMessage.style.display = 'none';
        }

        const slotsHTML = slots.map(slot => {
            const timeObj = new Date(`2000-01-01 ${slot}`);
            const displayTime = timeObj.toLocaleTimeString('en-US', {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });

            return `
                <button type="button" class="btn btn-outline-primary time-slot-btn" 
                        data-time="${slot}" data-display-time="${displayTime}">
                    ${displayTime}
                </button>
            `;
        }).join('');

        this.options.timeSlotsDiv.innerHTML = slotsHTML;
    }

    selectTimeSlot(button) {
        // Update button states
        this.options.timeSlotsDiv.querySelectorAll('.time-slot-btn').forEach(btn => {
            btn.classList.remove('active', 'btn-primary');
            btn.classList.add('btn-outline-primary');
        });

        button.classList.remove('btn-outline-primary');
        button.classList.add('active', 'btn-primary');

        // Store selection
        this.selectedTime = button.dataset.time;
        const displayTime = button.dataset.displayTime;

        // Update hidden input
        if (this.options.hiddenDateTimeInput && this.selectedDate) {
            this.options.hiddenDateTimeInput.value = `${this.selectedDate} ${this.selectedTime}`;
        }

        // Enable submit button
        if (this.options.submitBtn) {
            this.options.submitBtn.disabled = false;
        }

        // Update display if needed
        if (this.options.selectedDateDisplay && this.selectedDate) {
            const dateObj = new Date(this.selectedDate + 'T00:00:00');
            const dateStr = dateObj.toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            this.options.selectedDateDisplay.innerHTML = `${dateStr}<br><small class="text-primary">${displayTime}</small>`;
        }

        // Trigger custom event
        if (this.options.container) {
            const event = new CustomEvent('timeSlotSelected', {
                detail: {
                    date: this.selectedDate,
                    time: this.selectedTime,
                    displayTime: displayTime,
                    datetime: `${this.selectedDate} ${this.selectedTime}`
                }
            });
            this.options.container.dispatchEvent(event);
        }
    }

    isHoliday(date) {
        const year = date.getFullYear();
        const month = date.getMonth(); // 0-11
        const day = date.getDate();
        const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

        // Canadian holidays
        const fixedHolidays = [
            `${year}-01-01`, // New Year's Day
            `${year}-07-01`, // Canada Day
            `${year}-12-25`, // Christmas
            `${year}-12-26`  // Boxing Day
        ];

        // Check fixed holidays
        if (fixedHolidays.includes(dateStr)) return true;

        // Good Friday (varies by year)
        const goodFridays = ['2025-04-18', '2026-04-03', '2027-03-26', '2028-04-14', '2029-03-30'];
        if (goodFridays.includes(dateStr)) return true;

        // Victoria Day (Monday preceding May 25)
        if (month === 4) { // May
            const vicDayTest = new Date(year, 4, 25);
            const dayOfWeek = vicDayTest.getDay(); // 0=sun, 1=mon...
            const prevMonday = 25 - (dayOfWeek === 0 ? 6 : dayOfWeek - 1);
            if (day === prevMonday) return true;
        }

        // Family Day (Third Monday in February)
        if (month === 1) { // February
            const firstDay = new Date(year, 1, 1).getDay();
            const thirdMonday = 1 + (8 - firstDay) % 7 + 14;
            if (day === thirdMonday) return true;
        }

        // Labour Day (First Monday in September)
        if (month === 8) { // September
            const firstDay = new Date(year, 8, 1).getDay();
            const firstMonday = 1 + (8 - firstDay) % 7;
            if (day === firstMonday) return true;
        }

        // Thanksgiving (Second Monday in October)
        if (month === 9) { // October
            const firstDay = new Date(year, 9, 1).getDay();
            const secondMonday = 1 + (8 - firstDay) % 7 + 7;
            if (day === secondMonday) return true;
        }

        // Statutory Canada Day (when July 1 falls on weekend)
        if (month === 6) { // July
            const dayOfWeek = new Date(year, 6, 1).getDay();
            if ((dayOfWeek === 6 && day === 3) || (dayOfWeek === 0 && day === 2)) return true;
        }

        return false;
    }

    // Public methods
    getSelectedDateTime() {
        if (this.selectedDate && this.selectedTime) {
            return `${this.selectedDate} ${this.selectedTime}`;
        }
        return null;
    }

    reset() {
        this.selectedDate = null;
        this.selectedTime = null;
        this.availableSlots = [];

        if (this.options.hiddenDateTimeInput) {
            this.options.hiddenDateTimeInput.value = '';
        }

        if (this.options.submitBtn) {
            this.options.submitBtn.disabled = true;
        }

        if (this.options.timeSlotsWrapper) {
            this.options.timeSlotsWrapper.style.display = 'none';
        }

        // Clear selected states
        this.options.container?.querySelectorAll('.calendar-day.selected').forEach(day => {
            day.classList.remove('selected');
        });
    }

    goToDate(dateStr) {
        const date = new Date(dateStr);
        this.currentDate = new Date(date.getFullYear(), date.getMonth(), 1);
        this.generateCalendar(this.currentDate);
    }

    setMinDate(dateStr) {
        this.minDate = new Date(dateStr);
        this.generateCalendar(this.currentDate);
    }

    setMaxDate(dateStr) {
        this.maxDate = new Date(dateStr);
        this.generateCalendar(this.currentDate);
    }
}

// Global initialization function
window.initializeCalendar = function(options = {}) {
    if (window.bookingCalendar) {
        return window.bookingCalendar;
    }

    window.bookingCalendar = new BookingCalendar(options);
    return window.bookingCalendar;
};

// Auto-initialize default calendar when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    const defaultContainer = document.getElementById('calendar-container');
    if (defaultContainer && !window.bookingCalendar) {
        window.bookingCalendar = new BookingCalendar();
    }
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = BookingCalendar;
}
