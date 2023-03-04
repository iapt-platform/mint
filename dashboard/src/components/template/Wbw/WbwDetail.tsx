import { useState } from "react";
import { useIntl } from "react-intl";
import { Dropdown, Tabs, Divider, Button, Switch, Rate } from "antd";
import type { MenuProps } from "antd";
import { SaveOutlined } from "@ant-design/icons";

import { IWbw, IWbwField, TFieldName } from "./WbwWord";
import WbwDetailBasic from "./WbwDetailBasic";
import WbwDetailBookMark from "./WbwDetailBookMark";
import WbwDetailNote from "./WbwDetailNote";
import WbwDetailAdvance from "./WbwDetailAdvance";
import { LockIcon, UnLockIcon } from "../../../assets/icon";
import { UploadFile } from "antd/es/upload/interface";
import { IAttachmentResponse } from "../../api/Attachments";
import WbwDetailAttachment from "./WbwDetailAttachment";

interface IWidget {
  data: IWbw;
  onClose?: Function;
  onSave?: Function;
}
const Widget = ({ data, onClose, onSave }: IWidget) => {
  const intl = useIntl();
  const [currWbwData, setCurrWbwData] = useState(data);
  function fieldChanged(field: TFieldName, value: string) {
    let mData = currWbwData;
    switch (field) {
      case "note":
        mData.note = { value: value, status: 5 };
        break;
      case "bookMarkColor":
        mData.bookMarkColor = { value: parseInt(value), status: 5 };
        break;
      case "bookMarkText":
        mData.bookMarkText = { value: value, status: 5 };
        break;
      case "word":
        mData.word = { value: value, status: 5 };
        break;
      case "real":
        mData.real = { value: value, status: 5 };
        break;
      case "meaning":
        mData.meaning = { value: value.split("$"), status: 5 };
        break;
      case "factors":
        mData.factors = { value: value, status: 5 };
        break;
      case "factorMeaning":
        mData.factorMeaning = { value: value, status: 5 };
        break;
      case "parent":
        mData.parent = { value: value, status: 5 };
        break;
      case "case":
        mData.case = { value: value.split("$"), status: 5 };
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
        items={[
          {
            label: `basic`,
            key: "basic",
            children: (
              <div>
                <WbwDetailBasic
                  data={data}
                  onChange={(e: IWbwField) => {
                    console.log("WbwDetailBasic onchange", e);
                    fieldChanged(e.field, e.value);
                  }}
                />
              </div>
            ),
          },
          {
            label: `bookmark`,
            key: "bookmark",
            children: (
              <WbwDetailBookMark
                data={data}
                onChange={(e: IWbwField) => {
                  fieldChanged(e.field, e.value);
                }}
              />
            ),
          },
          {
            label: `Note`,
            key: "note",
            children: (
              <WbwDetailNote
                data={data}
                onChange={(e: IWbwField) => {
                  fieldChanged(e.field, e.value);
                }}
              />
            ),
          },
          {
            label: `advance`,
            key: "advance",
            children: (
              <div>
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
            label: `attachments`,
            key: "attachments",
            children: (
              <div>
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
          defaultValue={5}
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
            console.log("data", currWbwData);
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

export default Widget;
