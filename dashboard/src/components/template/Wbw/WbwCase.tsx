import { useIntl } from "react-intl";
import { Typography } from "antd";

import { IWbw } from "./WbwWord";
import "./wbw.css"; // 告诉 umi 编译这个 css

const { Text } = Typography;

interface IWidget {
  data: IWbw;
}

const Widget = ({ data }: IWidget) => {
  const intl = useIntl();
  console.log("case", data.case?.value);
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
        </div>
      </Text>
    </div>
  );
};

export default Widget;
