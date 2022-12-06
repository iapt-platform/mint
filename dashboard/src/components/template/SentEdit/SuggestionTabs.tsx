import { useState } from "react";
import { RadioChangeEvent, Space } from "antd";
import { Radio } from "antd";
import { ISentence } from "../SentEdit";
import { SuggestionIcon } from "../../../assets/icon";
import SuggestionAdd from "./SuggestionAdd";
import SuggestionList from "./SuggestionList";

interface IWidget {
  data: ISentence;
}
const Widget = ({ data }: IWidget) => {
  const [value, setValue] = useState("close");
  const [showPanel, setShowPanel] = useState("none");
  const [showSuggestion, setShowSuggestion] = useState("none");

  const onChange = ({ target: { value } }: RadioChangeEvent) => {
    console.log("radio1 checked", value);
    switch (value) {
      case "suggestion":
        setShowSuggestion("block");
        setShowPanel("block");
        break;
    }
    setValue(value);
  };
  const closeAll = () => {
    setShowPanel("none");
  };

  return (
    <div>
      <div>
        <Radio.Group
          size="small"
          optionType="button"
          buttonStyle="solid"
          onChange={onChange}
          value={value}
        >
          <Radio
            value="suggestion"
            onClick={() => {
              if (value === "suggestion") {
                setValue("close");
                closeAll();
              }
            }}
            style={{
              border: "none",
              backgroundColor: "wheat",
              borderRadius: 5,
            }}
          >
            <Space>
              <SuggestionIcon />
              {data.suggestionCount?.suggestion}
            </Space>
          </Radio>
          <Radio value="close" style={{ display: "none" }}></Radio>
        </Radio.Group>
      </div>
      <div style={{ display: showPanel }}>
        <div style={{ display: showSuggestion, paddingLeft: "1em" }}>
          <div>
            <SuggestionAdd data={data} />
          </div>
          <div>
            <SuggestionList {...data} />
          </div>
        </div>
      </div>
    </div>
  );
};

export default Widget;
