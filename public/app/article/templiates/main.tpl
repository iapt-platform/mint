<div class="article">
    {{#article}}
        <div class="title heading{{article.level}}">{{article.title}}</div>
        <div class="subtitle">{{article.subtitle}}</div>
        <div class="editor">
        {{article.editor.name}} at {{article.updated_at}}
        </div>
        <content>{{article.content}}</content>
    {{/article}}
</div>

<h2>Glossary</h2>
<glossary></glossary>

<h2>reference</h2>
<reference></reference>

<h2>footnote</h2>
<footnote></footnote>
