/**
 * Dynamic Form Builder JavaScript
 * Handles drag-and-drop form creation and field management
 */

class FormBuilder {
    constructor() {
        this.currentForm = null;
        this.formFields = [];
        this.fieldTypes = {};
        this.sortable = null;

        this.initializeComponents();
        this.loadFieldTypes();
        this.setupEventListeners();
    }

    initializeComponents() {
        this.elements = {
            formsList: document.getElementById('formsList'),
            builderContainer: document.getElementById('formBuilderContainer'),
            emptyState: document.getElementById('emptyState'),
            builderInterface: document.getElementById('builderInterface'),
            builderActions: document.getElementById('builderActions'),
            builderTitle: document.getElementById('builderTitle'),
            fieldPalette: document.getElementById('fieldPalette'),
            formFields: document.getElementById('formFields'),
            noFieldsMessage: document.getElementById('noFieldsMessage'),
            formName: document.getElementById('formName'),
            formActive: document.getElementById('formActive'),
            allowUpdates: document.getElementById('allowUpdates'),
            emailNotifications: document.getElementById('emailNotifications'),
            confirmationMessage: document.getElementById('confirmationMessage'),
            fieldConfigModal: document.getElementById('fieldConfigModal'),
            previewModal: document.getElementById('previewModal')
        };
    }

    async loadFieldTypes() {
        try {
            const response = await fetch('/formbuilder/fieldTypes');
            this.fieldTypes = await response.json();
            this.renderFieldPalette();
        } catch (error) {
            console.error('Failed to load field types:', error);
            this.showNotification('error', 'Failed to load field types');
        }
    }

    renderFieldPalette() {
        if (!this.elements.fieldPalette) return;

        const paletteHTML = Object.entries(this.fieldTypes).map(([type, config]) => `
            <div class="col-6 col-md-4 col-lg-6">
                <div class="field-palette-item" onclick="formBuilder.addField('${type}')" data-field-type="${type}">
                    <i class="${config.icon} fa-lg mb-2"></i>
                    <div class="field-name">${config.label}</div>
                </div>
            </div>
        `).join('');

        this.elements.fieldPalette.innerHTML = paletteHTML;
    }

    setupEventListeners() {
        // Initialize sortable for form fields
        if (this.elements.formFields) {
            this.sortable = new Sortable(this.elements.formFields, {
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',
                handle: '.field-handle',
                onUpdate: () => this.updateFieldOrder()
            });
        }

        // Form name input
        if (this.elements.formName) {
            this.elements.formName.addEventListener('input', () => {
                this.updateBuilderTitle();
            });
        }

        // Modal events
        if (this.elements.fieldConfigModal) {
            this.elements.fieldConfigModal.addEventListener('hidden.bs.modal', () => {
                this.clearFieldConfig();
            });
        }
    }

