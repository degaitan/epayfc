$(document).ready(function() {

    $('#drag-drop-hint').show();
    function saveNewOrder() {
        let newOrder = [];
        $('.image-card').each(function() {
            newOrder.push($(this).data('path'));
        });

        $.ajax({
            url: 'item/reorder_images_service',
            method: 'POST',
            data: {
                item_id: currentItemId,
                ordered_paths: newOrder
            },
            success: function(response) {
                updateButtonStates();
            },
            error: function() {
                alert("An error occurred while saving the new order.");
            }
        });
    }

    function updateButtonStates() {
        let $cards = $('.image-card');

        $cards.find('button[name="move_left"], button[name="move_right"]').prop('disabled', false);

        $cards.first().find('button[name="move_left"]').prop('disabled', true);
        $cards.last().find('button[name="move_right"]').prop('disabled', true);
    }

    $('.image-grid').sortable({
        items: '.image-card',
        cursor: 'move',
        opacity: 0.7,
        update: function(event, ui) {
            saveNewOrder(); // Save when dropped
        }
    });

    $('button[name="move_left"], button[name="move_right"]').on('click', function(e) {
        e.preventDefault();

        let $button = $(this);
        let $currentCard = $button.closest('.image-card');
        let direction = $button.attr('name');

        if (direction === 'move_left') {
            let $prevCard = $currentCard.prev('.image-card');
            if ($prevCard.length) {
                $currentCard.insertBefore($prevCard);
                saveNewOrder();
            }
        } else if (direction === 'move_right') {
            let $nextCard = $currentCard.next('.image-card');
            if ($nextCard.length) {
                $currentCard.insertAfter($nextCard);
                saveNewOrder();
            }
        }
    });

    updateButtonStates();
});