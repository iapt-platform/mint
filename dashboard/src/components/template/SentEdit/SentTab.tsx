import { useState } from "react";
import { Badge, Tabs, Typography } from "antd";
import {
  TranslationOutlined,
  CloseOutlined,
  BlockOutlined,
} from "@ant-design/icons";

import { IWidgetSentEditInner } from "../SentEdit";
import Article from "../../article/Article";
import SentTabButton from "./SentTabButton";
import SentCanRead from "./SentCanRead";

const { Text } = Typography;

const Widget = ({
  id,
  channels,
  tranNum,
  nissayaNum,
  commNum,
  originNum,
  simNum,
}: IWidgetSentEditInner) => {
  const [translationActive, setTranslationActive] = useState<boolean>(false);
  const [nissayaActive, setNissayaActive] = useState<boolean>(false);
  const [commentaryActive, setCommentaryActive] = useState<boolean>(false);
  const [originalActive, setOriginalActive] = useState<boolean>(false);
  if (typeof id === "undefined") {
    return <></>;
  }
  const sentId = id.split("_");
  const sId = sentId[0].split("-");

  const onChange = (key: string) => {
    switch (key) {
      case "translation":
        setTranslationActive(true);
        break;
      case "nissaya":
        setNissayaActive(true);
        break;
      case "commentary":
        setCommentaryActive(true);
        break;
      case "original":
        setOriginalActive(true);
        break;
    }
  };
  return (
    <>
      <Tabs
        style={{ marginBottom: 0 }}
        size="small"
        tabBarGutter={0}
        onChange={onChange}
        tabBarExtraContent={
          <Text copyable={{ text: sentId[0] }}>{sentId[0]}</Text>
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
              />
            ),
            key: "original",
            disabled: true,
            children: (
              <Article
                active={originalActive}
                type="corpus_sent/original"
                articleId={id}
                mode="edit"
              />
            ),
          },
        ]}
      />
    </>
  );
};

export default Widget;
