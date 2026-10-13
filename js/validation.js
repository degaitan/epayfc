$(document).ready(function () {
    let config = { title_min_length: 3, title_max_length: 255 };
    const type = $('#page-type').data('type');

    let touched = {};
    let validity = {
        title: false,
        duration: true,
        pricing: false,
        categories: true
    };

    const $form = $('.edit-form');
    const $saveBtn = $('.save-btn');

    $.ajax({
        url: 'item/get_validation_config_service',
        method: 'GET',
        success: function (data) {
            config = data;
            validateAll();
        }
    });


    $('input, textarea').on('input blur change', function (e) {
        const fieldName = $(this).attr('name');

        if (e.type === 'blur' || e.type === 'change' || touched[fieldName]) {
            touched[fieldName] = true;
        }
        validateAll();
    });

    $form.on('submit', function (e) {
        if ($saveBtn.prop('disabled')) {
            e.preventDefault();
            $('input, textarea').each(function() {
                touched[$(this).attr('name')] = true;
            });
            validateAll();
        }
    });

    function validateCheckBox() {
        let categories = $('input[name="categories[]"]:checked').length
        validity.categories = categories <= 3;
    }

    function validateDescript() {
        if (touched['description']) {
            let descVal = $('textarea[name="description"]').val().trim();
            if (descVal.length > 0 && descVal.length < 3) {
                setError('description', "Description must be at least 3 characters long");
            } else {
                setSuccess('description');
            }
        }
    }

    function validateDuration() {
        if (touched['duration']) {
            let durationVal = parseInt($('input[name="duration"]').val(), 10);
            if (isNaN(durationVal) || durationVal < 1 || durationVal > 365) {
                setError('duration', "Duration must be between 1 and 365 days");
                validity.duration = false;
            } else {
                setSuccess('duration');
                validity.duration = true;
            }
        } else if (type === 'edit') {
            validity.duration = true;
        }
    }

    function validatePricing() {
        if (touched['starting_bid'] || touched['buy_now_price'] || touched['direct_sale']) {
            let startBidVal = parseFloat($('input[name="starting_bid"]').val());
            let buyNowVal = parseFloat($('input[name="buy_now_price"]').val());
            let directVal = parseFloat($('input[name="direct_sale"]').val());

            let hasStart = !isNaN(startBidVal);
            let hasDirect = !isNaN(directVal);
            validity.pricing = false;

            clearFieldStatus('starting_bid');
            clearFieldStatus('buy_now_price');
            clearFieldStatus('direct_sale');

            if (hasStart && hasDirect) {
                setError('starting_bid', "If you choose direct sale, leave this blank.");
                setError('direct_sale', "If you choose auction, leave this blank.");
            } else if (hasStart) {
                if (startBidVal <= 0.0) {
                    setError('starting_bid', "Staring bid must be > 0");
                    validity.pricing = false;
                } else {
                    setSuccess('starting_bid');
                    validity.pricing = true;
                }

                if (!isNaN(buyNowVal)) {
                    if (buyNowVal < 0.0) {
                        setError('buy_now_price', "Purchase price must be > 0");
                        validity.pricing = false;
                    } else if (buyNowVal <= startBidVal) {
                        setError('buy_now_price', "Purchase price must be greater than starting bid");
                        validity.pricing = false;
                    } else {
                        setSuccess('buy_now_price');
                    }
                } else {
                    clearFieldStatus('buy_now_price');
                }
            } else if (hasDirect) {
                if (directVal <= 0.0) {
                    setError('direct_sale', "Purchase price must be > 0");
                    validity.pricing = false;
                } else {
                    setSuccess('direct_sale');
                    validity.pricing = true;
                }
            }
        } else if (type ==='edit') {
            validity.pricing = true;
        }
    }

    function validateTitle() {
        if (touched['title']) {
            let titleVal = $('input[name="title"]').val().trim();
            if (titleVal.length < config.title_min_length) {
                setError('title', `Title must be at least ${config.title_min_length} characters`);
                validity.title = false;
                updateButton();
            } else if (titleVal.length > config.title_max_length) {
                setError('title', `Title must be less than ${config.title_max_length} characters`);
                validity.title = false;
                updateButton();
            } else {
                $.ajax({
                    url: 'item/check_title_unique_service',
                    method: 'POST',
                    data: { title: titleVal, item_id: currentItemId },
                    success: function (res) {
                        if (res.is_valid) {
                            setSuccess('title');
                            validity.title = true;
                        } else {
                            setError('title', "You already have an item with this title.");
                            validity.title = false;
                        }
                        updateButton();
                    }
                });
            }
        } else if (type === 'edit') {
            validity.title = true;
        }
    }

    function validateAll() {
        validateCheckBox();
        validateDescript();
        validateDuration();
        validatePricing();
        validateTitle();
        updateButton();
    }

    function setError(fieldName, message) {
        if (!touched[fieldName]) return;
        let $input = $(`[name="${fieldName}"]`);
        $input.removeClass('custom-valid').addClass('custom-invalid');
        $(`.js-error-${fieldName}`).text(message).show();
    }

    function setSuccess(fieldName) {
        if (!touched[fieldName]) return;
        let $input = $(`[name="${fieldName}"]`);
        $input.removeClass('custom-invalid').addClass('custom-valid');
        $(`.js-error-${fieldName}`).hide();
    }

    function clearFieldStatus(fieldName) {
        let $input = $(`[name="${fieldName}"]`);
        $input.removeClass('custom-invalid custom-valid');
        $(`.js-error-${fieldName}`).hide();
    }

    function updateButton() {
        if (validity.title && validity.duration && validity.pricing && validity.categories) {
            $saveBtn.prop('disabled', false).css('opacity', '1');
        } else {
            $saveBtn.prop('disabled', true).css('opacity', '0.5');
        }
    }

    updateButton();
});