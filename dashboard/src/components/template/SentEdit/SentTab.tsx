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
              <Article
                active={translationActive}
                type="corpus_sent/translation"
                articleId={id}
                mode="edit"
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
              <Article
                active={nissayaActive}
                type="corpus_sent/nissaya"
                articleId={id}
                mode="edit"
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
              <Article
                active={commentaryActive}
                type="corpus_sent/commentary"
                articleId={id}
                mode="edit"
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
