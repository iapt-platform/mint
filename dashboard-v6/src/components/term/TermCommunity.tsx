import {
  Badge,
  Card,
  Dropdown,
  type MenuProps,
  Popover,
  Space,
  Typography,
} from "antd";
import { DownOutlined } from "@ant-design/icons";
import { useState, useEffect } from "react";
import { useIntl } from "react-intl";
import { lookupTerms } from "../../api/Term";
import { Link } from "react-router";
import type { IUser } from "../../api/Auth";

const { Text } = Typography;

interface IItem<R> {
  value: R;
  score: number;
}
interface IWord {
  meaning: IItem<string>[];
  summary: IItem<string>[];
  editor: IItem<IUser>[];
}

interface IWidget {
  word: string | undefined;
}
const TermCommunityWidget = ({ word }: IWidget) => {
  const intl = useIntl();
  const [show, setShow] = useState(false);
  const [wordData, setWordData] = useState<IWord>();

  useEffect(() => {
    if (typeof word === "undefined") {
      return;
    }
    // v3 没有 v2 的 exp（编辑者经验值）：每条按同样的分数计
    lookupTerms({ word, per_page: 200 })
      .then((json) => {
        const meaning = new Map<string, number>();
        const summary = new Map<string, number>();
        const editorId = new Map<string, number>();
        const editor = new Map<string, IUser>();
        for (const it of json.data ?? []) {
          let score: number | undefined;
          const currScore = 100;
          if (it.meaning) {
            score = meaning.get(it.meaning);
            meaning.set(it.meaning, score ? score + currScore : currScore);
          }

          // summary_text：有 summary 用它，没有取正文第一段（后端算好的纯文本）
          if (it.summary_text) {
            score = summary.get(it.summary_text);
            const summaryScore = it.summary_text.length;
            summary.set(
              it.summary_text,
              score ? score + summaryScore : summaryScore,
            );
          }

          if (it.editor?.id) {
            score = editorId.get(it.editor.id);
            editorId.set(it.editor.id, score ? score + currScore : currScore);
            editor.set(it.editor.id, it.editor as IUser);
          }
        }
        const _data: IWord = {
          meaning: [],
          summary: [],
          editor: [],
        };
        meaning.forEach((value, key) => {
          if (key && key.length > 0) {
            _data.meaning.push({ value: key, score: value });
          }
        });
        _data.meaning.sort((a, b) => b.score - a.score);
        summary.forEach((value, key) => {
          if (key && key.length > 0) {
            _data.summary.push({ value: key, score: value });
          }
        });
        _data.summary.sort((a, b) => b.score - a.score);

        editorId.forEach((value, key) => {
          const currEditor = editor.get(key);
          if (currEditor) {
            _data.editor.push({ value: currEditor, score: value });
          }
        });
        _data.editor.sort((a, b) => b.score - a.score);
        setWordData(_data);
        if (_data.editor.length > 0) {
          setShow(true);
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
    (value, index: number) => !isShow(value.score, index),
  );
  const meaningExtra = meaningLow?.map((item, id) => {
    return <span key={id}>{item.value}</span>;
  });

  const mainCollaboratorNum = 3; //默认显示的协作者数量，其余的在更多中显示
  const collaboratorRender = (name: string, id: number, score: number) => {
    return (
      <Space key={id}>
        {name}
        <Badge
          style={{ display: "none" }}
          color="geekblue"
          size="small"
          count={score}
        />
      </Space>
    );
  };
  const items: MenuProps["items"] = wordData?.editor
    .filter((_value, index) => index >= mainCollaboratorNum)
    .map((item, id) => {
      return {
        key: id,
        label: collaboratorRender(item.value.nickName, id, item.score),
      };
    });
  const more = wordData ? (
    wordData.editor.length > mainCollaboratorNum ? (
      <Dropdown menu={{ items }}>
        <Typography.Link>
          <Space>
            {intl.formatMessage({
              id: `buttons.more`,
            })}
            <DownOutlined />
          </Space>
        </Typography.Link>
      </Dropdown>
    ) : undefined
  ) : undefined;

  return show ? (
    <Card
      title={"社区术语"}
      extra={<Link to={`/term/list/${word}`}>详情</Link>}
    >
      <div key="meaning">
        <Space style={{ flexWrap: "wrap" }}>
          <Text strong>{"意思："}</Text>
          {wordData?.meaning
            .filter((value, index: number) => isShow(value.score, index))
            .map((item, id) => {
              return (
                <Space key={id}>
                  {item.value}
                  <Badge
                    style={{ display: "none" }}
                    color="geekblue"
                    size="small"
                    count={item.score}
                  />
                </Space>
              );
            })}
          {meaningLow && meaningLow.length > 0 ? (
            <Popover content={<Space>{meaningExtra}</Space>} placement="bottom">
              <Typography.Link>
                <Space>
                  {intl.formatMessage({
                    id: `buttons.more`,
                  })}
                  <DownOutlined />
                </Space>
              </Typography.Link>
            </Popover>
          ) : undefined}
        </Space>
      </div>
      <div key="summary">
        <Space style={{ flexWrap: "wrap" }}>
          <Text strong>{"摘要："}</Text>
          {wordData?.summary.map((item, id) => {
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
            .filter((_value, index) => index < mainCollaboratorNum)
            .map((item, id) => {
              return collaboratorRender(item.value.nickName, id, item.score);
            })}
          {more}
        </Space>
      </div>
    </Card>
  ) : (
    <></>
  );
};

export default TermCommunityWidget;
