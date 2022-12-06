import { Popover } from "antd";
import { InfoCircleOutlined } from "@ant-design/icons";
import { Typography } from "antd";

const { Paragraph, Link } = Typography;

interface IWidgetNoteCtl {
  trigger?: string;
  note?: string;
}
const NoteCtl = ({ trigger, note }: IWidgetNoteCtl) => {
  const noteCard = <Paragraph copyable>{note}</Paragraph>;
  const show = trigger ? trigger : <InfoCircleOutlined />;
  return (
    <>
      <Popover content={noteCard} placement="bottom">
        <Link>{show}</Link>
      </Popover>
    </>
  );
};

interface IWidgetTerm {
  props: string;
}
const Widget = ({ props }: IWidgetTerm) => {
  const prop = JSON.parse(atob(props)) as IWidgetNoteCtl;
  return <NoteCtl {...prop} />;
};

export default Widget;
