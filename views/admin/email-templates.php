<?php require_once 'views/templates/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">
                    <i class="bi bi-envelope-paper me-2"></i>Email Templates
                </h1>
                <p class="text-muted mb-0">Customize notification email templates with dynamic content</p>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#variablesModal">
                    <i class="bi bi-question-circle me-1"></i>Available Variables
                </button>
                <button type="button" class="btn btn-outline-primary" onclick="previewTemplate()">
                    <i class="bi bi-eye me-1"></i>Preview Template
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Template Selection -->
    <div class="col-md-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-list-ul me-2"></i>Email Templates
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php foreach ($templateTypes as $type => $config): ?>
                        <a href="#" class="list-group-item list-group-item-action template-item"
                           data-template-type="<?php echo htmlspecialchars($type); ?>">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($config['name']); ?></h6>
                                    <small class="text-muted"><?php echo htmlspecialchars($config['description']); ?></small>
                                </div>
                                <i class="bi bi-chevron-right"></i>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Template Editor -->
    <div class="col-md-9">
        <div class="card" id="templateEditor" style="display: none;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0" id="templateTitle">
                    <i class="bi bi-pencil me-2"></i>Edit Template
                </h5>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-secondary" onclick="resetTemplate()">
                        <i class="bi bi-arrow-clockwise me-1"></i>Reset
                    </button>
                    <button type="button" class="btn btn-outline-primary" onclick="previewTemplate()">
                        <i class="bi bi-eye me-1"></i>Preview
                    </button>
                    <button type="button" class="btn btn-primary" onclick="saveTemplate()">
                        <i class="bi bi-save me-1"></i>Save
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form id="templateForm">
                    <input type="hidden" id="templateType" name="template_type">

                    <div class="mb-3">
                        <label for="emailSubject" class="form-label">Email Subject</label>
                        <input type="text" class="form-control" id="emailSubject" name="subject"
                               placeholder="Enter email subject line">
                        <div class="form-text">You can use variables like {{company_name}} and {{name}} in the subject line</div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="htmlContent" class="form-label">HTML Content</label>
                            <div class="border rounded">
                                <textarea id="htmlContent" name="html_content" style="height: 400px;"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="textContent" class="form-label">Plain Text Content</label>
                            <textarea class="form-control" id="textContent" name="text_content" rows="18"
                                      placeholder="Plain text version for email clients that don't support HTML"></textarea>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <h6 class="alert-heading">
                            <i class="bi bi-lightbulb me-1"></i>Template Tips
                        </h6>
                        <ul class="mb-0">
                            <li>Use double curly braces for variables: <code>{{variable_name}}</code></li>
                            <li>HTML content supports full styling and formatting</li>
                            <li>Plain text is used as fallback for simple email clients</li>
                            <li>Always test your templates before saving</li>
                        </ul>
                    </div>
                </form>
            </div>
        </div>

        <!-- Default State -->
        <div class="card" id="defaultState">
            <div class="card-body text-center py-5">
                <i class="bi bi-envelope-paper fs-1 text-muted d-block mb-3"></i>
                <h5 class="text-muted">Select an Email Template</h5>
                <p class="text-muted mb-0">Choose a template from the left panel to start editing</p>
            </div>
        </div>
    </div>
</div>

