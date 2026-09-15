import { Popover } from "antd";
import { InfoCircleOutlined } from "@ant-design/icons";
import { Typography } from "antd";

const { Link } = Typography;

interface IWidgetNoteCtl {
  trigger?: string; //界面上显示的文字
  /**
   * note 内容。注释里含有嵌套模版时后端不下发这个字段，
   * 内容以 children（已渲染的嵌套模版）的形式传进来。
   */
  note?: string;
  children?: React.ReactNode;
}
const NoteCtl = ({ trigger, note, children }: IWidgetNoteCtl) => {
  const show = trigger ? trigger : <InfoCircleOutlined />;
  return (
    <>
      <Popover
        content={
          <div style={{ width: 500 }}>{note ? note : children}</div>
        }
        placement="bottom"
      >
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
