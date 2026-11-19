import { useState } from "react";
import { useIntl } from "react-intl";

import ToolModal from "./ToolModal";
import Dictionary from "../dict/Dictionary";
import GrammarBook from "../term/GrammarBook";
import UserDictList from "../dict/UserDictList";
import TagList from "../tag/TagList";
import AttachmentList from "../attachment/AttachmentList";
import { useAppSelector } from "../../hooks";
import { lookupWord } from "../../reducers/command";
import { currentUser } from "../../reducers/current-user";
import { useActiveStudio } from "../../hooks/useActiveStudio";
import { fullUrl } from "../../utils";
import type { ToolKey } from "./tools";
import {
  grammarBookSearch,
  type IGrammarBookState,
} from "../term/grammarBookState";

interface IWidget {
  /** 当前打开的工具；undefined = 都关着 */
  tool?: ToolKey;
  onClose: () => void;
}

/**
 * 「工具」的弹窗们。每个弹窗首次打开才渲染，关闭后不销毁。
 * 「在新标签页打开」：字典、语法手册去根下的 /dictionary、/grammar-book，其余去原来的 /workspace/* 页面。
 */
const ToolsModals = ({ tool, onClose }: IWidget) => {
  const intl = useIntl();
  const user = useAppSelector(currentUser);
  const { studioName } = useActiveStudio();
  const [word, setWord] = useState<string>();
  // 语法手册的检索词与选中术语，带进「在新标签页打开」的地址
  const [grammarState, setGrammarState] = useState<IGrammarBookState>({});

  // 字典结果里的巴利词点击查词走 redux 的 lookup，弹窗开着时跟上它（与 /dictionary 页面一致）
  const dictOpen = tool === "tool:dictionary";
  const lookup = useAppSelector(lookupWord);
  // 渲染期派生（记上一次的值），不放 effect 里 setState
  const [prevLookup, setPrevLookup] = useState(lookup);
  if (lookup !== prevLookup) {
    setPrevLookup(lookup);
    if (dictOpen && typeof lookup === "string") setWord(lookup);
  }

  return (
    <>
      <ToolModal
        open={dictOpen}
        onClose={onClose}
        title={intl.formatMessage({ id: "columns.library.dict.title" })}
        url={word ? `/dictionary/${encodeURIComponent(word)}` : "/dictionary"}
      >
        <Dictionary word={word} onSearch={setWord} />
      </ToolModal>
      <ToolModal
        open={tool === "tool:grammar-book"}
        onClose={onClose}
        title={intl.formatMessage({ id: "columns.library.palihandbook.title" })}
        url={`/grammar-book${grammarBookSearch(grammarState)}`}
      >
        <GrammarBook onStateChange={setGrammarState} />
      </ToolModal>
      <ToolModal
        open={tool === "tool:dict"}
        onClose={onClose}
        title={intl.formatMessage({ id: "columns.studio.userdict.title" })}
        url="/workspace/dict"
      >
        <UserDictList studioName={user?.realName} />
      </ToolModal>
      <ToolModal
        open={tool === "tool:tag"}
        onClose={onClose}
        title={intl.formatMessage({ id: "columns.studio.tag.title" })}
        url="/workspace/tag"
      >
        <TagList
          studioName={studioName}
          onSelect={(tag) =>
            window.open(fullUrl(`/workspace/tag/${tag.id}`), "_blank")
          }
        />
      </ToolModal>
      <ToolModal
        open={tool === "tool:driver"}
        onClose={onClose}
        title={intl.formatMessage({ id: "columns.studio.attachment.title" })}
        url="/workspace/driver"
      >
        <AttachmentList studioName={studioName} />
      </ToolModal>
    </>
  );
};

export default ToolsModals;
