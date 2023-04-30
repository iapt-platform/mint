import { List, Space } from "antd";
import { useEffect, useState } from "react";
import { IWbw } from "./WbwWord";

interface IRelation {
  sour_id: string;
  sour_spell: string;
  dest_id: string;
  dest_spell: string;
  relation: string;
}
interface IWidget {
  data: IWbw;
  onChange?: Function;
}
const WbwParent2Widget = ({ data, onChange }: IWidget) => {
  const [relation, setRelation] = useState<IRelation[]>();
  useEffect(() => {
    if (typeof data.relation === "undefined") {
      return;
    }
    const arrRelation: IRelation[] = JSON.parse(data.relation?.value);
    setRelation(arrRelation);
  }, [data.relation]);

  return (
    <List
      itemLayout="vertical"
      size="small"
      dataSource={relation}
      renderItem={(item) => (
        <List.Item>
          <Space>
            {item.dest_spell}-{item.relation}
          </Space>
        </List.Item>
      )}
    />
  );
};

export default WbwParent2Widget;
