import { useEffect, useState } from "react";
import { Badge, Space, Tabs, Typography, message } from "antd";
import {
  TranslationOutlined,
  CloseOutlined,
  BlockOutlined,
} from "@ant-design/icons";

import SentTabButton from "./SentTabButton";
import SentCanRead from "./SentCanRead";
import SentSim from "./SentSim";
import { useIntl } from "react-intl";
import TocPath, { ITocPathNode } from "../../corpus/TocPath";
import { IWbw } from "../Wbw/WbwWord";
import RelaGraphic from "../Wbw/RelaGraphic";
import SentMenu from "./SentMenu";
import { ArticleMode } from "../../article/Article";
import { IResNumber } from "../SentEdit";
import SentTabCopy from "./SentTabCopy";
import { fullUrl } from "../../../utils";
import SentWbw from "./SentWbw";
import SentTabButtonWbw from "./SentTabButtonWbw";

const { Text } = Typography;

interface IWidget {
  id: string;
  book: number;
  para: number;
  wordStart: number;
  wordEnd: number;
  channelsId?: string[];
  path?: ITocPathNode[];
  layout?: "row" | "column";
  tranNum?: number;
  nissayaNum?: number;
  commNum?: number;
  originNum: number;
  simNum?: number;
  wbwData?: IWbw[];
  magicDictLoading?: boolean;
  compact?: boolean;
  mode?: ArticleMode;
  loadedRes?: IResNumber;
  onMagicDict?: Function;
  onCompact?: Function;
  onModeChange?: Function;
  onAffix?: Function;
}
const SentTabWidget = ({
  id,
  book,
  para,
  wordStart,
  wordEnd,
  channelsId,
  path,
  tranNum = 0,
  nissayaNum = 0,
  commNum = 0,
  originNum,
  simNum = 0,
  wbwData,
  magicDictLoading = false,
  compact = false,
  mode,
  loadedRes,
  onMagicDict,
  onCompact,
  onModeChange,
  onAffix,
}: IWidget) => {
  const intl = useIntl();
  const [isCompact, setIsCompact] = useState(compact);
  const [hover, setHover] = useState(false);
  const [currKey, setCurrKey] = useState("close");
  const [currTranNum, setCurrTranNum] = useState(tranNum);
  const [currNissayaNum, setCurrNissayaNum] = useState(nissayaNum);
  const [currCommNum, setCurrCommNum] = useState(commNum);
  const [currSimilarNum, setCurrSimilarNum] = useState(simNum);
  const [showWbwProgress, setShowWbwProgress] = useState(false);

  console.log("SentTabWidget render");

  useEffect(() => setIsCompact(compact), [compact]);
  const mPath = path
    ? [
        ...path,
        { book: book, paragraph: para, title: para.toString(), level: 100 },
      ]
    : [];
  if (typeof id === "undefined") {
    return <></>;
  }
  const sentId = id.split("_");
  const sId = sentId[0].split("-");
  const tabButtonStyle: React.CSSProperties | undefined = compact
    ? { visibility: hover || currKey !== "close" ? "visible" : "hidden" }
    : undefined;

  return (
    <Tabs
      onMouseEnter={() => setHover(true)}
      onMouseLeave={() => setHover(false)}
      activeKey={currKey}
      onChange={(activeKey: string) => {
        setCurrKey(activeKey);
      }}
      style={
        isCompact
          ? {
              position: currKey === "close" ? "absolute" : "unset",
              marginTop: -32,
              width: "100%",
              marginRight: 10,
              backgroundColor:
                hover || currKey !== "close"
                  ? "rgba(128, 128, 128, 0.1)"
                  : "unset",
            }
          : {
              padding: "0 8px",
              backgroundColor: "rgba(128, 128, 128, 0.1)",
            }
      }
      tabBarStyle={{ marginBottom: 0 }}
      size="small"
      tabBarGutter={0}
      tabBarExtraContent={
        <Space>
          <TocPath
            link="none"
            data={mPath}
            channels={channelsId}
            trigger={path ? path.length > 0 ? path[0].title : <></> : <></>}
          />
          <Text>{sentId[0]}</Text>
          <SentTabCopy wbwData={wbwData} text={`{{${sentId[0]}}}`} />
          <SentMenu
            book={book}
            para={para}
            loading={magicDictLoading}
            mode={mode}
            onMagicDict={(type: string) => {
              if (typeof onMagicDict !== "undefined") {
                onMagicDict(type);
              }
            }}
            onMenuClick={(key: string) => {
              switch (key) {
                case "compact" || "normal":
                  if (typeof onCompact !== "undefined") {
                    setIsCompact(true);
                    onCompact(true);
                  }
                  break;
                case "normal":
                  if (typeof onCompact !== "undefined") {
                    setIsCompact(false);
                    onCompact(false);
                  }
                  break;
                case "origin-edit":
                  if (typeof onModeChange !== "undefined") {
                    onModeChange("edit");
                  }
                  break;
                case "origin-wbw":
                  if (typeof onModeChange !== "undefined") {
                    onModeChange("wbw");
                  }
                  break;
                case "copy-id":
                  const id = `{{${book}-${para}-${wordStart}-${wordEnd}}}`;
                  navigator.clipboard.writeText(id).then(() => {
                    message.success("编号已经拷贝到剪贴板");
                  });
                  break;
                case "copy-link":
                  let link = `/article/para/${book}-${para}?mode=edit`;
                  link += `&book=${book}&par=${para}`;
                  if (channelsId) {
                    link += `&channel=` + channelsId?.join("_");
                  }
                  link += `&focus=${book}-${para}-${wordStart}-${wordEnd}`;
                  navigator.clipboard.writeText(fullUrl(link)).then(() => {
                    message.success("链接地址已经拷贝到剪贴板");
                  });
                  break;
                case "affix":
                  if (typeof onAffix !== "undefined") {
                    onAffix();
                  }
                  break;
                default:
                  break;
              }
            }}
          />
        </Space>
      }
      items={[
        {
          label: (
            <span style={tabButtonStyle}>
              <Badge size="small" count={0}>
                <CloseOutlined />
              </Badge>
            </span>
          ),
          key: "close",
          children: <></>,
        },
        {
          label: (
            <SentTabButton
              style={tabButtonStyle}
              icon={<TranslationOutlined />}
              type="translation"
              sentId={id}
              count={
                currTranNum
                  ? currTranNum -
                    (loadedRes?.translation ? loadedRes.translation : 0)
                  : undefined
              }
              title={intl.formatMessage({
                id: "channel.type.translation.label",
              })}
            />
          ),
          key: "translation",
          children: (
            <SentCanRead
              book={parseInt(sId[0])}
              para={parseInt(sId[1])}
              wordStart={parseInt(sId[2])}
              wordEnd={parseInt(sId[3])}
              type="translation"
              channelsId={channelsId}
              onCreate={() => setCurrTranNum((origin) => origin + 1)}
            />
          ),
        },
        {
          label: (
            <SentTabButton
              style={tabButtonStyle}
              icon={<CloseOutlined />}
              type="nissaya"
              sentId={id}
              count={
                currNissayaNum
                  ? currNissayaNum -
                    (loadedRes?.nissaya ? loadedRes.nissaya : 0)
                  : undefined
              }
              title={intl.formatMessage({
                id: "channel.type.nissaya.label",
              })}
            />
          ),
          key: "nissaya",
          children: (
            <SentCanRead
              book={parseInt(sId[0])}
              para={parseInt(sId[1])}
              wordStart={parseInt(sId[2])}
              wordEnd={parseInt(sId[3])}
              type="nissaya"
              channelsId={channelsId}
              onCreate={() => setCurrNissayaNum((origin) => origin + 1)}
            />
          ),
        },
        {
          label: (
            <SentTabButton
              style={tabButtonStyle}
              icon={<TranslationOutlined />}
              type="commentary"
              sentId={id}
              count={
                currCommNum
                  ? currCommNum -
                    (loadedRes?.commentary ? loadedRes.commentary : 0)
                  : undefined
              }
              title={intl.formatMessage({
                id: "channel.type.commentary.label",
              })}
            />
          ),
          key: "commentary",
          children: (
            <SentCanRead
              book={parseInt(sId[0])}
              para={parseInt(sId[1])}
              wordStart={parseInt(sId[2])}
              wordEnd={parseInt(sId[3])}
              type="commentary"
              channelsId={channelsId}
              onCreate={() => setCurrCommNum((origin) => origin + 1)}
            />
          ),
        },
        /*{
            label: (
              <SentTabButton
                icon={<BlockOutlined />}
                type="original"
                sentId={id}
                count={originNum}
                title={intl.formatMessage({
                  id: "channel.type.original.label",
                })}
              />
            ),
            key: "original",
            children: (
              <SentCanRead
                book={parseInt(sId[0])}
                para={parseInt(sId[1])}
                wordStart={parseInt(sId[2])}
                wordEnd={parseInt(sId[3])}
                type="original"
              />
            ),
          },*/
        {
          label: (
            <SentTabButton
              style={tabButtonStyle}
              icon={<BlockOutlined />}
              type="original"
              sentId={id}
              count={currSimilarNum}
              title={intl.formatMessage({
                id: "buttons.sim",
              })}
            />
          ),
          key: "sim",
          children: (
            <SentSim
              book={parseInt(sId[0])}
              para={parseInt(sId[1])}
              wordStart={parseInt(sId[2])}
              wordEnd={parseInt(sId[3])}
              channelsId={channelsId}
              limit={5}
              onCreate={() => setCurrSimilarNum((origin) => origin + 1)}
            />
          ),
        },
        {
          label: (
            <SentTabButtonWbw
              style={tabButtonStyle}
              sentId={id}
              count={0}
              onMenuClick={(keyPath: string[]) => {
                switch (keyPath.join("-")) {
                  case "show-progress":
                    setShowWbwProgress((origin) => !origin);
                    break;
                }
              }}
            />
          ),
          key: "wbw",
          children: (
            <SentWbw
              book={parseInt(sId[0])}
              para={parseInt(sId[1])}
              wordStart={parseInt(sId[2])}
              wordEnd={parseInt(sId[3])}
              channelsId={channelsId}
              wbwProgress={showWbwProgress}
            />
          ),
        },
        {
          label: <span style={tabButtonStyle}>{"关系图"}</span>,
          key: "relation-graphic",
          children: <RelaGraphic wbwData={wbwData} />,
        },
      ]}
    />
  );
};

export default SentTabWidget;
