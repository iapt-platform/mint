import { useEffect, useRef, useState } from "react";
import { Button, Dropdown, MenuProps, Typography } from "antd";
import { LoadingOutlined, CloseOutlined } from "@ant-design/icons";

import { useAppSelector } from "../../hooks";
import {
  onChangeKey,
  onChangeValue,
  settingInfo,
} from "../../reducers/setting";
import { GetUserSetting } from "../auth/setting/default";
import { TCodeConvertor } from "./utilities";
import { ISentence, IWidgetSentEditInner, SentEditInner } from "./SentEdit";
import MdView from "./MdView";
import store from "../../store";
import { push } from "../../reducers/sentence";
import "./style.css";
import InteractiveButton from "./SentEdit/InteractiveButton";
import { prOpen } from "./SentEdit/SuggestionButton";
import { openDiscussion } from "../discussion/DiscussionButton";
import { IEditableSentence } from "../api/Corpus";
import { get } from "../../request";

const { Text } = Typography;

const items: MenuProps["items"] = [
  {
    label: "编辑",
    key: "edit",
  },
  {
    label: "讨论",
    key: "discussion",
  },
  {
    label: "修改建议",
    key: "pr",
  },
  {
    label: "标签",
    key: "tag",
  },
];

interface IWidgetSentReadFrame {
  origin?: ISentence[];
  translation?: ISentence[];
  layout?: "row" | "column";
  book?: number;
  para?: number;
  wordStart?: number;
  wordEnd?: number;
  sentId?: string;
  error?: string;
}
const SentReadFrame = ({
  origin,
  translation,
  layout = "column",
  book,
  para,
  wordStart,
  wordEnd,
  sentId,
  error,
}: IWidgetSentReadFrame) => {
  const [paliCode1, setPaliCode1] = useState<TCodeConvertor>("roman");
  const [active, setActive] = useState(false);
  const [loading, setLoading] = useState(false);
  const [sentData, setSentData] = useState<IWidgetSentEditInner>();
  const [showEdit, SetShowEdit] = useState(false);
  const [translationData, setTranslationData] = useState<
    ISentence[] | undefined
  >(translation);

  const key = useAppSelector(onChangeKey);
  const value = useAppSelector(onChangeValue);
  const settings = useAppSelector(settingInfo);
  const boxOrg = useRef<HTMLDivElement>(null);
  const boxSent = useRef<HTMLDivElement>(null);

  useEffect(() => {
    store.dispatch(
      push({
        id: `${book}-${para}-${wordStart}-${wordEnd}`,
        origin: origin?.map((item) => item.html),
        translation: translation?.map((item) => item.html),
      })
    );
  }, [book, origin, para, translation, wordEnd, wordStart]);

  useEffect(() => {
    const displayOriginal = GetUserSetting(
      "setting.display.original",
      settings
    );
    if (typeof displayOriginal === "boolean") {
      if (boxOrg.current) {
        if (
          displayOriginal === false &&
          translation &&
          translation.length > 0
        ) {
          boxOrg.current.style.display = "none";
        } else {
          boxOrg.current.style.display = "block";
        }
      }
    }
    const layoutDirection = GetUserSetting(
      "setting.layout.direction",
      settings
    );
    if (typeof layoutDirection === "string") {
      if (boxSent.current) {
        boxSent.current.style.flexDirection = layoutDirection;
      }
    }

    const _paliCode1 = GetUserSetting("setting.pali.script.primary", settings);
    if (typeof _paliCode1 !== "undefined") {
      setPaliCode1(_paliCode1.toString() as TCodeConvertor);
    }
  }, [key, value, settings]);

  return (
    <span ref={boxSent} className="sent_read_shell">
      <Text type="danger" mark>
        {error}
      </Text>
      <span
        dangerouslySetInnerHTML={{
          __html: `<span class="pcd_sent" id="sent_${book}-${para}-${wordStart}-${wordEnd}"></span>`,
        }}
      />
      <span style={{ flex: "5", color: "#9f3a01" }} ref={boxOrg}>
        {origin?.map((item, id) => {
          return (
            <Text key={id}>
              <MdView
                style={{ color: "brown" }}
                html={item.html}
                wordWidget={true}
                convertor={paliCode1}
              />
            </Text>
          );
        })}
      </span>
      <span className="sent_read" style={{ flex: "5" }}>
        {translationData?.map((item, id) => {
          return (
            <span key={id}>
              {loading ? <LoadingOutlined /> : <></>}
              <Dropdown
                menu={{
                  items,
                  onClick: (e) => {
                    console.log("click ", e);
                    switch (e.key) {
                      case "edit":
                        const url = `/v2/editable-sentence/${item.id}`;
                        console.info("api request", url);
                        setLoading(true);
                        get<IEditableSentence>(url)
                          .then((json) => {
                            console.info("api response", json);
                            if (json.ok) {
                              setSentData(json.data);
                              SetShowEdit(true);
                            }
                          })
                          .finally(() => setLoading(false));
                        break;
                      case "discussion":
                        if (item.id) {
                          openDiscussion(item.id, "sentence", false);
                        }
                        break;
                      case "pr":
                        prOpen(item);
                        break;
                      default:
                        break;
                    }
                  },
                }}
                trigger={["contextMenu"]}
              >
                <Text
                  key={id}
                  className="sent_read_translation"
                  style={{ display: showEdit ? "none" : "inline" }}
                >
                  <MdView
                    html={item.html}
                    style={{ backgroundColor: active ? "beige" : "unset" }}
                  />
                </Text>
              </Dropdown>
              <div style={{ display: showEdit ? "block" : "none" }}>
                <div style={{ textAlign: "right" }}>
                  <Button
                    size="small"
                    icon={<CloseOutlined />}
                    onClick={() => {
                      SetShowEdit(false);
                    }}
                  >
                    返回审阅模式
                  </Button>
                </div>

                {sentData ? (
                  <SentEditInner
                    mode="edit"
                    {...sentData}
                    onTranslationChange={(data: ISentence) => {
                      console.log("onTranslationChange", data);
                      if (translationData) {
                        let newData = [...translationData];
                        newData.forEach(
                          (
                            value: ISentence,
                            index: number,
                            array: ISentence[]
                          ) => {
                            if (index === id) {
                              array[index] = data;
                            }
                          }
                        );
                        setTranslationData(newData);
                      }
                    }}
                  />
                ) : (
                  "无数据"
                )}
              </div>
              <InteractiveButton
                data={item}
                compact={true}
                float={true}
                hideCount
                hideInZero
                onMouseEnter={() => setActive(true)}
                onMouseLeave={() => setActive(false)}
              />
            </span>
          );
        })}
      </span>
    </span>
  );
};

interface IWidget {
  props: string;
}
const Widget = ({ props }: IWidget) => {
  const prop = JSON.parse(atob(props)) as IWidgetSentReadFrame;
  return (
    <>
      <SentReadFrame {...prop} />
    </>
  );
};

export default Widget;
