import { Button, Dropdown, Input, List } from "antd";
import { useEffect, useMemo, useState } from "react";
import {
  ArrowLeftOutlined,
  FieldTimeOutlined,
  MoreOutlined,
  FileAddOutlined,
} from "@ant-design/icons";

import { type ITerm, getGrammar } from "../../reducers/term-vocabulary";
import { useAppSelector } from "../../hooks";
import TermSearch from "./TermSearch";
import {
  grammar,
  grammarId,
  grammarWord,
  grammarWordId,
} from "../../reducers/command";
import store from "../../store";
import GrammarRecent, { type IGrammarRecent } from "./GrammarRecent";
import { useIntl } from "react-intl";

import { fetchPublicChannels, type Channel } from "../../api/channel";
import { grammarTermFetch } from "../../load";
import TermModal from "./TermModal";
import { popRecent, pushRecent } from "./utils";
import type { IGrammarBookState } from "./grammarBookState";

const { Search } = Input;

interface IGrammarList {
  term: ITerm;
  weight: number;
}
interface IWidget {
  /** 初始状态（如从地址栏恢复），只在挂载时读一次 */
  defaultState?: IGrammarBookState;
  /** 状态变化时回报，供外部写进地址栏或拼「在新标签页打开」的链接 */
  onStateChange?: (state: IGrammarBookState) => void;
}

/** 按检索词给系统语法术语打分排序 */
function searchGrammar(
  sysGrammar: ITerm[] | undefined,
  keyWord: string,
): IGrammarList[] | undefined {
  if (keyWord.trim().length === 0) return undefined;
  /**
   * 权重算法
   * 约靠近头，分数约高
   * 剩余尾巴约短，分数越高
   */
  return sysGrammar
    ?.map((item) => {
      let weight = 0;
      const wordBegin = item.word
        .toLocaleLowerCase()
        .indexOf(keyWord.toLocaleLowerCase());
      if (wordBegin >= 0) {
        weight += (1 / (wordBegin + 1)) * 1000;
        const wordRemain = item.word.length - keyWord.length - wordBegin;
        weight += (1 / (wordRemain + 1)) * 100;
      }
      const meaningBegin = (item.meaning + item.other_meaning)
        .toLocaleLowerCase()
        .indexOf(keyWord);
      if (meaningBegin >= 0) {
        weight += (1 / (meaningBegin + 1)) * 1000;
        const meaningRemain = item.meaning.length - keyWord.length - wordBegin;
        weight += (1 / (meaningRemain + 1)) * 100;
      }
      return { term: item, weight: weight };
    })
    .filter((value) => value.weight > 0)
    .sort((a, b) => b.weight - a.weight);
}

