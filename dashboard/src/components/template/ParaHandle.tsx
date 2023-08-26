import { Button, Divider, Dropdown, MenuProps, message } from "antd";
import { useNavigate } from "react-router-dom";
import { fullUrl } from "../../utils";

interface IWidgetParaHandleCtl {
  book: number;
  para: number;
  channels?: string[];
  sentences: string[];
}
const ParaHandleCtl = ({
  book,
  para,
  channels,
  sentences,
}: IWidgetParaHandleCtl) => {
  const navigate = useNavigate();
  const items: MenuProps["items"] = [
    {
      key: "solo",
      label: "仅显示此段",
    },
    {
      key: "solo-in-tab",
      label: "在标签页打开此段",
    },
    {
      key: "copy-sent",
      label: "复制句子链接",
    },
  ];
  const onClick: MenuProps["onClick"] = (e) => {
    const channelQuery = channels?.join("_");
    const url = `/article/para/${book}-${para}?mode=read&book=${book}&par=${para}&channel=${channelQuery}`;
    switch (e.key) {
      case "solo":
        navigate(url);
        break;
      case "solo-in-tab":
        window.open(fullUrl(url), "_blank");
        break;
      case "copy-sent":
        navigator.clipboard
          .writeText(sentences.map((item) => `{{${item}}}`).join(""))
          .then(() => {
            message.success("链接地址已经拷贝到剪贴板");
          });
        break;
      default:
        break;
    }
  };
  return (
    <Divider orientation="left">
      <Dropdown
        menu={{ items, onClick }}
        placement="bottomLeft"
        trigger={["click"]}
      >
        <Button type="text">{para}</Button>
      </Dropdown>
    </Divider>
  );
};

interface IWidget {
  props: string;
}
const Widget = ({ props }: IWidget) => {
  const prop = JSON.parse(atob(props)) as IWidgetParaHandleCtl;
  return (
    <>
      <ParaHandleCtl {...prop} />
    </>
  );
};

export default Widget;
