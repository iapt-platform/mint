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
import { IChapter } from "../../corpus/BookViewer";
import store from "../../../store";
import { change } from "../../../reducers/para-change";

const { Text } = Typography;

interface IWidget {
  id: string;
  book: number;
  para: number;
  wordStart: number;
  wordEnd: number;
  path?: ITocPathNode[];
  layout?: "row" | "column";
  tranNum?: number;
  nissayaNum?: number;
  commNum?: number;
  originNum: number;
  simNum?: number;
  wbwData?: IWbw[];
  magicDictLoading?: boolean;
  onMagicDict?: Function;
}
const SentTabWidget = ({
  id,
  book,
  para,
  wordStart,
  wordEnd,
  path,
  tranNum,
  nissayaNum,
  commNum,
  originNum,
  simNum = 0,
  wbwData,
  magicDictLoading = false,
  onMagicDict,
}: IWidget) => {
  const intl = useIntl();
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
        style={{ marginBottom: 0 }}
        size="small"
        tabBarGutter={0}
        tabBarExtraContent={
          <Space>
            <TocPath
              link="none"
              data={mPath}
              trigger={
                path ? path.length > 0 ? path[0].paliTitle : <></> : <></>
              }
              onChange={(para: IChapter) => {
                //点击章节目录
                const type = para.level
                  ? para.level < 8
                    ? "chapter"
                    : "para"
                  : "para";
                store.dispatch(
                  change({ book: para.book, para: para.para, type: type })
                );
              }}
            />
            <Text copyable={{ text: sentId[0] }}>{sentId[0]}</Text>
            <SentMenu
              book={book}
              para={para}
              loading={magicDictLoading}
              onMagicDict={(type: string) => {
                if (typeof onMagicDict !== "undefined") {
                  onMagicDict(type);
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
              />
            ),
          },
          {
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
            disabled: true,
            children: (
              <SentCanRead
                book={parseInt(sId[0])}
                para={parseInt(sId[1])}
                wordStart={parseInt(sId[2])}
                wordEnd={parseInt(sId[3])}
                type="original"
              />
            ),
          },
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
