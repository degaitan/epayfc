$(document).ready(function(){
    //quand la page est prête, on cherche la balise 'html' et on ajoute 'js-enable' à ses listes
   $('html').addClass('js-enabled');


    const categoryDeleteModalElement = document.getElementById('categoryDeleteModal');
    const categoryDeleteModal = new bootstrap.Modal(categoryDeleteModalElement);


   const confirmDeleteButton = document.getElementById('confirmDeleteBtn');

    let categoryIdToDelete = null;

    $('.category-list').on('click', 'button[name="ask_delete"]', function(event) {
        event.preventDefault();
        categoryIdToDelete = $(this).data('id');
        categoryDeleteModal.show();
    });


   confirmDeleteButton.addEventListener('click', async (event) => {
        $.ajax({
            //chemin vers la méthode appelée, en post car le controlleur lit en post, le corps du post(data) c'est l'id de la cat selectionnée

            url: 'category/delete_service',
            method : 'POST',
            data : {id: categoryIdToDelete},
            success: function (response){
                if(response.success){
                    categoryDeleteModal.hide();
                    $('button[data-id="' + categoryIdToDelete + '"]').closest('.category-row').remove();
                }
            }
        })
   })

   function saveNewOrder() {
       let newOrder = [];
       $('.category-row').each(function() {
           newOrder.push($(this).data('id'));
       });
       $.ajax({
           url : 'category/reorder_categories_service',
           method : 'POST',
           data : {ordered_categories : newOrder},
           success: function(response) {
               if(response.success){

               }
           },
           error: function() {
               alert("An error occurred while saving the new priorities.");
           }
       })
   }

   $('.category-list').sortable({
       items : '.category-row',
       cursor : 'move',
       opacity: 0.7,
       update: function(event, ui) {
           saveNewOrder();
       }
   });

   // '#' permet d'appeler un id, pas une classe
   $('#showAddBtn').on('click', function() {
           $('#addCategoryForm').removeClass('hidden');
           $('#newCategoryName').focus();
   });

   //pas oublier ici de clear le input en cas de cancel
   $('#cancelAddBtn').on('click', function() {
       $('#addCategoryForm').addClass('hidden');
       $('#newCategoryName').val('');
   });

    $('#newCategoryName').on('blur',function(e){
        if (e.relatedTarget && e.relatedTarget.id === 'cancelAddBtn') {
            return;
        }
        validateName();
    });

    function validateName() {
       const $errorSpan = $('.add-error');
       let new_potential_name = $('#newCategoryName').val().trim();
        $errorSpan.text('');

        if(new_potential_name === ''){
            return;
        }
       if(new_potential_name.length<3 || new_potential_name.length >25){
            $errorSpan.text('New category name must be between 3 and 25 characters');
            return;
       } else {
           $.ajax({
               url :'category/check_name_unique',
               method : 'POST',
               data : { new_name : new_potential_name},
               success: function(response){
                   if(!response.is_valid) {
                       $errorSpan.text('This name is not available');
                   } else {
                       $.ajax({
                           url : 'category/add_service',
                           method : 'POST',
                           data : { new_name : new_potential_name},
                           success: function(response) {
                               if(response.success){
                                   const newRow = `
                                       
                                        <form method="post" action="category/manage" class = "category-row" data-id="${response.id}">
                                            <span id="drag-drop-hint" class="drag-handle js-only">
                                                <i class="bi bi-grip-vertical"></i>
                                            </span>
                                            <input type="hidden" name="id" value="${response.id}">
                                            <span class="category-name js-only" data-count="0">${response.name} (0)</span>
                                            <input type="text" name="name" value="${response.name}">
                                           
                                            <button type="button" class="js-only" name="edit">Edit</button>
                                            <button type="submit" class="nojs-only" name="up" value="1" >Up</button>
                                            <button type="submit" class="nojs-only" name="down" value="1">Down</button>
                                            <button type="submit" class="nojs-only" name="rename" value="1">Save</button>
                                            <button type="submit" name="ask_delete" data-id="${response.id}" value="1">Delete</button>
                                            <span class="form-error"></span>

                                            </form>
                                    `;
                                    //$('.category-list').append(newRow);
                                   $('#addCategoryForm').before(newRow);
                                   $('#addCategoryForm').addClass('hidden');
                                   $('#newCategoryName').val('');

                               }
                           }
                       })
                   }

               }
           })
       }
    }

    $('.category-list').on('click', 'button[name="edit"]', function(event){
        event.preventDefault();
        const $row = $(this).closest('.category-row');
        $row.find('.form-error').text('');
        $row.addClass('editing');
        $row.find('input[name="name"]').focus();

    })

    $('.category-list').on('focusout', 'input[name="name"]', function(event) {
        const $row = $(this).closest('.category-row');
        if (!$row.hasClass('editing')) return;
        validateNameEdit($(this));
    });

    function validateNameEdit($input) {
        const $row = $input.closest('.category-row');
        const id = $row.data('id');
        const new_name = $input.val().trim();
        const $errorSpan = $input.closest('.category-row').find('.form-error');

        $errorSpan.text('');

        if(new_name === ''){
            return;
        }
        if(new_name.length<3 || new_name.length >25){
            $errorSpan.text('New name must be between 3 and 25 characters');
            return;
        } else {
            $.ajax({
                url: 'category/check_name_unique',
                method: 'POST',
                data: {new_name: new_name, id: id},
                success: function (response) {
                    if (!response.is_valid) {
                        $errorSpan.text('This name is not available');
                    } else {
                        $.ajax({
                            url: 'category/rename_service',
                            method: 'POST',
                            data: {new_name: new_name, id: id},
                            success: function (response) {
                                if (response.success) {
                                    const $row = $input.closest('.category-row');
                                    const $name = $row.find('.category-name');
                                    const count = $name.data('count');
                                    $input.val(response.name);
                                    $row.removeClass('editing');
                                    $name.text(response.name + ' (' + count + ')');
                                    $errorSpan.text('');
                                }
                            }
                        });
                    }
                }
            });
        }
        }



});
