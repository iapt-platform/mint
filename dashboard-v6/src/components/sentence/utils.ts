import type { ISentence, ISentenceData } from "../../api/Corpus";

export const toISentence = (apiData: ISentenceData): ISentence => {
  return {
    id: apiData.id,
    content: apiData.content,
    contentType: apiData.content_type,
    html: apiData.html,
    book: apiData.book,
    para: apiData.paragraph,
    wordStart: apiData.word_start,
    wordEnd: apiData.word_end,
    editor: apiData.editor,
    studio: apiData.studio,
    channel: apiData.channel,
    updateAt: apiData.updated_at,
    acceptor: apiData.acceptor,
    prEditAt: apiData.pr_edit_at,
    forkAt: apiData.fork_at,
    suggestionCount: apiData.suggestionCount,
  };
};