<!-- Variables Reference Modal -->
<div class="modal fade" id="variablesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-code-square me-2"></i>Available Template Variables
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-bold">Customer Variables</h6>
                        <table class="table table-sm">
                            <tbody>
                            <tr>
                                <td><code>{{name}}</code></td>
                                <td>Customer name</td>
                            </tr>
                            <tr>
                                <td><code>{{email}}</code></td>
                                <td>Customer email address</td>
                            </tr>
                            <tr>
                                <td><code>{{phone}}</code></td>
                                <td>Customer phone number</td>
                            </tr>
                            <tr>
                                <td><code>{{access_code}}</code></td>
                                <td>Booking access code</td>
                            </tr>
                            </tbody>
                        </table>

                        <h6 class="fw-bold mt-4">Booking Variables</h6>
                        <table class="table table-sm">
                            <tbody>
                            <tr>
                                <td><code>{{booking_datetime}}</code></td>
                                <td>Booking date and time</td>
                            </tr>
                            <tr>
                                <td><code>{{booking_details}}</code></td>
                                <td>Formatted booking details</td>
                            </tr>
                            <tr>
                                <td><code>{{admin_notes}}</code></td>
                                <td>Admin notes (if any)</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold">Company Variables</h6>
                        <table class="table table-sm">
                            <tbody>
                            <tr>
                                <td><code>{{company_name}}</code></td>
                                <td>Company name</td>
                            </tr>
                            <tr>
                                <td><code>{{company_website}}</code></td>
                                <td>Company website URL</td>
                            </tr>
                            <tr>
                                <td><code>{{contact_email}}</code></td>
                                <td>Company contact email</td>
                            </tr>
                            <tr>
                                <td><code>{{contact_phone}}</code></td>
                                <td>Company phone number</td>
                            </tr>
                            <tr>
                                <td><code>{{address}}</code></td>
                                <td>Company address</td>
                            </tr>
                            </tbody>
                        </table>

                        <div class="alert alert-success">
                            <h6 class="alert-heading">Custom Fields</h6>
                            <p class="mb-0">Custom form fields are automatically available as variables using their field ID. For example, if you have a field with ID "department", use <code>{{department}}</code> in your template.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-eye me-2"></i>Email Template Preview
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>HTML Version</h6>
                        <div class="border rounded p-3" style="height: 500px; overflow-y: auto; background: white;" id="htmlPreview">
                            <!-- HTML preview will be loaded here -->
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>Plain Text Version</h6>
                        <div class="border rounded p-3" style="height: 500px; overflow-y: auto; background: #f8f9fa; font-family: monospace;" id="textPreview">
                            <!-- Text preview will be loaded here -->
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <h6>Subject Line Preview</h6>
                    <div class="alert alert-light mb-0" id="subjectPreview">
                        <!-- Subject preview will be loaded here -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary" onclick="sendTestEmail()">
                    <i class="bi bi-send me-1"></i>Send Test Email
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
    .template-item {
        transition: all 0.3s ease;
    }

    .template-item:hover {
        background-color: rgba(var(--primary-color), 0.1);
        border-color: var(--primary-color);
    }

    .template-item.active {
        background-color: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }

    .template-item.active small {
        color: rgba(255, 255, 255, 0.8);
    }

    .tox-tinymce {
        border: 1px solid #dee2e6 !important;
        border-radius: 0.375rem !important;
    }

    .variable-tag {
        background: var(--primary-color);
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-family: monospace;
        font-size: 0.875rem;
        cursor: pointer;
        user-select: all;
    }

    .variable-tag:hover {
        background: var(--accent-color);
    }

    code {
        background: #f8f9fa;
        padding: 0.125rem 0.25rem;
        border-radius: 0.25rem;
        font-size: 0.875rem;
    }
</style>

