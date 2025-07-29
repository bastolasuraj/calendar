<?php require_once 'views/templates/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">
                    <i class="bi bi-ui-checks me-2"></i>Dynamic Form Builder
                </h1>
                <p class="text-muted mb-0">Create and customize booking forms with drag-and-drop interface</p>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="bi bi-upload me-1"></i>Import Form
                </button>
                <button type="button" class="btn btn-primary" id="createNewFormBtn">
                    <i class="bi bi-plus-circle me-1"></i>Create New Form
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Form Configurations List -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-list-ul me-2"></i>Form Configurations
                </h5>
            </div>
            <div class="card-body">
                <?php if (iterator_count($configurations) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                            <tr>
                                <th>Form Name</th>
                                <th>Fields</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($configurations as $config): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($config['name']); ?></strong>
                                        <?php if ($config['is_active']): ?>
                                            <span class="badge bg-success ms-2">Active</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                    <span class="badge bg-light text-dark">
                                        <?php echo count($config['fields']); ?> fields
                                    </span>
                                    </td>
                                    <td>
                                        <?php if ($config['is_active']): ?>
                                            <span class="badge bg-success">
                                            <i class="bi bi-check-circle me-1"></i>Active
                                        </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">
                                            <i class="bi bi-pause-circle me-1"></i>Inactive
                                        </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo $config['created_at']->toDateTime()->format('M d, Y g:i A'); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-primary"
                                                    onclick="editForm('<?php echo $config['_id']; ?>')">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-info"
                                                    onclick="previewForm('<?php echo $config['_id']; ?>')">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary"
                                                    onclick="duplicateForm('<?php echo $config['_id']; ?>')">
                                                <i class="bi bi-files"></i>
                                            </button>
                                            <?php if (!$config['is_active']): ?>
                                                <button type="button" class="btn btn-outline-success"
                                                        onclick="activateForm('<?php echo $config['_id']; ?>')">
                                                    <i class="bi bi-check-circle"></i>
                                                </button>
                                            <?php endif; ?>
                                            <div class="dropdown">
                                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="<?php echo BASE_PATH; ?>/admin/form-builder/export/<?php echo $config['_id']; ?>">
                                                            <i class="bi bi-download me-2"></i>Export
                                                        </a></li>
                                                    <?php if (!$config['is_active']): ?>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><a class="dropdown-item text-danger" href="#"
                                                               onclick="deleteForm('<?php echo $config['_id']; ?>')">
                                                                <i class="bi bi-trash me-2"></i>Delete
                                                            </a></li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-ui-checks fs-1 text-muted d-block mb-3"></i>
                        <h5 class="text-muted">No Form Configurations</h5>
                        <p class="text-muted mb-4">Create your first dynamic form to get started.</p>
                        <button type="button" class="btn btn-primary" id="createFirstFormBtn">
                            <i class="bi bi-plus-circle me-1"></i>Create Your First Form
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Form Builder Modal -->
<div class="modal fade" id="formBuilderModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-ui-checks me-2"></i>Form Builder
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="row g-0 h-100">
                    <!-- Left Sidebar - Field Palette -->
                    <div class="col-md-3 bg-light border-end">
                        <div class="p-3">
                            <h6 class="fw-bold mb-3">
                                <i class="bi bi-palette me-1"></i>Field Types
                            </h6>
                            <div id="fieldPalette">
                                <!-- Field types will be loaded here -->
                            </div>

                            <hr>

                            <h6 class="fw-bold mb-3">
                                <i class="bi bi-gear me-1"></i>Form Settings
                            </h6>
                            <div class="mb-3">
                                <label for="formName" class="form-label">Form Name</label>
                                <input type="text" class="form-control" id="formName" placeholder="Enter form name">
                            </div>
                            <div class="mb-3">
                                <label for="submitButtonText" class="form-label">Submit Button Text</label>
                                <input type="text" class="form-control" id="submitButtonText" value="Submit Booking">
                            </div>
                            <div class="mb-3">
                                <label for="successMessage" class="form-label">Success Message</label>
                                <textarea class="form-control" id="successMessage" rows="3">Your booking has been submitted successfully!</textarea>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="isActiveForm">
                                    <label class="form-check-label" for="isActiveForm">
                                        Set as Active Form
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Center - Form Builder Area -->
                    <div class="col-md-6">
                        <div class="p-3 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0">
                                    <i class="bi bi-layout-text-window me-1"></i>Form Designer
                                </h6>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-primary" id="previewFormBtn">
                                        <i class="bi bi-eye me-1"></i>Preview
                                    </button>
                                    <button type="button" class="btn btn-primary" id="saveFormBtn">
                                        <i class="bi bi-save me-1"></i>Save Form
                                    </button>
                                </div>
                            </div>

                            <div class="form-builder-canvas bg-white border rounded p-4" id="formCanvas">
                                <div class="text-center text-muted py-5" id="emptyCanvas">
                                    <i class="bi bi-plus-circle fs-1 d-block mb-3"></i>
                                    <h5>Start Building Your Form</h5>
                                    <p>Drag field types from the left panel to add them to your form</p>
                                </div>
                                <div id="formFields" style="display: none;">
                                    <!-- Form fields will be added here -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Sidebar - Field Properties -->
                    <div class="col-md-3 bg-light border-start">
                        <div class="p-3">
                            <h6 class="fw-bold mb-3">
                                <i class="bi bi-sliders me-1"></i>Field Properties
                            </h6>
                            <div id="fieldProperties">
                                <div class="text-center text-muted py-4">
                                    <i class="bi bi-hand-index fs-3 d-block mb-2"></i>
                                    <p class="mb-0">Select a field to edit its properties</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-eye me-2"></i>Form Preview
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="previewContent">
                <!-- Preview content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-upload me-2"></i>Import Form Configuration
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?php echo BASE_PATH; ?>/admin/form-builder/import" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="importFile" class="form-label">Select Form Configuration File</label>
                        <input type="file" class="form-control" id="importFile" name="import_file"
                               accept=".json" required>
                        <div class="form-text">Select a JSON file exported from the form builder.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Import Form</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .field-palette-item {
        display: block;
        width: 100%;
        padding: 0.75rem;
        margin-bottom: 0.5rem;
        background: white;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        text-decoration: none;
        color: #495057;
        transition: all 0.3s ease;
        cursor: grab;
    }

    .field-palette-item:hover {
        border-color: var(--primary-color);
        background: var(--primary-color);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .field-palette-item:active {
        cursor: grabbing;
    }

    .form-builder-canvas {
        min-height: 500px;
        max-height: 70vh;
        overflow-y: auto;
    }

    .form-field-item {
        position: relative;
        padding: 1rem;
        margin-bottom: 1rem;
        background: #f8f9fa;
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .form-field-item:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        border-color: var(--primary-color);
    }

    .form-field-item.selected {
        border-color: var(--primary-color);
        background: rgba(var(--primary-color), 0.1);
        border-style: solid;
    }

    .form-field-controls {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .form-field-item:hover .form-field-controls {
        opacity: 1;
    }

    .drag-handle {
        cursor: move;
        color: #6c757d;
    }

    .drag-handle:hover {
        color: var(--primary-color);
    }

    .sortable-ghost {
        opacity: 0.5;
        background: var(--primary-color);
        border-color: var(--primary-color);
    }

    .field-properties-panel {
        max-height: 60vh;
        overflow-y: auto;
    }

    .validation-rules {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1rem;
        margin-top: 1rem;
    }

    .option-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .option-item input {
        flex: 1;
    }
</style>

<script>
    let formBuilder = {
        currentForm: null,
        selectedField: null,
        fieldCounter: 0,
        fieldTypes: <?php echo json_encode($fieldTypes); ?>,

        init() {
            this.loadFieldPalette();
            this.initSortable();
            this.bindEvents();
        },

        loadFieldPalette() {
            const palette = document.getElementById('fieldPalette');
            palette.innerHTML = '';

            Object.entries(this.fieldTypes).forEach(([type, config]) => {
                const item = document.createElement('div');
                item.className = 'field-palette-item';
                item.draggable = true;
                item.dataset.fieldType = type;
                item.innerHTML = `
                <i class="bi bi-grip-vertical me-2"></i>
                <strong>${config.label}</strong>
                <br><small class="text-muted">${this.getFieldTypeDescription(type)}</small>
            `;

                item.addEventListener('dragstart', (e) => {
                    e.dataTransfer.setData('text/plain', type);
                });

                item.addEventListener('click', () => {
                    this.addField(type);
                });

                palette.appendChild(item);
            });
        },

        initSortable() {
            const formFields = document.getElementById('formFields');

            Sortable.create(formFields, {
                animation: 150,
                ghostClass: 'sortable-ghost',
                handle: '.drag-handle',
                onEnd: () => {
                    this.updateFieldOrder();
                }
            });

            // Drop zone for new fields
            const canvas = document.getElementById('formCanvas');
            canvas.addEventListener('dragover', (e) => {
                e.preventDefault();
                canvas.style.background = 'rgba(var(--primary-color), 0.1)';
            });

            canvas.addEventListener('dragleave', (e) => {
                canvas.style.background = '';
            });

            canvas.addEventListener('drop', (e) => {
                e.preventDefault();
                canvas.style.background = '';

                const fieldType = e.dataTransfer.getData('text/plain');
                if (fieldType) {
                    this.addField(fieldType);
                }
            });
        },

        bindEvents() {
            document.getElementById('createNewFormBtn').addEventListener('click', () => {
                this.createNewForm();
            });

            document.getElementById('createFirstFormBtn')?.addEventListener('click', () => {
                this.createNewForm();
            });

            document.getElementById('saveFormBtn').addEventListener('click', () => {
                this.saveForm();
            });

            document.getElementById('previewFormBtn').addEventListener('click', () => {
                this.previewForm();
            });
        },

        createNewForm() {
            this.currentForm = {
                id: null,
                name: '',
                fields: [],
                settings: {
                    submit_button_text: 'Submit Booking',
                    success_message: 'Your booking has been submitted successfully!',
                    is_active: false
                }
            };

            this.clearCanvas();
            this.clearProperties();
            document.getElementById('formName').value = '';
            document.getElementById('submitButtonText').value = 'Submit Booking';
            document.getElementById('successMessage').value = 'Your booking has been submitted successfully!';
            document.getElementById('isActiveForm').checked = false;

            const modal = new bootstrap.Modal(document.getElementById('formBuilderModal'));
            modal.show();
        },

        addField(type) {
            const fieldId = `field_${++this.fieldCounter}`;
            const fieldConfig = this.fieldTypes[type];

            const field = {
                id: fieldId,
                type: type,
                label: `${fieldConfig.label} ${this.fieldCounter}`,
                placeholder: '',
                required: false,
                order: this.currentForm.fields.length,
                validation: {},
                options: fieldConfig.options ? [{value: 'option1', label: 'Option 1'}] : undefined
            };

            this.currentForm.fields.push(field);
            this.renderField(field);
            this.showEmptyState(false);
            this.selectField(fieldId);
        },

        renderField(field) {
            const formFields = document.getElementById('formFields');

            const fieldElement = document.createElement('div');
            fieldElement.className = 'form-field-item';
            fieldElement.dataset.fieldId = field.id;
            fieldElement.innerHTML = `
            <div class="form-field-controls">
                <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="formBuilder.moveFieldUp('${field.id}')">
                    <i class="bi bi-arrow-up"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="formBuilder.moveFieldDown('${field.id}')">
                    <i class="bi bi-arrow-down"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="formBuilder.removeField('${field.id}')">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
            <div class="d-flex align-items-center mb-2">
                <i class="bi bi-grip-vertical drag-handle me-2"></i>
                <strong>${field.label}</strong>
                ${field.required ? '<span class="badge bg-danger ms-2">Required</span>' : ''}
            </div>
            <div class="field-preview">
                ${this.renderFieldPreview(field)}
            </div>
        `;

            fieldElement.addEventListener('click', (e) => {
                if (!e.target.closest('.form-field-controls')) {
                    this.selectField(field.id);
                }
            });

            formFields.appendChild(fieldElement);
        },

        renderFieldPreview(field) {
            switch (field.type) {
                case 'text':
                case 'email':
                case 'phone':
                    return `<input type="${field.type}" class="form-control" placeholder="${field.placeholder}" disabled>`;
                case 'textarea':
                    return `<textarea class="form-control" placeholder="${field.placeholder}" rows="3" disabled></textarea>`;
                case 'select':
                    const selectOptions = field.options?.map(opt => `<option value="${opt.value}">${opt.label}</option>`).join('') || '';
                    return `<select class="form-select" disabled><option>Choose...</option>${selectOptions}</select>`;
                case 'radio':
                    const radioOptions = field.options?.map((opt, idx) =>
                        `<div class="form-check">
                        <input class="form-check-input" type="radio" name="${field.id}" id="${field.id}_${idx}" disabled>
                        <label class="form-check-label" for="${field.id}_${idx}">${opt.label}</label>
                    </div>`
                    ).join('') || '';
                    return radioOptions;
                case 'checkbox':
                    const checkboxOptions = field.options?.map((opt, idx) =>
                        `<div class="form-check">
                        <input class="form-check-input" type="checkbox" name="${field.id}[]" id="${field.id}_${idx}" disabled>
                        <label class="form-check-label" for="${field.id}_${idx}">${opt.label}</label>
                    </div>`
                    ).join('') || '';
                    return checkboxOptions;
                case 'number':
                    return `<input type="number" class="form-control" placeholder="${field.placeholder}" disabled>`;
                case 'date':
                    return `<input type="date" class="form-control" disabled>`;
                case 'file':
                    return `<input type="file" class="form-control" disabled>`;
                default:
                    return `<input type="text" class="form-control" placeholder="${field.placeholder}" disabled>`;
            }
        },

        selectField(fieldId) {
            // Remove previous selection
            document.querySelectorAll('.form-field-item').forEach(item => {
                item.classList.remove('selected');
            });

            // Select new field
            const fieldElement = document.querySelector(`[data-field-id="${fieldId}"]`);
            if (fieldElement) {
                fieldElement.classList.add('selected');
                this.selectedField = fieldId;
                this.showFieldProperties(fieldId);
            }
        },

        showFieldProperties(fieldId) {
            const field = this.currentForm.fields.find(f => f.id === fieldId);
            if (!field) return;

            const properties = document.getElementById('fieldProperties');
            properties.innerHTML = `
            <div class="field-properties-panel">
                <div class="mb-3">
                    <label class="form-label">Field Label</label>
                    <input type="text" class="form-control" id="prop_label" value="${field.label}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Field ID</label>
                    <input type="text" class="form-control" id="prop_id" value="${field.id}" readonly>
                    <div class="form-text">Unique identifier for this field</div>
                </div>

                ${field.type !== 'checkbox' && field.type !== 'radio' && field.type !== 'select' ? `
                <div class="mb-3">
                    <label class="form-label">Placeholder Text</label>
                    <input type="text" class="form-control" id="prop_placeholder" value="${field.placeholder || ''}">
                </div>
                ` : ''}

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="prop_required" ${field.required ? 'checked' : ''}>
                        <label class="form-check-label" for="prop_required">
                            Required Field
                        </label>
                    </div>
                </div>

                ${this.renderFieldTypeSpecificProperties(field)}

                <div class="validation-rules">
                    <h6 class="mb-3">Validation Rules</h6>
                    ${this.renderValidationRules(field)}
                </div>

                <div class="mt-3 pt-3 border-top">
                    <button type="button" class="btn btn-sm btn-outline-primary w-100" onclick="formBuilder.updateFieldProperties()">
                        <i class="bi bi-check me-1"></i>Update Field
                    </button>
                </div>
            </div>
        `;
        },

        renderFieldTypeSpecificProperties(field) {
            if (['select', 'radio', 'checkbox'].includes(field.type)) {
                const optionsHtml = field.options?.map((opt, idx) => `
                <div class="option-item">
                    <input type="text" class="form-control form-control-sm" placeholder="Value" value="${opt.value}" data-option-idx="${idx}" data-option-prop="value">
                    <input type="text" class="form-control form-control-sm" placeholder="Label" value="${opt.label}" data-option-idx="${idx}" data-option-prop="label">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="formBuilder.removeOption(${idx})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            `).join('') || '';

                return `
                <div class="mb-3">
                    <label class="form-label">Options</label>
                    <div id="optionsList">
                        ${optionsHtml}
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="formBuilder.addOption()">
                        <i class="bi bi-plus me-1"></i>Add Option
                    </button>
                </div>
            `;
            }

            return '';
        },

        renderValidationRules(field) {
            const allowedRules = this.fieldTypes[field.type]?.validation || [];
            let html = '';

            if (allowedRules.includes('minLength')) {
                html += `
                <div class="mb-2">
                    <label class="form-label form-label-sm">Minimum Length</label>
                    <input type="number" class="form-control form-control-sm" id="val_minLength" value="${field.validation.minLength || ''}" min="0">
                </div>
            `;
            }

            if (allowedRules.includes('maxLength')) {
                html += `
                <div class="mb-2">
                    <label class="form-label form-label-sm">Maximum Length</label>
                    <input type="number" class="form-control form-control-sm" id="val_maxLength" value="${field.validation.maxLength || ''}" min="0">
                </div>
            `;
            }

            if (allowedRules.includes('min')) {
                html += `
                <div class="mb-2">
                    <label class="form-label form-label-sm">Minimum Value</label>
                    <input type="number" class="form-control form-control-sm" id="val_min" value="${field.validation.min || ''}" step="any">
                </div>
            `;
            }

            if (allowedRules.includes('max')) {
                html += `
                <div class="mb-2">
                    <label class="form-label form-label-sm">Maximum Value</label>
                    <input type="number" class="form-control form-control-sm" id="val_max" value="${field.validation.max || ''}" step="any">
                </div>
            `;
            }

            if (allowedRules.includes('pattern')) {
                html += `
                <div class="mb-2">
                    <label class="form-label form-label-sm">Pattern (Regex)</label>
                    <input type="text" class="form-control form-control-sm" id="val_pattern" value="${field.validation.pattern || ''}" placeholder="^[A-Za-z]+$">
                </div>
            `;
            }

            return html || '<p class="text-muted small">No validation rules available for this field type.</p>';
        },

        updateFieldProperties() {
            if (!this.selectedField) return;

            const field = this.currentForm.fields.find(f => f.id === this.selectedField);
            if (!field) return;

            // Update basic properties
            field.label = document.getElementById('prop_label').value;
            field.required = document.getElementById('prop_required').checked;

            const placeholderInput = document.getElementById('prop_placeholder');
            if (placeholderInput) {
                field.placeholder = placeholderInput.value;
            }

            // Update options if applicable
            if (['select', 'radio', 'checkbox'].includes(field.type)) {
                field.options = [];
                document.querySelectorAll('.option-item').forEach(item => {
                    const valueInput = item.querySelector('[data-option-prop="value"]');
                    const labelInput = item.querySelector('[data-option-prop="label"]');
                    if (valueInput.value && labelInput.value) {
                        field.options.push({
                            value: valueInput.value,
                            label: labelInput.value
                        });
                    }
                });
            }

            // Update validation rules
            field.validation = {};
            const allowedRules = this.fieldTypes[field.type]?.validation || [];

            allowedRules.forEach(rule => {
                const input = document.getElementById(`val_${rule}`);
                if (input && input.value) {
                    if (['minLength', 'maxLength', 'min', 'max'].includes(rule)) {
                        field.validation[rule] = parseInt(input.value) || parseFloat(input.value);
                    } else {
                        field.validation[rule] = input.value;
                    }
                }
            });

            // Re-render the field
            this.rerenderField(field);

            // Show success feedback
            this.showToast('Field updated successfully', 'success');
        },

        rerenderField(field) {
            const fieldElement = document.querySelector(`[data-field-id="${field.id}"]`);
            if (fieldElement) {
                const label = fieldElement.querySelector('strong');
                label.textContent = field.label;

                const badge = fieldElement.querySelector('.badge');
                if (field.required && !badge) {
                    label.insertAdjacentHTML('afterend', '<span class="badge bg-danger ms-2">Required</span>');
                } else if (!field.required && badge) {
                    badge.remove();
                }

                const preview = fieldElement.querySelector('.field-preview');
                preview.innerHTML = this.renderFieldPreview(field);
            }
        },

        addOption() {
            if (!this.selectedField) return;

            const field = this.currentForm.fields.find(f => f.id === this.selectedField);
            if (!field || !field.options) return;

            const optionsList = document.getElementById('optionsList');
            const newIdx = field.options.length;

            const optionDiv = document.createElement('div');
            optionDiv.className = 'option-item';
            optionDiv.innerHTML = `
            <input type="text" class="form-control form-control-sm" placeholder="Value" value="option${newIdx + 1}" data-option-idx="${newIdx}" data-option-prop="value">
            <input type="text" class="form-control form-control-sm" placeholder="Label" value="Option ${newIdx + 1}" data-option-idx="${newIdx}" data-option-prop="label">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="formBuilder.removeOption(${newIdx})">
                <i class="bi bi-trash"></i>
            </button>
        `;

            optionsList.appendChild(optionDiv);
        },

        removeOption(index) {
            const optionItem = document.querySelector(`[data-option-idx="${index}"]`).closest('.option-item');
            if (optionItem) {
                optionItem.remove();
            }
        },

        removeField(fieldId) {
            if (confirm('Are you sure you want to remove this field?')) {
                this.currentForm.fields = this.currentForm.fields.filter(f => f.id !== fieldId);

                const fieldElement = document.querySelector(`[data-field-id="${fieldId}"]`);
                if (fieldElement) {
                    fieldElement.remove();
                }

                if (this.selectedField === fieldId) {
                    this.clearProperties();
                    this.selectedField = null;
                }

                if (this.currentForm.fields.length === 0) {
                    this.showEmptyState(true);
                }

                this.updateFieldOrder();
            }
        },

        moveFieldUp(fieldId) {
            const fieldIndex = this.currentForm.fields.findIndex(f => f.id === fieldId);
            if (fieldIndex > 0) {
                // Swap fields
                [this.currentForm.fields[fieldIndex - 1], this.currentForm.fields[fieldIndex]] =
                    [this.currentForm.fields[fieldIndex], this.currentForm.fields[fieldIndex - 1]];

                this.rerenderAllFields();
                this.updateFieldOrder();
            }
        },

        moveFieldDown(fieldId) {
            const fieldIndex = this.currentForm.fields.findIndex(f => f.id === fieldId);
            if (fieldIndex < this.currentForm.fields.length - 1) {
                // Swap fields
                [this.currentForm.fields[fieldIndex], this.currentForm.fields[fieldIndex + 1]] =
                    [this.currentForm.fields[fieldIndex + 1], this.currentForm.fields[fieldIndex]];

                this.rerenderAllFields();
                this.updateFieldOrder();
            }
        },

        rerenderAllFields() {
            const formFields = document.getElementById('formFields');
            formFields.innerHTML = '';

            this.currentForm.fields.forEach(field => {
                this.renderField(field);
            });

            // Restore selection if there was one
            if (this.selectedField) {
                this.selectField(this.selectedField);
            }
        },

        updateFieldOrder() {
            this.currentForm.fields.forEach((field, index) => {
                field.order = index;
            });
        },

        showEmptyState(show) {
            const emptyCanvas = document.getElementById('emptyCanvas');
            const formFields = document.getElementById('formFields');

            if (show) {
                emptyCanvas.style.display = 'block';
                formFields.style.display = 'none';
            } else {
                emptyCanvas.style.display = 'none';
                formFields.style.display = 'block';
            }
        },

        clearCanvas() {
            document.getElementById('formFields').innerHTML = '';
            this.showEmptyState(true);
        },

        clearProperties() {
            document.getElementById('fieldProperties').innerHTML = `
            <div class="text-center text-muted py-4">
                <i class="bi bi-hand-index fs-3 d-block mb-2"></i>
                <p class="mb-0">Select a field to edit its properties</p>
            </div>
        `;
        },

        saveForm() {
            const formName = document.getElementById('formName').value.trim();
            if (!formName) {
                this.showToast('Please enter a form name', 'error');
                return;
            }

            if (this.currentForm.fields.length === 0) {
                this.showToast('Please add at least one field to the form', 'error');
                return;
            }

            // Update form settings
            this.currentForm.name = formName;
            this.currentForm.settings.submit_button_text = document.getElementById('submitButtonText').value;
            this.currentForm.settings.success_message = document.getElementById('successMessage').value;
            this.currentForm.settings.is_active = document.getElementById('isActiveForm').checked;

            // Save form via AJAX
            const formData = new FormData();
            formData.append('name', this.currentForm.name);
            formData.append('fields', JSON.stringify(this.currentForm.fields));
            formData.append('settings', JSON.stringify(this.currentForm.settings));

            const url = this.currentForm.id ?
                `${BASE_PATH_JS}/admin/form-builder/update/${this.currentForm.id}` :
                `${BASE_PATH_JS}/admin/form-builder/create`;

            fetch(url, {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.showToast('Form saved successfully', 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        this.showToast(data.error || 'Failed to save form', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error saving form:', error);
                    this.showToast('Failed to save form', 'error');
                });
        },

        previewForm() {
            if (this.currentForm.fields.length === 0) {
                this.showToast('Please add fields to preview the form', 'error');
                return;
            }

            // Generate preview via AJAX
            fetch(`${BASE_PATH_JS}/admin/form-builder/preview`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    fields: this.currentForm.fields
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('previewContent').innerHTML = data.html;
                        const modal = new bootstrap.Modal(document.getElementById('previewModal'));
                        modal.show();
                    } else {
                        this.showToast(data.error || 'Failed to generate preview', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error generating preview:', error);
                    this.showToast('Failed to generate preview', 'error');
                });
        },

        getFieldTypeDescription(type) {
            const descriptions = {
                text: 'Single line text input',
                email: 'Email address with validation',
                phone: 'Phone number input',
                textarea: 'Multi-line text area',
                select: 'Dropdown selection',
                radio: 'Single choice from options',
                checkbox: 'Multiple choice selection',
                number: 'Numeric input with validation',
                date: 'Date picker',
                datetime: 'Date and time picker',
                file: 'File upload field'
            };
            return descriptions[type] || 'Custom field type';
        },

        showToast(message, type = 'info') {
            // Create toast element
            const toast = document.createElement('div');
            toast.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
            toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            toast.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

            document.body.appendChild(toast);

            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.remove();
                }
            }, 5000);
        }
    };

    // Global functions called from HTML
    function editForm(configId) {
        // Load form configuration for editing
        fetch(`${BASE_PATH_JS}/admin/form-builder/getConfiguration/${configId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    formBuilder.currentForm = {
                        id: configId,
                        name: data.configuration.name,
                        fields: data.configuration.fields,
                        settings: data.configuration.settings
                    };

                    // Populate form builder
                    formBuilder.clearCanvas();
                    document.getElementById('formName').value = data.configuration.name;
                    document.getElementById('submitButtonText').value = data.configuration.settings.submit_button_text || 'Submit Booking';
                    document.getElementById('successMessage').value = data.configuration.settings.success_message || '';
                    document.getElementById('isActiveForm').checked = data.configuration.is_active;

                    // Render fields
                    data.configuration.fields.forEach(field => {
                        formBuilder.renderField(field);
                    });

                    if (data.configuration.fields.length > 0) {
                        formBuilder.showEmptyState(false);
                    }

                    const modal = new bootstrap.Modal(document.getElementById('formBuilderModal'));
                    modal.show();
                } else {
                    formBuilder.showToast(data.error || 'Failed to load form configuration', 'error');
                }
            })
            .catch(error => {
                console.error('Error loading form:', error);
                formBuilder.showToast('Failed to load form configuration', 'error');
            });
    }

    function previewForm(configId) {
        // Similar to editForm but open preview directly
        fetch(`${BASE_PATH_JS}/admin/form-builder/getConfiguration/${configId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    return fetch(`${BASE_PATH_JS}/admin/form-builder/preview`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            fields: data.configuration.fields
                        })
                    });
                } else {
                    throw new Error(data.error || 'Failed to load form configuration');
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('previewContent').innerHTML = data.html;
                    const modal = new bootstrap.Modal(document.getElementById('previewModal'));
                    modal.show();
                } else {
                    throw new Error(data.error || 'Failed to generate preview');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                formBuilder.showToast(error.message, 'error');
            });
    }

    function duplicateForm(configId) {
        if (confirm('Do you want to create a copy of this form?')) {
            window.location.href = `${BASE_PATH_JS}/admin/form-builder/duplicate/${configId}`;
        }
    }

    function activateForm(configId) {
        if (confirm('Do you want to set this form as the active booking form?')) {
            window.location.href = `${BASE_PATH_JS}/admin/form-builder/setActive/${configId}`;
        }
    }

    function deleteForm(configId) {
        if (confirm('Are you sure you want to delete this form configuration? This action cannot be undone.')) {
            window.location.href = `${BASE_PATH_JS}/admin/form-builder/delete/${configId}`;
        }
    }

    // Initialize form builder when page loads
    document.addEventListener('DOMContentLoaded', function() {
        formBuilder.init();
    });
</script>

<?php require_once 'views/templates/footer.php'; ?>
