<!DOCTYPE html>
<html>
    <head>
        <style type="text/css">
            .img {
                max-height: 27.5%;
                /*max-width: 85%;*/
                overflow-x: auto;
                overflow-y: auto;
                border-width: 1px 0 1px 0;
                border-style: dashed;
                border-color: transparent;
                resize: vertical;
                position: relative;
            }

            .img:hover {
                border-color: black;
            }
            .dict {
                width: 45vw;
            }

            .crop_a {
                clip-path: inset(0 0 0 15%);
                margin-left: -15%;
            }
            .crop_b {
                clip-path: inset(0 15% 0 0);
                margin-right: -15%;
            }
            .word {
                width: 10vw;
				display: contents;
            }

            .word-img img{
                width: 35vw;
                margin: -5px;
            }
			.result{
				max-height: 200vw;
			    overflow: scroll;
			}
            /* 默认滚动条样式 */
            .img::-webkit-scrollbar {
                width: 6px;
                height: 6px;
            }

            .img::-webkit-scrollbar-track {
                background: #f1f1f1;
                border-radius: 6px;
            }

            .img:not(:hover)::-webkit-scrollbar-track {
                background: transparent;
            }

            .img::-webkit-scrollbar-thumb {
                background: #888;
                border-radius: 6px;
                transition: all 0.3s ease;
            }

            .img:not(:hover)::-webkit-scrollbar-thumb {
                background: transparent;
                border-radius: 6px;
                transition: all 0.3s ease;
            }

            .img::-webkit-scrollbar-thumb:hover {
                background: #555;
            }
            .result::-webkit-scrollbar {
                width: 6px;
                height: 6px;
            }

            .result::-webkit-scrollbar-track {
                background: #f1f1f1;
                border-radius: 6px;
            }

            .result:not(:hover)::-webkit-scrollbar-track {
                background: transparent;
            }

            .result::-webkit-scrollbar-thumb {
                background: #888;
                border-radius: 6px;
                transition: all 0.3s ease;
            }

            .result:not(:hover)::-webkit-scrollbar-thumb {
                background: transparent;
                border-radius: 6px;
                transition: all 0.3s ease;
            }

            .result::-webkit-scrollbar-thumb:hover {
                background: #555;
            }
            .copied {
                color: #01955e;
                display: none;
                position: fixed;
            }
        </style>
        <script>
            function copy(text, index) {
                navigator.clipboard.writeText(text).then(() => {
                    show(index);
                    setTimeout(() => {
                        hide(index);
                    }, 2000);
                });
            }
            function show(index) {
                let el = document.getElementById("copied-" + index);
                el.style.display = "inline-block";
            }
            function hide(index) {
                let el = document.getElementById("copied-" + index);
                el.style.display = "none";
            }
        </script>
    </head>
    <body>
        <h3>Page {{ page }}</h3>
        <div style="display: flex">
            <div>
                {{#dict}}
                <div class="img">
                    <img class="dict crop_{{ index }}" src="{{ img }}" />
                </div>
                {{/dict}}
            </div>
            <div>
                <table>
                    {{#words}}
                    <tr>
                        <td class="word">{{ index }}</td>
                        <td class="word">
                            {{ word }}
                            <button onclick="copy('{{ word }}',{{ index }})">复制</button>
                            <span class="copied" id="copied-{{ index }}">已经复制</span>
                        </td>
                        <td class="word-img">
                            <img src="img/{{ word }}.png" />
                        </td>
                    </tr>
                    {{/words}}
                </table>
            </div>
        </div>
    </body>
</html>