<script>
    let currentTemplate = null;
    let tinymceEditor = null;
    let templateData = <?php echo json_encode(iterator_to_array($templates)); ?>;

    document.addEventListener('DOMContentLoaded', function() {
        initializeTinyMCE();
        bindEvents();
    });

    function initializeTinyMCE() {
        tinymce.init({
            selector: '#htmlContent',
            height: 400,
            menubar: false,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'help', 'wordcount'
            ],
            toolbar: 'undo redo | blocks | bold italic underline strikethrough | forecolor backcolor | ' +
                'alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | ' +
                'removeformat | link image | code preview fullscreen',
            content_style: `
            body {
                font-family: Arial, sans-serif;
                font-size: 14px;
                line-height: 1.6;
            }
            .variable {
                background: #e7f3ff;
                padding: 2px 4px;
                border-radius: 3px;
                color: #0066cc;
                font-weight: bold;
            }
        `,
            setup: function (editor) {
                tinymceEditor = editor;

                // Add custom button for inserting variables
                editor.ui.registry.addMenuButton('variables', {
                    text: 'Variables',
                    fetch: function (callback) {
                        const items = [
                            {
                                type: 'menuitem',
                                text: 'Customer Name',
                                onAction: function () {
                                    editor.insertContent('<span class="variable">{{name}}</span>');
                                }
                            },
                            {
                                type: 'menuitem',
                                text: 'Email Address',
                                onAction: function () {
                                    editor.insertContent('<span class="variable">{{email}}</span>');
                                }
                            },
                            {
                                type: 'menuitem',
                                text: 'Booking Date & Time',
                                onAction: function () {
                                    editor.insertContent('<span class="variable">{{booking_datetime}}</span>');
                                }
                            },
                            {
                                type: 'menuitem',
                                text: 'Access Code',
                                onAction: function () {
                                    editor.insertContent('<span class="variable">{{access_code}}</span>');
                                }
                            },
                            {
                                type: 'menuitem',
                                text: 'Company Name',
                                onAction: function () {
                                    editor.insertContent('<span class="variable">{{company_name}}</span>');
                                }
                            },
                            {
                                type: 'menuitem',
                                text: 'Booking Details',
                                onAction: function () {
                                    editor.insertContent('<span class="variable">{{booking_details}}</span>');
                                }
                            }
                        ];
                        callback(items);
                    }
                });

                // Update toolbar to include variables button
                editor.settings.toolbar += ' | variables';
            },
            init_instance_callback: function(editor) {
                console.log('TinyMCE initialized');
            }
        });
    }

    function bindEvents() {
        // Template selection
        document.querySelectorAll('.template-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();

                // Remove active class from all items
                document.querySelectorAll('.template-item').forEach(el => {
                    el.classList.remove('active');
                });

                // Add active class to clicked item
                this.classList.add('active');

                // Load template
                const templateType = this.dataset.templateType;
                loadTemplate(templateType);
            });
        });
    }

    function loadTemplate(templateType) {
        currentTemplate = templateType;

        // Find template data
        const template = templateData.find(t => t.type === templateType);
        if (!template) {
            showToast('Template not found', 'error');
            return;
        }

        // Update UI
        document.getElementById('defaultState').style.display = 'none';
        document.getElementById('templateEditor').style.display = 'block';
        document.getElementById('templateTitle').innerHTML = `<i class="bi bi-pencil me-2"></i>Edit ${template.name}`;

        // Populate form
        document.getElementById('templateType').value = templateType;
        document.getElementById('emailSubject').value = template.subject || '';
        document.getElementById('textContent').value = template.text_content || '';

        // Set TinyMCE content
        if (tinymceEditor) {
            tinymceEditor.setContent(template.html_content || '');
        } else {
            // Retry after a short delay if TinyMCE isn't ready
            setTimeout(() => {
                if (tinymceEditor) {
                    tinymceEditor.setContent(template.html_content || '');
                }
            }, 500);
        }
    }

    function saveTemplate() {
        if (!currentTemplate) {
            showToast('Please select a template first', 'error');
            return;
        }

        const subject = document.getElementById('emailSubject').value.trim();
        const textContent = document.getElementById('textContent').value.trim();
        let htmlContent = '';

        if (tinymceEditor) {
            htmlContent = tinymceEditor.getContent();
        }

        if (!subject || !htmlContent) {
            showToast('Subject and HTML content are required', 'error');
            return;
        }

        // Show loading state
        const saveBtn = document.querySelector('[onclick="saveTemplate()"]');
        const originalText = saveBtn.innerHTML;
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';

        // Save template
        const formData = new FormData();
        formData.append('template_type', currentTemplate);
        formData.append('subject', subject);
        formData.append('html_content', htmlContent);
        formData.append('text_content', textContent);

        fetch(`${BASE_PATH_JS}/admin/company/email-templates`, { // Fixed this URL
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (response.ok) {
                    showToast('Template saved successfully!', 'success');

                    // Update template data
                    const templateIndex = templateData.findIndex(t => t.type === currentTemplate);
                    if (templateIndex !== -1) {
                        templateData[templateIndex].subject = subject;
                        templateData[templateIndex].html_content = htmlContent;
                        templateData[templateIndex].text_content = textContent;
                    }
                } else {
                    throw new Error('Failed to save template');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Failed to save template', 'error');
            })
            .finally(() => {
                saveBtn.disabled = false;
                saveBtn.innerHTML = originalText;
            });
    }

    function previewTemplate() {
        if (!currentTemplate) {
            showToast('Please select a template first', 'error');
            return;
        }

        const subject = document.getElementById('emailSubject').value.trim();
        const textContent = document.getElementById('textContent').value.trim();
        let htmlContent = '';

        if (tinymceEditor) {
            htmlContent = tinymceEditor.getContent();
        }

        // Sample data for preview
        const sampleData = {
            '{{name}}': 'John Doe',
            '{{email}}': 'john.doe@example.com',
            '{{phone}}': '+1 (555) 123-4567',
            '{{access_code}}': 'ABC123XYZ789',
            '{{booking_datetime}}': 'January 15, 2025 at 2:00 PM',
            '{{company_name}}': 'TechHub Coworking Space',
            '{{company_website}}': 'https://techhub.com',
            '{{contact_email}}': 'info@techhub.com',
            '{{contact_phone}}': '+1 (555) 123-4567',
            '{{address}}': '123 Innovation Drive, Tech City, TC 12345',
            '{{admin_notes}}': 'Please bring your laptop and charger.',
            '{{booking_details}}': 'Date & Time: January 15, 2025 at 2:00 PM\nPhone: +1 (555) 123-4567\nLaptop Model: MacBook Pro\nDepartment: Engineering\nPurpose: Working on the new product feature'
        };

        // Replace variables in content
        let previewSubject = subject;
        let previewHtml = htmlContent;
        let previewText = textContent;

        Object.keys(sampleData).forEach(variable => {
            const value = sampleData[variable];
            previewSubject = previewSubject.replace(new RegExp(escapeRegExp(variable), 'g'), value);
            previewHtml = previewHtml.replace(new RegExp(escapeRegExp(variable), 'g'), value);
            previewText = previewText.replace(new RegExp(escapeRegExp(variable), 'g'), value);
        });

        // Update preview modal
        document.getElementById('subjectPreview').textContent = previewSubject || 'No subject';
        document.getElementById('htmlPreview').innerHTML = previewHtml || '<em>No HTML content</em>';
        document.getElementById('textPreview').textContent = previewText || 'No plain text content';

        // Show preview modal
        const modal = new bootstrap.Modal(document.getElementById('previewModal'));
        modal.show();
    }

    function resetTemplate() {
        if (!currentTemplate) {
            showToast('Please select a template first', 'error');
            return;
        }

        if (confirm('Are you sure you want to reset this template to its default content? This will lose any unsaved changes.')) {
            // Reload the template from server defaults
            loadTemplate(currentTemplate);
            showToast('Template reset to default', 'info');
        }
    }

    function sendTestEmail() {
        const testEmail = prompt('Enter email address to send test to:');
        if (!testEmail) return;

        if (!/\S+@\S+\.\S+/.test(testEmail)) {
            showToast('Please enter a valid email address', 'error');
            return;
        }

        // Send test email with current template
        const formData = new FormData();
        formData.append('test_email', testEmail);
        formData.append('template_type', currentTemplate);

        fetch(`${BASE_PATH_JS}/admin/company/testEmail`, { // Fixed this URL
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (response.ok) {
                    showToast(`Test email sent to ${testEmail}`, 'success');
                } else {
                    throw new Error('Failed to send test email');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Failed to send test email', 'error');
            });
    }

    function escapeRegExp(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        toast.innerHTML = `
        <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

        document.body.appendChild(toast);

        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 5000);
    }

    // Auto-save functionality
    let autoSaveTimer;
    function autoSave() {
        if (!currentTemplate) return;

        const subject = document.getElementById('emailSubject').value.trim();
        const textContent = document.getElementById('textContent').value.trim();
        let htmlContent = '';

        if (tinymceEditor) {
            htmlContent = tinymceEditor.getContent();
        }

        // Store in localStorage
        const draftKey = `email_template_draft_${currentTemplate}`;
        const draftData = {
            subject: subject,
            html_content: htmlContent,
            text_content: textContent,
            timestamp: Date.now()
        };

        localStorage.setItem(draftKey, JSON.stringify(draftData));
        console.log('Email template auto-saved');
    }

    // Set up auto-save
    document.addEventListener('input', function(e) {
        if (e.target.closest('#templateForm')) {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(autoSave, 10000); // Auto-save every 10 seconds
        }
    });

    // Load draft on template change
    function loadDraft(templateType) {
        const draftKey = `email_template_draft_${templateType}`;
        const draftData = localStorage.getItem(draftKey);

        if (draftData) {
            const draft = JSON.parse(draftData);
            const ageHours = (Date.now() - draft.timestamp) / (1000 * 60 * 60);

            if (ageHours < 24 && confirm('You have unsaved changes for this template. Would you like to restore them?')) {
                document.getElementById('emailSubject').value = draft.subject || '';
                document.getElementById('textContent').value = draft.text_content || '';

                if (tinymceEditor) {
                    tinymceEditor.setContent(draft.html_content || '');
                }
            }
        }
    }
</script>

<?php require_once 'views/templates/footer.php'; ?>
