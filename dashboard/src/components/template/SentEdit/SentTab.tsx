import { useEffect, useState } from "react";
import { Badge, Space, Tabs, Typography } from "antd";
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
}
const SentTabWidget = ({
  id,
  book,
  para,
  wordStart,
  wordEnd,
  channelsId,
  path,
  tranNum,
  nissayaNum,
  commNum,
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
}: IWidget) => {
  const intl = useIntl();
  const [isCompact, setIsCompact] = useState(compact);
  const [hover, setHover] = useState(false);
  const [currKey, setCurrKey] = useState("close");
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
              backgroundColor: hover || currKey !== "close" ? "white" : "unset",
            }
          : undefined
      }
      tabBarStyle={{ marginBottom: 0 }}
      size="small"
      tabBarGutter={0}
      tabBarExtraContent={
        <Space>
          <TocPath
            link="none"
            data={mPath}
            trigger={path ? path.length > 0 ? path[0].title : <></> : <></>}
          />
          <Text copyable={{ text: `{{${sentId[0]}}}` }}>{sentId[0]}</Text>
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
                tranNum
                  ? tranNum -
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
                nissayaNum
                  ? nissayaNum - (loadedRes?.nissaya ? loadedRes.nissaya : 0)
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
                commNum
                  ? commNum - (loadedRes?.commentary ? loadedRes.commentary : 0)
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
              count={simNum}
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
              limit={5}
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