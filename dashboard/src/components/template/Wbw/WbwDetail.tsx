import { useEffect, useState } from "react";
import { useIntl } from "react-intl";
import { Dropdown, Tabs, Divider, Button, Switch, Rate } from "antd";
import type { MenuProps } from "antd";
import { SaveOutlined, CommentOutlined } from "@ant-design/icons";

import { IWbw, IWbwField, TFieldName } from "./WbwWord";
import WbwDetailBasic from "./WbwDetailBasic";
import WbwDetailBookMark from "./WbwDetailBookMark";
import WbwDetailNote from "./WbwDetailNote";
import WbwDetailAdvance from "./WbwDetailAdvance";
import { LockIcon, UnLockIcon } from "../../../assets/icon";
import { UploadFile } from "antd/es/upload/interface";
import { IAttachmentResponse } from "../../api/Attachments";
import WbwDetailAttachment from "./WbwDetailAttachment";
import CommentBox from "../../comment/CommentBox";

interface IWidget {
  data: IWbw;
  onClose?: Function;
  onSave?: Function;
  onCommentCountChange?: Function;
}
const WbwDetailWidget = ({
  data,
  onClose,
  onSave,
  onCommentCountChange,
}: IWidget) => {
  const intl = useIntl();
  const [currWbwData, setCurrWbwData] = useState(data);
  useEffect(() => {
    setCurrWbwData(data);
  }, [data]);
  function fieldChanged(field: TFieldName, value: string) {
    console.log("field", field, "value", value);
    let mData = currWbwData;
    switch (field) {
      case "note":
        mData.note = { value: value, status: 7 };
        break;
      case "bookMarkColor":
        mData.bookMarkColor = { value: parseInt(value), status: 7 };
        break;
      case "bookMarkText":
        mData.bookMarkText = { value: value, status: 7 };
        break;
      case "word":
        mData.word = { value: value, status: 7 };
        break;
      case "real":
        mData.real = { value: value, status: 7 };
        break;
      case "meaning":
        mData.meaning = { value: value, status: 7 };
        break;
      case "factors":
        mData.factors = { value: value, status: 7 };
        break;
      case "factorMeaning":
        mData.factorMeaning = { value: value, status: 7 };
        break;
      case "parent":
        mData.parent = { value: value, status: 7 };
        break;
      case "parent2":
        mData.parent2 = { value: value, status: 7 };
        break;
      case "grammar2":
        mData.grammar2 = { value: value, status: 7 };
        break;
      case "case":
        const arrCase = value.split("#");
        mData.case = { value: value, status: 7 };
        mData.type = { value: arrCase[0] ? arrCase[0] : "", status: 7 };
        mData.grammar = { value: arrCase[1] ? arrCase[1] : "", status: 7 };
        break;
      case "relation":
        mData.relation = { value: value, status: 7 };
        break;
      case "confidence":
        mData.confidence = parseFloat(value);
        break;
      case "locked":
        mData.locked = JSON.parse(value);
        break;
      default:
        break;
    }
    setCurrWbwData(mData);
  }
  const onMenuClick: MenuProps["onClick"] = (e) => {
    console.log("click", e);
  };

  const items = [
    {
      key: "user-dict",
      label: intl.formatMessage({ id: "buttons.save.publish" }),
    },
  ];
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
              trigger={<Button icon={<CommentOutlined />} type="text" />}
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
                  data={data}
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
                  onChange={(e: IWbwField) => {
                    fieldChanged(e.field, e.value);
                  }}
                  onUpload={(fileList: UploadFile<IAttachmentResponse>[]) => {
                    let mData = currWbwData;
                    mData.attachments = fileList.map((item) => {
                      return {
                        uid: item.uid,
                        name: item.name,
                        size: item.size,
                        type: item.type,
                        url: item.response?.data.url,
                      };
                    });
                    setCurrWbwData(mData);
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
          menu={{ items, onClick: onMenuClick }}
          onClick={() => {
            if (typeof onSave !== "undefined") {
              onSave(currWbwData);
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
