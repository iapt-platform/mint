import { useEffect, useState } from "react";
import { Alert, Button, Space } from "antd";

import SuggestionList from "./SuggestionList";
import SuggestionAdd from "./SuggestionAdd";
import { ISentence } from "../SentEdit";
import Marked from "../../general/Marked";
import { useAppSelector } from "../../../hooks";
import { message } from "../../../reducers/discussion";
import { useIntl } from "react-intl";

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
  const intl = useIntl();

  return (
    <Space direction="vertical" style={{ width: "100%" }}>
      {openNotification ? (
        <Alert
          message="温馨提示"
          type="info"
          showIcon
          description={
            <Marked
              text="此处专为提交修改建议译文。您输入的应该是**译文**
  而不是评论和问题。其他内容，请在讨论页面提交。"
            />
          }
          action={
            <Button
              type="text"
              onClick={() => {
                localStorage.setItem("read_pr_Notification", "ok");
                if (typeof onNotificationChange !== "undefined") {
                  onNotificationChange(false);
                }
              }}
            >
              {intl.formatMessage({
                id: "buttons.got.it",
              })}
            </Button>
          }
          closable
        />
      ) : undefined}

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

const SuggestionBoxWidget = () => {
  const [openNotification, setOpenNotification] = useState(false);
  const [sentData, setSentData] = useState<ISentence>();
  const discussionMessage = useAppSelector(message);
  useEffect(() => {
    if (discussionMessage?.type === "pr") {
      setSentData(discussionMessage.sent);
    }
  }, [discussionMessage]);

  useEffect(() => {
    if (localStorage.getItem("read_pr_Notification") === "ok") {
      setOpenNotification(false);
    } else {
      setOpenNotification(true);
    }
  }, []);

  return sentData ? (
    <Suggestion
      data={sentData}
      enable={true}
      openNotification={openNotification}
      onNotificationChange={(value: boolean) => setOpenNotification(value)}
      onPrChange={(value: number) => {}}
    />
  ) : (
    <></>
  );
};

export default SuggestionBoxWidget;
