<html>
    <head>
        <title>export word by word data</title>
    </head>
    <body>
        <div class="container">
        <form action="/api/v2/export_wbw" method="POST">
        channel id:<br>
        <input type="text" name="channel" value="" />
        <br>
        sentence list:<br>
        <textarea type="text" name="sent" value="Mouse"></textarea>
        <br><br>
        <input type="submit" value="Submit">
        </form>
        </div>

        @foreach ($sentences as $sent)
            <h3>{{ $sent["sid"] }}</h3>
            <div>
            @foreach ($sent["data"] as $wbw)
            <b>{{$wbw["pali"]}}</b>
            <span>{{$wbw["type"]}}</span>
            <span>{{$wbw["grammar"]}}</span>
            <span> of </span>
            <span>{{$wbw["parent"]}}</span>
            <span>{{$wbw["mean"]}}</span>
            <span style="color:gray;">
            <span>({{$wbw["factors"]}}</span>
            <span>{{$wbw["factormeaning"]}})</span>
            </span>
            @endforeach
            </div>
        @endforeach
    </body>
</html>
