document.addEventListener('DOMContentLoaded', function () {
    const tagCheckboxes = document.querySelectorAll('input[name="business_tags[]"]');
    tagCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            const checkedBoxes = document.querySelectorAll('input[name="business_tags[]"]:checked');
            if (checkedBoxes.length > 2) {
                this.checked = false; // Undo the check action
                alert('You can only select up to 2 tags.');
            }
        });
    });
});