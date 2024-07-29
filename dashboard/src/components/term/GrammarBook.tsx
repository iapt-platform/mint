import { Button, Dropdown, Input, List } from "antd";
import { useEffect, useState } from "react";
import {
  ArrowLeftOutlined,
  FieldTimeOutlined,
  MoreOutlined,
  FileAddOutlined,
} from "@ant-design/icons";

import { ITerm, getGrammar } from "../../reducers/term-vocabulary";
import { useAppSelector } from "../../hooks";
import TermSearch from "./TermSearch";
import {
  grammar,
  grammarId,
  grammarWord,
  grammarWordId,
} from "../../reducers/command";
import store from "../../store";
import GrammarRecent, {
  IGrammarRecent,
  popRecent,
  pushRecent,
} from "./GrammarRecent";
import { useIntl } from "react-intl";
import TermModal from "./TermModal";
import { get } from "../../request";
import {
  IApiResponseChannelData,
  IApiResponseChannelList,
} from "../api/Channel";
import { grammarTermFetch } from "../../load";

const { Search } = Input;

interface IGrammarList {
  term: ITerm;
  weight: number;
}
const GrammarBookWidget = () => {
  const intl = useIntl();

  const [result, setResult] = useState<IGrammarList[]>();
  const [termId, setTermId] = useState<string>();
  const [termSearch, setTermSearch] = useState<string>();
  const [showRecent, setShowRecent] = useState(false);
  const [create, setCreate] = useState(false);
  const [grammarChannel, setGrammarChannel] =
    useState<IApiResponseChannelData>();
  const sysGrammar = useAppSelector(getGrammar);
  const searchWord = useAppSelector(grammarWord);
  const searchWordId = useAppSelector(grammarWordId);

  useEffect(() => {
    const url = `/v2/channel?view=system`;
    get<IApiResponseChannelList>(url).then((json) => {
      if (json.ok) {
        const channel = json.data.rows.find(
          (value) => value.name === "_System_Grammar_Term_zh-hans_"
        );
        setGrammarChannel(channel);
      }
    });
  }, []);

  useEffect(() => {
    console.debug("grammar book", searchWord);
    if (searchWord && searchWord.length > 0) {
      setTermId(undefined);
      setTermSearch(searchWord);
      pushRecent({
        title: searchWord,
        description: searchWord,
        word: searchWord,
      });

      store.dispatch(grammar(""));
    }
  }, [searchWord]);

  useEffect(() => {
    console.debug("grammar book", searchWordId);
    if (searchWordId && searchWordId.length > 0) {
      setTermId(searchWordId);
      setTermSearch(undefined);
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
              setTermId(top.wordId);
              setTermSearch(top.word);
            }
          }}
        />
        <Search
          placeholder="input search text"
          onSearch={(value: string) => {}}
          onChange={(event: React.ChangeEvent<HTMLInputElement>) => {
            console.debug("on change", event.target.value);
            setTermId(undefined);
            setTermSearch(undefined);
            const keyWord = event.target.value;
            if (keyWord.trim().length === 0) {
              setShowRecent(true);
            } else {
              setShowRecent(false);
            }
            /**
             * 权重算法
             * 约靠近头，分数约高
             * 剩余尾巴约短，分数越高
             */
            const search = sysGrammar
              ?.map((item) => {
                let weight = 0;
                const wordBegin = item.word
                  .toLocaleLowerCase()
                  .indexOf(keyWord);
                if (wordBegin >= 0) {
                  weight += (1 / (wordBegin + 1)) * 1000;
                  const wordRemain =
                    item.word.length - keyWord.length - wordBegin;
                  weight += (1 / (wordRemain + 1)) * 100;
                }
                const meaningBegin = (item.meaning + item.other_meaning)
                  .toLocaleLowerCase()
                  .indexOf(keyWord);
                if (meaningBegin >= 0) {
                  weight += (1 / (meaningBegin + 1)) * 1000;
                  const meaningRemain =
                    item.meaning.length - keyWord.length - wordBegin;
                  weight += (1 / (meaningRemain + 1)) * 100;
                }
                return { term: item, weight: weight };
              })
              .filter((value) => value.weight > 0)
              .sort((a, b) => b.weight - a.weight);

            setResult(search);
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
              setTermId(value.wordId);
              setTermSearch(value.word);
              setShowRecent(false);
            }}
          />
        ) : termId || termSearch ? (
          <TermSearch
            wordId={termId}
            word={termSearch}
            onIdChange={(value: string) => {
              setTermId(value);
              setTermSearch(undefined);
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
                    setTermId(item.term.guid);
                    setTermSearch(undefined);
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
        parentChannelId={grammarChannel?.uid}
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
