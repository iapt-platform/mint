import { Badge, Space, Tabs, Typography } from "antd";
import {
  TranslationOutlined,
  CloseOutlined,
  BlockOutlined,
  BarsOutlined,
} from "@ant-design/icons";

import SentTabButton from "./SentTabButton";
import SentCanRead from "./SentCanRead";
import SentSim from "./SentSim";
import { useIntl } from "react-intl";
import TocPath, { ITocPathNode } from "../../corpus/TocPath";
import { IWbw } from "../Wbw/WbwWord";
import RelaGraphic from "../Wbw/RelaGraphic";
import SentMenu from "./SentMenu";
import { IChapter } from "../../corpus/BookViewer";
import store from "../../../store";
import { change } from "../../../reducers/para-change";
import { useEffect, useState } from "react";

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
  onMagicDict?: Function;
  onCompact?: Function;
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
  onMagicDict,
  onCompact,
}: IWidget) => {
  const intl = useIntl();
  const [isCompact, setIsCompact] = useState(compact);
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

  return (
    <>
      <Tabs
        style={
          isCompact
            ? {
                position: "absolute",
                marginTop: -32,
                width: "100%",
                marginRight: 10,
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
              onChange={(
                para: ITocPathNode,
                e: React.MouseEvent<
                  HTMLSpanElement | HTMLAnchorElement,
                  MouseEvent
                >
              ) => {
                //点击章节目录
                if (para.book && para.paragraph) {
                  const type = para.level
                    ? para.level < 8
                      ? "chapter"
                      : "para"
                    : "para";
                  store.dispatch(
                    change({
                      book: para.book,
                      para: para.paragraph,
                      type: type,
                    })
                  );
                }
              }}
            />
            <Text copyable={{ text: `{{${sentId[0]}}}` }}>{sentId[0]}</Text>
            <SentMenu
              book={book}
              para={para}
              loading={magicDictLoading}
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
              <Badge size="small" count={0}>
                <CloseOutlined />
              </Badge>
            ),
            key: "close",
            children: <></>,
          },
          {
            label: (
              <SentTabButton
                icon={<TranslationOutlined />}
                type="translation"
                sentId={id}
                count={tranNum}
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
              />
            ),
          },
          {
            label: (
              <SentTabButton
                icon={<CloseOutlined />}
                type="nissaya"
                sentId={id}
                count={nissayaNum}
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
              />
            ),
          },
          {
            label: (
              <SentTabButton
                icon={<TranslationOutlined />}
                type="commentary"
                sentId={id}
                count={commNum}
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
            label: "关系图",
            key: "relation-graphic",
            children: <RelaGraphic wbwData={wbwData} />,
          },
        ]}
      />
    </>
  );
};

export default SentTabWidget;
