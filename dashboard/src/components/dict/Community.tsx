import { Badge, Card, Popover, Space, Typography } from "antd";
import { MoreOutlined } from "@ant-design/icons";
import { useState, useEffect } from "react";
import { useIntl } from "react-intl";
import { get } from "../../request";
import { IApiResponseDictList } from "../api/Dict";
import { IUser } from "../auth/User";
import UserName from "../auth/UserName";
import GrammarPop from "./GrammarPop";

const { Title } = Typography;

interface IItem<R> {
  value: R;
  score: number;
}
interface IWord {
  grammar: IItem<string>[];
  parent: IItem<string>[];
  meaning: IItem<string>[];
  factors: IItem<string>[];
  editor: IItem<IUser>[];
}

interface IWidget {
  word: string | undefined;
}
const CommunityWidget = ({ word }: IWidget) => {
  const intl = useIntl();
  const [wordData, setWordData] = useState<IWord>();
  const minScore = 100; //分数阈值。低于这个分数只显示在弹出菜单中

  useEffect(() => {
    if (typeof word === "undefined") {
      return;
    }
    const url = `/v2/userdict?view=community&word=${word}`;
    get<IApiResponseDictList>(url)
      .then((json) => {
        console.log("community", json.data.rows);
        let meaning = new Map<string, number>();
        let grammar = new Map<string, number>();
        let parent = new Map<string, number>();
        let editorId = new Map<string, number>();
        let editor = new Map<string, IUser>();
        for (const it of json.data.rows) {
          let score: number | undefined;
          if (it.exp) {
            //分数计算
            const currScore = Math.floor(
              (it.exp / 3600) * (it.confidence / 100)
            );

            score = meaning.get(it.mean);
            meaning.set(it.mean, score ? score + currScore : currScore);

            if (it.type || it.grammar) {
              const strCase = it.type + "$" + it.grammar;
              score = grammar.get(strCase);
              grammar.set(strCase, score ? score + currScore : currScore);
            }

            score = parent.get(it.parent);
            parent.set(it.parent, score ? score + currScore : currScore);

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

        editorId.forEach((value, key, map) => {
          const currEditor = editor.get(key);
          if (currEditor) {
            _data.editor.push({ value: currEditor, score: value });
          }
        });
        _data.editor.sort((a, b) => b.score - a.score);
        setWordData(_data);
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
    return <>{item.value}</>;
  });

  return (
    <Card>
      <Title level={5} id={`community`}>
        {"社区字典"}
      </Title>
      <div>
        <Space>
          {"意思："}
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
              <MoreOutlined />
            </Popover>
          ) : undefined}
        </Space>
      </div>
      <div>
        <Space>
          {"语法："}
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
      <div>
        <Space>
          {"词干："}
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
      <div>
        <Space>
          {"贡献者："}
          {wordData?.editor.map((item, id) => {
            return (
              <Space key={id}>
                <UserName {...item.value} />
                <Badge color="geekblue" size="small" count={item.score} />
              </Space>
            );
          })}
        </Space>
      </div>
    </Card>
  );
};

export default CommunityWidget;
