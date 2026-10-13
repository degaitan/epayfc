document.addEventListener('DOMContentLoaded', () => {

    //on cherche tous les inputs dont le nom contient 'category[]'
    const checkboxes = document.querySelectorAll('input[name="categories[]"]');

    const categoryModalElement = document.getElementById('categoryModal');
    const categoryModal = new bootstrap.Modal(categoryModalElement);

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', (event) => {
            let checked = document.querySelectorAll('input[name="categories[]"]:checked').length;
            //si on est déjà à 3 checks, on show la modale au moment du 4eme, on uncheck la 4eme box
            if(checked > 3){
                checkbox.checked = false;
                categoryModal.show();
            }
        })
    });

});