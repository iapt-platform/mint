<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Universal Viewer</title>
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/universalviewer@4.0.0/dist/uv.css" />
    <script
        type="application/javascript"
        src="https://cdn.jsdelivr.net/npm/universalviewer@4.0.0/dist/umd/UV.js"></script>
    <style>
        #uv {
            width: 100%;
            height: 668px;
        }

        /* 自定义按钮样式 */
        .custom-menu-button {
            padding: 5px 10px;
            margin: 0 5px;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="uv" id="uv"></div>
    <div id="custom-menu">
        <button id="get-page-id" onclick="getPageId()">获取当前页面 ID</button>
    </div>
    <script>
        const data = {
            manifest: "https://wellcomelibrary.org/iiif/b18035723/manifest",
            embedded: true // needed for codesandbox frame
        };

        uv = UV.init("uv", data);

        // 监听 Universal Viewer 初始化完成事件
        uv.on('initialized', function() {
            // 创建自定义按钮
            var customButton = document.createElement('button');
            customButton.textContent = '获取当前页面 ID';
            customButton.className = 'custom-menu-button';

            // 为自定义按钮添加点击事件监听器
            customButton.addEventListener('click', function() {
                // 获取当前页面的索引
                var currentCanvas = uv.extension.getState().canvasIndex;
                // 获取当前页面的 ID
                var canvasId = uv.extension.getContent().canvases[currentCanvas].id;
                // 弹出提示框显示当前页面 ID
                alert('当前页面 ID: ' + canvasId);
                console.info('当前页面 ID: ', canvasId)
            });

            // 获取 Universal Viewer 的菜单容器
            var menu = document.querySelector('.options');
            // 将自定义按钮添加到菜单容器中
            menu.appendChild(customButton);
        });

        function getPageId() {
            var canvas = uv.extension.helper.getCurrentCanvas();
            console.log("当前页面 Canvas ID:", canvas.id);
        }
    </script>
</body>

</html>
