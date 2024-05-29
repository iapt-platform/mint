import { useEffect, useState } from "react";
import { useIntl } from "react-intl";
import {
  Dropdown,
  Tabs,
  Divider,
  Button,
  Switch,
  Rate,
  Space,
  Tooltip,
} from "antd";
import {
  SaveOutlined,
  VerticalAlignBottomOutlined,
  VerticalAlignTopOutlined,
} from "@ant-design/icons";

import { IWbw, IWbwAttachment, IWbwField, TFieldName } from "./WbwWord";
import WbwDetailBasic from "./WbwDetailBasic";
import WbwDetailBookMark from "./WbwDetailBookMark";
import WbwDetailNote from "./WbwDetailNote";
import WbwDetailAdvance from "./WbwDetailAdvance";
import { LockIcon, UnLockIcon } from "../../../assets/icon";
import { IAttachmentRequest } from "../../api/Attachments";
import WbwDetailAttachment from "./WbwDetailAttachment";
import { useAppSelector } from "../../../hooks";
import { currentUser } from "../../../reducers/current-user";
import DiscussionButton from "../../discussion/DiscussionButton";
import { courseUser } from "../../../reducers/course-user";
import { tempSet } from "../../../reducers/setting";
import { PopPlacement } from "./WbwPali";
import store from "../../../store";
import TagSelectButton from "../../tag/TagSelectButton";
import { ITagMapData } from "../../api/Tag";

