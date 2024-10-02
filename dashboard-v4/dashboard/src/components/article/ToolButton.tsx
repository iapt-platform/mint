import { Button, Drawer, Tooltip } from "antd";
import { useState } from "react";

interface IWidget {
  icon?: JSX.Element;
  content?: JSX.Element;
  title?: string;
}
const ToolButtonWidget = ({ icon, content, title }: IWidget) => {
  const [open, setOpen] = useState(false);

  return (
    <>
      <Tooltip placement="right" title={title}>
        <Button
          size="middle"
          icon={icon}
          onClick={() => {
            setOpen(true);
          }}
        />
      </Tooltip>
      <Drawer
        title={title}
        width={460}
        placement="left"
        onClose={() => {
          setOpen(false);
        }}
        open={open}
      >
        {content}
      </Drawer>
    </>
  );
};

export default ToolButtonWidget;
