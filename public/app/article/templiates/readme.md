# 标题

正文

{{1-2-3-4}}

<h1>标题</h1>
<p></p>
<p>{{1-2-3-4}}</p>

<h1>标题</h1>
<p></p>
<p>
<div class='sent'>
    <div class='org'>
        {{1-2-3-4.org}}
    </div>
    <div class='translation'>
        {{#1-2-3-4.translation}}
            <div>{{text}}</div>
        {{/1-2-3-4.translation}}
    </div>
</div>
</p>

{
    1-2-3-4:{
        org:"pali text"
        translation:[
            {
                text:"一句译文[[bhikkhu]]",
                channel:"channel1"
            },
            {
                text:"另一句译文[[bhikkhu]]",
                channel:"channel2"
            },
        ]
    }
}

<h1>标题</h1>
<p></p>
<p>
<div class='sent'>
    <div class='org'>
        pali text
    </div>
    <div class='translation'>
        <div>一句译文{{term.bhikkhu.channel1}}</div>
        <div>一句译文{{term.bhikkhu.channel2}}</div>
    </div>
</div>
</p>