const GrammarBookWidget = ({ defaultState, onStateChange }: IWidget) => {
  const intl = useIntl();

  const [keyword, setKeyword] = useState(defaultState?.q ?? "");
  const [localTermId, setLocalTermId] = useState(defaultState?.id);
  const [localTermSearch, setLocalTermSearch] = useState(defaultState?.word);
  const [showRecent, setShowRecent] = useState(false);
  const [create, setCreate] = useState(false);
  const [grammarChannel, setGrammarChannel] = useState<Channel>();
  const sysGrammar = useAppSelector(getGrammar);
  const searchWord = useAppSelector(grammarWord);
  const searchWordId = useAppSelector(grammarWordId);

  const termId = searchWordId || localTermId;
  const termSearch = searchWord || localTermSearch;

  // 术语表是异步加载的：从地址栏带着检索词进来时，要等它到了再算结果，所以派生而不是在 onChange 里算
  const result = useMemo(
    () => searchGrammar(sysGrammar, keyword),
    [sysGrammar, keyword],
  );

  useEffect(() => {
    onStateChange?.({
      q: keyword || undefined,
      id: localTermId || undefined,
      word: localTermSearch || undefined,
    });
  }, [keyword, localTermId, localTermSearch, onStateChange]);

  useEffect(() => {
    const name = "_System_Grammar_Term_zh-hans_";
    fetchPublicChannels({ system: true, search: name, per_page: 20 }).then(
      (res) =>
        setGrammarChannel(res.data?.find((value) => value.name === name)),
    );
  }, []);

  useEffect(() => {
    if (searchWord && searchWord.length > 0) {
      pushRecent({
        title: searchWord,
        description: searchWord,
        word: searchWord,
      });

      store.dispatch(grammar(""));
    }
  }, [searchWord]);

  useEffect(() => {
    if (searchWordId && searchWordId.length > 0) {
      pushRecent({
        title: searchWordId,
        description: searchWordId,
        wordId: searchWordId,
      });

      store.dispatch(grammarId(""));
    }
  }, [searchWordId]);

  return (
    <div>
      <div style={{ display: "flex" }}>
        <Button
          icon={<ArrowLeftOutlined />}
          type="text"
          onClick={() => {
            const top = popRecent();
            if (top) {
              setLocalTermId(top.wordId);
              setLocalTermSearch(top.word);
            }
          }}
        />
        <Search
          placeholder="input search text"
          value={keyword}
          onChange={(event: React.ChangeEvent<HTMLInputElement>) => {
            console.debug("on change", event.target.value);
            setLocalTermId(undefined);
            setLocalTermSearch(undefined);
            const keyWord = event.target.value;
            setKeyword(keyWord);
            setShowRecent(keyWord.trim().length === 0);
          }}
          style={{ width: "100%" }}
        />
        <Dropdown
          trigger={["click"]}
          menu={{
            items: [
              {
                key: "recent",
                label: "最近查询",
                icon: <FieldTimeOutlined />,
              },
              {
                key: "create",
                label: intl.formatMessage({ id: "buttons.create" }),
                icon: <FileAddOutlined />,
                children: [
                  {
                    key: "create_collection",
                    label: "固定搭配",
                  },
                ],
              },
            ],
            onClick: (e) => {
              switch (e.key) {
                case "recent":
                  setShowRecent(true);
                  break;
                case "create_collection":
                  setCreate(true);
                  break;
              }
            },
          }}
        >
          <Button type="text" icon={<MoreOutlined />} />
        </Dropdown>
      </div>
      <div>
        {showRecent ? (
          <GrammarRecent
            onClick={(value: IGrammarRecent) => {
              console.debug("grammar book recent click", value);
              setLocalTermId(value.wordId);
              setLocalTermSearch(value.word);
              setShowRecent(false);
            }}
          />
        ) : termId || termSearch ? (
          <TermSearch
            wordId={termId}
            word={termSearch}
            onIdChange={(value: string) => {
              setLocalTermId(value);
              setLocalTermSearch(undefined);
              pushRecent({ title: value, description: value, wordId: value });
            }}
          />
        ) : (
          <List
            size="small"
            dataSource={result}
            renderItem={(item) => {
              const description =
                item.term.meaning +
                (item.term.other_meaning ? "，" + item.term.other_meaning : "");

              return (
                <List.Item
                  key={item.term.guid}
                  style={{ cursor: "pointer" }}
                  onClick={() => {
                    setLocalTermId(item.term.guid);
                    setLocalTermSearch(undefined);
                    pushRecent({
                      title: item.term.word,
                      description: description,
                      wordId: item.term.guid,
                    });
                  }}
                >
                  <List.Item.Meta
                    title={item.term.word}
                    description={description}
                  />
                </List.Item>
              );
            }}
          />
        )}
      </div>
      <TermModal
        parentChannelId={grammarChannel?.id}
        tags={[":collection:"]}
        open={create}
        onClose={() => setCreate(false)}
        onUpdate={() => {
          //获取语法术语表
          grammarTermFetch();
        }}
      />
    </div>
  );
};

export default GrammarBookWidget;
