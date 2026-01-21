<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>数据格式转换器</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            color: white;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .converter-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 30px;
            backdrop-filter: blur(10px);
        }

        .input-section {
            padding: 30px;
            background: linear-gradient(135deg, #f8f9ff 0%, #e6f2ff 100%);
            border-bottom: 1px solid #e0e6ed;
        }

        .input-section h2 {
            color: #2d3748;
            margin-bottom: 15px;
            font-size: 1.3rem;
        }

        textarea {
            width: 100%;
            height: 250px;
            padding: 20px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 14px;
            line-height: 1.5;
            resize: vertical;
            transition: all 0.3s ease;
            background: white;
            font-family: 'Consolas', 'Monaco', monospace;
        }

        textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .controls {
            padding: 20px 30px;
            background: #f8fafc;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: #e2e8f0;
            color: #4a5568;
        }

        .btn-secondary:hover {
            background: #cbd5e0;
            transform: translateY(-1px);
        }

        .btn-success {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            color: white;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(72, 187, 120, 0.3);
        }

        .output-section {
            padding: 30px;
        }

        .output-section h2 {
            color: #2d3748;
            margin-bottom: 20px;
            font-size: 1.3rem;
        }

        .table-container {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
            font-size: 14px;
            line-height: 1.4;
        }

        tr:hover {
            background: #f7fafc;
        }

        .csv-output {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
        }

        .csv-content {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            font-family: 'Consolas', 'Monaco', monospace;
            font-size: 13px;
            line-height: 1.5;
            max-height: 300px;
            overflow-y: auto;
            white-space: pre-wrap;
        }

        .status-message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .status-success {
            background: #f0fff4;
            color: #38a169;
            border: 1px solid #9ae6b4;
        }

        .status-error {
            background: #fed7d7;
            color: #e53e3e;
            border: 1px solid #feb2b2;
        }

        .sample-data {
            background: #e6fffa;
            border: 1px solid #81e6d9;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
        }

        .sample-data h3 {
            color: #2d3748;
            margin-bottom: 10px;
            font-size: 1rem;
        }

        .sample-data code {
            background: #f7fafc;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Consolas', 'Monaco', monospace;
            font-size: 12px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .controls {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 数据格式转换器</h1>
            <p>将结构化文本数据转换为CSV格式，支持实时预览</p>
        </div>

        <div class="converter-card">
            <div class="input-section">
                <h2>📝 输入数据</h2>
                <textarea id="inputData" placeholder="请粘贴您的数据...

示例格式：
**No**
**Myanmar Pāḷi**
**Nissaya**
**Roman Pāḷi**
**Translation**
**Remark**
1
ဧဝံ မေ သုတန္တိအာဒိ၊
ဧဝံမေသုတံ ဤသို့အစရှိသောစကားသည်။
Evaṃ me sutantiādi,
The word 'evam me sutam, etc.'
2
ကန္ဒရကသုတ္တံ၊
ကန္ဒရကသုတ်တည်း။
kandarakasuttaṃ,
is the Kandaraka sutta"></textarea>
            </div>

            <div class="controls">
                <button class="btn btn-primary" onclick="convertData()">🔄 转换数据</button>
                <button class="btn btn-secondary" onclick="clearData()">🗑️ 清空数据</button>
                <button class="btn btn-success" onclick="downloadCSV()" id="downloadBtn" disabled>📥 下载CSV</button>
            </div>

            <div class="output-section">
                <h2>📋 转换结果</h2>
                <div id="statusMessage"></div>
                <div id="tablePreview"></div>
                <div id="csvOutput"></div>
            </div>
        </div>

        <div class="sample-data">
            <h3>💡 使用说明</h3>
            <p>1. 请确保输入数据格式正确，表头用 <code>**标题**</code> 格式标记</p>
            <p>2. 数据行按顺序排列，每行一个字段</p>
            <p>3. 支持多语言文本，包括缅甸语、巴利语等</p>
            <p>4. 转换后可预览表格并下载CSV文件</p>
        </div>
    </div>

    <script>
        let csvData = '';
        let tableData = [];

        function showStatus(message, type = 'success') {
            const statusDiv = document.getElementById('statusMessage');
            statusDiv.innerHTML = `<div class="status-message status-${type}">${message}</div>`;
            setTimeout(() => {
                statusDiv.innerHTML = '';
            }, 5000);
        }

        function parseData(text) {
            const lines = text.trim().split('\n').filter(line => line.trim() !== '');
            
            // 提取表头
            const headers = [];
            let i = 0;
            while (i < lines.length) {
                const line = lines[i].trim();
                if (line.startsWith('**') && line.endsWith('**')) {
                    headers.push(line.slice(2, -2));
                    i++;
                } else {
                    break;
                }
            }
            
            if (headers.length === 0) {
                throw new Error('未找到表头，请确保表头格式为 **标题**');
            }
            
            // 解析数据行
            const data = [];
            let currentRow = [];
            
            for (let j = i; j < lines.length; j++) {
                const line = lines[j].trim();
                
                // 检查是否是新行的开始（数字开头）
                if (/^\d+$/.test(line)) {
                    // 如果当前行有数据，先保存
                    if (currentRow.length > 0) {
                        // 补齐缺失的列
                        while (currentRow.length < headers.length) {
                            currentRow.push('');
                        }
                        data.push([...currentRow]);
                    }
                    // 开始新行
                    currentRow = [line];
                } else if (line !== '') {
                    // 添加到当前行
                    currentRow.push(line);
                }
            }
            
            // 添加最后一行
            if (currentRow.length > 0) {
                // 补齐缺失的列
                while (currentRow.length < headers.length) {
                    currentRow.push('');
                }
                data.push(currentRow);
            }
            
            return { headers, data };
        }

        function generateCSV(headers, data) {
            const csvRows = [];
            
            // 添加表头
            csvRows.push(headers.map(header => `"${header.replace(/"/g, '""')}"`).join(','));
            
            // 添加数据行
            data.forEach(row => {
                const csvRow = row.map(cell => `"${cell.replace(/"/g, '""')}"`).join(',');
                csvRows.push(csvRow);
            });
            
            return csvRows.join('\n');
        }

        function createTable(headers, data) {
            if (data.length === 0) {
                return '<p>没有数据可显示</p>';
            }
            
            let html = '<div class="table-container"><table>';
            
            // 表头
            html += '<thead><tr>';
            headers.forEach(header => {
                html += `<th>${header}</th>`;
            });
            html += '</tr></thead>';
            
            // 数据行
            html += '<tbody>';
            data.forEach(row => {
                html += '<tr>';
                row.forEach(cell => {
                    html += `<td>${cell}</td>`;
                });
                html += '</tr>';
            });
            html += '</tbody>';
            
            html += '</table></div>';
            return html;
        }

        function convertData() {
            const inputText = document.getElementById('inputData').value.trim();
            
            if (!inputText) {
                showStatus('请输入数据', 'error');
                return;
            }
            
            try {
                const { headers, data } = parseData(inputText);
                
                console.log('解析结果:', { headers, data }); // 调试信息
                
                if (data.length === 0) {
                    showStatus('未找到有效数据行', 'error');
                    return;
                }
                
                // 生成CSV
                csvData = generateCSV(headers, data);
                
                // 生成表格预览
                const tableHTML = createTable(headers, data);
                document.getElementById('tablePreview').innerHTML = tableHTML;
                
                // 显示CSV内容
                const csvHTML = `
                    <div class="csv-output">
                        <h3>📄 CSV格式预览</h3>
                        <div class="csv-content">${csvData}</div>
                    </div>
                `;
                document.getElementById('csvOutput').innerHTML = csvHTML;
                
                // 启用下载按钮
                document.getElementById('downloadBtn').disabled = false;
                
                showStatus(`转换成功！共转换 ${data.length} 行数据，${headers.length} 个字段`, 'success');
                
            } catch (error) {
                showStatus(`转换失败：${error.message}`, 'error');
                console.error('转换错误:', error);
            }
        }

        function clearData() {
            document.getElementById('inputData').value = '';
            document.getElementById('tablePreview').innerHTML = '';
            document.getElementById('csvOutput').innerHTML = '';
            document.getElementById('downloadBtn').disabled = true;
            csvData = '';
            showStatus('数据已清空', 'success');
        }

        function downloadCSV() {
            if (!csvData) {
                showStatus('没有可下载的数据', 'error');
                return;
            }
            
            // 添加BOM以支持Excel中的UTF-8显示
            const BOM = '\uFEFF';
            const blob = new Blob([BOM + csvData], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            
            if (link.download !== undefined) {
                const url = URL.createObjectURL(blob);
                link.setAttribute('href', url);
                link.setAttribute('download', `converted_data_${new Date().toISOString().slice(0, 10)}.csv`);
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                showStatus('CSV文件下载完成', 'success');
            }
        }

        // 自动加载示例数据
        document.addEventListener('DOMContentLoaded', function() {
            const sampleData = `**No**
**Myanmar Pāḷi**
**Nissaya**
**Roman Pāḷi**
**Translation**
**Remark**
1
ဧဝံ မေ သုတန္တိအာဒိ၊
ဧဝံမေသုတံ ဤသို့အစရှိသောစကားသည်။
Evaṃ me sutantiādi,
The word 'evam me sutam, etc.'

2
ကန္ဒရကသုတ္တံ၊
ကန္ဒရကသုတ်တည်း။
kandarakasuttaṃ,
is the Kandaraka sutta
`;
            
            document.getElementById('inputData').value = sampleData;
        });
    </script>
</body>
</html>