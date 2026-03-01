import { useEffect, useState, useMemo, useReducer } from "react";
import { Link } from "react-router";
import { Button, Popover, Skeleton, Space, Tag } from "antd";
import { Typography } from "antd";
import { SearchOutlined, EditOutlined } from "@ant-design/icons";

import store from "../../store";
import TermModal from "../term/TermModal";

import type { ITerm, ITermDataResponse, ITermResponse } from "../../api/Term";
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

// 合并相关状态，避免多次 setState 触发多次渲染
interface ITermState {
  termData: ITerm;
  isInit: boolean;
  community: boolean | undefined;
}

type TermAction =
  | { type: "UPDATE_TERM"; payload: ITermDataResponse }
  | { type: "SET_TERM_DATA"; payload: ITerm };

function termReducer(state: ITermState, action: TermAction): ITermState {
  switch (action.type) {
    case "UPDATE_TERM":
      return {
        termData: dataMap(action.payload),
        isInit: false,
        community: false,
      };
    case "SET_TERM_DATA":
      return {
        ...state,
        termData: action.payload,
        isInit: false,
      };
    default:
      return state;
  }
}

export interface IWidgetTermCtl {
  id?: string;
  word?: string;
  meaning?: string;
  meaning2?: string;
  channel?: string;
  parentChannelId?: string;
  parentStudioId?: string;
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

  const [{ termData, isInit, community }, dispatch] = useReducer(termReducer, {
    termData: {
      id,
      word,
      meaning,
      meaning2: meaning2?.split(","),
      summary,
      channelId: channel,
    },
    isInit: true,
    community: isCommunity,
  });

  const [loading, setLoading] = useState(false);

  const newTerm: ITermDataResponse | undefined = useAppSelector(changedTerm);
  const cache = useAppSelector(termCache);

  const [uid] = useState<string>(
    lodash.times(20, () => lodash.random(35).toString(36)).join("")
  );
  const termOrder = useAppSelector(order);

  // 用 useMemo 派生 isFirst，避免 useEffect + setState
  const isFirst = useMemo(() => {
    if (!word || !parentChannelId) return false;
    const index = termOrder?.findIndex(
      (value) =>
        value.word === word &&
        value.channelId === parentChannelId &&
        value.first === uid
    );
    return index !== -1;
  }, [termOrder, word, parentChannelId, uid]);

  useEffect(() => {
    if (word && parentChannelId) {
      store.dispatch(push({ word, channelId: parentChannelId, first: uid }));
    }
  }, [parentChannelId, uid, word]);

  // ✅ 修改后：用 if (newTerm) 收窄类型
  useEffect(() => {
    if (
      newTerm && // 收窄掉 undefined
      newTerm.word === word &&
      parentStudioId === newTerm.studio.id
    ) {
      console.debug("Term studio 匹配", newTerm);
      dispatch({ type: "UPDATE_TERM", payload: newTerm }); // 此时 newTerm: ITermDataResponse ✅
    }
  }, [newTerm, parentStudioId, word]);

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
        // term 已收窄为 ITermDataResponse，非 undefined ✅
        dispatch({ type: "SET_TERM_DATA", payload: dataMap(term) });
        return;
      } else {
        const url = `/v2/terms/${id}?community_summary=1`;
        console.info("api request", url);
        setLoading(true);
        get<ITermResponse>(url)
          .then((json) => {
            if (json.ok) {
              dispatch({ type: "UPDATE_TERM", payload: json.data });
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
                      onClick={() => {
                        setOpenPopover(false);
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
            style={{
              color: community ? "green" : undefined,
              wordBreak: "keep-all",
            }}
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
            <Text type="danger" style={{ wordBreak: "keep-all" }}>
              {termData?.word}
            </Text>
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
