import { Button, Divider, Dropdown, MenuProps, message } from "antd";
import { useNavigate, useSearchParams } from "react-router-dom";
import { fullUrl } from "../../utils";

interface IWidgetParaHandleCtl {
  book: number;
  para: number;
  mode?: string;
  channels?: string[];
  sentences: string[];
}
const ParaHandleCtl = ({
  book,
  para,
  mode = "read",
  channels,
  sentences,
}: IWidgetParaHandleCtl) => {
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();

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
    /**
     * TODO 临时的解决方案。以后应该从传参获取其他参数，然后reducer 通知更新。
     * 因为如果是Article组件被嵌入其他页面。不能直接更新浏览器，而是应该更新Article组件内部
     */
    let url = `/article/para/${book}-${para}?book=${book}&par=${para}`;
    let param: string[] = [];
    searchParams.forEach((value, key) => {
      if (key !== "book" && key !== "par") {
        param.push(`${key}=${value}`);
      }
    });
    if (param.length > 0) {
      url += "&" + param.join("&");
    }
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
