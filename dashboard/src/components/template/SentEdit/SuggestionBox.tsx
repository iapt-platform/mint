import { useEffect, useState } from "react";
import { Button, Card, Drawer } from "antd";

import SuggestionList from "./SuggestionList";
import SuggestionAdd from "./SuggestionAdd";
import { ISentence } from "../SentEdit";

export interface IAnswerCount {
  id: string;
  count: number;
}
interface IWidget {
  data: ISentence;
  trigger?: JSX.Element;
}
const Widget = ({ trigger, data }: IWidget) => {
  const [open, setOpen] = useState(false);
  const [openNotification, setOpenNotification] = useState(false);

  useEffect(() => {
    if (localStorage.getItem("read_pr_Notification") === "ok") {
      setOpenNotification(false);
    } else {
      setOpenNotification(true);
    }
  }, []);
  const showDrawer = () => {
    setOpen(true);
  };

  const onClose = () => {
    setOpen(false);
  };

  return (
    <>
      <span onClick={showDrawer}>{trigger}</span>
      <Drawer
        title="修改建议"
        width={520}
        onClose={onClose}
        open={open}
        maskClosable={false}
      >
        <div>
          <Card
            title="温馨提示"
            size="small"
            style={{
              width: "100%",
              display: openNotification ? "block" : "none",
            }}
          >
            <p>此处专为提交修改建议译文。如果有问题，请在讨论页面提交。</p>
            <p style={{ textAlign: "center" }}>
              <Button
                onClick={() => {
                  localStorage.setItem("read_pr_Notification", "ok");
                  setOpenNotification(false);
                }}
              >
                知道了
              </Button>
            </p>
          </Card>
          <div>
            <SuggestionAdd data={data} />
          </div>
          <div>
            <SuggestionList {...data} />
          </div>
        </div>
      </Drawer>
    </>
  );
};

export default Widget;
