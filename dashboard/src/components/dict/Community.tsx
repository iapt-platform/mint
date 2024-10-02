import {
  Badge,
  Card,
  Dropdown,
  MenuProps,
  Popover,
  Space,
  Typography,
} from "antd";
import { DownOutlined } from "@ant-design/icons";
import { useState, useEffect } from "react";
import { useIntl } from "react-intl";
import { get } from "../../request";
import { IApiResponseDictList } from "../api/Dict";
import { IUser } from "../auth/User";
import GrammarPop from "./GrammarPop";
import Marked from "../general/Marked";
import MdView from "../template/MdView";

const { Title, Link, Text } = Typography;

interface IItem<R> {
  value: R;
  score: number;
}
interface IWord {
  grammar: IItem<string>[];
  parent: IItem<string>[];
  note: IItem<string>[];
  meaning: IItem<string>[];
  factors: IItem<string>[];
  editor: IItem<IUser>[];
}

interface IWidget {
  word: string | undefined;
}
const CommunityWidget = ({ word }: IWidget) => {
  const intl = useIntl();
  const [loaded, setLoaded] = useState(false);
  const [wordData, setWordData] = useState<IWord>();
  const minScore = 100; //分数阈值。低于这个分数只显示在弹出菜单中

  useEffect(() => {
    if (typeof word === "undefined") {
      return;
    }
    const url = `/v2/userdict?view=community&word=${word}`;
    console.info("dict community url", url);
    get<IApiResponseDictList>(url)
      .then((json) => {
        if (json.ok === false) {
          console.log("dict community", json.message);
          return;
        }
        console.debug("dict community", json.data);
        let meaning = new Map<string, number>();
        let grammar = new Map<string, number>();
        let parent = new Map<string, number>();
        let note = new Map<string, number>();
        let editorId = new Map<string, number>();
        let editor = new Map<string, IUser>();
        for (const it of json.data.rows) {
          let score: number | undefined;
          if (it.exp) {
            //分数计算
            let conf = it.confidence / 100;
            if (it.confidence <= 1) {
              conf = 1;
            }
            const currScore = Math.floor((it.exp / 3600) * conf);
            if (it.mean) {
              score = meaning.get(it.mean);
              meaning.set(it.mean, score ? score + currScore : currScore);
            }

            if (it.type || it.grammar) {
              const strCase = it.type + "$" + it.grammar;
              score = grammar.get(strCase);
              grammar.set(strCase, score ? score + currScore : currScore);
            }
            if (it.parent) {
              score = parent.get(it.parent);
              parent.set(it.parent, score ? score + currScore : currScore);
            }

            if (it.note) {
              score = note.get(it.note);
              note.set(it.note, score ? score + currScore : currScore);
            }

            if (it.editor) {
              score = editorId.get(it.editor.id);
              editorId.set(it.editor.id, score ? score + currScore : currScore);
              editor.set(it.editor.id, it.editor);
            }
          }
        }
        let _data: IWord = {
          grammar: [],
          parent: [],
          note: [],
          meaning: [],
          factors: [],
          editor: [],
        };
        meaning.forEach((value, key, map) => {
          if (key && key.length > 0) {
            _data.meaning.push({ value: key, score: value });
          }
        });
        _data.meaning.sort((a, b) => b.score - a.score);
        grammar.forEach((value, key, map) => {
          if (key && key.length > 0) {
            _data.grammar.push({ value: key, score: value });
          }
        });
        _data.grammar.sort((a, b) => b.score - a.score);

        parent.forEach((value, key, map) => {
          if (key && key.length > 0) {
            _data.parent.push({ value: key, score: value });
          }
        });
        _data.parent.sort((a, b) => b.score - a.score);

        note.forEach((value, key, map) => {
          if (key && key.length > 0) {
            _data.note.push({ value: key, score: value });
          }
        });
        _data.note.sort((a, b) => b.score - a.score);

        editorId.forEach((value, key, map) => {
          const currEditor = editor.get(key);
          if (currEditor) {
            _data.editor.push({ value: currEditor, score: value });
          }
        });
        _data.editor.sort((a, b) => b.score - a.score);
        setWordData(_data);
        if (_data.editor.length > 0) {
          setLoaded(true);
        }
      })
      .catch((error) => {
        console.error(error);
      });
  }, [word, setWordData]);

  const isShow = (score: number, index: number) => {
    const Ms = 500,
      Rd = 5,
      minScore = 15;
    const minOrder = Math.log(score) / Math.log(Math.pow(Ms, 1 / Rd));
    if (index < minOrder && score > minScore) {
      return true;
    } else {
      return false;
    }
  };

  const meaningLow = wordData?.meaning.filter(
    (value, index: number) => !isShow(value.score, index)
  );
  const meaningExtra = meaningLow?.map((item, id) => {
    return <span key={id}>{item.value}</span>;
  });

  const mainCollaboratorNum = 3; //默认显示的协作者数量，其余的在更多中显示
  const collaboratorRender = (name: string, id: number, score: number) => {
    return (
      <Space key={id}>
        {name}
        <Badge color="geekblue" size="small" count={score} />
      </Space>
    );
  };
  const items: MenuProps["items"] = wordData?.editor
    .filter((value, index) => index >= mainCollaboratorNum)
    .map((item, id) => {
      return {
        key: id,
        label: collaboratorRender(item.value.nickName, id, item.score),
      };
    });
  const more = wordData ? (
    wordData.editor.length > mainCollaboratorNum ? (
      <Dropdown menu={{ items }}>
        <Link>
          <Space>
            {intl.formatMessage({
              id: `buttons.more`,
            })}
            <DownOutlined />
          </Space>
        </Link>
      </Dropdown>
    ) : undefined
  ) : undefined;

  return loaded ? (
    <Card>
      <Title level={5} id={`community`}>
        {"社区字典"}
      </Title>
      <div key="meaning">
        <Space style={{ flexWrap: "wrap" }}>
          <Text strong>{"意思："}</Text>
          {wordData?.meaning
            .filter((value, index: number) => isShow(value.score, index))
            .map((item, id) => {
              return (
                <Space key={id}>
                  {item.value}
                  <Badge color="geekblue" size="small" count={item.score} />
                </Space>
              );
            })}
          {meaningLow && meaningLow.length > 0 ? (
            <Popover content={<Space>{meaningExtra}</Space>} placement="bottom">
              <Link>
                <Space>
                  {intl.formatMessage({
                    id: `buttons.more`,
                  })}
                  <DownOutlined />
                </Space>
              </Link>
            </Popover>
          ) : undefined}
        </Space>
      </div>
      <div key="grammar">
        <Space style={{ flexWrap: "wrap" }}>
          <Text strong>{"语法："}</Text>
          {wordData?.grammar
            .filter((value) => value.score >= minScore)
            .map((item, id) => {
              const grammar = item.value.split("$");
              const grammarGuide = grammar.map((item, id) => {
                const strCase = item.replaceAll(".", "");

                return strCase.length > 0 ? (
                  <GrammarPop
                    key={id}
                    gid={strCase}
                    text={intl.formatMessage({
                      id: `dict.fields.type.${strCase}.label`,
                      defaultMessage: strCase,
                    })}
                  />
                ) : undefined;
              });
              return (
                <Space key={id}>
                  <Space
                    style={{
                      backgroundColor: "rgba(0.5,0.5,0.5,0.2)",
                      borderRadius: 5,
                      paddingLeft: 5,
                      paddingRight: 5,
                    }}
                  >
                    {grammarGuide}
                  </Space>
                  <Badge color="geekblue" size="small" count={item.score} />
                </Space>
              );
            })}
        </Space>
      </div>
      <div key="base">
        <Space style={{ flexWrap: "wrap" }}>
          <Text strong>{"词干："}</Text>
          {wordData?.parent
            .filter((value) => value.score >= minScore)
            .map((item, id) => {
              return (
                <Space key={id}>
                  {item.value}
                  <Badge color="geekblue" size="small" count={item.score} />
                </Space>
              );
            })}
        </Space>
      </div>
      <div key="collaborator">
        <Space style={{ flexWrap: "wrap" }}>
          <Text strong>{"贡献者："}</Text>
          {wordData?.editor
            .filter((value, index) => index < mainCollaboratorNum)
            .map((item, id) => {
              return collaboratorRender(item.value.nickName, id, item.score);
            })}
          {more}
        </Space>
      </div>

      <div key="note">
        <Text strong>{"注释："}</Text>
        <div>
          {wordData?.note
            .filter((value) => value.score >= minScore)
            .slice(0, 1)
            .map((item, id) => {
              return <MdView html={item.value} key={id} />;
            })}
        </div>
      </div>
    </Card>
  ) : (
    <></>
  );
};

export default CommunityWidget;
