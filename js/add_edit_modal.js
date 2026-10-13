document.addEventListener('DOMContentLoaded', () => {

    const form = document.querySelector('.edit-form');
    const links = document.querySelectorAll('a');
    const confirmLeaveBtn = document.getElementById('confirmLeaveBtn');

    // Initialize the Bootstrap modal using Bootstrap's JS API
    const leaveModalElement = document.getElementById('leaveModal');
    const leaveModal = new bootstrap.Modal(leaveModalElement);

    let isPristine = true;
    let pendingNavigationUrl = '';

    form.addEventListener('input', () => {
        isPristine = false;
    });

    form.addEventListener('submit', () => {
        isPristine = true;
    });

    //Si on clique sur un lien (bouton back, barre en bas)
    links.forEach(link => {
        link.addEventListener('click', (event) => {
            // Si y a eu une modif dans le formulaire
            if (!isPristine && link.getAttribute('href')) {
                event.preventDefault();
                pendingNavigationUrl = link.href;
                leaveModal.show();
            }
        });
    });


    confirmLeaveBtn.addEventListener('click', () => {
        isPristine = true;
        window.location.href = pendingNavigationUrl;
    });



    window.addEventListener('beforeunload', (event) => {
        if (!isPristine) {
            event.preventDefault();
            event.returnValue = '';
        }
    });
});