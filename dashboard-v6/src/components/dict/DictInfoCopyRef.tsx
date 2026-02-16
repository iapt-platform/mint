import { Button, message, Segmented, Typography } from "antd";
import { SegmentedValue } from "antd/lib/segmented";
import { useState } from "react";
import { CopyOutlined } from "@ant-design/icons";
import { IWordByDict } from "./WordCardByDict";
import { useIntl } from "react-intl";
const { Text } = Typography;

interface IWidget {
  data: IWordByDict;
}
const DictInfoCopyRef = ({ data }: IWidget) => {
  const apaStr = `${data.meta?.author}. (${data.meta?.published}). ${data.dictname}. ${data.meta?.publisher}.`;
  const mlaStr = `${data.meta?.author}. ${data.dictname}.  ${data.meta?.publisher}, ${data.meta?.published}.`;
  const [text, setText] = useState(apaStr);
  const intl = useIntl();

  return (
    <div>
      <div style={{ textAlign: "center", padding: 20 }}>
        <Segmented
          options={["APA", "MLA"]}
          onChange={(value: SegmentedValue) => {
            switch (value) {
              case "APA":
                setText(apaStr);
                break;
              case "MLA":
                setText(mlaStr);
                break;
              default:
                break;
            }
          }}
        />
      </div>
      <div>
        <Text>{text}</Text>
      </div>

      <div style={{ textAlign: "center", padding: 20 }}>
        <Button
          type="primary"
          style={{ width: 200 }}
          icon={<CopyOutlined />}
          onClick={() => {
            navigator.clipboard.writeText(text).then(() => {
              message.success("链接地址已经拷贝到剪贴板");
            });
          }}
        >
          {intl.formatMessage({
            id: "buttons.copy",
          })}
        </Button>
      </div>
    </div>
  );
};

export default DictInfoCopyRef;