    selectForm(formId) {
        this.showLoading();

        fetch(`${BASE_PATH_JS}/admin/form_builder/getConfiguration/${formId}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    this.showNotification('error', data.error);
                    return;
                }

                this.currentForm = data;
                this.loadFormIntoBuilder(data);
                this.updateFormSelection(formId);
                this.hideLoading();
            })
            .catch(error => {
                this.hideLoading();
                this.showNotification('error', 'Failed to load form: ' + error.message);
            });
    }

    loadFormIntoBuilder(form) {
        // Show builder interface
        this.elements.emptyState.style.display = 'none';
        this.elements.builderInterface.style.display = 'block';
        this.elements.builderActions.style.display = 'block';

        // Populate form settings
        this.elements.formName.value = form.name || '';
        this.elements.formActive.checked = form.active || false;

        if (form.settings) {
            this.elements.allowUpdates.checked = form.settings.allow_updates !== false;
            this.elements.emailNotifications.checked = form.settings.email_notifications !== false;
            this.elements.confirmationMessage.value = form.settings.confirmation_message || '';
        }

        // Load form fields
        this.formFields = form.fields || [];
        this.renderFormFields();
        this.updateBuilderTitle();
    }

    renderFormFields() {
        if (!this.elements.formFields) return;

        if (this.formFields.length === 0) {
            this.elements.noFieldsMessage.style.display = 'block';
            this.elements.formFields.innerHTML = `
                <div class="text-center py-4 text-muted" id="noFieldsMessage">
                    <i class="fas fa-mouse-pointer fa-2x mb-2"></i>
                    <p>Click on field types above to add them to your form</p>
                </div>
            `;
            return;
        }

        this.elements.noFieldsMessage.style.display = 'none';

        // Sort fields by order
        this.formFields.sort((a, b) => (a.order || 0) - (b.order || 0));

        const fieldsHTML = this.formFields.map((field, index) =>
            this.renderFieldItem(field, index)
        ).join('');

        this.elements.formFields.innerHTML = fieldsHTML;
    }

    renderFieldItem(field, index) {
        const fieldType = this.fieldTypes[field.type] || {label: field.type, icon: 'fas fa-question'};
        const requiredBadge = field.required ? '<span class="badge bg-danger ms-2">Required</span>' : '';

        return `
            <div class="form-field-item" data-field-index="${index}">
                <div class="field-header">
                    <div class="field-info">
                        <div class="field-title">
                            ${this.escapeHtml(field.label || field.name)}
                            ${requiredBadge}
                        </div>
                        <div class="field-type">
                            <i class="${fieldType.icon} me-1"></i>
                            ${fieldType.label}
                        </div>
                    </div>
                    <div class="field-actions">
                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                onclick="formBuilder.editField(${index})" 
                                title="Edit Field">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                onclick="formBuilder.deleteField(${index})" 
                                title="Delete Field">
                            <i class="fas fa-trash"></i>
                        </button>
                        <div class="field-handle" title="Drag to reorder">
                            <i class="fas fa-grip-vertical"></i>
                        </div>
                    </div>
                </div>
                <div class="field-preview">
                    ${this.renderFieldPreview(field)}
                </div>
            </div>
        `;
    }

    renderFieldPreview(field) {
        const placeholder = field.placeholder ? `placeholder="${this.escapeHtml(field.placeholder)}"` : '';
        const required = field.required ? 'required' : '';

        switch (field.type) {
            case 'text':
            case 'email':
            case 'phone':
            case 'number':
                return `<input type="${field.type}" class="form-control" ${placeholder} ${required} readonly>`;

            case 'textarea':
                return `<textarea class="form-control" rows="3" ${placeholder} ${required} readonly></textarea>`;

            case 'select':
                const selectOptions = field.options ?
                    Object.entries(field.options).map(([value, label]) =>
                        `<option value="${this.escapeHtml(value)}">${this.escapeHtml(label)}</option>`
                    ).join('') :
                    '<option>No options defined</option>';
                return `
                    <select class="form-select" ${required} disabled>
                        <option value="">Choose...</option>
                        ${selectOptions}
                    </select>
                `;

            case 'radio':
                if (!field.options) return '<p class="text-muted">No options defined</p>';
                return Object.entries(field.options).map(([value, label]) => `
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="preview_${field.name}" disabled>
                        <label class="form-check-label">${this.escapeHtml(label)}</label>
                    </div>
                `).join('');

            case 'checkbox':
                if (!field.options) return '<p class="text-muted">No options defined</p>';
                return Object.entries(field.options).map(([value, label]) => `
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" disabled>
                        <label class="form-check-label">${this.escapeHtml(label)}</label>
                    </div>
                `).join('');

            case 'file':
                return `<input type="file" class="form-control" ${required} disabled>`;

            case 'date':
                return `<input type="date" class="form-control" ${required} readonly>`;

            default:
                return `<input type="text" class="form-control" ${placeholder} ${required} readonly>`;
        }
    }

    addField(fieldType) {
        const fieldConfig = this.fieldTypes[fieldType];
        if (!fieldConfig) return;

        const newField = {
            name: this.generateFieldName(fieldType),
            type: fieldType,
            label: fieldConfig.label,
            required: false,
            order: this.formFields.length,
            placeholder: '',
            validation: {},
            options: fieldType === 'select' || fieldType === 'radio' || fieldType === 'checkbox' ? {} : undefined
        };

        this.formFields.push(newField);
        this.renderFormFields();

        // Automatically open field configuration
        this.editField(this.formFields.length - 1);
    }

    editField(index) {
        const field = this.formFields[index];
        if (!field) return;

        this.currentFieldIndex = index;
        this.populateFieldConfig(field);

        const modal = new bootstrap.Modal(this.elements.fieldConfigModal);
        modal.show();
    }

    populateFieldConfig(field) {
        document.getElementById('fieldIndex').value = this.currentFieldIndex;
        document.getElementById('fieldName').value = field.name || '';
        document.getElementById('fieldLabel').value = field.label || '';
        document.getElementById('fieldType').value = field.type || '';
        document.getElementById('fieldPlaceholder').value = field.placeholder || '';
        document.getElementById('fieldRequired').checked = field.required || false;

        // Load field type options
        this.loadFieldTypeOptions(field.type);

        // Populate field properties
        this.populateFieldProperties(field);
    }

    loadFieldTypeOptions(selectedType) {
        const typeSelect = document.getElementById('fieldType');
        typeSelect.innerHTML = Object.entries(this.fieldTypes).map(([type, config]) =>
            `<option value="${type}" ${type === selectedType ? 'selected' : ''}>${config.label}</option>`
        ).join('');
    }

    populateFieldProperties(field) {
        const propertiesDiv = document.getElementById('fieldProperties');
        const fieldType = this.fieldTypes[field.type];

        if (!fieldType || !fieldType.properties) {
            propertiesDiv.innerHTML = '';
            return;
        }

        let propertiesHTML = '';

        fieldType.properties.forEach(property => {
            switch (property) {
                case 'min_length':
                    propertiesHTML += `
                        <div class="mb-3">
                            <label class="form-label">Minimum Length</label>
                            <input type="number" class="form-control" id="prop_min_length" 
                                   value="${field.validation?.min_length || ''}" min="0">
                        </div>
                    `;
                    break;

                case 'max_length':
                    propertiesHTML += `
                        <div class="mb-3">
                            <label class="form-label">Maximum Length</label>
                            <input type="number" class="form-control" id="prop_max_length" 
                                   value="${field.validation?.max_length || ''}" min="1">
                        </div>
                    `;
                    break;

                case 'pattern':
                    propertiesHTML += `
                        <div class="mb-3">
                            <label class="form-label">Validation Pattern (RegEx)</label>
                            <input type="text" class="form-control" id="prop_pattern" 
                                   value="${field.validation?.pattern || ''}"
                                   placeholder="^[a-zA-Z0-9]+$">
                        </div>
                    `;
                    break;

                case 'min':
                    propertiesHTML += `
                        <div class="mb-3">
                            <label class="form-label">Minimum Value</label>
                            <input type="number" class="form-control" id="prop_min" 
                                   value="${field.validation?.min || ''}">
                        </div>
                    `;
                    break;

                case 'max':
                    propertiesHTML += `
                        <div class="mb-3">
                            <label class="form-label">Maximum Value</label>
                            <input type="number" class="form-control" id="prop_max" 
                                   value="${field.validation?.max || ''}">
                        </div>
                    `;
                    break;

                case 'options':
                    const optionsHTML = field.options ?
                        Object.entries(field.options).map(([value, label]) =>
                            `<div class="option-item mb-2">
                                <div class="input-group">
                                    <input type="text" class="form-control option-value" placeholder="Value" value="${this.escapeHtml(value)}">
                                    <input type="text" class="form-control option-label" placeholder="Label" value="${this.escapeHtml(label)}">
                                    <button type="button" class="btn btn-outline-danger" onclick="this.parentElement.parentElement.remove()">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>`
                        ).join('') : '';

                    propertiesHTML += `
                        <div class="mb-3">
                            <label class="form-label">Options</label>
                            <div id="optionsContainer">
                                ${optionsHTML}
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="formBuilder.addOption()">
                                <i class="fas fa-plus me-1"></i>Add Option
                            </button>
                        </div>
                    `;
                    break;
            }
        });

        propertiesDiv.innerHTML = propertiesHTML;
    }

    addOption() {
        const container = document.getElementById('optionsContainer');
        const optionHTML = `
            <div class="option-item mb-2">
                <div class="input-group">
                    <input type="text" class="form-control option-value" placeholder="Value">
                    <input type="text" class="form-control option-label" placeholder="Label">
                    <button type="button" class="btn btn-outline-danger" onclick="this.parentElement.parentElement.remove()">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', optionHTML);
    }

    saveFieldConfig() {
        const index = parseInt(document.getElementById('fieldIndex').value);
        const field = this.formFields[index];

        if (!field) return;

        // Update basic properties
        field.name = document.getElementById('fieldName').value.trim();
        field.label = document.getElementById('fieldLabel').value.trim();
        field.placeholder = document.getElementById('fieldPlaceholder').value.trim();
        field.required = document.getElementById('fieldRequired').checked;

        // Update validation properties
        field.validation = {};

        const minLength = document.getElementById('prop_min_length');
        if (minLength && minLength.value) field.validation.min_length = parseInt(minLength.value);

        const maxLength = document.getElementById('prop_max_length');
        if (maxLength && maxLength.value) field.validation.max_length = parseInt(maxLength.value);

        const pattern = document.getElementById('prop_pattern');
        if (pattern && pattern.value) field.validation.pattern = pattern.value;

        const min = document.getElementById('prop_min');
        if (min && min.value) field.validation.min = parseFloat(min.value);

        const max = document.getElementById('prop_max');
        if (max && max.value) field.validation.max = parseFloat(max.value);

        // Update options for select/radio/checkbox fields
        const optionsContainer = document.getElementById('optionsContainer');
        if (optionsContainer) {
            const options = {};
            const optionItems = optionsContainer.querySelectorAll('.option-item');

            optionItems.forEach(item => {
                const valueInput = item.querySelector('.option-value');
                const labelInput = item.querySelector('.option-label');

                if (valueInput.value.trim() && labelInput.value.trim()) {
                    options[valueInput.value.trim()] = labelInput.value.trim();
                }
            });

            field.options = options;
        }

        // Validate field name
        if (!field.name) {
            this.showNotification('error', 'Field name is required');
            return;
        }

        // Check for duplicate field names
        const duplicateIndex = this.formFields.findIndex((f, i) =>
            i !== index && f.name === field.name
        );

        if (duplicateIndex !== -1) {
            this.showNotification('error', 'Field name must be unique');
            return;
        }

        this.renderFormFields();

        const modal = bootstrap.Modal.getInstance(this.elements.fieldConfigModal);
        modal.hide();

        this.showNotification('success', 'Field updated successfully');
    }

    deleteField(index) {
        if (!confirm('Are you sure you want to delete this field?')) return;

        this.formFields.splice(index, 1);
        this.updateFieldOrder();
        this.renderFormFields();
        this.showNotification('success', 'Field deleted successfully');
    }

    updateFieldOrder() {
        this.formFields.forEach((field, index) => {
            field.order = index;
        });
    }

    generateFieldName(fieldType) {
        const baseName = fieldType.toLowerCase().replace(/[^a-z0-9]/g, '_');
        let counter = 1;
        let fieldName = baseName;

        while (this.formFields.some(field => field.name === fieldName)) {
            fieldName = `${baseName}_${counter}`;
            counter++;
        }

        return fieldName;
    }

    updateFormSelection(formId) {
        document.querySelectorAll('[data-form-id]').forEach(item => {
            item.classList.remove('active');
        });

        const selectedItem = document.querySelector(`[data-form-id="${formId}"]`);
        if (selectedItem) {
            selectedItem.classList.add('active');
        }
    }

    updateBuilderTitle() {
        const formName = this.elements.formName.value.trim();
        const title = formName ? `Editing: ${formName}` : 'Form Builder';
        this.elements.builderTitle.innerHTML = `<i class="fas fa-tools me-2"></i>${title}`;
    }

    createNewForm() {
        this.currentForm = null;
        this.formFields = [];

        // Reset form
        this.elements.formName.value = '';
        this.elements.formActive.checked = false;
        this.elements.allowUpdates.checked = true;
        this.elements.emailNotifications.checked = true;
        this.elements.confirmationMessage.value = '';

        // Show builder interface
        this.elements.emptyState.style.display = 'none';
        this.elements.builderInterface.style.display = 'block';
        this.elements.builderActions.style.display = 'block';

        this.renderFormFields();
        this.updateBuilderTitle();
        this.elements.formName.focus();

        // Clear form selection
        document.querySelectorAll('[data-form-id]').forEach(item => {
            item.classList.remove('active');
        });
    }

    saveForm() {
        const formName = this.elements.formName.value.trim();

        if (!formName) {
            this.showNotification('error', 'Form name is required');
            this.elements.formName.focus();
            return;
        }

        if (this.formFields.length === 0) {
            this.showNotification('error', 'Form must have at least one field');
            return;
        }

        const formData = {
            name: formName,
            fields: this.formFields,
            settings: {
                allow_updates: this.elements.allowUpdates.checked,
                email_notifications: this.elements.emailNotifications.checked,
                confirmation_message: this.elements.confirmationMessage.value.trim()
            }
        };

        const url = this.currentForm ?
            `/formbuilder/update/${this.currentForm._id}` :
            '/formbuilder/create';

        this.showLoading();

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        })
            .then(response => response.json())
            .then(data => {
                this.hideLoading();

                if (data.success) {
                    this.showNotification('success', 'Form saved successfully');

                    if (data.form_id && !this.currentForm) {
                        // Reload page to update form list
                        setTimeout(() => window.location.reload(), 1000);
                    }
                } else {
                    this.showNotification('error', data.error || 'Failed to save form');
                }
            })
            .catch(error => {
                this.hideLoading();
                this.showNotification('error', 'Save failed: ' + error.message);
            });
    }

    previewCurrentForm() {
        if (this.formFields.length === 0) {
            this.showNotification('warning', 'Add some fields to preview the form');
            return;
        }

        const previewHTML = this.generateFormPreview();
        document.getElementById('previewContent').innerHTML = previewHTML;

        const modal = new bootstrap.Modal(this.elements.previewModal);
        modal.show();
    }

    generateFormPreview() {
        const formName = this.elements.formName.value.trim() || 'Untitled Form';

        let html = `
            <div class="form-preview">
                <h3>${this.escapeHtml(formName)}</h3>
                <form class="preview-form">
        `;

        // Sort fields by order
        const sortedFields = [...this.formFields].sort((a, b) => (a.order || 0) - (b.order || 0));

        sortedFields.forEach(field => {
            html += `<div class="mb-3">`;
            html += `<label class="form-label">${this.escapeHtml(field.label)}`;
            if (field.required) {
                html += ' <span class="text-danger">*</span>';
            }
            html += '</label>';
            html += this.renderFieldPreview(field);
            html += '</div>';
        });

        html += `
                    <div class="mb-3">
                        <label class="form-label">Booking Date & Time <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" disabled>
                    </div>
                    <button type="button" class="btn btn-primary" disabled>Submit Booking</button>
                </form>
            </div>
        `;

        return html;
    }

    clearFieldConfig() {
        document.getElementById('fieldIndex').value = '';
        document.getElementById('fieldName').value = '';
        document.getElementById('fieldLabel').value = '';
        document.getElementById('fieldPlaceholder').value = '';
        document.getElementById('fieldRequired').checked = false;
        document.getElementById('fieldProperties').innerHTML = '';
    }

    showLoading() {
        const overlay = document.createElement('div');
        overlay.className = 'loading-overlay';
        overlay.innerHTML = '<div class="loading-spinner"></div>';
        document.body.appendChild(overlay);
    }

    hideLoading() {
        const overlay = document.querySelector('.loading-overlay');
        if (overlay) {
            overlay.remove();
        }
    }

    showNotification(type, message) {
        const alertClass = {
            'success': 'alert-success',
            'error': 'alert-danger',
            'warning': 'alert-warning',
            'info': 'alert-info'
        }[type] || 'alert-info';

        const alert = document.createElement('div');
        alert.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
        alert.style.cssText = 'top: 20px; right: 20px; z-index: 1060; max-width: 400px;';
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(alert);

        setTimeout(() => {
            alert.remove();
        }, 5000);
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Global functions for onclick handlers
window.selectForm = function (formId) {
    window.formBuilder.selectForm(formId);
};

window.editForm = function (formId) {
    window.formBuilder.selectForm(formId);
};

window.previewForm = function (formId) {
    fetch(`${BASE_PATH_JS}/admin/form_builder/preview/${formId}`)
        .then(response => response.json())
        .then(data => {
            if (data.html) {
                document.getElementById('previewContent').innerHTML = data.html;
                new bootstrap.Modal(document.getElementById('previewModal')).show();
            }
        })
        .catch(error => {
            console.error('Preview failed:', error);
        });
};

window.activateForm = function (formId) {
    if (!confirm('Set this form as the active booking form?')) return;

    fetch(`/formbuilder/activate/${formId}`, {method: 'POST'})
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to activate form');
            }
        });
};

