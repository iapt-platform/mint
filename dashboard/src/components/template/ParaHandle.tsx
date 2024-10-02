import { Button, Dropdown, MenuProps, message, notification } from "antd";
import { useNavigate, useSearchParams } from "react-router-dom";
import { fullUrl } from "../../utils";
import { useIntl } from "react-intl";
import { addToCart } from "./SentEdit/SentCart";
import { scrollToTop } from "../../pages/library/article/show";
import store from "../../store";
import { modeChange } from "../../reducers/article-mode";

interface IWidgetParaHandleCtl {
  book: number;
  para: number;
  mode?: string;
  channels?: string[];
  sentences: string[];
  onTranslate?: Function;
}
export const ParaHandleCtl = ({
  book,
  para,
  mode = "read",
  channels,
  sentences,
  onTranslate,
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
      key: "ai-translate",
      label: intl.formatMessage({
        id: "buttons.ai.translate",
      }),
    },
    {
      type: "divider",
    },
    {
      key: "mode",
      label: intl.formatMessage({
        id: "buttons.set.display.mode",
      }),
      children: [
        {
          key: "mode-translate",
          label: intl.formatMessage({
            id: "buttons.translate",
          }),
        },
        {
          key: "mode-wbw",
          label: intl.formatMessage({
            id: "buttons.wbw",
          }),
        },
      ],
    },
    {
      type: "divider",
    },
    {
      key: "copy-sent",
      label: intl.formatMessage({
        id: "labels.curr.paragraph.copy.tpl",
      }),
    },
    {
      key: "cart-sent",
      label: intl.formatMessage({
        id: "labels.curr.paragraph.cart.tpl",
      }),
    },
    {
      key: "quote-link-tpl",
      label: intl.formatMessage({
        id: "labels.curr.paragraph.copy.quote.link.tpl",
      }),
      children: [
        {
          key: "quote-link-tpl-c",
          label: intl.formatMessage({
            id: "labels.page.number.type.c",
          }),
        },
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
        scrollToTop();
        break;
      case "solo-in-tab":
        window.open(fullUrl(url), "_blank");
        break;
      case "mode-translate":
        store.dispatch(modeChange({ mode: "edit", id: `${book}-${para}` }));
        break;
      case "ai-translate":
        if (typeof onTranslate !== "undefined") {
          onTranslate();
        }
        break;
      case "mode-wbw":
        store.dispatch(modeChange({ mode: "wbw", id: `${book}-${para}` }));
        break;
      case "copy-sent":
        copyToClipboard(sentences.map((item) => `{{${item}}}`).join(""));
        break;
      case "cart-sent":
        const cartData = sentences.map((item) => {
          return { id: `{{${item}}}`, text: `{{${item}}}` };
        });
        addToCart(cartData);
        notification.success({
          message: cartData.length + "个句子已经添加到Cart",
        });
        break;
      case "quote-link-tpl-c":
        copyToClipboard(`{{ql|type=c|book=${book}|para=${para}}}`);
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
    <div>
      <Dropdown
        menu={{ items, onClick }}
        placement="bottomLeft"
        trigger={["click"]}
      >
        <Button size="small" type="text">
          {para}
        </Button>
      </Dropdown>
    </div>
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
