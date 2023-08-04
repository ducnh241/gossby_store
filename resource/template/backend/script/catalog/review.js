(function ($) {
    'use strict';

    window.catalogInitReviewCustomerFrm = function () {
        var container = $(this);

        var json_data = this.getAttribute('data-customer');

        container.find('[data-section="customer-browser"]').osc_itemSelector({
            click_callback: function (checked) {
                container.find('[data-section="customer-info"]')[checked ? 'hide' : 'show']();
            },
            multi_select: false,
            browse_url: this.getAttribute('data-browse-url'),
            placeholder_text: 'Search for customers',
            no_selected_text: 'No customers selected',
            input_name: 'customer_id',
            attributes_extend: [{key: 'email', class: 'catalog-customer-selector-email', position: 4}],
            data: json_data ? JSON.parse(json_data) : []
        });
    };

    window.catalogInitReviewOrderBrowser = function () {
        const json_data = this.getAttribute('data-order');
        let json_data_order_item_id = this.getAttribute('data-order-item-id');

        $(this).osc_itemSelector({
            multi_select: false,
            browse_url: this.getAttribute('data-browse-url'),
            placeholder_text: 'Search for orders',
            no_selected_text: 'No orders selected ',
            input_name: 'order_id',
            attributes_extend: [{key: 'shipping_full_name', class: 'catalog-customer-selector-email', position: 3}, {key: 'email', class: 'catalog-customer-selector-email', position: 4}],
            data: json_data ? JSON.parse(json_data) : [],
            alway_render_seleted_item: true,
            selected_item_render_callback: function (elem, order) {
                $('input[name="country_code"]').val(order.country_code);
                $.ajax({
                    type: 'POST',
                    url: $.base_url + '/catalog/backend_order/browseOrderItem/hash/' + OSC_HASH,
                    data: {
                        order_id: order.id
                    },
                    success: function (response) {
                        if (response.result === 'OK') {
                            const order_items = response.data.order_items;
                            $('#catalogInitReviewOrderItem').empty();
                            order_items.forEach((element, index) => {
                                const item_id = `item_${element.id}`;
                                const input = $('<input />').attr({
                                    id: item_id,
                                    type: 'radio',
                                    name: 'order_item_id',
                                    value: element.id,
                                });

                                if (!index) {
                                    input.attr('checked', 'checked');
                                }

                                const item = $('<div />')
                                    .append(
                                        $('<div />')
                                            .addClass('styled-radio mr5')
                                            .append(input)
                                            .append($('<ins />'))
                                    )
                                    .append(
                                        $('<label />')
                                            .addClass('label-inline')
                                            .attr('for', item_id)
                                            .text(`[${element.id}] ${element.title}`)
                                    )

                                const product_type_item = $('<div />').addClass('input-desc pl25 mb15').text(`Product type: ${element.product_type}`)
                                $('#catalogInitReviewOrderItem').append(item).append(product_type_item);
                            });

                            if (json_data_order_item_id) {
                                $(`input[id="item_${json_data_order_item_id}"]`).attr('checked', 'checked');
                                json_data_order_item_id = undefined;
                            }

                            if (order_items.length === 1) {
                                $('#catalogInitReviewOrderItem').hide();
                            } else {
                                $('#catalogInitReviewOrderItem').show();
                            }
                        } else {
                            alert(response.message);
                        }
                    }
                })
            },
        });
    };

    window.catalogInitReviewProductBrowser = function () {
        var json_data = this.getAttribute('data-product');

        $(this).osc_itemSelector({
            multi_select: false,
            browse_url: this.getAttribute('data-browse-url'),
            placeholder_text: 'Search for products',
            no_selected_text: 'No products selected',
            input_name: 'product_id',
            data: json_data ? JSON.parse(json_data) : []
        });
    };

    window.catalogInitReviewStateSwitcher = function () {
        $(this).click(function (e) {
            e.preventDefault();

            if (this.getAttribute('disabled') === 'disabled') {
                return;
            }

            this.setAttribute('disabled', 'disabled');

            var node = this;

            $.ajax({
                url: this.getAttribute('href'),
                success: function (response) {
                    node.removeAttribute('disabled');

                    if (response.result !== 'OK') {
                        alert(response.message);
                        return;
                    }

                    $(node).closest('.catalog-review-item').before(response.data.item).remove();
                }
            });
        });
    }

	window.initReviewImgUploader = function () {
		var image_list = $(this).closest('form').find('.review-images');

		$(this).osc_uploader({
			max_files: -1,
			max_connections: 5,
			process_url: this.getAttribute('data-process-url'),
			btn_content: 'Upload image',
			dragdrop_content: 'Drop here to upload',
			image_mode: true,
			xhrFields: {withCredentials: true},
			headers: {
				'X-Requested-With': 'XMLHttpRequest',
				'X-OSC-Cross-Request': 'OK'
			}
		}).bind('uploader_add_file', function (e, file_id, file, file_name, file_size) {
			var item = _reviewFrm_renderImage().attr('file-id', file_id).attr('data-uploader-step', 'queue');

			$('<div />').addClass('uploader-progress-bar').appendTo(item).append($('<div />'));
			$('<div />').addClass('step').appendTo(item);

			/*var reader = new FileReader();
			reader.onload = function (e) {
				var item = image_list.find('> [file-id="' + file_id + '"]');

				if (!item[0]) {
					return;
				}

				var img = document.createElement('img');

				img.onload = function () {
					var canvas = document.createElement('canvas');

					var MAX_WIDTH = 400;
					var MAX_HEIGHT = 400;

					var width = img.width;
					var height = img.height;

					if (width > height) {
						if (width > MAX_WIDTH) {
							height *= MAX_WIDTH / width;
							width = MAX_WIDTH;
						}
					} else {
						if (height > MAX_HEIGHT) {
							width *= MAX_HEIGHT / height;
							height = MAX_HEIGHT;
						}
					}

					canvas.width = width;
					canvas.height = height;

					var ctx = canvas.getContext('2d');
					ctx.drawImage(img, 0, 0, width, height);

					canvas.toBlob(function (blob) {
						item.trigger('update', [{url: URL.createObjectURL(blob)}]);
					});
				};

				img.src = e.target.result;
			};
			reader.readAsDataURL(file);*/
		}).bind('uploader_upload_progress', function (e, file_id, file, file_size, uploaded_size, uploaded_percent) {
			var item = image_list.find('> [file-id="' + file_id + '"]');

			if (!item[0]) {
				return;
			}

			if (parseInt(uploaded_percent) === 100) {
				item.attr('data-uploader-step', 'process');
			} else {
				item.attr('data-uploader-step', 'upload');
				item.find('.uploader-progress-bar > div').css('width', uploaded_percent + '%');
			}

		}).bind('uploader_upload_complete', function (e, file_id, response, pointer) {
			var item = image_list.find('> [file-id="' + file_id + '"]');

			if (!item[0]) {
				return;
			}

			eval('response = ' + response);

			if (response.result !== 'OK') {
				alert(response.message);
				item.remove();
				return;
			}

			item.trigger('update', [{
			    id: response.data.file,
				url: response.data.url,
				filename: response.data.file,
				width: response.data.width,
				height: response.data.height,
				extension: response.data.extension,
			}]);

			item.removeAttr('file-id').removeAttr('data-uploader-step');

			item.find('.uploader-progress-bar').remove();
			item.find('.step').remove();
		}).bind('uploader_upload_error', function (e, file_id, error_code, error_message) {
			var item = image_list.find('> [file-id="' + file_id + '"]');

			if (!item[0]) {
				return;
			}

			alert('Có vấn đề xảy ra trong quá trình upload file, xin hãy thử lại');
			item.remove();
		});
	};

	function _reviewFrm_renderImage(image) {
		var data = {
		    id: 0,
			url: '',
			filename: '',
			width: 0,
			height: 0,
			extension: ''
		};

		var image_list = $('.review-images');

		var item = $('<div />').addClass('review-image').appendTo(image_list).bind('update', function (e, new_data) {
			if (new_data !== null && typeof new_data === 'object') {
				$.extend(data, new_data);
			}

			item.css('background-image', data.url ? ('url(' + data.url + ')') : 'initial');

			item.find('input[type="hidden"]').remove();

			if (data.url) {
                item.append($('<input />').attr({type: 'hidden', name: 'images[' + data.id + ']', value: data.id}));
			}
		});

		initItemReorder(item, '.review-images', '.review-image', 'product-post-frm-img-reorder-helper', function (helper) {
			helper.html('');
		});

		var control_bars = $('<div />').addClass('controls').appendTo(item);

		$($.renderIcon('trash-alt-regular')).mousedown(function (e) {
			e.stopPropagation();
			e.stopImmediatePropagation();

			item.remove();
		}).appendTo(control_bars);

		item.trigger('update', [image]);

		return item;
	}

	window.reviewFrm__initImages = function () {
		var images = JSON.parse(this.getAttribute('data-images'));

		$.each(images, function (k, image) {
			_reviewFrm_renderImage({
                id: image.id,
				url: image.url,
				filename: image.filename,
				width: image.width,
				height: image.height,
				extension: image.extension,
			});
		});
	};
})(jQuery);