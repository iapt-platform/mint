// Template builder (components/tpl-builder)
const items = {
  "tpl-builder.title": "Template builder",
  "tpl-builder.code": "Code",
  "tpl-builder.preview.empty": "Fill in the parameters to see a preview",
  "tpl-builder.preview.failed": "Preview failed",
  "tpl-builder.select.article": "Choose an article",
  "tpl-builder.select.channel": "Choose a channel",
  "tpl-builder.select.attachment": "Choose from attachments",

  "tpl-builder.tpl.article": "Article / chapter",
  "tpl-builder.tpl.chapter": "Chapter",
  "tpl-builder.tpl.para": "Paragraph",
  "tpl-builder.tpl.sent": "Sentence",
  "tpl-builder.tpl.quote": "Quote",
  "tpl-builder.tpl.ql": "Page reference",
  "tpl-builder.tpl.term": "Term",
  "tpl-builder.tpl.g": "Grammar term",
  "tpl-builder.tpl.note": "Footnote",
  "tpl-builder.tpl.qa": "Q&A",
  "tpl-builder.tpl.v": "Video",
  "tpl-builder.tpl.nissaya": "Word gloss",
  "tpl-builder.tpl.exercise": "Exercise",
  "tpl-builder.tpl.ai": "AI notice",
  "tpl-builder.tpl.category": "Category",
  "tpl-builder.tpl.quality": "Quality",
  "tpl-builder.tpl.ref": "References",
  "tpl-builder.tpl.toc": "Contents",
  "tpl-builder.tpl.dict-pref": "Dictionary prefixes",
  "tpl-builder.tpl.mermaid": "Diagram",
  "tpl-builder.tpl.Tpl": "Custom template",

  "tpl-builder.desc.article":
    "Embed an article, an anthology or a chapter / paragraph. A chapter can be given by book + paragraph instead of an id.",
  "tpl-builder.desc.chapter":
    "Embed a chapter; the id is book-paragraph, or give book + paragraph instead.",
  "tpl-builder.desc.para": "Embed a paragraph; the id is book-paragraph.",
  "tpl-builder.desc.sent":
    "Embed a sentence; the id is book-paragraph-start-end. Without a channel, the content's own channel is used.",
  "tpl-builder.desc.quote":
    "Quote a paragraph, showing the title of its chapter.",
  "tpl-builder.desc.ql":
    "Cite a volume and page of a printed edition, either by book + paragraph or by book name, volume and page.",
  "tpl-builder.desc.term":
    "A term, written as the [[word]] shorthand. word@tag looks up by tag; word#text shows the given text instead of the translation.",
  "tpl-builder.desc.g": "Look up a term in the system grammar glossary.",
  "tpl-builder.desc.note":
    "A footnote. The text may span lines and paragraphs and contain templates.",
  "tpl-builder.desc.qa": "Embed a Q&A topic.",
  "tpl-builder.desc.v":
    "A video. Only http(s) and site-relative addresses are allowed.",
  "tpl-builder.desc.nissaya": "Burmese word-by-word gloss.",
  "tpl-builder.desc.ai":
    "Mark AI-generated content: block is a quote block, tag is a small inline tag.",
  "tpl-builder.desc.category": "A category tag.",
  "tpl-builder.desc.quality": "Quality tag of a term or article.",
  "tpl-builder.desc.ref":
    "List the Pali works cited in this text, wherever it is placed.",
  "tpl-builder.desc.toc": "Contents generated from the headings of this text.",
  "tpl-builder.desc.dict-pref": "Dictionary prefix list.",
  "tpl-builder.desc.mermaid":
    "A mermaid diagram, written as a ```mermaid fence.",
  "tpl-builder.desc.Tpl":
    'Use the article titled "Tpl:name" as a template; parameters fill its text by position (1, 2…) or by name.',

  "tpl-builder.field.type": "Type",
  "tpl-builder.field.id": "id",
  "tpl-builder.field.book": "Book",
  "tpl-builder.field.paragraphs": "Paragraph",
  "tpl-builder.field.para": "Paragraph",
  "tpl-builder.field.title": "Title",
  "tpl-builder.field.channel": "Channel",
  "tpl-builder.field.anthology": "Anthology",
  "tpl-builder.field.style": "Display as",
  "tpl-builder.field.bookname": "Book name",
  "tpl-builder.field.volume": "Volume",
  "tpl-builder.field.page": "Page",
  "tpl-builder.field.word": "Word",
  "tpl-builder.field.trigger": "Marker text",
  "tpl-builder.field.cite": "Source",
  "tpl-builder.field.citelink": "Source location",
  "tpl-builder.field.url": "Address",
  "tpl-builder.field.pali": "Pali",
  "tpl-builder.field.meaning": "Meaning",
  "tpl-builder.field.message": "Message",
  "tpl-builder.field.name": "Name",
  "tpl-builder.field.value": "Quality",
  "tpl-builder.field.levels": "Heading levels",
  "tpl-builder.field.size": "Page size",
  "tpl-builder.field.params": "Parameters",
  "tpl-builder.field.sent.text": "Show",
  "tpl-builder.field.note.text": "Text",
  "tpl-builder.field.mermaid.text": "Diagram source",
  "tpl-builder.field.quote.para": "Paragraph",
  "tpl-builder.field.dict-pref.page": "Page",
  "tpl-builder.field.ql.type": "Edition",
};

export default items;
