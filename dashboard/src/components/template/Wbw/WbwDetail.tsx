import { useEffect, useState } from "react";
import { useIntl } from "react-intl";
import { Dropdown, Tabs, Divider, Button, Switch, Rate } from "antd";
import { SaveOutlined } from "@ant-design/icons";

import { IWbw, IWbwAttachment, IWbwField, TFieldName } from "./WbwWord";
import WbwDetailBasic from "./WbwDetailBasic";
import WbwDetailBookMark from "./WbwDetailBookMark";
import WbwDetailNote from "./WbwDetailNote";
import WbwDetailAdvance from "./WbwDetailAdvance";
import {
  CommentOutlinedIcon,
  LockIcon,
  UnLockIcon,
} from "../../../assets/icon";
import { UploadFile } from "antd/es/upload/interface";
import { IAttachmentRequest, IAttachmentResponse } from "../../api/Attachments";
import WbwDetailAttachment from "./WbwDetailAttachment";
import CommentBox from "../../discussion/DiscussionDrawer";

interface IWidget {
  data: IWbw;
  onClose?: Function;
  onSave?: Function;
  onCommentCountChange?: Function;
  onAttachmentSelectOpen?: Function;
}
const WbwDetailWidget = ({
  data,
  onClose,
  onSave,
  onCommentCountChange,
  onAttachmentSelectOpen,
}: IWidget) => {
  const intl = useIntl();
  const [currWbwData, setCurrWbwData] = useState<IWbw>(
    JSON.parse(JSON.stringify(data))
  );
  useEffect(() => {
    setCurrWbwData(JSON.parse(JSON.stringify(data)));
  }, [data]);

  function fieldChanged(field: TFieldName, value: string) {
    console.log("field", field, "value", value);
    setCurrWbwData((origin) => {
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
      return origin;
    });
  }

  return (
    <div
      style={{
        minWidth: 450,
      }}
    >
      <Tabs
        size="small"
        type="card"
        tabBarExtraContent={
          data.uid ? (
            <CommentBox
              resId={data.uid}
              resType="wbw"
              trigger={<Button icon={<CommentOutlinedIcon />} type="text" />}
              onCommentCountChange={(count: number) => {
                if (typeof onCommentCountChange !== "undefined") {
                  onCommentCountChange(count);
                }
              }}
            />
          ) : undefined
        }
        items={[
          {
            label: intl.formatMessage({ id: "buttons.basic" }),
            key: "basic",
            children: (
              <div>
                <WbwDetailBasic
                  data={currWbwData}
                  onChange={(e: IWbwField) => {
                    console.log("WbwDetailBasic onchange", e);
                    fieldChanged(e.field, e.value);
                  }}
                  onRelationAdd={() => {
                    if (typeof onClose !== "undefined") {
                      onClose();
                    }
                  }}
                />
              </div>
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
        <Rate
          allowHalf
          defaultValue={data.confidence * 5}
          onChange={(value: number) => {
            fieldChanged("confidence", (value / 5).toString());
          }}
        />
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
          style={{ width: "unset" }}
          type="primary"
          menu={{
            items: [
              {
                key: "user-dict",
                label: intl.formatMessage({ id: "buttons.save.publish" }),
              },
            ],
            onClick: (e) => {
              if (typeof onSave !== "undefined") {
                //保存并发布
                onSave(currWbwData, true);
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
