import { ProCard } from "@ant-design/pro-components";
import { Button, Popover } from "antd";
import { SearchOutlined, EditOutlined } from "@ant-design/icons";
import { Typography } from "antd";

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
          <Button type="link" size="small" icon={<EditOutlined />}>
            修改
          </Button>,
        ]}
      >
        <div>{id ? "" : <Button>新建</Button>}</div>
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
  console.log(prop);
  return (
    <>
      <TermCtl {...prop} />
    </>
  );
};

export default Widget;
