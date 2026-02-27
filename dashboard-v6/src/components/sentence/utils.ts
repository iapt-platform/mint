import type { ISentence, ISentenceData } from "../../api/sentence";

export const toISentence = (
  item: ISentenceData,
  channelsId?: string[]
): ISentence => {
  return {
    id: item.id,
    content: item.content,
    contentType: item.content_type,
    html: item.html,
    book: item.book,
    para: item.paragraph,
    wordStart: item.word_start,
    wordEnd: item.word_end,
    editor: item.editor,
    studio: item.studio,
    channel: item.channel,
    updateAt: item.updated_at,
    acceptor: item.acceptor,
    prEditAt: item.pr_edit_at,
    forkAt: item.fork_at,
    suggestionCount: item.suggestionCount,
    translationChannels: channelsId,
  };
};
