
const container = $("#render");

const data = JSON.parse(container.find('script[data-json="filter_setting"]')[0].innerHTML);


const getTotalChildren = (data) => {
    let total = data.length;
    data.forEach((i) => {
        const children = i.children;
        if (children) total += getTotalChildren(children);
    });
    return total;
};

const renderTree = ({data, container, parentID}) => {
    data.forEach((item) => {
        const div = $("<div/>");
        const header = $("<div/>").addClass("header").appendTo(div);
        const titleContainer = $("<div/>").appendTo(header);
        const children = item.children;
        if (children?.length) {
            const content = $("<div/>").addClass("content").appendTo(div);
            renderTree({data: children, container: content, parentID: !parentID ? item.id : parentID});
            const plusIcon = $("<span/>").addClass("plus hidden").text("[+]");
            const minusIcon = $("<span/>").addClass("minus").text("[-]");
            plusIcon.click(() => {
                minusIcon.removeClass("hidden");
                plusIcon.addClass("hidden");
                content.removeClass("hidden");
            });
            minusIcon.click(() => {
                minusIcon.addClass("hidden");
                plusIcon.removeClass("hidden");
                content.addClass("hidden");
            });
            plusIcon.appendTo(titleContainer);
            minusIcon.appendTo(titleContainer);
        }
        titleContainer.append($("<span>").text(item.title));
        const inputContainer = $("<div/>")
            .attr({
                class: "input-container",
                "data-id": item.id,
            })
            .appendTo(header);
        const showCheckbox = $("<input/>")
            .attr({
                type: "checkbox",
            })
            .prop("checked", item.show)
            .change(function () {
                if (parentID && $(this).is(":checked")) {
                    $(`.input-container[data-id='${parentID}']`).children("input[type='checkbox']").prop("checked", true)
                }
            });

        const positionInput = $("<input/>").attr({
            type: "number",
        });
        item.position !== undefined && positionInput.val(item.position)
        showCheckbox.appendTo(inputContainer);
        positionInput.appendTo(inputContainer);
        div.appendTo(container);
    });
};

renderTree({data, container});

const getFomrValue = () => {
    let value = [];
    const inputContainers = $(".input-container");
    let flag = true;
    $.each(inputContainers, function () {
        if (!flag) return;
        const id = $(this).attr("data-id");
        const show = $(this).children("input[type='checkbox']").is(":checked");
        const position = $(this).children("input[type='number']").val();
        const positionInt = parseInt(position);
        if (show && (isNaN(positionInt) || !positionInt || positionInt <= 0)) flag = false;
        value.push({id, show, position});
    });
    if (flag) return value;
    throw new Error('Please type position > 0')
};

function initGetFormValue() {
    $(this).click(function () {
        try {
            if (this.getAttribute('disabled') === 'disabled') {
                return;
            }

            if (this.getAttribute('data-confirm')) {
                if (!window.confirm(this.getAttribute('data-confirm'))) {
                    return;
                }
            }

            this.setAttribute('disabled', 'disabled');

            const value = getFomrValue();

            $.ajax({
                url: $.base_url + '/filter/tag/settingFilter/hash/' + OSC_HASH,
                data: {
                    action: 'save',
                    data: value
                },
                method: 'POST',
                success: function (response) {
                    if (response.result !== 'OK') {
                        alert(response.message);
                        return;
                    }

                    alert(response.data.message);

                    window.location.reload();
                },
                error: function (data) {
                    alert('Error! Please try again!');
                }
            });
        } catch (e) {
            alert(e)
            this.removeAttribute('disabled');
        }
    })
}
