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
import { IUser } from "../auth/User";
import { ITermListResponse } from "../api/Term";
import { Link } from "react-router-dom";

const { Title, Text } = Typography;

interface IItem<R> {
  value: R;
  score: number;
}
interface IWord {
  meaning: IItem<string>[];
  note: IItem<string>[];
  editor: IItem<IUser>[];
}

interface IWidget {
  word: string | undefined;
}
const TermCommunityWidget = ({ word }: IWidget) => {
  const intl = useIntl();
  const [show, setShow] = useState(false);
  const [wordData, setWordData] = useState<IWord>();
  const minScore = 100; //分数阈值。低于这个分数只显示在弹出菜单中

  useEffect(() => {
    if (typeof word === "undefined") {
      return;
    }
    const url = `/v2/terms?view=word&word=${word}&exp=1`;
    console.log("url", url);
    get<ITermListResponse>(url)
      .then((json) => {
        if (json.ok === false) {
          return;
        }
        let meaning = new Map<string, number>();
        let note = new Map<string, number>();
        let editorId = new Map<string, number>();
        let editor = new Map<string, IUser>();
        for (const it of json.data.rows) {
          let score: number | undefined;
          let currScore = 100;
          if (it.exp) {
            //分数计算
            currScore = Math.floor(it.exp / 3600);
          }
          if (it.meaning) {
            score = meaning.get(it.meaning);
            meaning.set(it.meaning, score ? score + currScore : currScore);
          }

          if (it.note) {
            score = note.get(it.note);
            const noteScore = it.note.length;
            note.set(it.note, score ? score + noteScore : noteScore);
          }

          if (it.editor) {
            score = editorId.get(it.editor.id);
            editorId.set(it.editor.id, score ? score + currScore : currScore);
            editor.set(it.editor.id, it.editor);
          }
        }
        let _data: IWord = {
          meaning: [],
          note: [],
          editor: [],
        };
        meaning.forEach((value, key, map) => {
          if (key && key.length > 0) {
            _data.meaning.push({ value: key, score: value });
          }
        });
        _data.meaning.sort((a, b) => b.score - a.score);
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
    <Card>
      <Space>
        <Title level={5} id={`community`}>
          {"社区术语"}
        </Title>
        <Link to={`/term/list/${word}`}>详情</Link>
      </Space>

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
      <div key="note">
        <Space style={{ flexWrap: "wrap" }}>
          <Text strong>{"note:"}</Text>
          {wordData?.note
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
    </Card>
  ) : (
    <></>
  );
};

export default TermCommunityWidget;
