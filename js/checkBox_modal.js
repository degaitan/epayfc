document.addEventListener('DOMContentLoaded', () => {

    const form = document.querySelector('.edit-form');

    const leaveModalElement = document.getElementById('checkBoxModal');
    const checkBoxModal = new bootstrap.Modal(leaveModalElement);


    form.addEventListener('input', () => {
        checkedBoxes = form.querySelectorAll('input[type="checkbox"]:checked').length;
        if (checkedBoxes > 3) {
            checkBoxModal.show();
        }
    });
});