<!DOCTYPE html>
<html>
    <body>
        <h1>逐词解析数据分析</h1>
        <table>
        @foreach($data as $row)
        <tr>
        <td>{{ $row->d1 }}</td>
        <td>{{ $row->data }}</td>
        <td style='width:500px;'><span style='display:inline-block;width:{{ $row->ct }}px;background-color:green;'>{{ $row->ct }}</span></td>
        </tr>
        @endforeach
        </table>

        <script>
        var app = @json($data);
        </script>
    </body>
</html>