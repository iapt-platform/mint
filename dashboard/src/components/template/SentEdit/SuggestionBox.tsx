import { useEffect, useState } from "react";
import { Button, Card, Drawer, Space } from "antd";

import SuggestionList from "./SuggestionList";
import SuggestionAdd from "./SuggestionAdd";
import { ISentence } from "../SentEdit";
import Marked from "../../general/Marked";

interface ISuggestionWidget {
  data: ISentence;
  openNotification: boolean;
  enable?: boolean;
  onNotificationChange?: Function;
  onPrChange?: Function;
}
const Suggestion = ({
  data,
  enable = true,
  openNotification,
  onNotificationChange,
  onPrChange,
}: ISuggestionWidget) => {
  const [reload, setReload] = useState(false);
  return (
    <Space direction="vertical" style={{ width: "100%" }}>
      <Card
        title="温馨提示"
        size="small"
        style={{
          width: "100%",
          display: openNotification ? "block" : "none",
        }}
      >
        <Marked
          text="此处专为提交修改建议译文。您输入的应该是**译文**
  而不是评论和问题。其他内容，请在讨论页面提交。"
        />
        <p style={{ textAlign: "center" }}>
          <Button
            onClick={() => {
              localStorage.setItem("read_pr_Notification", "ok");
              if (typeof onNotificationChange !== "undefined") {
                onNotificationChange(false);
              }
            }}
          >
            知道了
          </Button>
        </p>
      </Card>
      <SuggestionAdd
        data={data}
        onCreate={() => {
          setReload(true);
        }}
      />
      <SuggestionList
        {...data}
        enable={enable}
        reload={reload}
        onReload={() => {
          setReload(false);
        }}
        onChange={(count: number) => {
          if (typeof onPrChange !== "undefined") {
            onPrChange(count);
          }
        }}
      />
    </Space>
  );
};

export interface IAnswerCount {
  id: string;
  count: number;
}
interface IWidget {
  data: ISentence;
  trigger?: JSX.Element;
  open?: boolean;
  openInDrawer?: boolean;
  onClose?: Function;
}
const SuggestionBoxWidget = ({
  trigger,
  data,
  open = false,
  openInDrawer = false,
  onClose,
}: IWidget) => {
  const [isOpen, setIsOpen] = useState(open);
  const [openNotification, setOpenNotification] = useState(false);
  const [prNumber, setPrNumber] = useState(data.suggestionCount?.suggestion);

  useEffect(() => setIsOpen(open), [open]);
  useEffect(() => {
    if (localStorage.getItem("read_pr_Notification") === "ok") {
      setOpenNotification(false);
    } else {
      setOpenNotification(true);
    }
  }, []);
  const showDrawer = () => {
    setIsOpen(true);
  };

  const onBoxClose = () => {
    setIsOpen(false);
    if (typeof onClose !== "undefined") {
      onClose();
    }
  };

  return (
    <>
      <Space onClick={showDrawer}>
        {trigger}
        {prNumber}
      </Space>

      {openInDrawer ? (
        <Drawer
          title="修改建议"
          width={520}
          onClose={onBoxClose}
          open={isOpen}
          maskClosable={false}
        >
          <Suggestion
            data={data}
            enable={isOpen}
            openNotification={openNotification}
            onNotificationChange={(value: boolean) =>
              setOpenNotification(value)
            }
            onPrChange={(value: number) => setPrNumber(value)}
          />
        </Drawer>
      ) : (
        <div
          style={{
            position: "absolute",
            display: isOpen ? "none" : "none",
            zIndex: 1030,
            marginLeft: 300,
            marginTop: -250,
          }}
        >
          <Suggestion
            data={data}
            enable={isOpen}
            openNotification={openNotification}
            onNotificationChange={(value: boolean) =>
              setOpenNotification(value)
            }
            onPrChange={(value: number) => setPrNumber(value)}
          />
        </div>
      )}
    </>
  );
};

export default SuggestionBoxWidget;
