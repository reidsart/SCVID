document.addEventListener('DOMContentLoaded', function () {
    // Select all form fields
    const formFields = document.querySelectorAll('form input, form select, form textarea');

    // Attach 'blur' or 'change' event listeners to fields
    formFields.forEach((field, index) => {
        field.addEventListener('blur', function () {
            // Scroll to the next field if it exists
            const nextField = formFields[index + 1];
            if (nextField) {
                nextField.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    });
});