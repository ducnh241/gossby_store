<html>
    <head>
        <script type="text/javascript" src="http://shop.com/resource/script/community/jquery/jquery-1.7.2.js"></script>
        <style>
            * {
                box-sizing: border-box;
                padding: 0;
                margin: 0;
                outline: none;
            }

            body {
                background: #f7f7f7;
            }

            .map {
                line-height: 0;
                margin: 20px;
                border: 1px solid #ddd;
                background: #fff;
            }

            .item {
                display: inline-block;
                width: calc(100% / 10);
                box-shadow: 0 0 0 1px #ddd;
                background: #fff;
            }

            .icon {
                position: relative;
                margin: 20px;
                color: #666;
            }

            .icon:after {                
                content: "";
                display: block;
                padding-bottom: 100%;
            }

            .icon svg {
                position: absolute;
                top: 50%;
                left: 50%;
                width: 100%;
                height: 100%;
                transform: translate(-50%, -50%);
            }

            .name {
                line-height: 1.5;
                padding: 10px 20px;
                background: #f3f3f3;
                font-family: tahoma;
                font-size: 12px;
                text-align: center;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
        </style>
    </head>
    <body>
        <script>
            var map = $('<div />').addClass('map').appendTo(document.body);

            $.ajax({
                type: 'get',
                url: 'http://shop.com/resource/template/core/image/sprites.svg?t=' + (new Date()).getTime(),
                success: function (response) {
                    var symbols = response.documentElement.getElementsByTagName('symbol');

                    for (var i = 0; i < symbols.length; i++) {
                        var symbol = symbols[i];
                        symbol.setAttribute('id', 'osc-' + symbol.getAttribute('id'));
                    }

                    var res_container = document.createElement("div");
                    document.body.insertBefore(res_container, document.body.firstChild);
                    res_container.innerHTML = new XMLSerializer().serializeToString(response.documentElement);

                    for (var i = 0; i < symbols.length; i++) {
                        var symbol = symbols[i];

                        var viewBox = symbol.getAttribute('viewBox');

                        var icon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');

                        icon.setAttribute('viewBox', viewBox);
                        var use = document.createElementNS('http://www.w3.org/2000/svg', 'use');
                        use.setAttributeNS("http://www.w3.org/1999/xlink", "xlink:href", '#' + symbol.getAttribute('id'));
                        icon.appendChild(use);

                        var item = $('<div />').addClass('item').appendTo(map);

                        $('<div />').addClass('icon').append(icon).appendTo(item);
                        $('<div />').addClass('name').html(symbol.getAttribute('id').substring(4)).attr('title', symbol.getAttribute('id').substring(4)).appendTo(item);
                    }
                }
            });
        </script>
    </body>
</html>