window.cloneForm = function (formId) {
    const newName = prompt('Enter name for the cloned form:');
    if (!newName) return;

    fetch(`/formbuilder/clone_form/${formId}`, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({name: newName})
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to clone form');
            }
        });
};

window.deleteForm = function (formId) {
    if (!confirm('Are you sure you want to delete this form? This action cannot be undone.')) return;

    fetch(`${BASE_PATH_JS}/admin/form_builder/delete/${formId}`, {method: 'POST'}).then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to delete form');
            }
        });
};

window.viewStats = function (formId) {
    fetch(`${BASE_PATH_JS}/admin/form_builder/getAnalytics/${formId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert('Failed to load statistics');
                return;
            }

            alert(`Form Statistics:\nTotal Submissions: ${data.total_submissions}\nPending: ${data.pending}\nApproved: ${data.approved}\nRejected: ${data.rejected}`);
        });
};

window.createNewForm = function () {
    window.formBuilder.createNewForm();
};

window.saveForm = function () {
    window.formBuilder.saveForm();
};

window.previewCurrentForm = function () {
    window.formBuilder.previewCurrentForm();
};

window.saveFieldConfig = function () {
    window.formBuilder.saveFieldConfig();
};

// Initialize form builder when DOM is loaded
document.addEventListener('DOMContentLoaded', function () {
    window.formBuilder = new FormBuilder();
});
