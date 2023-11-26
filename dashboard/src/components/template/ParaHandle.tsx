import { Button, Divider, Dropdown, MenuProps, message } from "antd";
import { useNavigate, useSearchParams } from "react-router-dom";
import { fullUrl } from "../../utils";
import { useIntl } from "react-intl";

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
  const intl = useIntl();

  const items: MenuProps["items"] = [
    {
      key: "solo",
      label: intl.formatMessage({
        id: "labels.curr.paragraph.only",
      }),
    },
    {
      key: "solo-in-tab",
      label: intl.formatMessage({
        id: "labels.curr.paragraph.open",
      }),
    },
    {
      key: "copy-sent",
      label: intl.formatMessage({
        id: "labels.curr.paragraph.copy.tpl",
      }),
    },
    {
      key: "quote-link-tpl",
      label: intl.formatMessage({
        id: "labels.curr.paragraph.quote.link.tpl",
      }),
      children: [
        {
          key: "quote-link-tpl-m",
          label: intl.formatMessage({
            id: "labels.page.number.type.M",
          }),
        },
        {
          key: "quote-link-tpl-p",
          label: intl.formatMessage({
            id: "labels.page.number.type.P",
          }),
        },
        {
          key: "quote-link-tpl-t",
          label: intl.formatMessage({
            id: "labels.page.number.type.T",
          }),
        },
      ],
    },
  ];
  const copyToClipboard = (text: string) => {
    navigator.clipboard.writeText(text).then(() => {
      message.success("链接地址已经拷贝到剪贴板");
    });
  };
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
        copyToClipboard(sentences.map((item) => `{{${item}}}`).join(""));
        break;
      case "quote-link-tpl-m":
        copyToClipboard(`{{ql|type=m|book=${book}|para=${para}}}`);
        break;
      case "quote-link-tpl-p":
        copyToClipboard(`{{ql|type=p|book=${book}|para=${para}}}`);
        break;
      case "quote-link-tpl-t":
        copyToClipboard(`{{ql|type=t|book=${book}|para=${para}}}`);
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
