// popular collection
$(function() {
    const $list = $('.popular-collection-list');
    const $collectionTemplate = $('#popular-collection-template').html();

    $list
        .on('click', '.popular-collection-delete', function() {
            $(this).closest('.popular-collection-item').remove();
            indexingCollection();
        })
        .on('click', '.popular-collection-add', function(e) {
            e.preventDefault();
            const uniqueID = $.makeUniqid();

            $list.append(
                $collectionTemplate.replaceAll('{{key}}', uniqueID)
            );

            $list.find('.popular-collection-item').last().find('[data-insert-cb]').each(function () {
                const cb = $(this).data('insert-cb');
                if (typeof window[cb] === 'function') {
                    window[cb].bind(this);
                }
            });

            indexingCollection();
        })
        .on('change', '.popular-collection-select', function() {
            const $this = $(this);
            const $option = $this.find('option:selected')
            const title = $option.data('title');
            const image = $option.data('image');

            $this.siblings('.popular-collection-title').val(title);
            $this.siblings('.popular-collection-uploader').trigger('change_image', [image])
        });

    indexingCollection();

    function indexingCollection() {
        const $labels = $list.find('.popular-collection-label');

        if (!$labels.length) {
            $('.popular-collection-description').addClass('hidden');
        } else {
            $('.popular-collection-description').removeClass('hidden');
        }

        if ($labels.length >= 4) {
            $('.popular-collection-add-wrapper').addClass('hidden');
        } else {
            $('.popular-collection-add-wrapper').removeClass('hidden');
        }

        $labels.each(function(index) {
            $(this).text(`Collection #${index + 1}:`);
        });
    }
});

// trending keywords
$(function() {
    let trendingList = [];
    let expired = '';
    const $trending = $('.trending');
    const $list = $('.trending-list');
    const $input = $('.trending-input');
    const $dropdown = $('.trending-input-dropdown');
    const $hiddenInput = $('.trending-hidden-input');
    const $expiredInput = $('.trending-expired');
    const $addKeywordBtn = $('.trending-add');
    const trendingItemTemplate = $('#trending-item').html();
    let suggestions = [];

    try {
        let input = JSON.parse($('.trending-suggestion-input').val());

        for (const [key, value] of Object.entries(input)) {
            suggestions.push({
                text: key,
                count: value,
            });
        }
    } catch (e) {
        suggestions = [];
    }

    $trending
        .on('click', '.trending-add', function() {
            let keyword = $input.val();
            if (!keyword) return;

            if (trendingList.includes(keyword)) {
                return alert('This keyword is being used');
            }

            $input.val('');

            renderTrendingList([
                ...trendingList,
                keyword
            ]);
        })
        .on('click', '.trending-delete', function() {
            let index = parseInt($(this).data('index'));

            if (isNaN(index)) return;

            renderTrendingList([
                ...trendingList.slice(0, index),
                ...trendingList.slice(index + 1)
            ]);
        });

    $input.on('keydown', function(e) {
        if (e.keyCode == 13) {
            e.preventDefault();
            $addKeywordBtn.trigger('click');
            return false;
        }
    });

    $expiredInput
        .on('change', function() {
            expired = $(this).val();
            renderTrendingList(trendingList);
        })
        .on('keydown', function() {
            $(this).trigger('click');
        });

    $dropdown
        .on('click', '.trending-suggestion', function() {
            let keyword = $(this).data('value');
            if (!keyword) return;

            if (trendingList.includes(keyword)) {
                return alert('This keyword is being used');
            }

            $input.val('');

            renderTrendingList([
                ...trendingList,
                keyword
            ]);
        });

    try {
        let inputVal = JSON.parse($hiddenInput.val());

        trendingList = [
            ...inputVal.list
        ];

        expired = inputVal?.expired || '';
    } catch (e) {
        trendingList = [];
    }

    $('#form').on('submit', function(e) {
        if (trendingList?.length && trendingList.length > 0 && trendingList.length < 3) {
            e.preventDefault();
            alert('You must set 3 trending keywords at a time!');
        }
    });

    renderTrendingList(trendingList);

    function renderTrendingList(list) {
        if (list.length > 3) {
            return false;
        } else if (list.length === 3) {
            $('.trending-add').addClass('disabled');
        } else {
            $('.trending-add').removeClass('disabled');
        }

        trendingList = list;
        $hiddenInput.val(JSON.stringify({
            list: list,
            expired: expired,
        }));

        $list.empty();
        renderSuggestion();

        $expiredInput.val(expired);

        if (!trendingList.length) {
            $list.text('No trending keywords selected!');
            $expiredInput.attr('required', false);
            return;
        }

        $expiredInput.attr('required', true);
        $expiredInput.attr('readonly', false);

        $list.show();

        if (expired && new Date(expired)?.setHours(0,0,0,0) < new Date().setHours(0,0,0,0)) {
            $list.addClass('expired');
        } else {
            $list.removeClass('expired');
        }

        trendingList.forEach((text, index) => {
            $list.append(
                trendingItemTemplate
                    .replaceAll('{{index}}', index)
                    .replaceAll('{{text}}', text)
            );
        });
    }

    function renderSuggestion() {
        const $suggestions = $dropdown.find('.trending-suggestions');

        $suggestions.find('.trending-suggestion').remove();

        if (!suggestions.length) {
            $suggestions.append(`<div class="trending-suggestion" >No suggestion keywords!</div>`);
        }

        suggestions.forEach((suggestion, index) => {
            let addedClass = '';
            let text = `#${index + 1} ${suggestion.text} (${suggestion.count})`;
            if (trendingList.includes(suggestion.text)) {
                text += ' -- used';
                addedClass = 'disabled';
            }
            $suggestions.append(`<div class="trending-suggestion ${addedClass}" data-value="${suggestion.text}">${text}</div>`);
        });
    }
});

window.initSelect2 = function () {
    const $el = $(this);
    const option = {};

    if ($el.data('placeholder')) {
        option['placeholder'] = $el.data('placeholder');
    }

    $el.select2(option);
};
