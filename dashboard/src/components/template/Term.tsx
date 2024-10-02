import { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import { Button, Popover, Skeleton, Space, Tag } from "antd";
import { Typography } from "antd";
import { SearchOutlined, EditOutlined } from "@ant-design/icons";

import store from "../../store";
import TermModal from "../term/TermModal";
import { ITerm } from "../term/TermEdit";
import { ITermDataResponse, ITermResponse } from "../api/Term";
import {
  changedTerm,
  refresh,
  termCache,
  upgrade,
} from "../../reducers/term-change";
import { useAppSelector } from "../../hooks";
import { get } from "../../request";
import { fullUrl } from "../../utils";
import lodash from "lodash";
import { order, push } from "../../reducers/term-order";
import { click } from "../../reducers/term-click";

const { Text, Title } = Typography;

const dataMap = (input?: ITermDataResponse): ITerm => {
  return {
    id: input?.guid,
    word: input?.word,
    meaning: input?.meaning,
    meaning2: input?.other_meaning?.split(","),
    summary: input?.summary ?? "",
    channelId: input?.channal,
    studioId: input?.studio.id,
    summary_is_community: input?.summary_is_community,
  };
};

interface ITermExtra {
  pali?: string;
  meaning2?: string[];
}
const TermExtra = ({ pali, meaning2 }: ITermExtra) => (
  <>
    {" "}
    {"("}
    <Text italic>{pali}</Text>
    {meaning2 ? <Text>{`,${meaning2}`}</Text> : undefined}
    {")"}
  </>
);

export interface IWidgetTermCtl {
  id?: string;
  word?: string;
  meaning?: string;
  meaning2?: string;
  channel?: string /**该术语在term表中的channel_id */;
  parentChannelId?: string /**该术语所在译文的channel_id */;
  parentStudioId?: string /**该术语所在译文的studio_id */;
  summary?: string;
  isCommunity?: boolean;
  compact?: boolean;
}
export const TermCtl = ({
  id,
  word,
  meaning,
  meaning2,
  channel,
  parentChannelId,
  parentStudioId,
  summary,
  isCommunity,
  compact = false,
}: IWidgetTermCtl) => {
  const [openPopover, setOpenPopover] = useState(false);
  const [termData, setTermData] = useState<ITerm>({
    id: id,
    word: word,
    meaning: meaning,
    meaning2: meaning2?.split(","),
    summary: summary,
    channelId: channel,
  });
  const [isInit, setIsInit] = useState(true);
  const [loading, setLoading] = useState(false);
  const [community, setCommunity] = useState(isCommunity);
  const newTerm: ITermDataResponse | undefined = useAppSelector(changedTerm);
  const cache = useAppSelector(termCache);

  const [isFirst, setIsFirst] = useState(true);
  const [uid] = useState<string>(
    lodash.times(20, () => lodash.random(35).toString(36)).join("")
  );
  const termOrder = useAppSelector(order);

  useEffect(() => {
    if (word && parentChannelId) {
      const currTerm = {
        word: word,
        channelId: parentChannelId,
        first: uid,
      };
      store.dispatch(push(currTerm));
    }
  }, [parentChannelId, uid, word]);

  useEffect(() => {
    const index = termOrder?.findIndex(
      (value) =>
        value.word === word &&
        value.channelId === parentChannelId &&
        value.first === uid
    );

    if (index === -1) {
      setIsFirst(false);
    } else {
      setIsFirst(true);
    }
  }, [parentChannelId, termOrder, uid, word]);

  useEffect(() => {
    if (newTerm?.word === word && parentStudioId === newTerm?.studio.id) {
      console.debug("Term studio 匹配", newTerm);
      const newData = dataMap(newTerm);
      if (
        termData.channelId &&
        termData.channelId !== "" &&
        newTerm?.channal === termData.channelId
      ) {
        console.debug("Term channel 匹配");
        setTermData(newData);
        setIsInit(false);
        setCommunity(false);
      } else {
        console.debug("Term channel 不 匹配");
        setTermData(newData);
        setIsInit(false);
        setCommunity(false);
      }
    }
  }, [newTerm, parentStudioId, termData.channelId, word]);

  const onModalClose = () => {
    if (document.getElementsByTagName("body")[0].hasAttribute("style")) {
      document.getElementsByTagName("body")[0].removeAttribute("style");
    }
  };
  const onPopoverOpen = (visible: boolean) => {
    setOpenPopover(visible);
    if (visible && isInit && typeof id !== "undefined") {
      const term = cache?.find((value) => value.guid === id);
      if (term) {
        setTermData(dataMap(term));
        setIsInit(false);
        return;
      } else {
        const url = `/v2/terms/${id}?community_summary=1`;
        console.info("api request", url);
        setLoading(true);
        get<ITermResponse>(url)
          .then((json) => {
            if (json.ok) {
              setTermData(dataMap(json.data));
              setIsInit(false);
              store.dispatch(upgrade(json.data));
            }
          })
          .finally(() => setLoading(false));
      }
    }
  };

  if (typeof termData?.id === "string") {
    return (
      <>
        <span className="term"></span>
        <Popover
          title={
            <Space style={{ justifyContent: "space-between", width: "100%" }}>
              <span>
                <Text strong>{termData.meaning}</Text>{" "}
                {community ? <Tag>{"社区"}</Tag> : undefined}
              </span>
              <Space>
                <Button
                  onClick={() => {
                    window.open(
                      fullUrl(`/term/list/${termData.word}`),
                      "_blank"
                    );
                  }}
                  type="link"
                  size="small"
                  icon={<SearchOutlined />}
                />
                <TermModal
                  onUpdate={(value: ITermDataResponse) => {
                    onModalClose();
                    sessionStorage.removeItem(`term/summary/${value.guid}`);
                    store.dispatch(refresh(value));
                  }}
                  onClose={() => {
                    onModalClose();
                  }}
                  trigger={
                    <Button
                      onClick={(event: React.MouseEvent<any, MouseEvent>) => {
                        setOpenPopover(false);
                        //event.stopPropagation();
                      }}
                      type="link"
                      size="small"
                      icon={<EditOutlined />}
                    />
                  }
                  id={termData.id}
                  word={termData.word}
                  channelId={termData.channelId}
                  parentChannelId={parentChannelId}
                  parentStudioId={parentStudioId}
                  community={community}
                />
              </Space>
            </Space>
          }
          open={openPopover}
          onOpenChange={onPopoverOpen}
          content={
            <div style={{ maxWidth: 500, minWidth: 300 }}>
              <Title level={5}>
                <Link to={`/term/list/${termData.word}`} target="_blank">
                  {word}
                </Link>
              </Title>
              {loading ? (
                <Skeleton
                  title={{ width: 200 }}
                  paragraph={{ rows: 4 }}
                  active
                />
              ) : (
                <>
                  <div>{termData.summary}</div>
                  <div style={{ textAlign: "right" }}>
                    {termData.summary_is_community ? "社区解释" : ""}
                  </div>
                </>
              )}
            </div>
          }
          placement="bottom"
        >
          <Typography.Link
            style={{ color: community ? "green" : undefined }}
            onClick={() => {
              console.debug("term send redux");
              store.dispatch(click(termData));
            }}
          >
            {termData?.meaning ?? termData?.word ?? "unknown"}
          </Typography.Link>
        </Popover>
        {isFirst && !compact ? (
          <TermExtra pali={word} meaning2={termData?.meaning2} />
        ) : undefined}
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
          <Typography.Link>
            <Text type="danger">{termData?.word}</Text>
          </Typography.Link>
        }
        word={termData?.word}
        parentChannelId={parentChannelId}
        parentStudioId={parentStudioId}
        community={community}
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