interface IWidget {
  data: IWbw;
  visible?: boolean;
  popIsTop?: boolean;
  readonly?: boolean;
  onClose?: Function;
  onSave?: Function;
  onAttachmentSelectOpen?: Function;
  onPopTopChange?: Function;
  onTagCreate?: Function;
}
const WbwDetailWidget = ({
  data,
  visible = true,
  popIsTop = false,
  readonly = false,
  onClose,
  onSave,
  onAttachmentSelectOpen,
  onPopTopChange,
  onTagCreate,
}: IWidget) => {
  const intl = useIntl();
  const [currWbwData, setCurrWbwData] = useState<IWbw>(
    JSON.parse(JSON.stringify(data))
  );
  const [tabKey, setTabKey] = useState<string>("basic");
  const currUser = useAppSelector(currentUser);

  useEffect(() => {
    console.debug("input data", data);
    setCurrWbwData(JSON.parse(JSON.stringify(data)));
  }, [data]);

  function fieldChanged(field: TFieldName, value: string) {
    console.log("field", field, "value", value);
    let origin = JSON.parse(JSON.stringify(currWbwData));
    switch (field) {
      case "note":
        origin.note = { value: value, status: 7 };
        break;
      case "bookMarkColor":
        origin.bookMarkColor = { value: parseInt(value), status: 7 };
        break;
      case "bookMarkText":
        origin.bookMarkText = { value: value, status: 7 };
        break;
      case "word":
        origin.word = { value: value, status: 7 };
        break;
      case "real":
        origin.real = { value: value, status: 7 };
        break;
      case "meaning":
        origin.meaning = { value: value, status: 7 };
        break;
      case "factors":
        origin.factors = { value: value, status: 7 };
        break;
      case "factorMeaning":
        origin.factorMeaning = { value: value, status: 7 };
        break;
      case "parent":
        origin.parent = { value: value, status: 7 };
        break;
      case "parent2":
        origin.parent2 = { value: value, status: 7 };
        break;
      case "grammar2":
        origin.grammar2 = { value: value, status: 7 };
        break;
      case "case":
        const arrCase = value.split("#");
        origin.case = { value: value, status: 7 };
        origin.type = { value: arrCase[0] ? arrCase[0] : "", status: 7 };
        origin.grammar = { value: arrCase[1] ? arrCase[1] : "", status: 7 };
        break;
      case "relation":
        origin.relation = { value: value, status: 7 };
        break;
      case "confidence":
        origin.confidence = parseFloat(value);
        break;
      case "locked":
        origin.locked = JSON.parse(value);
        break;
      case "attachments":
        //mData.attachments = value;
        break;
      default:
        break;
    }
    console.debug("origin", origin);
    setCurrWbwData(origin);
  }
  const userInCourse = useAppSelector(courseUser);
  if (userInCourse && userInCourse.role === "student") {
  }
  return (
    <div
      className="wbw_detail"
      style={{
        minWidth: 450,
      }}
    >
      <Tabs
        size="small"
        type="card"
        tabBarExtraContent={
          <Space>
            <Tooltip
              title={popIsTop ? "底端弹窗" : "顶端弹窗"}
              getTooltipContainer={(node: HTMLElement) =>
                document.getElementsByClassName("wbw_detail")[0] as HTMLElement
              }
            >
              <Button
                type="text"
                icon={
                  popIsTop ? (
                    <VerticalAlignBottomOutlined />
                  ) : (
                    <VerticalAlignTopOutlined />
                  )
                }
                onClick={() => {
                  store.dispatch(
                    tempSet({
                      key: PopPlacement,
                      value: !popIsTop,
                    })
                  );
                  if (typeof onPopTopChange !== "undefined") {
                    //onPopTopChange(popIsTop);
                  }
                }}
              />
            </Tooltip>
            <TagSelectButton
              selectorTitle={data.word.value}
              resType="wbw"
              resId={data.uid}
              onOpen={() => {
                if (typeof onClose !== "undefined") {
                  onClose();
                }
              }}
              onCreate={(tags: ITagMapData[]) => {
                if (typeof onTagCreate !== "undefined") {
                  onTagCreate(tags);
                }
              }}
            />
            <DiscussionButton
              initCount={data.hasComment ? 1 : 0}
              hideCount
              resId={data.uid}
              resType="wbw"
            />
          </Space>
        }
        onChange={(activeKey: string) => {
          setTabKey(activeKey);
        }}
        items={[
          {
            label: intl.formatMessage({ id: "buttons.basic" }),
            key: "basic",
            children: (
              <WbwDetailBasic
                visible={visible && tabKey === "basic"}
                data={currWbwData}
                readonly={readonly}
                onChange={(e: IWbwField) => {
                  console.debug("WbwDetailBasic onchange", e);
                  fieldChanged(e.field, e.value);
                }}
                onRelationAdd={() => {
                  if (typeof onClose !== "undefined") {
                    onClose();
                  }
                }}
              />
            ),
          },
          {
            label: intl.formatMessage({ id: "buttons.bookmark" }),
            key: "bookmark",
            children: (
              <div style={{ minHeight: 270 }}>
                <WbwDetailBookMark
                  data={data}
                  onChange={(e: IWbwField) => {
                    fieldChanged(e.field, e.value);
                  }}
                />
              </div>
            ),
          },
          {
            label: intl.formatMessage({ id: "buttons.note" }),
            key: "note",
            children: (
              <div style={{ minHeight: 270 }}>
                <WbwDetailNote
                  data={data}
                  onChange={(e: IWbwField) => {
                    fieldChanged(e.field, e.value);
                  }}
                />
              </div>
            ),
          },
          {
            label: intl.formatMessage({ id: "buttons.spell" }),
            key: "spell",
            children: (
              <div style={{ minHeight: 270 }}>
                <WbwDetailAdvance
                  data={currWbwData}
                  onChange={(e: IWbwField) => {
                    fieldChanged(e.field, e.value);
                  }}
                />
              </div>
            ),
          },
          {
            label: intl.formatMessage({ id: "buttons.attachments" }),
            key: "attachments",
            children: (
              <div style={{ minHeight: 270 }}>
                <WbwDetailAttachment
                  data={currWbwData}
                  onUpload={(fileList: IAttachmentRequest[]) => {
                    let mData = JSON.parse(JSON.stringify(currWbwData));
                    mData.attachments = fileList.map((item) => {
                      return {
                        id: item.id,
                        title: item.title,
                        size: item.size ? item.size : 0,
                        content_type: item.content_type,
                      };
                    });
                    setCurrWbwData(mData);
                  }}
                  onDialogOpen={(open: boolean) => {
                    if (typeof onAttachmentSelectOpen !== "undefined") {
                      onAttachmentSelectOpen(open);
                    }
                  }}
                  onChange={(value: IWbwAttachment[]) => {
                    let mData = JSON.parse(JSON.stringify(currWbwData));
                    mData.attachments = value;
                    setCurrWbwData(mData);
                    //fieldChanged(e.field, e.value);
                  }}
                />
              </div>
            ),
          },
        ]}
      />
      <Divider style={{ margin: "4px 0" }}></Divider>
      <div style={{ display: "flex", justifyContent: "space-between" }}>
        <Switch
          checkedChildren={<LockIcon />}
          unCheckedChildren={<UnLockIcon />}
        />
        <div>
          {"信心指数"}
          <Rate
            defaultValue={data.confidence * 5}
            onChange={(value: number) => {
              fieldChanged("confidence", (value / 5).toString());
            }}
          />
        </div>
      </div>
      <Divider style={{ margin: "4px 0" }}></Divider>
      <div style={{ display: "flex", justifyContent: "space-between" }}>
        <div>
          <Button
            danger
            onClick={() => {
              if (typeof onClose !== "undefined") {
                onClose();
              }
            }}
          >
            {intl.formatMessage({ id: "buttons.cancel" })}
          </Button>
        </div>
        <Dropdown.Button
          disabled={readonly}
          style={{ width: "unset" }}
          type="primary"
          menu={{
            items: [
              {
                key: "user-dict-public",
                label: intl.formatMessage({ id: "buttons.save.publish" }),
                disabled: currUser?.roles?.includes("basic"),
              },
              {
                key: "user-dict-private",
                label: intl.formatMessage({ id: "buttons.save.my.dict" }),
              },
            ],
            onClick: (e) => {
              if (typeof onSave !== "undefined") {
                //保存并发布
                if (e.key === "user-dict-public") {
                  onSave(currWbwData, true, true);
                } else {
                  onSave(currWbwData, true, false);
                }
              }
            },
          }}
          onClick={() => {
            if (typeof onSave !== "undefined") {
              onSave(currWbwData, false);
            }
          }}
        >
          <SaveOutlined />
          {intl.formatMessage({ id: "buttons.save" })}
        </Dropdown.Button>
      </div>
    </div>
  );
};

export default WbwDetailWidget;
