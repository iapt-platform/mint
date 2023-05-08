import { Button, Popover } from "antd";
import { Typography } from "antd";
import { SearchOutlined } from "@ant-design/icons";
import { ProCard } from "@ant-design/pro-components";

import { command } from "../../reducers/command";
import store from "../../store";
import TermModal from "../term/TermModal";
import { ITerm } from "../term/TermEdit";

const { Text, Link } = Typography;

interface IWidgetTermCtl {
  id?: string;
  word?: string;
  meaning?: string;
  meaning2?: string;
  channel?: string;
}
const TermCtl = ({ id, word, meaning, meaning2, channel }: IWidgetTermCtl) => {
  const show = meaning ? meaning : word ? word : "unknown";
  let textShow = <></>;

  if (typeof id === "undefined") {
    console.log("danger");
    textShow = <Text type="danger">{show}</Text>;
  } else {
    textShow = <Link>{show}</Link>;
  }
  const editButton = (
    <Button
      onClick={() => {
        const it: ITerm = {
          word: word,
          channelId: channel,
        };
        store.dispatch(command({ prop: it, type: "term" }));
      }}
    >
      新建
    </Button>
  );
  const userCard = (
    <>
      <ProCard
        title={word}
        style={{ maxWidth: 500, minWidth: 300 }}
        actions={[
          <Button type="link" size="small" icon={<SearchOutlined />}>
            更多
          </Button>,
          <Button type="link" size="small" icon={<SearchOutlined />}>
            详情
          </Button>,
          editButton,
        ]}
      >
        <div>{id ? "" : <TermModal trigger="新建" word={word} />}</div>
      </ProCard>
    </>
  );
  return (
    <>
      <Popover content={userCard} placement="bottom">
        {textShow}
      </Popover>
      {"("}
      <Text italic>{word}</Text>
      {","}
      <Text>{meaning2}</Text>
      {")"}
    </>
  );
};

interface IWidgetTerm {
  props: string;
}
const Widget = ({ props }: IWidgetTerm) => {
  const prop = JSON.parse(atob(props)) as IWidgetTermCtl;
  return (
    <>
      <TermCtl {...prop} />
    </>
  );
};

export default Widget;
