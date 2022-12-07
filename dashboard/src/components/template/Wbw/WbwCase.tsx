import { useIntl } from "react-intl";
import { Typography, Button } from "antd";
import { SwapOutlined } from "@ant-design/icons";

import { IWbw } from "./WbwWord";
import "./wbw.css";

const { Text } = Typography;

interface IWidget {
  data: IWbw;
  onSplit?: Function;
}
const Widget = ({ data, onSplit }: IWidget) => {
  const intl = useIntl();
  return (
    <div className="wbw_word_item" style={{ display: "flex" }}>
      <Text type="secondary">
        <div>
          {data.case?.value.map((item, id) => {
            return (
              <span key={id} className="case">
                {intl.formatMessage({ id: `dict.fields.type.${item}.label` })}
              </span>
            );
          })}
          <Button
            icon={<SwapOutlined />}
            onClick={() => {
              if (typeof onSplit !== "undefined") {
                onSplit();
              }
            }}
          />
        </div>
      </Text>
    </div>
  );
};

export default Widget;
