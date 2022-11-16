import { useState } from "react";
import { Badge, Tabs } from "antd";
import {
  TranslationOutlined,
  BookOutlined,
  CloseOutlined,
  BlockOutlined,
} from "@ant-design/icons";
import { useIntl } from "react-intl";
import { IWidgetSentEditInner } from "../SentEdit";
import Article from "../../article/Article";

import store from "../../../store";
import openArticle, { IOpenArticle } from "../../../reducers/open-article";

const Widget = ({
  id,
  channels,
  tranNum,
  nissayaNum,
  commNum,
  originNum,
  simNum,
}: IWidgetSentEditInner) => {
  const intl = useIntl();

  const [translationActive, setTranslationActive] = useState<boolean>(false);
  const [nissayaActive, setNissayaActive] = useState<boolean>(false);
  const [commentaryActive, setCommentaryActive] = useState<boolean>(false);
  const [originalActive, setOriginalActive] = useState<boolean>(false);
  const onChange = (key: string) => {
    switch (key) {
      case "translation":
        setTranslationActive(true);
        const it: IOpenArticle = {
          type: "corpus_sent/translation",
          articleId: id,
        };
        store.dispatch(openArticle(it));
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
    <Tabs
      size="small"
      onChange={onChange}
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
            <Badge size="small" count={tranNum ? tranNum : 0}>
              <TranslationOutlined />
              {intl.formatMessage({
                id: "channel.type.translation.label",
              })}
            </Badge>
          ),
          key: "translation",
          children: (
            <Article
              active={translationActive}
              type="corpus_sent/translation"
              articleId={id}
              mode="edit"
              showModeSwitch={false}
              showMainMenu={false}
              showContextMenu={false}
            />
          ),
        },
        {
          label: (
            <Badge size="small" count={nissayaNum ? nissayaNum : 0}>
              <BookOutlined />
              {intl.formatMessage({
                id: "channel.type.nissaya.label",
              })}
            </Badge>
          ),
          key: "nissaya",
          children: (
            <Article
              active={nissayaActive}
              type="corpus_sent/nissaya"
              articleId={id}
              mode="edit"
              showModeSwitch={false}
              showMainMenu={false}
              showContextMenu={false}
            />
          ),
        },
        {
          label: (
            <Badge size="small" count={commNum ? commNum : 0}>
              <BlockOutlined />
              {intl.formatMessage({
                id: "channel.type.commentary.label",
              })}
            </Badge>
          ),
          key: "commentary",
          children: (
            <Article
              active={commentaryActive}
              type="corpus_sent/commentary"
              articleId={id}
              mode="edit"
              showModeSwitch={false}
              showMainMenu={false}
              showContextMenu={false}
            />
          ),
        },
        {
          label: (
            <Badge size="small" count={originNum ? originNum : 0}>
              <BlockOutlined />
              {intl.formatMessage({
                id: "channel.type.original.label",
              })}
            </Badge>
          ),
          key: "original",
          children: (
            <Article
              active={originalActive}
              type="corpus_sent/original"
              articleId={id}
              mode="edit"
              showModeSwitch={false}
              showMainMenu={false}
              showContextMenu={false}
            />
          ),
        },
      ]}
    />
  );
};

export default Widget;
