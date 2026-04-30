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
            <h3>{{ $sent["text"] }}</h3>
            <div>{{ $sent["sid"] }}</div>
            <div>
            @foreach ($sent["data"] as $wbw)
            <b>{{$wbw["pali"]}}:</b>
            <span class='type' style="font-style: italic;">{{$wbw["type"]}}</span>
            <span class='grammar' style="font-style: italic;">{{$wbw["grammar"]}}</span>
            @if(!empty($wbw["grammar"]) && !empty($wbw["parent"]))
                <span class='of'> of </span>
            @endif
            <span class="parent" >{{$wbw["parent"]}} / </span>

            <span class='meaning'>{{$wbw["mean"]}}</span>
            <span class="factors" style="color:gray;">
                <span>({{$wbw["factors"]}}</span>
                <span>{{$wbw["factormeaning"]}})</span>
            </span>
            @endforeach
            </div>
        @endforeach
    </body>
</html>
