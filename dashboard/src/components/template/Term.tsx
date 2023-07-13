import { Button, Popover, Skeleton } from "antd";
import { Typography } from "antd";
import { SearchOutlined, EditOutlined } from "@ant-design/icons";
import { ProCard } from "@ant-design/pro-components";

import store from "../../store";
import TermModal from "../term/TermModal";
import { ITerm } from "../term/TermEdit";
import { useEffect, useState } from "react";
import { ITermDataResponse } from "../api/Term";
import { changedTerm, refresh } from "../../reducers/term-change";
import { useAppSelector } from "../../hooks";
import { get } from "../../request";

const { Text, Link } = Typography;

interface ITermSummary {
  ok: boolean;
  message: string;
  data: string;
}

interface IWidgetTermCtl {
  id?: string;
  word?: string;
  meaning?: string;
  meaning2?: string;
  channel?: string;
  parentChannelId?: string;
  parentStudioId?: string;
  summary?: string;
  isCommunity?: string;
}
const TermCtl = ({
  id,
  word,
  meaning,
  meaning2,
  channel,
  parentChannelId,
  parentStudioId,
  summary,
  isCommunity,
}: IWidgetTermCtl) => {
  const [openPopover, setOpenPopover] = useState(false);
  const [termData, setTermData] = useState<ITerm>();
  const [content, setContent] = useState<string>();

  const newTerm: ITermDataResponse | undefined = useAppSelector(changedTerm);

  useEffect(() => {
    setTermData({
      id: id,
      word: word,
      meaning: meaning,
      meaning2: meaning2?.split(","),
      summary: summary,
      channelId: channel,
    });
  }, []);

  useEffect(() => {
    if (newTerm?.word === word) {
      setTermData({
        id: newTerm?.guid,
        word: newTerm?.word,
        meaning: newTerm?.meaning,
        meaning2: newTerm?.other_meaning?.split(","),
        summary: newTerm?.note,
        channelId: newTerm?.channal,
      });
    }
  }, [newTerm]);

  const onModalClose = () => {
    if (document.getElementsByTagName("body")[0].hasAttribute("style")) {
      document.getElementsByTagName("body")[0].removeAttribute("style");
    }
  };
  if (typeof termData?.id === "string") {
    return (
      <>
        <Popover
          open={openPopover}
          onOpenChange={(visible) => {
            setOpenPopover(visible);
            if (
              visible &&
              typeof content === "undefined" &&
              typeof id !== "undefined"
            ) {
              const value = sessionStorage.getItem(`term/summary/${id}`);
              if (value !== null) {
                setContent(value);
                return;
              } else {
                const url = `/v2/term-summary/${id}`;
                console.log("url", url);
                get<ITermSummary>(url).then((json) => {
                  if (json.ok) {
                    setContent(json.data !== "" ? json.data : " ");
                    sessionStorage.setItem(`term/summary/${id}`, json.data);
                  }
                });
              }
            }
          }}
          content={
            <ProCard
              title={word}
              style={{ maxWidth: 500, minWidth: 300 }}
              actions={[
                <Button type="link" size="small" icon={<SearchOutlined />}>
                  更多
                </Button>,
                <Button type="link" size="small" icon={<SearchOutlined />}>
                  详情
                </Button>,
                <TermModal
                  onUpdate={(value: ITermDataResponse) => {
                    onModalClose();
                    store.dispatch(refresh(value));
                  }}
                  onClose={() => {
                    onModalClose();
                  }}
                  trigger={
                    <Button
                      onClick={() => {
                        setOpenPopover(false);
                      }}
                      type="link"
                      size="small"
                      icon={<EditOutlined />}
                    >
                      修改
                    </Button>
                  }
                  id={termData.id}
                  word={termData.word}
                  channelId={termData.channelId}
                  parentChannelId={parentChannelId}
                  parentStudioId={parentStudioId}
                />,
              ]}
            >
              <div>
                {content ? (
                  content
                ) : (
                  <Skeleton
                    title={{ width: 200 }}
                    paragraph={{ rows: 4 }}
                    active
                  />
                )}
              </div>
            </ProCard>
          }
          placement="bottom"
        >
          <Link style={{ color: isCommunity ? "green" : undefined }}>
            {termData?.meaning
              ? termData?.meaning
              : termData?.word
              ? termData?.word
              : "unknown"}
          </Link>
        </Popover>
        {"("}
        <Text italic>{word}</Text>
        {termData?.meaning2 ? (
          <Text>{`,${termData?.meaning2}`}</Text>
        ) : undefined}
        {")"}
      </>
    );
  } else {
    //术语未创建
    return (
      <TermModal
        onUpdate={(value: ITermDataResponse) => {
          onModalClose();
          store.dispatch(refresh(value));
        }}
        onClose={() => {
          onModalClose();
        }}
        trigger={
          <Link>
            <Text type="danger">{termData?.word}</Text>
          </Link>
        }
        word={word}
        channelId={channel}
      />
    );
  }
};

interface IWidgetTerm {
  props: string;
}
const Widget = ({ props }: IWidgetTerm) => {
  const prop = JSON.parse(atob(props)) as IWidgetTermCtl;
  return (
    <>
      <TermCtl {...prop} />
    </>
  );
};

export default Widget;
