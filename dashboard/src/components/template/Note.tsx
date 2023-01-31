import { Popover } from "antd";
import { InfoCircleOutlined } from "@ant-design/icons";
import { Typography } from "antd";

const { Paragraph, Link } = Typography;

interface IWidgetNoteCtl {
  trigger?: string;
  note?: string;
  children?: React.ReactNode;
}
const NoteCtl = ({ trigger, note, children }: IWidgetNoteCtl) => {
  const noteCard = children ? children : <Paragraph copyable>{note}</Paragraph>;
  const show = trigger ? trigger : <InfoCircleOutlined />;
  return (
    <>
      <Popover content={children} placement="bottom">
        <Link>{show}</Link>
      </Popover>
    </>
  );
};

interface IWidget {
  props: string;
  children?: React.ReactNode;
}
const Widget = ({ props, children }: IWidget) => {
  const prop = JSON.parse(atob(props)) as IWidgetNoteCtl;
  return <NoteCtl {...prop}>{children}</NoteCtl>;
};

export default Widget;
