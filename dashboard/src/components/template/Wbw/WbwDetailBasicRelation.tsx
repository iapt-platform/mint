import { Badge, Button, Collapse, Space, Tooltip } from "antd";
import { QuestionCircleOutlined } from "@ant-design/icons";
import WbwDetailRelation from "./WbwDetailRelation";
import store from "../../../store";
import { grammar } from "../../../reducers/command";
import { IWbw, IWbwField } from "./WbwWord";
import { useIntl } from "react-intl";
import { useState } from "react";
import { openPanel } from "../../../reducers/right-panel";

interface IWidget {
  data: IWbw;
  showRelation?: boolean;
  onChange?: Function;
  onRelationAdd?: Function;
}
const WbwDetailBasicRelationWidget = ({
  data,
  showRelation,
  onChange,
  onRelationAdd,
}: IWidget) => {
  const intl = useIntl();
  const [fromList, setFromList] = useState<string[]>();

  const relationCount = data.relation?.value
    ? JSON.parse(data.relation.value).length
    : 0;
  return (
    <Collapse bordered={false} collapsible={"icon"}>
      <Collapse.Panel
        header={
          <div style={{ display: "flex", justifyContent: "space-between" }}>
            <Space>
              {intl.formatMessage({ id: "buttons.relate" })}
              <Badge color="geekblue" count={relationCount} />
            </Space>
            <Tooltip
              title={intl.formatMessage({
                id: "columns.library.palihandbook.title",
              })}
            >
              <Button
                type="link"
                onClick={() => {
                  if (fromList) {
                    const endCase = fromList
                      .map((item) => item + ".relations")
                      .join(",");
                    console.debug("from", fromList, endCase);
                    store.dispatch(grammar(endCase));
                    store.dispatch(openPanel("grammar"));
                  }
                }}
                icon={<QuestionCircleOutlined />}
              />
            </Tooltip>
          </div>
        }
        key="relation"
        style={{ display: showRelation ? "block" : "none" }}
      >
        <WbwDetailRelation
          data={data}
          onChange={(e: IWbwField) => {
            if (typeof onChange !== "undefined") {
              onChange(e);
            }
          }}
          onAdd={() => {
            if (typeof onRelationAdd !== "undefined") {
              onRelationAdd();
            }
          }}
          onFromList={(value: string[]) => setFromList(value)}
        />
      </Collapse.Panel>
    </Collapse>
  );
};

export default WbwDetailBasicRelationWidget;
