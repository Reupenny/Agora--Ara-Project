// WYSIWYG Editor Functions

// Execute formatting command on the currently focused editor
function execCmd(command) {
    document.execCommand(command, false, null);
}

// Sync contenteditable content with hidden textareas for form submission
function syncEditorContent() {
    // Product page editors
    const descriptionEditor = document.getElementById('description-editor');
    const descriptionHidden = document.getElementById('description-hidden');
    const additionalInfoEditor = document.getElementById('additional-info-editor');
    const additionalInfoHidden = document.getElementById('additional-info-hidden');

    // Business page editors
    const detailsEditor = document.getElementById('details-editor');
    const detailsHidden = document.getElementById('details-hidden');
    const additionalDetailsEditor = document.getElementById('additional-details-editor');
    const additionalDetailsHidden = document.getElementById('additional-details-hidden');

    // Profile page editors
    const bioEditor = document.getElementById('user-bio-editor');
    const bioHidden = document.getElementById('user-bio-hidden');

    // Sync product page editors
    if (descriptionEditor && descriptionHidden) {
        descriptionHidden.value = descriptionEditor.innerHTML;
    }
    if (additionalInfoEditor && additionalInfoHidden) {
        additionalInfoHidden.value = additionalInfoEditor.innerHTML;
    }

    // Sync business page editors
    if (detailsEditor && detailsHidden) {
        detailsHidden.value = detailsEditor.innerHTML;
    }
    if (additionalDetailsEditor && additionalDetailsHidden) {
        additionalDetailsHidden.value = additionalDetailsEditor.innerHTML;
    }

    // Sync profile page editors
    if (bioEditor && bioHidden) {
        bioHidden.value = bioEditor.innerHTML;
    }
}

// Add placeholder support for contenteditable
function handlePlaceholder(editor) {
    const placeholder = editor.getAttribute('data-placeholder');

    if (!editor.textContent.trim()) {
        editor.innerHTML = `<span style="color: #999; font-style: italic;">${placeholder}</span>`;
    }
}

// Initialise WYSIWYG editors when page loads
document.addEventListener('DOMContentLoaded', function () {
    const editors = document.querySelectorAll('.wysiwyg-editor');

    editors.forEach(editor => {
        // Handle placeholder
        handlePlaceholder(editor);

        // Focus event - clear placeholder
        editor.addEventListener('focus', function () {
            const placeholder = this.getAttribute('data-placeholder');
            if (this.innerHTML.includes(placeholder)) {
                this.innerHTML = '';
            }
        });

        // Blur event - restore placeholder if empty
        editor.addEventListener('blur', function () {
            if (!this.textContent.trim()) {
                handlePlaceholder(this);
            }
        });

        // Input event - sync content with hidden textareas
        editor.addEventListener('input', syncEditorContent);
    });

    // Sync content before form submission
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', syncEditorContent);
    }
});

// Update toolbar button states based on current selection
document.addEventListener('selectionchange', function () {
    const selection = window.getSelection();
    if (selection.rangeCount > 0) {
        const toolbarButtons = document.querySelectorAll('.toolbar-btn');

        toolbarButtons.forEach(btn => {
            btn.classList.remove('active');

            const command = btn.getAttribute('onclick')?.match(/execCmd\('([^']+)'\)/)?.[1];
            if (command && document.queryCommandState(command)) {
                btn.classList.add('active');
            }
        });
    }
});