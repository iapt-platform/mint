import { useState } from "react";
import { useIntl } from "react-intl";
import { Dropdown, Tabs, Divider, Button } from "antd";
import type { MenuProps } from "antd";
import { SaveOutlined } from "@ant-design/icons";

import { IWbw, IWbwField } from "./WbwWord";
import WbwDetailBasic from "./WbwDetailBasic";
import WbwDetailBookMark from "./WbwDetailBookMark";
import WbwDetailNote from "./WbwDetailNote";
import WbwDetailAdvance from "./WbwDetailAdvance";

interface IWidget {
  data: IWbw;
  onClose?: Function;
  onChange?: Function;
  onSave?: Function;
}
const Widget = ({ data, onClose, onChange, onSave }: IWidget) => {
  const intl = useIntl();
  const [currWbwData, setCurrWbwData] = useState(data);
  const fieldChanged = (value: IWbwField) => {
    let mData = currWbwData;
    switch (value.field) {
      case "note":
        mData.note = { value: value.value, status: 5 };
        break;
      case "bookMarkColor":
        mData.bookMarkColor = { value: value.value, status: 5 };
        break;
      case "bookMarkText":
        mData.bookMarkText = { value: value.value, status: 5 };
        break;
      case "word":
        mData.word = { value: value.value, status: 5 };
        break;
      case "real":
        mData.real = { value: value.value, status: 5 };
        break;
      case "meaning":
        mData.meaning = { value: value.value.split("$"), status: 5 };
        break;
      case "factors":
        mData.factors = { value: value.value, status: 5 };
        break;
      case "factorMeaning":
        mData.factorMeaning = { value: value.value, status: 5 };
        break;
      case "parent":
        mData.parent = { value: value.value, status: 5 };
        break;
      case "case":
        mData.case = { value: value.value, status: 5 };
        break;
      default:
        break;
    }
    setCurrWbwData(mData);
  };
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
                    console.log(e);
                    fieldChanged(e);
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
                  fieldChanged(e);
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
                  fieldChanged(e);
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
                    fieldChanged(e);
                  }}
                />
              </div>
            ),
          },
        ]}
      />
      <Divider style={{ margin: "8px 0" }}></Divider>
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
